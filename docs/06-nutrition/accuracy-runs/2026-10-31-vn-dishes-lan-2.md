# QA-002 lần 2 — Đo lại độ chính xác sau Phương án A (đổi quy ước dầu món chiên)

**Ngày chạy:** 2026-10-31 (lần 2) · **engine_version:** 1.1.0 (không đổi) · **Kho dữ liệu:** 500 món / 386 nguyên liệu
**Người thực hiện:** phiên triển khai Phương án A (script tính riêng, không dùng code engine — xem §2)
**Kết luận:** ❌ **VẪN CHƯA ĐẠT mục tiêu ≥80% món trong ±20%** — đạt **77,5%**. Nhóm chiên/rán vẫn là
nguyên nhân duy nhất: **10,0%** trong ±20%, bias **+49,0%**. Bốn nhóm còn lại vẫn đạt **100%**.

> Đọc §5 trước khi trích con số này. Lần đo này cho ra một kết luận khác với kỳ vọng ban đầu của
> Phương án A — và kết luận đó quan trọng hơn con số: **khoảng cách engine ↔ tham chiếu ở nhóm chiên
> gần như không thu hẹp** (Bland–Altman nhóm chiên: +143,7 kcal lần 1 → +137,0 kcal lần 2), vì khoảng
> cách đó *chính là* lượng dầu hấp thụ do engine mô hình hoá, còn phía tham chiếu "công thức nguyên
> văn" theo định nghĩa không bao giờ chứa nó. Việc cần quyết tiếp nằm ở §7 — thuộc chủ dự án.

---

## 1. Việc đã làm trước khi đo — Phương án A (chủ dự án chốt 2026-10-31)

Đổi **quy ước dữ liệu**, không đổi engine: trong món `chien`/`chien-ngap-dau`, dòng `dau-an` chỉ còn
ghi dầu dùng cho chế biến *khác* ngoài bước chiên chính; dầu hấp thụ do mô hình CALC-004 đảm nhiệm.
Quy ước đầy đủ đã ghi vào [EDITORIAL-GUIDE §12](../../09-standards/EDITORIAL-GUIDE.md). Rà soát cả
46 món chiên trong kho (đếm lại từ `data/dishes/vn`):

| Cách xử lý | Số món | Ghi chú |
|---|---|---|
| Bỏ hẳn dòng `dau-an` | 33 | Dầu chỉ dùng cho bước chiên chính (gồm `chao-tom`: bỏ `dau-an` 15 g, **giữ** `mo-lon-nuoc` 15 g vì đó là mỡ trộn trong chạo — nguyên liệu ăn vào, không phải dầu chiên) |
| Giảm còn **3 g** | 7 | Có bước phụ có thật trong `recipe_steps`: `banh-goi`, `banh-quai-vac` (xào nhân), `ca-chim-sot-ca-chua`, `ca-tram-sot-chua-ngot` (phi tỏi + xào sốt), `cua-rang-muoi` (phi tỏi ớt rang lại), `com-chay-ninh-binh` (xào thịt dê), `com-chien-trung-muoi` (phi trứng muối). 3 g/khẩu phần = định lượng chế biến cho một bước phi/xào nhanh, phần dầu này ăn vào cùng nhân/sốt — lý do ghi trong `import_note` từng món |
| **Giữ nguyên** (ngoại lệ) | 2 | `xoi-chien` (15 g), `banh-chung-chien` (10 g): chủ thể chiên là tinh bột đã nấu chín (gạo nếp) có `oil_absorption_pct = 0` **theo thiết kế CALC-004** ("tinh bột đã nấu chín rán lại với dầu đã kê trong công thức") — dòng dầu là cơ chế tính dầu *duy nhất*, không hề tính trùng; bỏ đi thì engine tính 0 g dầu cho một món chiên. Đưa hai món này về quy ước A thuần tuý cần gán hệ số cho gạo nếp đã nấu — thay đổi phía engine/dữ liệu nguyên liệu, **ngoài phạm vi Phương án A** |
| Vốn không có dòng dầu | 4 | `banh-re`, `bun-dau-mam-tom`, `pho-mai-que` — không đổi. `bim-bim` — trường hợp đặc biệt: đã bỏ `dau-an` từ CALC-004 (thành phẩm chiên sẵn, dầu do hệ số 12 của khoai-tay đảm nhiệm); ghi chú rõ trong file. Lệch +155,8% lần này vẫn do phía tham chiếu tính từ khoai sống — giới hạn phương pháp đã biết |

Sau sửa: `validate-data` **0 🔴** (968 bản ghi), `migrate:fresh --seed` dựng lại được,
`recompute-calories --with-ingredients`: **495/500 món tính được** (như trước), Pest **106/106**.

## 2. Phương pháp — và một minh bạch về bộ mẫu

Hai phía như lần 1 ([báo cáo lần 1](2026-10-31-vn-dishes.md) §2): phía engine lấy điểm giữa khoảng
`kcal_min`–`kcal_max` trong `dish_nutrition` sau recompute; phía tham chiếu tính tay độc lập từ
`data/ingredients/` trên **công thức nguyên văn sau Phương án A**, không cộng mô hình dầu hấp thụ.

**Minh bạch:** danh sách 160 món của lần 1 không được lưu lại (chỉ còn cỡ tầng + seed 20261031).
Lần 2 dựng lại mẫu bằng `random.Random(20261031)` trên pool sắp xếp theo slug, cỡ tầng y hệt lần 1.
Kiểm chứng tính tương đương: **bốn tầng không thay đổi dữ liệu cho kết quả khớp lần 1 gần như từng
số lẻ** (luộc/hấp MAPE 1,6/bias −0,0; xào 1,7/+1,0; nướng/kho 2,5/+1,2; BA của ba tầng này khớp
tuyệt đối) — bộ mẫu dựng lại tương đương bộ mẫu lần 1 về phân phối. Danh sách 160 món lần này được
lưu nguyên văn ở **Phụ lục** để mọi lần đo sau dùng lại đúng danh sách, không dựng lại nữa.
(Hồ `nau` đối chiếu được 190/191 món: 1 món không có ở cả hai phía nên không vào mẫu được.)

## 3. Kết quả tổng — so với lần 1

| Chỉ số | Lần 1 (trước A) | Lần 2 (sau A) |
|---|---|---|
| n | 160 | 160 |
| **% món trong ±20%** | **76,9%** (123/160) | **77,5%** (124/160) — ngưỡng ≥80%: **CHƯA ĐẠT** |
| MAPE | 11,5% | 13,8% |
| Bias | +10,3% | +12,6% |
| Sai lệch % trung vị | +1,8% | +1,7% |
| Bland–Altman: chênh lệch trung bình | +37,7 kcal | +35,7 kcal |
| Bland–Altman: LoA 95% | −93,0 … +168,3 | −89,8 … +161,3 |

## 4. Tách theo cách chế biến

| Tầng | n | % trong ±20% (lần 1 → lần 2) | MAPE | Bias | BA mean (kcal) |
|---|---|---|---|---|---|
| Luộc/hấp | 32 | 100% → **100%** | 1,6% | −0,0% | −1,1 |
| Xào | 28 | 100% → **100%** | 1,7% | +1,0% | +5,3 |
| **Chiên/rán** | 40 | 7,5% → **10,0%** | **49,0%** | **+49,0%** | **+137,0** (LoA +43,7 … +230,2) |
| Nấu/canh | 32 | 100% → **100%** | 2,4% | −0,1% | +0,4 |
| Nướng/kho | 28 | 100% → **100%** | 2,5% | +1,2% | +4,2 |

Số bổ trợ ngoài mẫu: **toàn hồ chiên 46 món**: 10,9% trong ±20%, MAPE = bias = +50,9%.
**Toàn kho**: 41/495 món lệch >20% (QA-001 lần trước: 42) — **tất cả vẫn là món chiên**.
Hai món ngoại lệ ở §1 đều nằm trong ±20% (`xoi-chien` ở trong mẫu và đạt; `banh-chung-chien` đạt
ở mức toàn kho) — logic ngoại lệ vận hành đúng như phân tích.

## 5. Vì sao bias nhóm chiên không về 0 — phân tích lại chẩn đoán "tính hai lần"

Phân rã một món điển hình, `ca-keo-chien-gion` (số làm tròn):

| | Tham chiếu (kcal) | Engine (kcal) | Chênh |
|---|---|---|---|
| Lần 1: công thức có `dau-an` 15 g (132,6 kcal) | ≈328 (gốc 195 + dầu kê 133) | ≈534 (gốc + dầu kê + hấp thụ mô hình 166, đã nới khoảng) | **+63%** |
| Lần 2: bỏ dòng dầu | ≈196 (chỉ còn gốc) | ≈395 (gốc + hấp thụ mô hình 166, đã nới khoảng) | **+101%** |

Ba điều rút ra:

1. **Chênh lệch tuyệt đối gần như không đổi** (nhóm chiên: BA +143,7 → +137,0 kcal). Khoảng cách
   đó *là* dầu hấp thụ theo mô hình CALC-004 (trung vị ~+100 kcal/món cộng phần nới khoảng) —
   phía tham chiếu nguyên văn không chứa nó ở cả hai lần đo.
2. **Lần 1, dòng `dau-an` trong công thức vô tình đóng vai trò đại diện cho dầu hấp thụ ở phía
   tham chiếu**, che một phần khoảng cách. Bỏ dòng đó (đúng quy ước A) làm mẫu số tham chiếu nhỏ
   đi trong khi tử số gần như nguyên — nên bias % *tăng* (39,8% → 49,0%) dù số món trong ±20%
   nhích nhẹ (3 → 4/40). Chẩn đoán lần 1 đúng ở nửa phía engine (engine cộng mô hình lên trên
   dòng dầu đã kê), nhưng chưa nói hết nửa phía tham chiếu.
3. Hệ quả: **cặp (quy ước A + tham chiếu nguyên văn không mô hình dầu) không tương thích về
   định nghĩa.** Muốn phép đo phản ánh đúng món chiên thật, phía tham chiếu phải tính dầu hấp
   thụ một cách tường minh — đó chính là phương án C của báo cáo lần 1, áp ở phía tham chiếu.
   Về mặt vật lý, số engine (gốc + dầu hấp thụ có nguồn) đang gần món ăn thật hơn số tham chiếu
   nguyên văn (món chiên tính như nguyên liệu sống) — nhưng đó là nhận định phân tích, **không**
   phải căn cứ để tự tuyên bố đạt: thước đo đã chốt ở DEC-001/ACCURACY-SPEC chưa hề được đổi.

**Không nới định nghĩa mục tiêu.** Ngưỡng ±20% / ≥80% giữ nguyên. Nhiệm vụ Phương án A không sửa
engine và không sửa thước đo — phần việc còn lại là quyết định của chủ dự án (§7).

## 6. Giới hạn (giữ nguyên từ lần 1)

Hai phía vẫn dùng chung một bảng thành phần của dự án: phép đo kiểm chứng đường biến đổi
nguyên liệu → món, không kiểm chứng sai số của bảng thành phần so với phân tích hoá học. Tuyên bố
độ chính xác ra bên ngoài vẫn cần mẫu tham chiếu từ nguồn ngoài dự án.

## 7. Việc tiếp theo — chờ chủ dự án chốt (không tự thực hiện)

1. **Phương án C ở phía tham chiếu:** dựng lại phía tham chiếu của QA-002 có cộng dầu hấp thụ
   tường minh theo cùng hệ số có nguồn của CALC-004 (hoặc hệ số độc lập nếu tìm được), rồi đo lại
   trên đúng danh sách mẫu ở Phụ lục. Đây là thay đổi *phương pháp đo* — phải được duyệt riêng,
   không gộp vào việc sửa dữ liệu.
2. **Rà soát độ lớn hệ số CALC-004** ở phía engine/dữ liệu nguyên liệu (vd hệ số theo nhóm có đang
   cao hơn thực tế chiên tại bếp Việt Nam không) — thay đổi phía engine, ngoài phạm vi Phương án A;
   kèm theo đó là quyết định cho hai món ngoại lệ (gán hệ số cho gạo nếp đã nấu hay giữ ngoại lệ).
3. **Giữ nguyên trạng:** ghi nhận nhóm chiên là giới hạn đã biết của cả dữ liệu lẫn phép đo hiện
   tại, không tuyên bố ">80%" ra ngoài.

## Phụ lục — Danh sách mẫu 160 món của lần đo 2 (dùng nguyên văn cho các lần sau)

- **Luộc/hấp (32):** ca-nuc-hap, banh-te, ghe-hap, banh-chung, xoi-la-cam, banh-bo, banh-nam, banh-tet, xoi-dau-xanh, banh-phu-the, banh-hoi, banh-flan, banh-beo-bi, banh-giay-quanh-ganh, banh-gai, xoi-ngo, xoi-ga, banh-uot-long-ga, xoi-vo, banh-cuon, long-lon-luoc, banh-loc-tran, cha-chia, banh-khuc, ngan-luoc, xoi-xeo, banh-gat-gu, banh-bo-thot-not, banh-bao-viet-nam, banh-ong-la-dua, banh-chuoi, ca-tram-hap-xi-dau
- **Xào (28 — toàn hồ):** mi-xao-gion, mien-luon, muc-xao-can-toi, ghe-rang-me, mi-xao-tom-cang, long-lon-xao-dua-chua, mi-xao-bo, pho-xao, oc-len-xao-dua, oc-buou-xao-sa-ot, bun-thit-xao-sa, cua-rang-me, com-dua-bo, be-be-rang-muoi, com-ga-xao-sa, bo-luc-lac, cha-ca-la-vong, tom-cang-rang-me, nui-xao-bo, suon-xao-chua-ngot, mien-xao-cua, ech-xao-sa-ot, pho-xao-ga, ngan-xao-sa-ot, rau-muong-xao-toi, thit-bo-xao-khe, bun-thit-xao, com-hai-san
- **Chiên/rán (40):** banh-goi, banh-cam, banh-xeo-chay, khoai-lang-ken, ca-linh-chien-gion, ca-ro-dong-chien-xu, com-chay-ninh-binh, com-chien-trung-muoi, banh-xeo, xoi-chien, ca-chim-sot-ca-chua, com-chien-ca-man, banh-tieu, banh-tom-ho-tay, cha-muc, com-rang, ca-bong-chien-gion, chao-tom, bim-bim, banh-gia, banh-xeo-mien-tay, bot-chien, ca-me-chien, pho-mai-que, chuoi-chien, ca-tram-chien-gion, ca-keo-chien-gion, banh-quai-vac, bun-dau-mam-tom, ca-tram-sot-chua-ngot, banh-cong, banh-bao-chien, banh-re, banh-ran-duong, nem-chua-ran, cha-ca-thac-lac, nem-ran, com-ga-xoi-mo, banh-ran, muc-nhoi-thit
- **Nấu/canh (32):** com-ga-quang-nam, chao-canh, mien-mang-ga, rau-cau-dua, canh-khoai-so, canh-bong-tha, canh-mang-ca, canh-ca-troi-nau-mang, lau-vit, hu-tieu, canh-tom-nau-bi-dao, lau-de, canh-rau-muong-nau-tom, bun-tom-cang, che-khoai-mon, che-dua, com-ga-hoi-an, banh-ngao, oc-nau-chuoi-dau, banh-canh-ghe, sup-hai-san, com-ruou, che-nhan-boc-hat-sen, don, hu-tieu-ca, canh-kho-qua-ca-thac-lac, bo-nhung-dam, bun-nuoc-leo, lau-tom-cang, sup-ca, com-tho, sam-bo-luong
- **Nướng/kho (28):** dau-phu-kho-nam, banh-pia, com-ca-kho, mam-tep-chung-thit, com-tam, ca-tram-kho-rieng, bo-kho, banh-khoai-mi, muc-rim-me, com-tam-chay, dau-phu-sot-ca, bun-cha, hau-nuong, vit-om-sau, thit-lon-gac-bep, ca-me-kho-chua, mam-ruoi, kho-quet, vit-nuong-chao, mam-kho, thit-kho-tieu, oc-vu-nang, suon-lon-nuong-mac-khen, ga-kho-hat-doi, ca-doi-kho-tieu, chuoi-nuong, com-bo-kho, bun-chao-tom
