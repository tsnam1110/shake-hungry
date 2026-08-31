# Lắc Đói

> Bấm một nút, hệ thống gợi ý món nên ăn.

Ứng dụng mã nguồn mở gợi ý món ăn theo quốc gia, vùng miền, bữa trong ngày, hình thức (ăn ngoài hay
tự nấu), món đã ăn bữa trước, và thể trạng người dùng. Món tự nấu có công thức và calo tính từ bảng
thành phần thực phẩm; món ăn ngoài có calo ước lượng kèm độ tin cậy.

**Trạng thái (2026-08-31):** 🚧 Đang ở Giai đoạn 1 — **55/250 nguyên liệu** đã biên soạn trong
`data/ingredients/`; **chưa có dòng code nào**, nên dữ liệu chưa từng được nạp vào database.
Xem [ROADMAP](docs/05-tasks/ROADMAP.md).

---

## Dự án này thực chất là gì

Nút Lắc chỉ là vài chục dòng code. **Giá trị của dự án nằm hoàn toàn ở cơ sở dữ liệu món ăn và
nguyên liệu được biên soạn thủ công, có kiểm chứng, và ở engine tính calo dựa trên dữ liệu đó.**

Vì vậy thứ tự làm việc là: **nguyên liệu → engine calo → công cụ nhập liệu → dữ liệu món → nút Lắc.**

## Nguyên tắc

- **Không bịa số.** Calo do hàm thuần tuý tính từ bảng thành phần thực phẩm. LLM chỉ hỗ trợ ánh xạ và
  định lượng, không bao giờ sinh ra con số dinh dưỡng.
- **Không giấu độ bất định.** Mọi giá trị dinh dưỡng đi kèm khoảng min–max và độ tin cậy. Không hiển
  thị "437 kcal" cho một món ăn ngoài.
- **Không dùng dữ liệu license bẩn.** Mọi nguồn đều được đối chiếu license trước khi nhập. Nhiều bộ
  dataset công thức nổi tiếng chỉ cho phép dùng phi thương mại — dự án này không dùng.
- **Mọi tài nguyên nằm trong repo.** Clone về máy trắng, chạy seed, ra đúng y hệt dữ liệu gốc.

## Tài liệu

Bắt đầu ở [docs/README.md](docs/README.md). Nếu bạn là AI agent hoặc dev mới tiếp nhận dự án, đọc
[CLAUDE.md](CLAUDE.md) trước tiên — đó là file quy tắc bắt buộc.

## Stack

Laravel 12+ · PostgreSQL 16+ (pg_trgm, pgvector) · Filament 3+ · Livewire · Laragon trên Windows.

## Giấy phép

Chưa chốt — xem [LICENSE-COMPLIANCE](docs/04-data-sources/LICENSE-COMPLIANCE.md) §6. Lưu ý: việc dùng
Open Food Facts (ODbL) và Wikibooks (CC BY-SA) có ràng buộc share-alike ảnh hưởng tới lựa chọn này,
nên phải quyết định **trước** khi nhập các nguồn đó.
