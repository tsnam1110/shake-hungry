#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
link_rot_check.py — Quét link rot (nguồn chết) cho kho dữ liệu Lắc Đói.

Trích toàn bộ URL nguồn trong data/ (recipe_sources của món, source_url của
nguyên liệu và của dòng giá hoa quả), kiểm tra trạng thái sống/chết theo quy
trình 2 lượt rút gọn rút ra từ AUDIT-002:

  Lượt 1: curl hàng loạt với nhịp lịch sự (giới hạn khoảng cách theo domain).
  Lượt 2: mọi URL không đạt 2xx/3xx ở lượt 1 được thử lại một lần nữa.

Phân loại (KỶ LUẬT AUDIT-002 — không bao giờ kết luận "chết" từ một lần curl):
  - song          : bất kỳ lượt nào trả HTTP 2xx/3xx (sau khi đã đi theo redirect).
  - chet          : CẢ HAI lượt đều 404/410, hoặc cả hai lượt đều lỗi phân giải DNS
                    (curl exit 6 — domain không còn tồn tại).
  - chua-ket-luan : mọi trường hợp còn lại (000, 403, 5xx, timeout, lỗi mạng,
                    kết quả hai lượt mâu thuẫn, hoặc domain nằm trong danh sách
                    chặn bot đã biết — xem BOT_BLOCK_DOMAINS).

So sánh với lần chạy trước (state.json cùng thư mục) và chỉ nêu THAY ĐỔI trong
báo cáo; đầu ra quan trọng nhất là các món Mức 2 bị rớt xuống dưới 3 nguồn sống
xác nhận vì link rot.

Chỉ dùng thư viện chuẩn Python 3 + lệnh curl của hệ thống. Không sửa dữ liệu.

Cách chạy:  python3 tools/link-rot-check/link_rot_check.py [--limit N] [--only-url URL] [--no-write]
Xem README.md cùng thư mục để biết cách vận hành định kỳ và cách đọc báo cáo.
"""

import argparse
import json
import random
import subprocess
import sys
import threading
import time
from concurrent.futures import ThreadPoolExecutor
from datetime import datetime, timezone, timedelta
from pathlib import Path
from urllib.parse import urlparse

HERE = Path(__file__).resolve().parent
REPO = HERE.parents[1]
DATA = REPO / "data"
STATE_PATH = HERE / "state.json"
REPORTS_DIR = HERE / "reports"

USER_AGENT = (
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 "
    "(KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36"
)

# Domain đã được AUDIT-002 chứng minh là chặn bot/curl (ĐMX trả 000 cho curl
# nhưng sống khi mở trình duyệt; cholaithieu/giavihanhphuc trả 404 giả cho bot;
# cookidoo trả 403 chống bot dù nội dung đã đọc được ở vòng nghiên cứu).
# Với các domain này, kết quả không-200 của curl KHÔNG bao giờ được dùng để
# kết luận chết — luôn xếp "chua-ket-luan" chờ mở trình duyệt kiểm chứng.
BOT_BLOCK_DOMAINS = {
    "www.dienmayxanh.com",
    "dienmayxanh.com",
    "cholaithieu.com",
    "www.cholaithieu.com",
    "giavihanhphuc.com",
    "www.giavihanhphuc.com",
    "cookidoo.com.vn",
    "www.cookidoo.com.vn",
}

# Khoảng cách tối thiểu giữa hai request liên tiếp tới cùng một domain (giây).
DOMAIN_INTERVAL = {
    "api.nal.usda.gov": 1.2,   # DEMO_KEY của USDA có giới hạn tốc độ riêng
    "cookpad.com": 0.9,
}
DEFAULT_INTERVAL = 0.7

STATUS_SONG = "song"
STATUS_CHET = "chet"
STATUS_KL = "chua-ket-luan"


# ---------------------------------------------------------------- trích xuất

def extract():
    """Trả về (url -> set(refs), dish_sources).

    dish_sources: slug -> {"level": int, "name": str, "urls": [url, ...]}
    refs có dạng "dish:<slug>", "ingredient:<slug>", "fruit:<slug>".
    """
    url_refs = {}
    dish_sources = {}

    def add(url, ref):
        if url and isinstance(url, str) and url.startswith(("http://", "https://")):
            url_refs.setdefault(url.strip(), set()).add(ref)

    for path in sorted((DATA / "dishes" / "vn").glob("*.json")):
        dish = json.loads(path.read_text(encoding="utf-8"))
        slug = dish.get("slug") or path.stem
        urls = []
        for src in dish.get("recipe_sources") or []:
            u = (src or {}).get("url")
            if u:
                add(u, f"dish:{slug}")
                urls.append(u.strip())
        name = ""
        for n in dish.get("names") or []:
            if n.get("locale") == "vi" and n.get("is_primary"):
                name = n.get("name", "")
                break
        dish_sources[slug] = {
            "level": dish.get("completeness_level") or 1,
            "name": name,
            "urls": urls,
        }

    for path in sorted((DATA / "ingredients").glob("*.json")):
        if path.name.startswith("_"):
            continue
        ing = json.loads(path.read_text(encoding="utf-8"))
        slug = ing.get("slug") or path.stem
        ref_food = ing.get("reference_food") or {}
        add(ref_food.get("source_url"), f"ingredient:{slug}")
        for src in ing.get("secondary_sources") or []:
            add((src or {}).get("source_url"), f"ingredient:{slug}")

    for path in sorted((DATA / "fruits").glob("*.json")):
        if path.name.startswith("_"):
            continue
        fruit = json.loads(path.read_text(encoding="utf-8"))
        slug = fruit.get("slug") or path.stem
        for price in fruit.get("prices") or []:
            add((price or {}).get("source_url"), f"fruit:{slug}")

    return url_refs, dish_sources


# ---------------------------------------------------------------- kiểm tra

class DomainPacer:
    """Đảm bảo khoảng cách tối thiểu giữa các request tới cùng một domain."""

    def __init__(self):
        self._locks = {}
        self._last = {}
        self._guard = threading.Lock()

    def wait(self, domain):
        with self._guard:
            lock = self._locks.setdefault(domain, threading.Lock())
        with lock:
            interval = DOMAIN_INTERVAL.get(domain, DEFAULT_INTERVAL)
            now = time.monotonic()
            earliest = self._last.get(domain, 0.0) + interval
            if earliest > now:
                time.sleep(earliest - now)
            self._last[domain] = time.monotonic()


def fetch(url, pacer):
    """Một lần curl. Trả {"code": int, "exit": int, "final": str}."""
    domain = urlparse(url).netloc
    pacer.wait(domain)
    time.sleep(random.uniform(0.05, 0.25))
    try:
        proc = subprocess.run(
            [
                "curl", "-s", "-L", "--max-redirs", "5",
                "--connect-timeout", "10", "--max-time", "30",
                "-A", USER_AGENT,
                "-H", "Accept: text/html,application/xhtml+xml,application/pdf,*/*",
                "-o", "/dev/null",
                "-w", "%{http_code}\n%{url_effective}",
                url,
            ],
            capture_output=True, text=True, timeout=45,
        )
        lines = (proc.stdout or "0\n").splitlines()
        try:
            code = int(lines[0])
        except (ValueError, IndexError):
            code = 0
        final = lines[1] if len(lines) > 1 else ""
        return {"code": code, "exit": proc.returncode, "final": final}
    except (subprocess.TimeoutExpired, OSError):
        return {"code": 0, "exit": -1, "final": ""}


def is_ok(result):
    return result is not None and 200 <= result["code"] < 400


def classify(pass1, pass2, domain):
    """Phân loại theo kỷ luật 2 lượt. pass2 có thể là None nếu pass1 đã sống."""
    if is_ok(pass1) or is_ok(pass2):
        return STATUS_SONG
    if domain in BOT_BLOCK_DOMAINS:
        return STATUS_KL
    results = [r for r in (pass1, pass2) if r is not None]
    if len(results) == 2:
        if all(r["code"] in (404, 410) for r in results):
            return STATUS_CHET
        if all(r["exit"] == 6 for r in results):  # curl: couldn't resolve host
            return STATUS_CHET
    return STATUS_KL


def check_all(urls, workers):
    """Chạy lượt 1 cho mọi URL, lượt 2 cho URL chưa sống. Trả url -> record."""
    pacer = DomainPacer()
    results = {}

    def run_one(url):
        return url, fetch(url, pacer)

    with ThreadPoolExecutor(max_workers=workers) as pool:
        for url, res in pool.map(run_one, urls):
            results[url] = {"pass1": res, "pass2": None}

    retry = [u for u in urls if not is_ok(results[u]["pass1"])]
    random.shuffle(retry)
    with ThreadPoolExecutor(max_workers=workers) as pool:
        for url, res in pool.map(run_one, retry):
            results[url]["pass2"] = res

    out = {}
    for url in urls:
        r = results[url]
        domain = urlparse(url).netloc
        out[url] = {
            "status": classify(r["pass1"], r["pass2"], domain),
            "codes": [r["pass1"]["code"], r["pass2"]["code"] if r["pass2"] else None],
            "exits": [r["pass1"]["exit"], r["pass2"]["exit"] if r["pass2"] else None],
        }
    return out


# ---------------------------------------------------------------- trạng thái

def load_state():
    if STATE_PATH.exists():
        try:
            return json.loads(STATE_PATH.read_text(encoding="utf-8"))
        except (ValueError, OSError):
            return None
    return None


def dish_gate(dish_sources, statuses):
    """Món Mức 2 nào còn dưới 3 nguồn sống xác nhận.

    Trả (rot_cong_chac_chan, nguy_co): chắc chắn = sống + chưa-kết-luận < 3;
    nguy cơ = sống < 3 nhưng cộng chưa-kết-luận thì đủ.
    """
    certain, at_risk = [], []
    for slug, info in sorted(dish_sources.items()):
        if info["level"] != 2:
            continue
        alive = sum(1 for u in info["urls"] if statuses.get(u) == STATUS_SONG)
        dead = sum(1 for u in info["urls"] if statuses.get(u) == STATUS_CHET)
        unknown = len(info["urls"]) - alive - dead
        row = (slug, info["name"], alive, unknown, dead, len(info["urls"]))
        if alive + unknown < 3:
            certain.append(row)
        elif alive < 3:
            at_risk.append(row)
    return certain, at_risk


# ---------------------------------------------------------------- báo cáo

def build_report(now, totals, n_refs, prev, transitions, certain, at_risk,
                 dead_urls, unknown_by_domain, elapsed_min, is_baseline):
    recovered, new_dead, new_unknown, still_dead = transitions
    lines = []
    lines.append(f"# Báo cáo quét link nguồn — {now.strftime('%Y-%m-%d %H:%M')}")
    lines.append("")
    lines.append(f"- Tổng URL duy nhất đã quét: **{totals['urls']}** ({n_refs} lượt tham chiếu trong `data/`)")
    lines.append(f"- Sống: **{totals[STATUS_SONG]}** · Chết xác nhận: **{totals[STATUS_CHET]}** · "
                 f"Chưa kết luận: **{totals[STATUS_KL]}**")
    lines.append(f"- Thời lượng quét: ~{elapsed_min:.0f} phút (2 lượt, nhịp theo domain — xem README)")
    if is_baseline:
        lines.append("- Đây là **lần chạy đầu tiên (baseline)** — chưa có lần trước để so sánh thay đổi.")
    lines.append("")
    lines.append("## Thay đổi so với lần chạy trước")
    lines.append("")
    if is_baseline:
        lines.append("(baseline — mọi trạng thái dưới đây là hiện trạng ban đầu của cơ chế)")
    else:
        if not (recovered or new_dead or new_unknown):
            lines.append("Không có thay đổi trạng thái nào.")
        if new_dead:
            lines.append(f"### 🔴 Mới chết xác nhận ({len(new_dead)})")
            lines.append("")
            for url, refs in new_dead:
                lines.append(f"- {url} — {', '.join(refs[:4])}")
            lines.append("")
        if recovered:
            lines.append(f"### ✅ Sống lại ({len(recovered)})")
            lines.append("")
            for url, refs in recovered:
                lines.append(f"- {url} — {', '.join(refs[:4])}")
            lines.append("")
        if new_unknown:
            lines.append(f"### ❔ Mới rơi vào chưa kết luận ({len(new_unknown)})")
            lines.append("")
            for url, refs in new_unknown:
                lines.append(f"- {url} — {', '.join(refs[:4])}")
            lines.append("")
        if still_dead:
            lines.append(f"(Còn {len(still_dead)} URL giữ trạng thái chết xác nhận từ lần trước — danh sách đầy đủ ở mục dưới.)")
    lines.append("")
    lines.append("## ⚠️ Món Mức 2 dưới 3 nguồn sống xác nhận")
    lines.append("")
    if certain:
        lines.append("### Rớt cổng chắc chắn (sống + chưa kết luận < 3) — cần thay nguồn hoặc hạ mức")
        lines.append("")
        lines.append("| Món | Sống | Chưa kết luận | Chết | Tổng nguồn |")
        lines.append("|---|---|---|---|---|")
        for slug, name, alive, unknown, dead, total in certain:
            lines.append(f"| `{slug}` {name} | {alive} | {unknown} | {dead} | {total} |")
        lines.append("")
    else:
        lines.append("Không có món Mức 2 nào rớt cổng chắc chắn.")
        lines.append("")
    if at_risk:
        lines.append("### Nguy cơ (sống < 3, nhưng đủ nếu các URL chưa kết luận hoá ra còn sống — cần mở trình duyệt kiểm chứng)")
        lines.append("")
        lines.append("| Món | Sống | Chưa kết luận | Chết | Tổng nguồn |")
        lines.append("|---|---|---|---|---|")
        for slug, name, alive, unknown, dead, total in at_risk:
            lines.append(f"| `{slug}` {name} | {alive} | {unknown} | {dead} | {total} |")
        lines.append("")
    lines.append("## URL chết xác nhận (cả hai lượt)")
    lines.append("")
    if dead_urls:
        for url, refs in dead_urls:
            lines.append(f"- {url} — {', '.join(refs[:6])}")
    else:
        lines.append("Không có.")
    lines.append("")
    lines.append("## Chưa kết luận — tóm tắt theo domain")
    lines.append("")
    lines.append("Trạng thái này KHÔNG có nghĩa là chết (lỗi mạng, 403/5xx, hoặc domain chặn bot như "
                 "dienmayxanh.com/cholaithieu.com).Muốn kết luận phải mở bằng trình duyệt như lượt 2 của AUDIT-002.")
    lines.append("")
    if unknown_by_domain:
        lines.append("| Domain | Số URL |")
        lines.append("|---|---|")
        for domain, count in sorted(unknown_by_domain.items(), key=lambda kv: -kv[1]):
            lines.append(f"| {domain} | {count} |")
    else:
        lines.append("Không có.")
    lines.append("")
    lines.append("---")
    lines.append("Sinh bởi `tools/link-rot-check/link_rot_check.py`. Script chỉ báo cáo — không sửa dữ liệu; "
                 "việc thay nguồn/hạ mức đi qua quy trình phân xử riêng (tiền lệ AUDIT-FIX).")
    lines.append("")
    return "\n".join(lines)


# ---------------------------------------------------------------- main

def main():
    parser = argparse.ArgumentParser(description="Quét link rot cho kho Lắc Đói")
    parser.add_argument("--limit", type=int, default=None,
                        help="chỉ quét N URL đầu tiên (để thử)")
    parser.add_argument("--only-url", default=None,
                        help="chỉ kiểm tra một URL, in kết quả, không ghi state/báo cáo")
    parser.add_argument("--no-write", action="store_true",
                        help="không ghi state.json và báo cáo")
    parser.add_argument("--workers", type=int, default=5)
    args = parser.parse_args()

    tz = timezone(timedelta(hours=7))
    now = datetime.now(tz)

    if args.only_url:
        pacer = DomainPacer()
        p1 = fetch(args.only_url, pacer)
        p2 = None if is_ok(p1) else fetch(args.only_url, pacer)
        status = classify(p1, p2, urlparse(args.only_url).netloc)
        print(json.dumps({
            "url": args.only_url,
            "pass1": p1, "pass2": p2, "status": status,
        }, ensure_ascii=False, indent=2))
        return 0

    started = time.monotonic()
    url_refs, dish_sources = extract()
    urls = sorted(url_refs)
    if args.limit:
        urls = urls[: args.limit]
    n_refs = sum(len(url_refs[u]) for u in urls)

    prev_state = load_state()
    prev_urls = (prev_state or {}).get("urls", {})
    is_baseline = prev_state is None

    print(f"[link-rot] {len(urls)} URL duy nhất, {n_refs} lượt tham chiếu — bắt đầu quét 2 lượt…",
          file=sys.stderr)
    results = check_all(urls, args.workers)
    elapsed_min = (time.monotonic() - started) / 60.0

    statuses = {u: results[u]["status"] for u in urls}
    totals = {
        "urls": len(urls),
        STATUS_SONG: sum(1 for s in statuses.values() if s == STATUS_SONG),
        STATUS_CHET: sum(1 for s in statuses.values() if s == STATUS_CHET),
        STATUS_KL: sum(1 for s in statuses.values() if s == STATUS_KL),
    }

    # chuyển trạng thái so với lần trước
    recovered, new_dead, new_unknown, still_dead = [], [], [], []
    for url in urls:
        prev_status = prev_urls.get(url, {}).get("status")
        cur = statuses[url]
        refs = sorted(url_refs[url])
        if prev_status == STATUS_CHET and cur == STATUS_SONG:
            recovered.append((url, refs))
        elif cur == STATUS_CHET and prev_status != STATUS_CHET:
            new_dead.append((url, refs))
        elif cur == STATUS_KL and prev_status == STATUS_SONG:
            new_unknown.append((url, refs))
        elif cur == STATUS_CHET and prev_status == STATUS_CHET:
            still_dead.append((url, refs))

    certain, at_risk = dish_gate(dish_sources, statuses)
    dead_urls = [(u, sorted(url_refs[u])) for u in urls if statuses[u] == STATUS_CHET]
    unknown_by_domain = {}
    for u in urls:
        if statuses[u] == STATUS_KL:
            d = urlparse(u).netloc
            unknown_by_domain[d] = unknown_by_domain.get(d, 0) + 1

    if not args.no_write:
        # ghi state.json (giữ first_dead của các URL đã chết từ lần trước)
        state_urls = {}
        for url in urls:
            prev_rec = prev_urls.get(url, {})
            first_dead = prev_rec.get("first_dead")
            if statuses[url] == STATUS_CHET and not first_dead:
                first_dead = now.strftime("%Y-%m-%d")
            if statuses[url] != STATUS_CHET:
                first_dead = None
            refs = sorted(url_refs[url])
            state_urls[url] = {
                "status": statuses[url],
                "codes": results[url]["codes"],
                "first_dead": first_dead,
                "last_checked": now.strftime("%Y-%m-%d"),
                "ref_count": len(refs),
                "refs": refs[:20],
            }
        state = {
            "version": 1,
            "updated_at": now.isoformat(timespec="seconds"),
            "totals": totals,
            "urls": state_urls,
        }
        tmp = STATE_PATH.with_suffix(".json.tmp")
        tmp.write_text(json.dumps(state, ensure_ascii=False, indent=1), encoding="utf-8")
        tmp.replace(STATE_PATH)

        REPORTS_DIR.mkdir(exist_ok=True)
        report_name = now.strftime("%Y-%m-%d") + ".md"
        report_path = REPORTS_DIR / report_name
        if report_path.exists():
            report_path = REPORTS_DIR / (now.strftime("%Y-%m-%d-%H%M") + ".md")
        report_path.write_text(
            build_report(now, totals, n_refs, prev_state,
                         (recovered, new_dead, new_unknown, still_dead),
                         certain, at_risk, dead_urls, unknown_by_domain,
                         elapsed_min, is_baseline),
            encoding="utf-8",
        )
        print(f"[link-rot] state: {STATE_PATH.relative_to(REPO)} · báo cáo: {report_path.relative_to(REPO)}",
              file=sys.stderr)

    # tóm tắt stdout
    print(f"Tổng {totals['urls']} URL — sống {totals[STATUS_SONG]} · "
          f"chết xác nhận {totals[STATUS_CHET]} · chưa kết luận {totals[STATUS_KL]}")
    if is_baseline:
        print("Lần chạy baseline (chưa có lần trước để so sánh).")
    else:
        print(f"Thay đổi: mới chết {len(new_dead)} · sống lại {len(recovered)} · "
              f"mới chưa kết luận {len(new_unknown)}")
        for url, _refs in new_dead:
            print(f"  🔴 MỚI CHẾT: {url}")
    print(f"Món Mức 2 rớt cổng chắc chắn: {len(certain)} · nguy cơ: {len(at_risk)}")
    for slug, name, alive, unknown, dead, total in certain:
        print(f"  🔴 {slug} ({name}): sống {alive}/{total}")
    return 0


if __name__ == "__main__":
    sys.exit(main())
