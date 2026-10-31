# Roadmap & Task Tracking — Lắc Đói

**Ngày bắt đầu:** 2026-08-31
**Cập nhật lần cuối:** 2026-09-01 (lần 2) — repo đã có remote GitLab (Q8 đóng), phát sinh Q9 (CI
sai nền). Trước đó: soát toàn dự án, sửa **hai lỗi engine/schema chưa ai bắt**:
khoảng tin cậy rộng bằng 0 (vi phạm QT-5, đã hiệu chuẩn → engine 1.1.0) và `needs_review` không có
cơ chế duyệt + mặc định sai chiều (→ R10 + ING-004). 40 test xanh.
GĐ1 còn: hệ số dầu (chặn TEST-002), hệ số hao hụt thuỷ sản, duyệt 255 nguyên liệu, Foundation

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
| **0** | Thủ tục & khởi tạo | Gửi đề nghị cấp phép, chốt các quyết định nền, dựng repo | Repo chạy được, các quyết định đã chốt | 🔵 IN PROGRESS — repo chạy được (Laravel 13 + MySQL 8, `migrate:fresh --seed` xanh); còn [LEGAL-002](./tasks/LEGAL-002.md) và Q1 chờ chủ dự án |
| **1** | Xương sống dữ liệu | Schema + bảng tham chiếu + **250 nguyên liệu** + engine calo | Engine calo qua kiểm chứng FNDDS/Nutrition5k | 🔵 IN PROGRESS — schema, engine, **255/250 nguyên liệu**, SR Legacy, CY R2, FNDDS, Wikidata, DATA-008 xong; **kiểm chứng lần 1 đạt 89,7%**. Cổng chặn chưa mở: nhóm chiên chưa đo được |
| **2** | Trình nhập liệu | Công cụ nhập món (trang quản trị antd) + trợ lý LLM | **≤5 phút/món** — cổng chặn cứng | ⬜ TODO |
| **3** | Sản xuất dữ liệu | 150 món (dùng được) → 500 món (đủ dùng) | ≥500 món Mức 1; đo độ chính xác đạt ≥80% | ⬜ TODO |
| **4** | Engine gợi ý | Nút Lắc bản chính thức, 3 tầng, có embedding | Độ phủ kho món ≥60% trong 30 ngày | ⬜ TODO |
| **5** | Người dùng | Hồ sơ, TDEE, lịch sử bữa ăn | Gợi ý có tính tới bữa trước và TDEE | ⬜ TODO |
| **6** | Lớp phụ | Đóng góp cộng đồng + ngũ hành (tuỳ chọn) | — | ⬜ TODO |

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
| [LEGAL-002](./tasks/LEGAL-002.md) | Chốt giấy phép mã nguồn & dữ liệu của dự án | Quyết định | ⬜ | — | — |
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
| [TEST-001](./tasks/TEST-001.md) | Test các ràng buộc toàn vẹn R1–R9 | Test | ✅ (CI chưa kiểm trên GitHub — chưa có remote) | SETUP-003 | 2026-08-31 |
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

**DoD GĐ2:** đạt toàn bộ 8 yêu cầu Y1–Y8 ở [DATA-PIPELINE §4](../04-data-sources/DATA-PIPELINE.md).
**Chưa đạt Y1 (≤5 phút/món) thì không được mở GĐ3.**

---

## GIAI ĐOẠN 3 — Sản xuất dữ liệu

| ID | Task | Loại | Status | Phụ thuộc | Ngày xong |
|---|---|---|---|---|---|
| [DISH-001](./tasks/DISH-001.md) | 50 món đầu tiên | Dữ liệu | 🔶 | ADM-007 | — |
| [DISH-002](./tasks/DISH-002.md) | Lên 150 món — **mốc app dùng được** | Dữ liệu | 🔶 | DISH-001 | 2026-10-31 (đã lên 200 món, chờ duyệt) |
| [DISH-003](./tasks/DISH-003.md) | Lên 300 món | Dữ liệu | 🔶 | DISH-002 | 2026-10-31 (đã lên 300 món + hệ nhãn phân loại, chờ duyệt) |
| [DISH-004](./tasks/DISH-004.md) | Lên 500 món — **mốc đủ dùng** | Dữ liệu | ⬜ | DISH-003 | — |
| [QA-001](./tasks/QA-001.md) | Rà soát chất lượng định kỳ mỗi 50 món | QA | ⬜ | DISH-001 | — |
| [QA-002](./tasks/QA-002.md) | Chạy đo độ chính xác calo theo ACCURACY-SPEC | QA | ⬜ | DISH-003 | — |

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
| Q1 | Tên chính thức: "Lắc Đói" hay "Lắc Đối"? Tài liệu đang dùng **Lắc Đói** | — (chỉ ảnh hưởng hiển thị) | Chủ dự án | ⬜ Chờ |
| Q2 | Giấy phép mã nguồn & dữ liệu của dự án? | Việc nhập Open Food Facts và Wikibooks | Chủ dự án | ⬜ Chờ ([LEGAL-002](./tasks/LEGAL-002.md)) |
| ~~Q3~~ | ~~Viện Dinh dưỡng có cấp phép không?~~ | — | — | ❌ **Đã đóng 2026-08-31** — chọn hướng tự tra cứu, không xin phép. Xem [LEGAL-001](./tasks/LEGAL-001.md) |
| Q4 | Ảnh món lấy từ đâu? Tự chụp / mua / CC BY? | DISH-001 trở đi; ảnh hưởng kích thước repo | Chủ dự án | ⬜ Chờ |
| Q5 | Có cần Git LFS cho ảnh không? | Phụ thuộc Q4 | Chủ dự án | ⬜ Chờ |
| **Q7** | **Nguồn nào cho hệ số hao hụt khi nấu của THUỶ SẢN?** Bảng USDA Cooking Yields R2 đã nhập chỉ có thịt và gia cầm — **0 dòng thuỷ sản**. Không thể suy từ SR Legacy (raw ÷ cooked) vì chính USDA là vế tham chiếu của TEST-002 ⇒ phép kiểm thành vòng tròn. Ứng viên: USDA Agriculture Handbook 102 (bản quét 1975, chưa có lớp text) | Nhóm `hap` của TEST-002; mọi món cá hấp/luộc VN | Chủ dự án + tra cứu | ⬜ Chờ ([DATA-004](./tasks/DATA-004.md)) |
| ~~Q8~~ | ~~Repo có đẩy lên remote không, và ở đâu?~~ | — | — | ✅ **Đã đóng 2026-09-01** — `origin` = `git@gitlab.com:Tran-Son-Nam/shake-hungry.git`, branch `dev` đã đẩy (8 commit, 513 file). Rủi ro mất trắng đã gỡ |
| **Q9** | **CI chạy trên nền nào?** Repo đang ở **GitLab**, nhưng file CI duy nhất là `.github/workflows/tests.yml` — cú pháp **GitHub Actions**, GitLab **bỏ qua hoàn toàn**. Nên dù đã có remote, CI vẫn chưa chạy lần nào. Chọn một: (a) viết `.gitlab-ci.yml` tương đương — tốn compute minutes của tài khoản GitLab; (b) mirror sang GitHub để dùng Actions; (c) chấp nhận chạy tay trước mỗi commit | Chặn merge khi test đỏ; branch protection | Chủ dự án | ⬜ Chờ |
| **Q6** | **Có chấp nhận bảng MEXT (Nhật) làm nguồn thứ hai không?** 5 nguyên liệu thuỷ sản (cá nục, ghẹ, mực khô, cá cơm khô, sò huyết) chỉ MEXT có, và **đúng chi** trong khi USDA sai chi tới 43% kcal. Nhưng nhận MEXT là có hai bảng song song → xung đột QT-4 | 5 nguyên liệu thuỷ sản; món có ghẹ/mực khô/cá cơm khô | Chủ dự án | ⬜ Chờ ([ING-002](./tasks/ING-002.md)) |

---

## Nhật ký chất lượng dữ liệu

Ghi kết quả mỗi lần rà soát định kỳ (mỗi 50 món — xem [DATA-PIPELINE §5](../04-data-sources/DATA-PIPELINE.md)).

| Ngày | Phạm vi | Mẫu kiểm | Phát hiện | Hành động |
|---|---|---|---|---|
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
| 2026-08-31 | MySQL 8 có sẵn trong Laragon, tài khoản mặc định `root`; **cột `embedding` hoãn tới GĐ4** | Không cần cài thêm CSDL ngoài mặc định của Laragon; GĐ1–GĐ3 không cần embedding | [SETUP-001](./tasks/SETUP-001.md) |
| 2026-08-31 | Nguyên liệu tuỳ chọn chỉ tính vào cận trên; `fct_source='mixed'` ép confidence ≤ medium; món chiên thiếu hệ số dầu ⇒ nguyên liệu vào `missing[]` | Đặc tả gốc không định nghĩa — chọn cách bảo toàn QT-1/QT-5 (không bịa, khoảng phản ánh bất định thật) | [CALORIE-ENGINE §5b](../06-nutrition/CALORIE-ENGINE.md) |
| 2026-08-31 | Lần export đầu chuẩn hoá lại 140 file nguyên liệu về định dạng canonical | Bắt buộc để export ổn định (DoD DATA-007). Kiểm chứng bằng so sánh ngữ nghĩa: 0 mất mát; phát hiện và sửa 1 lỗi tròn số (hạt sen 0,675 g) | [DATA-007](./tasks/DATA-007.md) |
| 2026-08-31 | FNDDS/SR Links để ở **bảng riêng** (`fndds_foods`, `fndds_food_inputs`), không vào `dishes` | Đây là món Mỹ, chỉ dùng kiểm chứng engine; trộn vào kho món Việt là làm bẩn dữ liệu (cạm bẫy DATA-003) | [DATA-003](./tasks/DATA-003.md) |
| 2026-08-31 | Kết quả Nutrition5k được gọi là **sanity check**, không phải "độ chính xác" | Kiểm tra thực tế: calo của bộ này tính từ chính bảng nguyên liệu của nó (100% khớp <0,5 kcal), nên phép thử là vòng tròn. Trích nó như độ chính xác sẽ là con số vô nghĩa | [ACCURACY-SPEC §4](../06-nutrition/ACCURACY-SPEC.md) |
| 2026-08-31 | **Không hiệu chỉnh hệ số** dù hai nhóm chưa đạt ngưỡng | Nguyên nhân đã truy được (thiếu yield factor cho cá hấp; ngoại lệ ở nhóm kho) — nới hệ số để đạt số đẹp là đúng thứ cạm bẫy #2 của TEST-002 cấm | [TEST-002](./tasks/TEST-002.md) |
