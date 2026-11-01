# Link-rot check — quét định kỳ nguồn chết cho kho dữ liệu Lắc Đói

Cơ chế cảnh báo sớm chống **link rot**: nguồn công thức/dinh dưỡng đã ghi trong
`data/` chết dần theo thời gian (AUDIT-002 phát hiện 16 món Mức 2 rớt cổng
"≥3 nguồn sống" chỉ vì link chết; AUDIT-FIX đã phải thay nguồn hàng loạt).
Script này quét lại toàn bộ URL nguồn theo định kỳ, so sánh với lần chạy trước
và chỉ nêu **thay đổi** — đầu ra quan trọng nhất là danh sách món Mức 2 bị rớt
xuống dưới 3 nguồn sống xác nhận.

Script **chỉ báo cáo, không bao giờ sửa dữ liệu**. Thay nguồn/hạ mức vẫn đi qua
quy trình phân xử riêng (tiền lệ AUDIT-FIX).

## Vì sao đặt ở `tools/` gốc repo

- Không phải code của ứng dụng Laravel (không chạy qua `artisan`, không phụ
  thuộc framework/DB) — là công cụ vận hành độc lập chạy bằng Python 3 + curl.
- `state.json` và `reports/` phải được **version trong repo** (tinh thần QT-3:
  mọi thứ nằm trong repo) để lần chạy sau — kể cả trên máy khác — so sánh được
  với lần chạy trước. Đặt cạnh script là nơi tự nhiên nhất.

## Phạm vi quét

Toàn bộ URL nguồn trong `data/`, trích bằng cách đọc JSON có cấu trúc:

| Khu vực | Trường | Lượt ghi trong file |
|---|---|---|
| `data/dishes/vn/*.json` | `recipe_sources[].url` | 1.511 |
| `data/ingredients/*.json` | `reference_food.source_url` + `secondary_sources[].source_url` | 383 |
| `data/fruits/*.json` | `prices[].source_url` | 1.387 |

Tổng 3.281 lượt ghi → 2.099 cặp (URL, thực thể tham chiếu) → **1.994 URL duy
nhất** tại thời điểm baseline 2026-11-01 (số liệu tự tính lại mỗi lần chạy).

## Cách chạy

Chạy một lần (đây là lệnh chính xác để đăng ký vào cron của Muse — mỗi lần
cron chạy, agent chỉ cần chạy lệnh này rồi commit `state.json` + báo cáo mới):

```bash
python3 ~/workspace/projects/shake-hungry/tools/link-rot-check/link_rot_check.py
```

Tuỳ chọn:

- `--only-url <URL>`: kiểm tra một URL, in JSON kết quả (không ghi gì).
- `--limit N`: chỉ quét N URL đầu (thử nhanh).
- `--no-write`: chạy nhưng không ghi `state.json`/báo cáo.
- `--workers N`: số luồng song song (mặc định 5 — giữ thấp để lịch sự).

Đầu ra: tóm tắt ngắn trên stdout; báo cáo Markdown đầy đủ tại
`reports/<ngày>.md`; trạng thái mới ghi vào `state.json`.

## Kỷ luật 2 lượt (bài học AUDIT-002 — bắt buộc, không rút gọn thêm)

Một lần curl **không bao giờ** đủ để kết luận một URL đã chết: AUDIT-002 ghi
nhận 211 URL dienmayxanh.com bị curl báo `000` nhưng mở trình duyệt đều sống,
10/14 URL cholaithieu.com bị curl báo 404 giả, giavihanhphuc.com trả 404 cho
bot. Vì vậy:

1. **Lượt 1** — curl toàn bộ URL (user-agent trình duyệt thật, đi theo
   redirect, timeout 30s), với nhịp lịch sự: tối thiểu 0,7s giữa hai request
   tới cùng một domain (cookpad.com 0,9s; api.nal.usda.gov 1,2s vì DEMO_KEY có
   giới hạn tốc độ riêng), tối đa 5 luồng.
2. **Lượt 2** — mọi URL không đạt ở lượt 1 được thử lại một lần nữa sau khi
   lượt 1 kết thúc (cách nhau tự nhiên theo thời gian quét).

Phân loại cuối:

| Trạng thái | Điều kiện |
|---|---|
| `song` (sống) | Bất kỳ lượt nào trả HTTP 2xx/3xx sau redirect |
| `chet` (chết xác nhận) | **Cả hai** lượt đều 404/410, hoặc cả hai lượt đều lỗi phân giải DNS (domain không còn tồn tại) |
| `chua-ket-luan` (chưa kết luận) | Mọi trường hợp còn lại: 000/403/5xx/timeout/lỗi mạng, hai lượt mâu thuẫn nhau, hoặc domain thuộc danh sách chặn bot đã biết (`BOT_BLOCK_DOMAINS` trong script: dienmayxanh.com, cholaithieu.com, giavihanhphuc.com, cookidoo.com.vn — curl không đáng tin trên các domain này nên không bao giờ kết luận chết từ curl) |

## Cách đọc báo cáo

- **Thay đổi so với lần chạy trước** — phần cần nhìn đầu tiên: URL mới chết xác
  nhận, URL sống lại, URL mới rơi vào chưa kết luận.
- **Món Mức 2 dưới 3 nguồn sống** — hai mức:
  - *Rớt cổng chắc chắn*: số nguồn sống + chưa kết luận cộng lại vẫn < 3 → cần
    xử lý như nhóm A của AUDIT-002 (thay nguồn hoặc hạ mức qua phân xử).
  - *Nguy cơ*: nguồn sống < 3 nhưng các URL chưa kết luận nếu sống thì đủ →
    cần một lượt mở bằng trình duyệt (như lượt 2 của AUDIT-002) để kết luận,
    không tự hạ mức.
- **URL chết xác nhận** — danh sách đầy đủ kèm thực thể tham chiếu
  (món/nguyên liệu/quả) để lên đợt thay nguồn.
- **Chưa kết luận theo domain** — nếu một domain lạ bỗng chiếm tỉ lệ lớn, khả
  năng cao là domain đó bắt đầu chặn bot: bổ sung vào `BOT_BLOCK_DOMAINS` sau
  khi kiểm chứng bằng trình duyệt, đừng kết luận chết hàng loạt.

## Giới hạn đã biết (đọc trước khi hành động theo báo cáo)

- Curl không đọc nội dung trang: một URL "sống" theo script vẫn có thể là
  *soft-404* (trang chủ/trang lỗi trả 200) hoặc nội dung đã đổi khác món —
  AUDIT-002 bắt được 10 ca "sống nhưng sai nội dung" nhờ mở trình duyệt. Báo
  cáo của script là **cảnh báo sớm**, không thay thế vòng rà soát bằng trình
  duyệt khi phân xử.
- Các URL `api.nal.usda.gov` dùng `api_key=DEMO_KEY` (khoá demo công khai của
  USDA): nếu USDA siết giới hạn, cả cụm 260 URL có thể rơi vào chưa kết luận
  cùng lúc — đó là nhiễu đã biết, không phải link rot.
- `state.json` lưu trạng thái theo URL: URL bị xoá khỏi `data/` sẽ biến mất
  khỏi state ở lần chạy sau (không báo cáo như "thay đổi"); URL mới thêm vào
  `data/` xuất hiện lần đầu với trạng thái hiện tại của nó.
