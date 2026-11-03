# Tài liệu dự án Lắc Đói

**Cập nhật lần cuối:** 2026-11-02 — thêm [GOLIVE-DOCKER.md](10-deployment/GOLIVE-DOCKER.md) (hướng dẫn go-live production bằng Docker); trước đó cùng ngày thêm [SETUP-DEV.md](10-deployment/SETUP-DEV.md) (hướng dẫn cài môi trường dev trên máy khác)

Thư mục này là **nguồn sự thật về thiết kế** của dự án. Code phải khớp tài liệu; khi lệch nhau thì
một trong hai là sai và phải sửa ngay, không để tồn tại song song.

---

## Vì sao cấu trúc tài liệu này khác các dự án khác

Cấu trúc thư mục ở đây tham khảo quy ước trình bày của `edu-crm-v2` (đánh số thư mục, mỗi task một
file riêng, khối "Đính chính" khi phát hiện tài liệu cũ sai) nhưng **nội dung được thiết kế lại cho
đúng bản chất dự án này**.

`edu-crm-v2` là hệ thống CRUD nghiệp vụ, nên trọng tâm tài liệu của nó là API contract, user story và
ma trận phân quyền. **Lắc Đói là dự án biên soạn cơ sở dữ liệu**, nên trọng tâm dịch chuyển hẳn:

| edu-crm-v2 có | Lắc Đói thay bằng | Lý do |
|---|---|---|
| `04-api-contract/` (227 KB) | `04-data-sources/` | v1 không có API công khai; rủi ro lớn nhất là nguồn dữ liệu và license |
| `06-user-stories/` (hàng chục FRD) | `06-nutrition/` | Chức năng người dùng rất ít; độ chính xác dinh dưỡng mới là thứ cần đặc tả chặt |
| `08-permissions/` | `08-cultural/` | v1 chỉ có admin và khách; lớp ngũ hành mới là thứ cần quy tắc rõ |
| — | `09-standards/EDITORIAL-GUIDE.md` | Dự án dữ liệu cần chuẩn **biên soạn**, không chỉ chuẩn code |

Khi dự án đổi bản chất (ví dụ mở API công khai ở v2), hãy thêm thư mục mới chứ đừng nhét vào chỗ cũ.

---

## Bản đồ tài liệu

### Hiểu dự án

| Tài liệu | Nội dung |
|---|---|
| [01-prd/PRD.md](01-prd/PRD.md) | Dự án làm gì, cho ai, phạm vi v1, cái gì **không** làm |
| [02-architecture/ARCHITECTURE.md](02-architecture/ARCHITECTURE.md) | Stack, các khối hệ thống, quyết định kỹ thuật và lý do |
| [03-data-model/DATA-MODEL.md](03-data-model/DATA-MODEL.md) | Toàn bộ bảng, cột, quan hệ, ràng buộc |
| [03-data-model/TAXONOMY.md](03-data-model/TAXONOMY.md) | Hệ phân loại + nhãn cho món và nguyên liệu (suy nhãn, V16, API lọc) |

### Dữ liệu — phần quan trọng nhất của dự án

| Tài liệu | Nội dung |
|---|---|
| [04-data-sources/DATA-SOURCES.md](04-data-sources/DATA-SOURCES.md) | Từng nguồn dữ liệu: lấy gì, ở đâu, bao nhiêu, định dạng nào |
| [04-data-sources/LICENSE-COMPLIANCE.md](04-data-sources/LICENSE-COMPLIANCE.md) | Danh sách trắng / danh sách đen, quy trình duyệt nguồn mới |
| [04-data-sources/DATA-REPOSITORY.md](04-data-sources/DATA-REPOSITORY.md) | Quy tắc lưu trữ tài nguyên trong repo, định dạng file, quy trình đồng bộ |
| [04-data-sources/DATA-PIPELINE.md](04-data-sources/DATA-PIPELINE.md) | Quy trình nhập một món từ đầu đến cuối, vai trò của LLM |
| [04-data-sources/DATA-COLLECTION-PLAN-2026-10-31.md](04-data-sources/DATA-COLLECTION-PLAN-2026-10-31.md) | Kế hoạch + bảng điểm đợt thu thập dữ liệu 2026-10-31 (đã đóng) |

### Dinh dưỡng và thuật toán

| Tài liệu | Nội dung |
|---|---|
| [06-nutrition/CALORIE-ENGINE.md](06-nutrition/CALORIE-ENGINE.md) | Công thức tính calo, hệ số hao hụt, hấp thụ dầu, công thức TDEE |
| [06-nutrition/ACCURACY-SPEC.md](06-nutrition/ACCURACY-SPEC.md) | Định nghĩa "chính xác >80%", phương pháp đo, tiêu chí nghiệm thu |
| [07-recommendation/RECOMMENDATION-ENGINE.md](07-recommendation/RECOMMENDATION-ENGINE.md) | Logic nút Lắc: luật lọc, chấm điểm, chống lặp món |
| [08-cultural/NGU-HANH-AM-DUONG.md](08-cultural/NGU-HANH-AM-DUONG.md) | Mô hình dữ liệu tính vị, cách tổng hợp lên món, giới hạn pháp lý |

### Thực thi

| Tài liệu | Nội dung |
|---|---|
| [05-tasks/ROADMAP.md](05-tasks/ROADMAP.md) | **Bắt đầu từ đây khi tiếp nhận dự án.** Đã làm gì, đang làm gì, làm gì tiếp |
| [05-tasks/DEFINITION-OF-DONE.md](05-tasks/DEFINITION-OF-DONE.md) | Tiêu chí "xong" cho từng loại task |
| [05-tasks/TASK-TEMPLATE.md](05-tasks/TASK-TEMPLATE.md) | Mẫu tạo task mới |
| [05-tasks/tasks/](05-tasks/tasks/) | Chi tiết từng task |
| [09-standards/CODING-STANDARDS.md](09-standards/CODING-STANDARDS.md) | Quy ước code, đặt tên bảng/cột |
| [09-standards/RESEARCH-PROTOCOL.md](09-standards/RESEARCH-PROTOCOL.md) | **Quy tắc tra cứu dữ liệu**: chính sách nền, thứ tự nguồn, endpoint, bẫy kỹ thuật, cách tra món và công thức |
| [09-standards/EDITORIAL-GUIDE.md](09-standards/EDITORIAL-GUIDE.md) | Chuẩn biên soạn dữ liệu: đặt tên món, phân biệt biến thể, phân vùng miền, các bẫy đã gặp |
| [09-standards/DESIGN-SYSTEM.md](09-standards/DESIGN-SYSTEM.md) | **Hệ thiết kế v2 (UI-004)**: token màu, ngữ nghĩa huy hiệu, component `ld-*`, bố cục trang chi tiết và thẻ danh sách chuẩn (món ăn, nguyên liệu, hoa quả làm chuẩn), danh sách lệch chuẩn cần sửa |
| [10-deployment/DEPLOYMENT.md](10-deployment/DEPLOYMENT.md) | Dựng lại toàn bộ dự án trên máy mới (bản 2026-08-31; giữ checklist bàn giao + production) |
| [10-deployment/SETUP-DEV.md](10-deployment/SETUP-DEV.md) | **Cài môi trường dev trên máy khác — bản hiện hành**: điều kiện, MySQL 8, seed, tài khoản admin, chạy, kiểm thử, troubleshooting |
| [10-deployment/GOLIVE-DOCKER.md](10-deployment/GOLIVE-DOCKER.md) | **Go-live production bằng Docker — bản hiện hành**: kiến trúc prod, chuẩn bị VPS, cấu hình `.env.production`, HTTPS/domain, nghiệm thu, vận hành, backup/rollback, checklist bảo mật |

---

## Quy tắc viết tài liệu

1. **Tiếng Việt.** Thuật ngữ kỹ thuật giữ nguyên tiếng Anh (migration, seeder, embedding).
2. **Ghi ngày cập nhật** ở đầu mỗi file, kèm một dòng nói rõ lần này đổi gì.
3. **Không xoá thông tin sai — đính chính nó.** Dùng khối:
   ```markdown
   > ⚠️ **Đính chính 2026-09-15 — đoạn dưới đây SAI.**
   > [nêu rõ sai chỗ nào, vì sao, và cái đúng là gì]
   ```
   Lý do: người kế nhiệm cần biết dự án đã từng hiểu sai chỗ nào, nếu không họ sẽ mắc lại.
4. **Mọi con số phải có nguồn.** Ghi URL hoặc tên tài liệu. Con số không nguồn coi như không có.
5. **Phân biệt rõ "đã có" và "dự kiến".** Mọi thứ chưa hiện thực phải đánh dấu
   `⚠️ CHƯA HIỆN THỰC`. Đây là lỗi tài liệu nguy hiểm nhất: người đọc tưởng có sẵn rồi đi tìm mãi.
6. **Liên kết chéo bằng đường dẫn tương đối** để click được trên cả GitHub lẫn editor.
