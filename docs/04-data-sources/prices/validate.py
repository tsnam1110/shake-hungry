#!/usr/bin/env python3
"""Kiem tra file gia tuan trong kho docs/04-data-sources/prices/weekly/.

Chi kiem tra hinh thuc va toan hoc theo SCHEMA.md: ten file khop tuan ISO,
ngay thu thap nam trong tuan do, truong bat buoc, trung dong, cong thuc
quy doi don vi va goi, va khong duoc ghi gia/kg cho hang ban theo cai,
qua, con, hop, mo khi chua co khoi luong. Script KHONG kiem tra duoc gia
co dung voi trang ban hay khong.

Cach dung, chay tu goc repo:
    python3 docs/04-data-sources/prices/validate.py \
        docs/04-data-sources/prices/weekly/YYYY-Www.json

Thoat 0 khi dat, thoat 1 kem thong bao loi dau tien gap duoc khi khong dat.
"""

import json
import re
import sys
from datetime import date
from pathlib import Path

ALLOWED_PACK_UNITS = {
    "g", "kg", "ml", "l", "qua", "cai", "con", "hop", "goi", "tui",
    "thung", "loc", "vi", "mo", "bo", "chai", "lon", "bich", "khay",
}
ALLOWED_MISSING_REASONS = {
    "khong-tim-thay-trang",
    "trang-khong-tai-duoc",
    "khong-co-co-goi",
    "chi-co-o-kenh-khac",
    "can-khao-sat-truc-tiep",
    "bi-chan-truy-cap",
    "het-hang",
}
COUNT_UNITS = {
    "qua", "cai", "con", "hop", "goi", "tui", "vi", "mo", "bo",
    "chai", "lon", "bich", "khay",
}
WEEK_FILENAME_RE = re.compile(r"^(\d{4})-W(\d{2})\.json$")
LEGACY_DATE_FILENAME_RE = re.compile(r"^\d{4}-\d{2}-\d{2}\.json$")
COLLECTED_DATE_RE = re.compile(r"^(\d{4})-(\d{2})-(\d{2})$")
SUB_SIZE_RE = re.compile(r"^(\d+(?:\.\d+)?)\s*(g|kg|ml|l)$")


def fail(message):
    print(f"LOI: {message}", file=sys.stderr)
    raise SystemExit(1)


def is_int(value):
    return isinstance(value, int) and not isinstance(value, bool)


def is_number(value):
    return (isinstance(value, (int, float)) and not isinstance(value, bool)
            and value > 0)


def round_vnd(value):
    return int(round(value))


def check_derived(entry, field, expected):
    """Kiem tra mot so suy ra neu co, bao loi neu lech khoi cong thuc."""
    value = entry.get(field)
    if value is None:
        return
    if not is_int(value):
        fail(f"{entry.get('product_key')}: {field} phai la so nguyen dong, "
             f"nhan duoc {value!r}.")
    if value != expected:
        fail(f"{entry.get('product_key')}: {field} sai cong thuc. "
             f"Ghi {value}, tinh dung la {expected} tu gia niem yet "
             f"{entry.get('listed_price_vnd')} va co goi da ghi.")


def require_derived(entry, field, expected):
    value = entry.get(field)
    if value is None:
        fail(f"{entry.get('product_key')}: thieu {field}. Theo SCHEMA thi "
             f"truong hop nay tinh duoc {expected}, khong duoc de trong.")
    check_derived(entry, field, expected)


def parse_sub_size(raw):
    if not isinstance(raw, str):
        return None
    match = SUB_SIZE_RE.match(raw.strip().lower())
    if not match:
        return None
    amount = float(match.group(1))
    unit = match.group(2)
    if unit == "kg":
        return amount * 1000.0, "g"
    if unit == "l":
        return amount * 1000.0, "ml"
    return amount, unit


def find_repo_root(start):
    current = start.resolve()
    for parent in [current, *current.parents]:
        if (parent / "data" / "ingredients").is_dir():
            return parent
    return None


def main(argv):
    if len(argv) != 2:
        print(__doc__)
        return 2
    path = Path(argv[1])
    if path.name == "TEMPLATE.json":
        fail("TEMPLATE.json la file mau, khong phai file tuan. "
             "Hay tao file weekly/YYYY-Www.json moi.")
    if LEGACY_DATE_FILENAME_RE.match(path.name):
        fail("Kho gia da doi sang chu ky tuan. File ngay dang "
             "YYYY-MM-DD.json khong con hop le, hay dung file tuan "
             "dang YYYY-Www.json.")
    match = WEEK_FILENAME_RE.match(path.name)
    if not match:
        fail(f"Ten file phai la YYYY-Www.json, nhan duoc {path.name}.")
    expected_week = f"{match.group(1)}-W{match.group(2)}"
    try:
        data = json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError) as exc:
        fail(f"Khong doc duoc file JSON: {exc}.")

    if not isinstance(data, dict):
        fail("File tuan phai la mot doi tuong JSON.")
    meta = data.get("meta")
    entries = data.get("entries")
    missing = data.get("missing")
    if not isinstance(meta, dict):
        fail("Thieu phan meta.")
    if not isinstance(entries, list):
        fail("Thieu phan entries, phai la mot danh sach.")
    if not isinstance(missing, list):
        fail("Thieu phan missing, phai la mot danh sach.")

    collected_raw = meta.get("collected_date")
    collected_match = COLLECTED_DATE_RE.match(str(collected_raw or ""))
    if not collected_match:
        fail(f"meta.collected_date khong dung dang YYYY-MM-DD: "
             f"{collected_raw!r}.")
    try:
        collected = date(int(collected_match.group(1)),
                         int(collected_match.group(2)),
                         int(collected_match.group(3)))
    except ValueError:
        fail(f"meta.collected_date khong phai ngay hop le: "
             f"{collected_raw!r}.")
    iso = collected.isocalendar()
    actual_week = f"{iso.year}-W{iso.week:02d}"
    if actual_week != expected_week:
        fail(f"meta.collected_date {collected.isoformat()} thuoc tuan "
             f"{actual_week}, khac tuan trong ten file ({expected_week}).")
    if meta.get("week_iso") != expected_week:
        fail(f"meta.week_iso ({meta.get('week_iso')}) khac tuan trong ten "
             f"file ({expected_week}).")
    if not is_int(meta.get("scope_total")) or meta["scope_total"] <= 0:
        fail("meta.scope_total phai la so nguyen duong.")
    if not meta.get("channels_attempted"):
        fail("meta.channels_attempted khong duoc de trong.")

    seen_variants = set()
    covered_keys = set()
    entry_channels = set()
    for entry in entries:
        if not isinstance(entry, dict):
            fail("Moi dong trong entries phai la mot doi tuong.")
        for field in ("product_key", "product_name_as_listed", "channel",
                      "region", "collected_date", "source_name",
                      "listed_price_vnd", "pack_quantity", "pack_unit"):
            if entry.get(field) in (None, ""):
                fail(f"Thieu truong bat buoc {field} trong dong "
                     f"{entry.get('product_key')!r}.")
        if entry.get("collected_date") != collected.isoformat():
            fail(f"Dong {entry['product_key']} co collected_date "
                 f"{entry.get('collected_date')}, khac "
                 f"meta.collected_date ({collected.isoformat()}).")
        if not entry.get("source_url") and not entry.get("source_note"):
            fail(f"Dong {entry['product_key']} thieu ca source_url va "
                 f"source_note.")
        if not is_int(entry["listed_price_vnd"]) \
                or entry["listed_price_vnd"] <= 0:
            fail(f"Dong {entry['product_key']} co listed_price_vnd khong "
                 f"phai so nguyen dong duong.")
        if not is_number(entry["pack_quantity"]):
            fail(f"Dong {entry['product_key']} co pack_quantity khong "
                 f"phai so duong.")
        if entry["pack_unit"] not in ALLOWED_PACK_UNITS:
            fail(f"Dong {entry['product_key']} co pack_unit khong hop le: "
                 f"{entry['pack_unit']!r}.")
        variant = entry.get("variant_key") or (
            f"{entry['product_name_as_listed']}|{entry['pack_quantity']}|"
            f"{entry['pack_unit']}")
        variant_id = (entry["product_key"], entry["channel"], variant)
        if variant_id in seen_variants:
            fail(f"Trung dong: {variant_id}. Hai bien the khac nhau phai "
                 f"co variant_key rieng, khong gop chung.")
        seen_variants.add(variant_id)
        covered_keys.add(entry["product_key"])
        entry_channels.add((entry["product_key"], entry["channel"]))

        listed = entry["listed_price_vnd"]
        qty = entry["pack_quantity"]
        unit = entry["pack_unit"]
        weight = entry.get("pack_weight_g")

        # Gia/kg tu khoi luong tong da ghi ro, ap dung moi don vi ban.
        if weight is not None:
            if not is_number(weight):
                fail(f"Dong {entry['product_key']} co pack_weight_g khong "
                     f"phai so duong.")
            require_derived(entry, "price_per_kg_vnd",
                            round_vnd(listed / weight * 1000))
            continue

        if unit == "g":
            require_derived(entry, "price_per_kg_vnd",
                            round_vnd(listed / qty * 1000))
            check_derived(entry, "price_per_100g_vnd",
                          round_vnd(listed / qty * 100))
        elif unit == "kg":
            require_derived(entry, "price_per_kg_vnd",
                            round_vnd(listed / qty))
        elif unit == "ml":
            require_derived(entry, "price_per_litre_vnd",
                            round_vnd(listed / qty * 1000))
            if entry.get("price_per_kg_vnd") is not None and \
                    "density" not in str(entry.get("pack_size_note", "")).lower():
                fail(f"Dong {entry['product_key']}: gia/kg tu hang ban theo "
                     f"ml chi duoc tinh khi co density_g_per_ml kem nguon "
                     f"cua dung mat hang, ghi trong pack_size_note.")
        elif unit == "l":
            require_derived(entry, "price_per_litre_vnd",
                            round_vnd(listed / qty))
            if entry.get("price_per_kg_vnd") is not None and \
                    "density" not in str(entry.get("pack_size_note", "")).lower():
                fail(f"Dong {entry['product_key']}: gia/kg tu hang ban theo "
                     f"lit chi duoc tinh khi co density_g_per_ml kem nguon "
                     f"cua dung mat hang, ghi trong pack_size_note.")
        elif unit in ("thung", "loc"):
            sub = parse_sub_size(entry.get("sub_unit_size"))
            if sub is None:
                if entry.get("price_per_kg_vnd") is not None or \
                        entry.get("price_per_litre_vnd") is not None:
                    fail(f"Dong {entry['product_key']}: thung hoac loc chua "
                         f"biet co don vi con thi khong duoc ghi gia/kg "
                         f"hoac gia/lit.")
            else:
                sub_amount, sub_unit = sub
                if sub_unit == "g":
                    total_g = qty * sub_amount
                    require_derived(entry, "price_per_kg_vnd",
                                    round_vnd(listed / total_g * 1000))
                else:
                    total_ml = qty * sub_amount
                    require_derived(entry, "price_per_litre_vnd",
                                    round_vnd(listed / total_ml * 1000))
        elif unit in COUNT_UNITS:
            if entry.get("price_per_unit_vnd") is not None:
                check_derived(entry, "price_per_unit_vnd",
                              round_vnd(listed / qty))
            if entry.get("price_per_kg_vnd") is not None or \
                    entry.get("price_per_litre_vnd") is not None:
                fail(f"Dong {entry['product_key']}: hang ban theo "
                     f"{unit} ma khong co khoi luong thi khong duoc ghi "
                     f"gia/kg hoac gia/lit.")
        else:  # pragma: no cover - pack_unit da duoc kiem tra o tren.
            fail(f"Don vi chua co cong thuc: {unit}.")

        printed = entry.get("printed_price_per_kg_vnd")
        if printed is not None:
            if not is_int(printed) or printed <= 0:
                fail(f"Dong {entry['product_key']} co "
                     f"printed_price_per_kg_vnd khong phai so nguyen duong.")
            derived = entry.get("price_per_kg_vnd")
            if derived is not None and printed != derived \
                    and entry.get("price_discrepancy") is not True:
                fail(f"Dong {entry['product_key']}: gia/kg in san "
                     f"{printed} khac so tu tinh {derived} nhung chua dat "
                     f"price_discrepancy la true.")
            if derived is not None and printed == derived \
                    and entry.get("price_discrepancy") is True:
                fail(f"Dong {entry['product_key']}: gia/kg in san bang so "
                     f"tu tinh nhung lai dat co lech.")

    missing_global = set()
    for item in missing:
        if not isinstance(item, dict):
            fail("Moi dong trong missing phai la mot doi tuong.")
        if not item.get("product_key"):
            fail("Dong trong missing thieu product_key.")
        if item.get("reason") not in ALLOWED_MISSING_REASONS:
            fail(f"Missing {item.get('product_key')} co reason khong hop "
                 f"le: {item.get('reason')!r}.")
        channel = item.get("channel")
        if channel:
            if (item["product_key"], channel) in entry_channels:
                fail(f"Missing {item['product_key']} kenh {channel} lai "
                     f"xuat hien trong entries.")
        else:
            if item["product_key"] in covered_keys:
                fail(f"Missing {item['product_key']} lai xuat hien trong "
                     f"entries.")
            missing_global.add(item["product_key"])

    repo_root = find_repo_root(Path(__file__).parent)
    if repo_root is not None:
        slugs = set()
        for ingredient_file in (repo_root / "data" / "ingredients").glob("*.json"):
            if ingredient_file.name == "_index.json":
                continue
            try:
                slugs.add(json.loads(
                    ingredient_file.read_text(encoding="utf-8"))["slug"])
            except (OSError, KeyError, json.JSONDecodeError):
                continue
        unknown = covered_keys - slugs
        if unknown:
            fail(f"Co product_key khong nam trong kho nguyen lieu: "
                 f"{sorted(unknown)[:10]}.")
        uncovered = slugs - covered_keys - missing_global
        if uncovered:
            fail(f"Con {len(uncovered)} khoa chua co trong entries lan "
                 f"missing: {sorted(uncovered)[:10]}. Moi khoa trong pham "
                 f"vi phai xuat hien o entries hoac o missing.")
        if meta.get("scope_total") != len(slugs):
            fail(f"meta.scope_total ({meta.get('scope_total')}) khac so "
                 f"khoa nguyen lieu thuc te ({len(slugs)}).")

    print(f"DAT: {len(entries)} dong gia, {len(covered_keys)} khoa da thu, "
          f"{len(missing)} dong missing, tuan {expected_week}, ngay thu "
          f"thap {collected.isoformat()}.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main(sys.argv))
