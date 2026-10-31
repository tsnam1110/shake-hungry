# Roadmap & Task Tracking — Lắc Đói

**Ngày bắt đầu:** 2026-08-31
**Cập nhật lần cuối:** 2026-10-31 — ứng dụng hoàn thiện end-to-end; kho dữ liệu **500 món /
333 nguyên liệu** (300 công thức đầu đã duyệt; 200 công thức DISH-004 chờ duyệt); **CALC-004** (hệ số dầu theo nhóm)
và **LEGAL-002** (license MIT + CC BY 4.0/ODbL) đã chốt; repo chuyển hẳn sang **GitHub**
(`origin` = github.com/tsnam1110/shake-hungry, không còn remote GitLab) — CI là GitHub Actions
đã đúng nền nhưng chưa chạy vì code chưa push (chờ credential). 77 test Pest xanh.

> Mỗi task có file chi tiết riêng tại [`tasks/`](./tasks/). Click mã task để xem đầy đủ.
> Tạo task mới: copy [`TASK-TEMPLATE.md`](./TASK-TEMPLATE.md) vào `tasks/`, đặt tên theo mã task.
> Trước khi đánh dấu DONE: đối chiếu [`DEFINITION-OF-DONE.md`](./DEFINITION-OF-DONE.md).

---

## Trạng thái

| Icon | Trạng thái |
|---|---|
| ⬜ | TODO — chưa bắt đầu |
| 🔵 | IN PROGRESS — đang làm |
| ✅ | DONE — hoàn thành |
| ⏸️ | BLOCKED — bị chặn, ghi rõ chặn bởi cái gì |
| ❌ | CANCELLED — huỷ, ghi rõ lý do |

---

## Tổng quan các giai đoạn

**Thứ tự này không được đảo.** Lý do ở [DATA-PIPELINE §1](../04-data-sources/DATA-PIPELINE.md).

| GĐ | Tên | Mục tiêu | Cổng chặn để sang giai đoạn sau | Trạng thái |
|---|---|---|---|---|
| **0** | Thủ tục & khởi tạo | Gửi đề nghị cấp phép, chốt các quyết định nền, dựng repo | Repo chạy được, các quyết định đã chốt | 🔵 IN PROGRESS — repo chạy được (Laravel 13 + MySQL 8, `migrate:fresh --seed` xanh); Q1 đã chốt "Lắc Đói" 2026-10-31, [LEGAL-002](./tasks/LEGAL-002.md) đã chốt 2026-10-31 |
| **1** | Xương sống dữ liệu | Schema + bảng tham chiếu + **250 nguyên liệu** + engine calo | Engine calo qua kiểm chứng FNDDS/Nutrition5k | ✅ DONE — schema, engine, **313 nguyên liệu**, SR Legacy, CY R2, FNDDS, Wikidata xong; kiểm chứng đạt 89,7%; nhóm chiên đã tính được sau CALC-004 (2026-10-31) |
| **2** | Trình nhập liệu | Công cụ nhập món (trang quản trị antd) + trợ lý LLM | **≤5 phút/món** — cổng chặn cứng | 🔵 IN PROGRESS — lõi xong (ADM-001…005 ✅); còn ADM-006 (trợ lý LLM, chờ API key) và ADM-007 (đo giờ nhập — cổng ≤5 phút/món chưa đo chính thức) |
| **3** | Sản xuất dữ liệu | 150 món (dùng được) → 500 món (đủ dùng) | ≥500 món Mức 1; đo độ chính xác đạt ≥80% | 🔵 IN PROGRESS — **500 món** (DISH-004 đạt mốc đủ dùng 2026-10-31); QA-002 đã đo 2026-10-31: **76,9% — chưa đạt** (nhóm chiên, chờ sửa + đo lại); còn duyệt 200 công thức mới |
| **4** | Engine gợi ý | Nút Lắc bản chính thức, 3 tầng, có embedding | Độ phủ kho món ≥60% trong 30 ngày | ✅ DONE — REC-001 3 tầng chạy thật ở trang chủ (embedding REC-002 hoãn sang giai đoạn sau, không chặn) |
| **5** | Người dùng | Hồ sơ, TDEE, lịch sử bữa ăn | Gợi ý có tính tới bữa trước và TDEE | ✅ DONE — USR-001…004 chạy thật |
| **6** | Lớp phụ | Đóng góp cộng đồng + ngũ hành (tuỳ chọn) | — | 🔵 IN PROGRESS — đóng góp cộng đồng ✅ (CTR-001); ngũ hành ⬜ (TCM-001…003, tuỳ chọn) |

### Vì sao thứ tự này

```
nguyên liệu → engine calo → công cụ nhập → dữ liệu món → nút Lắc
```

Khoảng **250 nguyên liệu** phủ gần hết món Việt thường ngày. Làm sạch chúng trước thì mọi món sau đó
tự tính được calo — món thứ 400 gần như không tốn công thêm. Làm ngược lại là lặp cùng một việc hàng
trăm lần.

**Ước lượng công sức GĐ3:** 500 món × 5 phút ≈ **40 giờ công thuần** — khả thi cho một người làm buổi
tối trong vài tháng. Cũng 500 món đó mà công cụ nhập liệu tệ (15 phút/món) thì thành **125 giờ**, và
dự án sẽ bỏ dở. Đó là toàn bộ lý do GĐ2 tồn tại và có cổng chặn cứng.

---

## GIAI ĐOẠN 0 — Thủ tục & khởi tạo

**Mục tiêu:** khởi động những việc có thời gian chờ dài (giấy phép) và chốt các quyết định mà mọi
thứ khác phụ thuộc vào.

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [DOC-001](./tasks/DOC-001.md) | Viết bộ tài liệu nền của dự án | Tài liệu | ✅ | — | 2026-08-31 |
| [DEC-001](./tasks/DEC-001.md) | Chốt định nghĩa "độ chính xác calo >80%" | Quyết định | ✅ | DOC-001 | 2026-08-31 |
| [LEGAL-001](./tasks/LEGAL-001.md) | ~~Gửi đề nghị xin phép sử dụng dữ liệu Viện Dinh dưỡng~~ | Thủ tục | ❌ | — | Huỷ 2026-08-31 |
| [LEGAL-002](./tasks/LEGAL-002.md) | Chốt giấy phép mã nguồn & dữ liệu của dự án | Quyết định | ✅ | — | 2026-10-31 |
| [SETUP-001](./tasks/SETUP-001.md) | Khởi tạo Laravel + MySQL | Backend | ✅ | — | 2026-08-31 |
| [SETUP-002](./tasks/SETUP-002.md) | Dựng cấu trúc `data/` + `.gitignore` + MANIFEST | Backend | ✅ | SETUP-001 | 2026-08-31 |

**DoD GĐ0:** `php artisan --version` chạy được; kết nối được MySQL 8 với collation `utf8mb4_vi_0900_ai_ci`;
thư mục `data/` đúng cấu trúc; câu hỏi Q1/Q2 ở [PRD §9](../01-prd/PRD.md) đã có câu trả lời.

> ## ⚠️ Đính chính 2026-08-31 — đổi hướng về dữ liệu dinh dưỡng Việt Nam
>
> Bản đầu của ROADMAP ghi *"LEGAL-001 phải gửi ngay trong tuần đầu... đây là rủi ro có thời gian chờ
> dài nhất của dự án."* **Điều đó không còn đúng.**
>
> Chủ dự án đã quyết định **không xin phép Viện Dinh dưỡng**, mà **tự tra cứu từng nguyên liệu, từng
> công thức từ nguồn công khai**, chấp nhận tốn thời gian hơn để đổi lấy độ chính xác và quyền sở
> hữu dữ liệu.
>
> **Hệ quả:** rủi ro "chờ giấy phép" biến mất, thay bằng **chi phí công sức tra cứu** dồn vào
> ING-001 → ING-003. Đổi lại, dự án **sở hữu hoàn toàn** dữ liệu mình biên soạn, không phụ thuộc
> giấy phép của ai. Quy trình tra cứu bắt buộc: [DATA-PIPELINE §7](../04-data-sources/DATA-PIPELINE.md).
>
> Chi tiết quyết định: [LEGAL-001](./tasks/LEGAL-001.md).

---

## GIAI ĐOẠN 1 — Xương sống dữ liệu

**Mục tiêu:** có nền móng để mọi món nhập sau đó tự tính được calo.

### 1a. Schema và dữ liệu tham chiếu

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [SETUP-003](./tasks/SETUP-003.md) | Migration toàn bộ schema theo DATA-MODEL | Backend | ✅ (cột embedding hoãn tới GĐ4) | SETUP-001 | 2026-08-31 |
| [SETUP-004](./tasks/SETUP-004.md) | Thiết lập nền tảng CSDL MySQL 8 + tài khoản mặc định | Backend | ✅ 255/61/7.793, 40/40 test | SETUP-003 | 2026-10-30 |
| [DATA-001](./tasks/DATA-001.md) | Biên soạn cây `regions` Việt Nam | Dữ liệu | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-006](./tasks/DATA-006.md) | Lệnh `lacdoi:import-usda` + `lacdoi:validate-data` | Backend | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-002](./tasks/DATA-002.md) | Nhập USDA SR Legacy + Foundation Foods | Dữ liệu | 🔵 SR Legacy xong (7.793 dòng, 116 ánh xạ khớp/0 lệch); Foundation chưa | DATA-006 | — |
| [DATA-003](./tasks/DATA-003.md) | Nhập USDA FNDDS + SR Links | Dữ liệu | ✅ 333 Food Code / 1.810 dòng | DATA-006 | 2026-08-31 |
| [DATA-004](./tasks/DATA-004.md) | Nhập bảng hệ số hao hụt khi nấu | Dữ liệu | 🔵 thịt/gia cầm xong (163 dòng CY R2, 9 yield suy có nguồn); còn hút nước tinh bột + retention | DATA-006 | — |
| [DATA-005](./tasks/DATA-005.md) | Nhập món Việt từ Wikidata (SPARQL) | Dữ liệu | ✅ 61 món, status=draft | SETUP-003 | 2026-08-31 |
| [DATA-007](./tasks/DATA-007.md) | Lệnh `lacdoi:export-data` — JSON ổn định | Backend | ✅ | SETUP-003 | 2026-08-31 |
| [DATA-008](./tasks/DATA-008.md) | Bảng hệ số phần ăn được (nguyên liệu có xương/vỏ) | Dữ liệu | ✅ khung + 3 hệ số có nguồn; 16 hệ số null chờ phép cân | — | 2026-08-31 |

### 1b. Nguyên liệu — phần tốn công nhất

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [ING-001](./tasks/ING-001.md) | 50 nguyên liệu cốt lõi (gạo, thịt, mắm, rau nền) | Dữ liệu | 🔵 | — | Biên soạn xong **55 nguyên liệu** 2026-08-31; **3 tiêu chí DoD chưa kiểm được** vì thiếu công cụ |
| [ING-002](./tasks/ING-002.md) | Mở rộng lên 150 nguyên liệu | Dữ liệu | 🔵 | ING-001 | ✅ **150/150** đạt mốc 2026-08-31; 3 tiêu chí DoD chưa kiểm được vì thiếu công cụ; 5 mục giữ ngoài repo chờ thẩm định MEXT |
| [ING-003](./tasks/ING-003.md) | Mở rộng lên 250 nguyên liệu | Dữ liệu | 🔵 **255/250 đạt mốc 2026-08-31** (125 high/64 medium/28 low/38 null); còn rà soát độc lập + duyệt needs_review | ING-002 | — |
| [ING-004](./tasks/ING-004.md) | Dấu vết duyệt + ràng buộc R10 (cơ chế duyệt dữ liệu) | Backend | ✅ | SETUP-003 | 2026-09-01 |

### 1c. Engine tính calo

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [CALC-001](./tasks/CALC-001.md) | Engine tính dinh dưỡng (hàm thuần tuý) | Backend | ✅ | ING-001 | 2026-08-31 |
| [CALC-002](./tasks/CALC-002.md) | Hệ số hao hụt khi nấu + hấp thụ dầu khi chiên | Backend | 🔵 code xong, chờ dữ liệu DATA-004/008 | CALC-001, DATA-004 | — |
| [CALC-003](./tasks/CALC-003.md) | Hiệu chuẩn khoảng min–max (QT-5) — engine 1.1.0 | Backend | ✅ 1,0% → 80,4% tham chiếu nằm trong khoảng | CALC-001, TEST-002 | 2026-09-01 |
| [CALC-004](./tasks/CALC-004.md) | Hệ số hấp thụ dầu theo nhóm thực phẩm cho món chiên | Backend/Dữ liệu | ✅ | CALC-002 | 2026-10-31 (297/300 món tính được) |
| [TEST-001](./tasks/TEST-001.md) | Test các ràng buộc toàn vẹn R1–R9 | Test | ✅ (remote GitHub đã có; CI sẽ kiểm khi code được push) | SETUP-003 | 2026-08-31 |
| [TEST-002](./tasks/TEST-002.md) | Kiểm chứng engine trên FNDDS + Nutrition5k | Test | 🔵 lần 1 xong: **89,7% trong ±20%** (tiêu chí chính ĐẠT); nhóm chiên chưa đo được → chưa đóng | CALC-002, DATA-003 | — |

### 1d. Có thứ nhìn thấy được

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [UI-001](./tasks/UI-001.md) | Nút Lắc bản thô sơ — giữ động lực | Frontend | ✅ | DATA-005 | 2026-10-30 |

**DoD GĐ1:** 250 nguyên liệu có ánh xạ dinh dưỡng và quy đổi đơn vị; engine tính calo chạy và **qua
kiểm chứng TEST-002**; `migrate:fresh --seed` dựng lại đúng toàn bộ dữ liệu từ `data/`.

> **UI-001 cố ý xấu.** Mục đích duy nhất là có thứ nhìn thấy được sau tháng đầu. Đừng đầu tư vào nó —
> bản chính thức là [UI-002](./tasks/UI-002.md) ở GĐ4.

---

## GIAI ĐOẠN 2 — Trình nhập liệu

**Mục tiêu:** biến việc nhập món từ cực hình thành việc làm được hàng loạt.
**Đây là sản phẩm quan trọng nhất của dự án ở thời điểm này** — quan trọng hơn giao diện người dùng cuối.

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [ADM-001](./tasks/ADM-001.md) | Dựng trang quản trị antd + đăng nhập + phân quyền cơ bản | Backend | ✅ | SETUP-003 | 2026-10-30 |
| [ADM-002](./tasks/ADM-002.md) | Màn hình quản lý nguyên liệu + quy đổi đơn vị | Backend | ✅ | ADM-001 | 2026-10-30 |
| [ADM-003](./tasks/ADM-003.md) | Màn hình nhập món Mức 1 | Backend | ✅ | ADM-001 | 2026-10-30 |
| [ADM-004](./tasks/ADM-004.md) | Màn hình nhập nguyên liệu: bàn phím + calo cập nhật trực tiếp | Backend | ✅ | ADM-003, CALC-002 | 2026-10-30 |
| [ADM-005](./tasks/ADM-005.md) | Cảnh báo trùng món bằng độ giống trigram ngay khi gõ | Backend | ✅ | ADM-003 | 2026-10-30 |
| [ADM-006](./tasks/ADM-006.md) | Tích hợp LLM đề xuất nguyên liệu + ánh xạ | Backend | ⬜ | ADM-004 | — |
| [ADM-007](./tasks/ADM-007.md) | **Đo và đạt ≤5 phút/món** — cổng chặn sang GĐ3 | Nghiệm thu | ⬜ | ADM-006 | — |
| [ADM-008](./tasks/ADM-008.md) | Thống kê số lượng bản ghi + bộ lọc danh sách + trang tổng quan | Backend | ✅ | ADM-001 | 2026-10-31 |

**DoD GĐ2:** đạt toàn bộ 8 yêu cầu Y1–Y8 ở [DATA-PIPELINE §4](../04-data-sources/DATA-PIPELINE.md).
**Chưa đạt Y1 (≤5 phút/món) thì không được mở GĐ3.**

---

## GIAI ĐOẠN 3 — Sản xuất dữ liệu

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [DISH-001](./tasks/DISH-001.md) | 50 món đầu tiên | Dữ liệu | 🔶 | ADM-007 (cổng này được vượt theo chỉ đạo riêng của chủ dự án — kho món đã sản xuất trước khi đo giờ nhập) | 2026-10-31 (món đã có + đã duyệt) |
| [DISH-002](./tasks/DISH-002.md) | Lên 150 món — **mốc app dùng được** | Dữ liệu | 🔶 | DISH-001 | 2026-10-31 (đã lên 200 món; công thức đã được chủ dự án duyệt 2026-10-31) |
| [DISH-003](./tasks/DISH-003.md) | Lên 300 món | Dữ liệu | 🔶 | DISH-002 | 2026-10-31 (đã lên 300 món + hệ nhãn phân loại; công thức đã được chủ dự án duyệt 2026-10-31) |
| [DISH-004](./tasks/DISH-004.md) | Lên 500 món — **mốc đủ dùng** | Dữ liệu | 🔶 | DISH-003 | 2026-10-31 (đã lên 500 món + 20 nguyên liệu mới; chờ duyệt công thức) |
| [QA-001](./tasks/QA-001.md) | Rà soát chất lượng định kỳ mỗi 50 món | QA | 🔵 đợt rà toàn kho 500 món xong 2026-10-31 (task lặp lại, không đóng) | DISH-001 | — |
| [QA-002](./tasks/QA-002.md) | Chạy đo độ chính xác calo theo ACCURACY-SPEC | QA | ✅ 2026-10-31 — **76,9% trong ±20%, chưa đạt ngưỡng 80%** (nhóm chiên 7,5%, dầu tính hai lần; chờ chốt phương án sửa rồi đo lại) | DISH-003 | 2026-10-31 |
| [FRUIT-001](./tasks/FRUIT-001.md) | Schema + hạ tầng dữ liệu nhánh hoa quả | Dữ liệu | ✅ | — | 2026-10-31 (hạ tầng + dữ liệu FRUIT-003/004 hoàn tất) |
| [FRUIT-002](./tasks/FRUIT-002.md) | Bổ sung nguyên liệu quả còn thiếu (53 loại — danh sách chốt khi triển khai) | Dữ liệu | ✅ | — | 2026-10-31 (85 nguyên liệu trai-cay: 79 có số, 6 null có hồ sơ) |
| [FRUIT-003](./tasks/FRUIT-003.md) | Soạn hồ sơ hoa quả (82 hồ sơ) | Dữ liệu | ✅ | FRUIT-001, FRUIT-002 | 2026-10-31 (validate 0 🔴; needs_review chờ duyệt) |
| [FRUIT-004](./tasks/FRUIT-004.md) | Thu thập giá mùa vụ 3 miền × 12 tháng | Dữ liệu | ✅ | FRUIT-003 | 2026-10-31 (1.387 dòng giá có nguồn, 78/82 hồ sơ; 4 hồ sơ để trống có lý do) |
| [FRUIT-005](./tasks/FRUIT-005.md) | Module quản trị /admin/hoa-qua | Backend | ✅ | FRUIT-001 | 2026-10-31 |
| [FRUIT-006](./tasks/FRUIT-006.md) | Trang người dùng /hoa-qua | Frontend | ✅ | FRUIT-003, FRUIT-004 | 2026-10-31 (+ khối "Tháng này nên ăn gì") |
| [ALIAS-001](./tasks/ALIAS-001.md) | Tên gọi khác (tên vùng miền) cho món + nguyên liệu/quả, tìm kiếm được | Dữ liệu | ✅ | — | 2026-10-31 (19 quả + 66 món có tên gọi khác; 2 cặp nghi trùng chờ chủ dự án quyết gộp) |

**DoD GĐ3:** ≥500 món Mức 1; QA-002 cho kết quả ≥80% số món trong ±20%; nhật ký đo đã ghi vào
[ACCURACY-SPEC §6](../06-nutrition/ACCURACY-SPEC.md).

> Đây là giai đoạn dài nhất và **là lúc dự án dễ chết nhất**. Hai thứ bảo hiểm: cổng chặn GĐ2, và mốc
> dùng được sớm ở 150 món để thấy thành quả trước khi hết hứng.

---

## GIAI ĐOẠN 4 — Engine gợi ý

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [REC-001](./tasks/REC-001.md) | Engine gợi ý 3 tầng | Backend | ✅ | DISH-002 | 2026-10-30 |
| [REC-002](./tasks/REC-002.md) | Sinh embedding + tìm món tương tự bằng embedding vector | Backend | ⬜ | REC-001 | — |
| [UI-002](./tasks/UI-002.md) | Nút Lắc bản chính thức | Frontend | ✅ | REC-001 | — |
| [UI-003](./tasks/UI-003.md) | Trang chi tiết món (công thức, dinh dưỡng, độ tin cậy) | Frontend | ✅ | UI-002 | — |
| [UI-004](./tasks/UI-004.md) | Vỏ ứng dụng + hệ thiết kế v2 (Apple HIG) | Frontend | ✅ | UI-003 | 2026-10-31 |
| [UI-005](./tasks/UI-005.md) | Trang chủ: sân khấu Lắc theo bản mẫu v2 | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-006](./tasks/UI-006.md) | Khám phá món dạng danh sách biên tập | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-007](./tasks/UI-007.md) | Chi tiết món: khối Nguồn gốc con số mở sẵn | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-008](./tasks/UI-008.md) | Hồ sơ — trung tâm quản trị của người dùng | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-009](./tasks/UI-009.md) | Trang Hoa quả theo cùng hệ thiết kế | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-010](./tasks/UI-010.md) | QA giao diện v2 + tài liệu | Frontend | ✅ | UI-005…UI-009 | 2026-10-31 |
| [UI-011](./tasks/UI-011.md) | Khối Lắc: tuỳ chọn trên nút, gợi ý bữa theo giờ, tuỳ chọn lưu ở hồ sơ | Frontend + Backend | ✅ | UI-005, UI-008 | 2026-10-31 |
| [UI-012](./tasks/UI-012.md) | Thẻ đồng nhất cho món ăn, nguyên liệu, hoa quả (sẵn sàng cho ảnh thật) | Frontend | ✅ | UI-004 | 2026-10-31 |
| [UI-013](./tasks/UI-013.md) | Khối Lắc: lựa chọn loại trừ, chip Không ăn được, kcal gợi ý theo TDEE | Frontend | ✅ | UI-011 | 2026-10-31 |
| [UI-014](./tasks/UI-014.md) | Yêu thích + thống kê số lượng món/quả đã ăn | Frontend + Backend | ✅ | UI-012 | 2026-10-31 |

**DoD GĐ4:** bấm Lắc ra món hợp bữa, không lặp món vừa ăn, hiển thị dinh dưỡng đúng quy tắc QT-5.

---

## GIAI ĐOẠN 5 — Người dùng

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [USR-001](./tasks/USR-001.md) | Đăng ký / đăng nhập người dùng | Backend | ✅ | UI-002 | — |
| [USR-002](./tasks/USR-002.md) | Hồ sơ thể trạng + tính khoảng TDEE | Backend | ✅ | USR-001 | — |
| [USR-003](./tasks/USR-003.md) | Ghi nhật ký bữa ăn (`meal_logs`) | Backend | ✅ | USR-001 | — |
| [USR-004](./tasks/USR-004.md) | Đưa lịch sử + TDEE vào engine gợi ý | Backend | ✅ | USR-002, USR-003, REC-001 | — |

---

## GIAI ĐOẠN 6 — Lớp phụ

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [CTR-001](./tasks/CTR-001.md) | Form đóng góp + hàng đợi duyệt trong trang quản trị | Backend | ✅ | ADM-001 | — |
| [TCM-001](./tasks/TCM-001.md) | Bảng `ingredient_tcm_traits` + gieo mầm từ dược thực đồng nguyên | Dữ liệu | ⬜ | ING-003 | — |
| [TCM-002](./tasks/TCM-002.md) | Tổng hợp tính vị từ nguyên liệu lên món | Backend | ⬜ | TCM-001 | — |
| [TCM-003](./tasks/TCM-003.md) | Hiển thị lớp tính vị (mặc định tắt, khung văn hoá) | Frontend | ⬜ | TCM-002 | — |

---

## Câu hỏi mở

Ghi mọi câu hỏi chưa có lời giải ở đây. **Không đoán rồi làm tiếp.**

| # | Câu hỏi | Chặn task nào | Ai trả lời | Trạng thái |
|---|---|---|---|---|
| Q1 | Tên chính thức: "Lắc Đói" hay "Lắc Đối"? | — (chỉ ảnh hưởng hiển thị) | Chủ dự án | ✅ **Đã chốt 2026-10-31: "Lắc Đói"** — lắc món ăn khi đói; phương án "Lắc Đối" bị loại |
| Q2 | Giấy phép mã nguồn & dữ liệu của dự án? | Việc nhập Open Food Facts và Wikibooks | Chủ dự án | ✅ Đã chốt 2026-10-31 ([LEGAL-002](./tasks/LEGAL-002.md)): mã nguồn MIT; dữ liệu collective database — tự biên soạn CC BY 4.0, dòng có nguồn OFF giữ ODbL 1.0 |
| ~~Q3~~ | ~~Viện Dinh dưỡng có cấp phép không?~~ | — | — | ❌ **Đã đóng 2026-08-31** — chọn hướng tự tra cứu, không xin phép. Xem [LEGAL-001](./tasks/LEGAL-001.md) |
| Q4 | Ảnh món lấy từ đâu? Tự chụp / mua / CC BY? | DISH-001 trở đi; ảnh hưởng kích thước repo | Chủ dự án | ⬜ Chờ |
| Q5 | Có cần Git LFS cho ảnh không? | Phụ thuộc Q4 | Chủ dự án | ⬜ Chờ |
| **Q7** | **Nguồn nào cho hệ số hao hụt khi nấu của THUỶ SẢN?** Bảng USDA Cooking Yields R2 đã nhập chỉ có thịt và gia cầm — **0 dòng thuỷ sản**. Không thể suy từ SR Legacy (raw ÷ cooked) vì chính USDA là vế tham chiếu của TEST-002 ⇒ phép kiểm thành vòng tròn. Ứng viên: USDA Agriculture Handbook 102 (bản quét 1975, chưa có lớp text) | Nhóm `hap` của TEST-002; mọi món cá hấp/luộc VN | Chủ dự án + tra cứu | ⬜ Chờ ([DATA-004](./tasks/DATA-004.md)) |
| ~~Q8~~ | ~~Repo có đẩy lên remote không, và ở đâu?~~ | — | — | ✅ **Đã đóng 2026-09-01** — `origin` = `git@gitlab.com:Tran-Son-Nam/shake-hungry.git`, branch `dev` đã đẩy (8 commit, 513 file). Rủi ro mất trắng đã gỡ |
| ~~Q9~~ | ~~CI chạy trên nền nào?~~ | — | — | ✅ **Đã đóng 2026-10-31** — tiền đề đã đảo ngược: repo nay ở **GitHub** (không còn remote GitLab) nên `.github/workflows/tests.yml` đã đúng nền. CI vẫn chưa chạy lần nào vì code **chưa push** — sẽ tự chạy ở lần push đầu (đang chờ credential) |
| ~~Q6~~ | ~~Có chấp nhận bảng MEXT (Nhật) làm nguồn thứ hai không?~~ | — | — | ✅ **Đã đóng 2026-10-31** — quyết định **tầng 6** của chủ dự án bao quát cả MEXT/FNRI/IFCT/Bảng VN/văn liệu: chỉ tra từng mục khi nguồn chính thống không có, kèm trích dẫn + cờ "chưa kiểm định" (xem [DATA-PIPELINE §7.2](../04-data-sources/DATA-PIPELINE.md)). Xung đột QT-4 được xử lý bằng cơ chế `mixed` có kiểm soát + hạ confidence |

---

## Nhật ký chất lượng dữ liệu

Ghi kết quả mỗi lần rà soát định kỳ (mỗi 50 món — xem [DATA-PIPELINE §5](../04-data-sources/DATA-PIPELINE.md)).

| Ngày | Phạm vi | Mẫu kiểm | Phát hiện | Hành động |
|---|---|---|---|---|
| 2026-10-31 | Toàn kho 500 món (QA-001 đợt toàn kho) + QA-002 đo độ chính xác trên món Việt | validate-data 968 bản ghi; kiểm tay ngẫu nhiên 5 món; mẫu QA-002 160 món phân tầng | validate **0 🔴, 26 🟡** (đã phân loại hết: V9 chủ yếu là món chấm khẩu phần nhỏ — đề xuất dải riêng cho loại nước chấm; V8 gồm 67 nguyên liệu trái cây của module hoa quả). Kiểm tay: 2/5 món lệch >20%, cả hai là món chiên. QA-002: **76,9% trong ±20% — chưa đạt 80%**; 4 nhóm không chiên đạt 100%, nhóm chiên chỉ 7,5% (bias +39,8%) | Nguyên nhân gốc duy nhất: **dầu chiên tính hai lần** — công thức đã kê `dau-an` 8–20 g, engine cộng thêm mô hình hấp thụ CALC-004 (trung vị +100,5 kcal). Chờ chủ dự án chốt phương án sửa (dữ liệu/engine/cả hai) rồi đo lại trên cùng bộ mẫu; không nới định nghĩa. Báo cáo: [QA-001](../06-nutrition/accuracy-runs/2026-10-31-qa001-ra-soat-500-mon.md) · [QA-002](../06-nutrition/accuracy-runs/2026-10-31-vn-dishes.md) |
| 2026-08-31 | 55 nguyên liệu đầu tiên ([ING-001](./tasks/ING-001.md)) | Toàn bộ, tra chéo USDA / Ciqual / nhãn sản phẩm | 20 `high`, 21 `medium`, 12 `low`, **2 không tra được** (cá lóc, mắm tôm). Bốn ô sai số lớn: ba chỉ (380–518), nạm bò (155–345), đậu phụ (80–144), cơm trắng (130–155). Bẫy loài: USDA không có mục `water spinach` cho rau muống | Tách đôi 3 nguyên liệu (thăn lợn, nước mắm); ghi 6 nhóm bẫy vào [EDITORIAL-GUIDE §9](../09-standards/EDITORIAL-GUIDE.md); 4 nguyên liệu dùng OFF → **chặn bởi [LEGAL-002](./tasks/LEGAL-002.md)** |
| 2026-09-01 | Soát toàn dự án (code + dữ liệu + tài liệu) | Máy: chạy lại `validate-data`, 40 test Pest, `migrate:fresh --seed`, `accuracy-run`; đối chiếu từng con số của CLAUDE.md/ROADMAP với thực tế | Số liệu tài liệu **khớp thực tế**, trừ 2 chỗ đã đính chính. Bốn phát hiện mới: (1) engine trả khoảng **rộng bằng 0** cho ánh xạ `high` — vi phạm QT-5, đúng thứ chỉ số "1,0% tham chiếu trong khoảng" đã tố cáo từ lần đo 1 mà không ai đọc; (2) **255/255** nguyên liệu `needs_review=true` (tài liệu ghi 105) vì schema **không có chỗ ghi ai đã duyệt**; (3) `needs_review` mặc định **`false`** ở cả hai bảng — ngược QT-1, lỗ hổng mở đúng lúc GĐ2 ghi thẳng vào DB; (4) bảng Cooking Yields R2 **0 dòng thuỷ sản** nên nhóm `hap` không có nguồn hệ số độc lập | Engine 1.1.0 (bất định nền ±4%, hiệu chuẩn từ p90 nhóm bảo toàn khối lượng); R10 + V15 + 3 cột dấu vết duyệt; đảo mặc định `needs_review`; ghi Q7 (hệ số thuỷ sản) và Q8 (remote) vào Câu hỏi mở |
| 2026-08-31 | Đợt ING-003: 105 nguyên liệu mới (150→255) + đối chiếu toàn kho | Máy: đối chiếu snapshot ↔ bảng SR Legacy đã nhập (197 mục), validator V1–V14, V10 | **197/197 ánh xạ khớp tuyệt đối, 0 lệch.** Số liệu 105 file mới do script máy-chép từ USDA gốc theo fdc_id (không gõ tay). 38 mục null có tài liệu tra cứu. V10: chỉ mỡ nước vượt 900 kcal — đúng ngoại lệ. Bẫy mới ghi nhận: chili powder Mỹ là hỗn hợp; sake ≠ rượu trắng (cồn bằng nửa); lươn Anguilla ≠ Monopterus (béo ~6×) | Toàn bộ 105 file needs_review=true chờ người duyệt; 5 mục MEXT tiếp tục giữ ngoài repo (Q6) |

---

## Nhật ký quyết định

Ghi lại các quyết định kỹ thuật quan trọng để người sau không lật lại vô ích.

| Ngày | Quyết định | Lý do tóm tắt | Tài liệu |
|---|---|---|---|
| 2026-08-31 | MySQL 8 | Khớp mờ tên món bằng trigram ở tầng PHP + FULLTEXT ngram; tìm không dấu/sắp xếp tiếng Việt bằng collation `utf8mb4_vi_0900_ai_ci`; embedding hoãn tới GĐ4 | [ARCHITECTURE §4](../02-architecture/ARCHITECTURE.md) |
| 2026-08-31 | Không dùng Python | `ingredient-parser-nlp` và `recipe-scrapers` đều chỉ hỗ trợ tiếng Anh, không có site VN → lợi thế hệ sinh thái Python biến mất | [ARCHITECTURE §3](../02-architecture/ARCHITECTURE.md) |
| 2026-08-31 | Không fork bất kỳ app quản lý công thức nào | Tandoor có Commons Clause (không phải OSS); Mealie/KitchenOwl/Nextcloud là AGPL copyleft mạng | [LICENSE-COMPLIANCE §2.4](../04-data-sources/LICENSE-COMPLIANCE.md) |
| 2026-08-31 | Hoãn dữ liệu địa điểm quán sang v2 | OSM chỉ có 1.845 nhà hàng ở lõi TP.HCM — quá mỏng; quán vỉa hè gần như không được map | [DATA-SOURCES §5](../04-data-sources/DATA-SOURCES.md) |
| 2026-08-31 | LLM không được sinh số dinh dưỡng | Không truy nguyên được; phá thẳng mục tiêu chính | [DATA-PIPELINE §2](../04-data-sources/DATA-PIPELINE.md) |
| 2026-08-31 | Nguyên liệu trước, món sau | ~250 nguyên liệu phủ hết món Việt thường ngày; làm ngược là lặp việc hàng trăm lần | [DATA-PIPELINE §1](../04-data-sources/DATA-PIPELINE.md) |
| 2026-08-31 | Định nghĩa độ chính xác = ≥80% món trong ±20% | "±10% so với phân tích hoá học" không khả thi — nhãn menu nhà hàng Mỹ còn lệch trung bình 18% | [ACCURACY-SPEC §2](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | **Không xin phép Viện Dinh dưỡng; tự tra cứu từng nguyên liệu từ nguồn công khai** | Chấp nhận đánh đổi thời gian lấy độ chính xác và quyền sở hữu dữ liệu. Giá trị dinh dưỡng là **dữ kiện**, biên soạn độc lập có ghi nguồn là hợp lệ; cái bị bảo hộ là **bảng biên soạn** của họ, và ta không chép nó | [LEGAL-001](./tasks/LEGAL-001.md) · [DATA-PIPELINE §7](../04-data-sources/DATA-PIPELINE.md) |
| 2026-10-31 | **License: mã nguồn MIT; dữ liệu dạng collective database** (tự biên soạn CC BY 4.0, dòng có nguồn Open Food Facts giữ ODbL 1.0 + ghi công) | Phụ lục kiểm kê cho thấy chốt lúc này không tốn chi phí; thực tế đã có vài dòng nhãn tra qua OFF từ đợt ING-001 nên mô hình tập hợp xử lý đúng thực tế thay vì xoá dữ liệu trụ cột hay ép toàn bộ dataset sang share-alike | [LEGAL-002](./tasks/LEGAL-002.md) · [LICENSE-COMPLIANCE §5](../04-data-sources/LICENSE-COMPLIANCE.md) |
| 2026-08-31 | MySQL 8 có sẵn trong Laragon, tài khoản mặc định `root`; **cột `embedding` hoãn tới GĐ4** | Không cần cài thêm CSDL ngoài mặc định của Laragon; GĐ1–GĐ3 không cần embedding | [SETUP-001](./tasks/SETUP-001.md) |
| 2026-08-31 | Nguyên liệu tuỳ chọn chỉ tính vào cận trên; `fct_source='mixed'` ép confidence ≤ medium; món chiên thiếu hệ số dầu ⇒ nguyên liệu vào `missing[]` | Đặc tả gốc không định nghĩa — chọn cách bảo toàn QT-1/QT-5 (không bịa, khoảng phản ánh bất định thật) | [CALORIE-ENGINE §5b](../06-nutrition/CALORIE-ENGINE.md) |
| 2026-08-31 | Lần export đầu chuẩn hoá lại 140 file nguyên liệu về định dạng canonical | Bắt buộc để export ổn định (DoD DATA-007). Kiểm chứng bằng so sánh ngữ nghĩa: 0 mất mát; phát hiện và sửa 1 lỗi tròn số (hạt sen 0,675 g) | [DATA-007](./tasks/DATA-007.md) |
| 2026-08-31 | FNDDS/SR Links để ở **bảng riêng** (`fndds_foods`, `fndds_food_inputs`), không vào `dishes` | Đây là món Mỹ, chỉ dùng kiểm chứng engine; trộn vào kho món Việt là làm bẩn dữ liệu (cạm bẫy DATA-003) | [DATA-003](./tasks/DATA-003.md) |
| 2026-08-31 | Kết quả Nutrition5k được gọi là **sanity check**, không phải "độ chính xác" | Kiểm tra thực tế: calo của bộ này tính từ chính bảng nguyên liệu của nó (100% khớp <0,5 kcal), nên phép thử là vòng tròn. Trích nó như độ chính xác sẽ là con số vô nghĩa | [ACCURACY-SPEC §4](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | **Không hiệu chỉnh hệ số** dù hai nhóm chưa đạt ngưỡng | Nguyên nhân đã truy được (thiếu yield factor cho cá hấp; ngoại lệ ở nhóm kho) — nới hệ số để đạt số đẹp là đúng thứ cạm bẫy #2 của TEST-002 cấm | [TEST-002](./tasks/TEST-002.md) |
