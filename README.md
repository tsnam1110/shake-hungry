# Lắc Đói

> Bấm một nút, hệ thống gợi ý món nên ăn.

Ứng dụng mã nguồn mở gợi ý món ăn theo quốc gia, vùng miền, bữa trong ngày, hình thức (ăn ngoài hay
tự nấu), món đã ăn bữa trước, và thể trạng người dùng. Món tự nấu có công thức và calo tính từ bảng
thành phần thực phẩm; món ăn ngoài có calo ước lượng kèm độ tin cậy.

**Trạng thái (2026-08-31):** 🚧 Giai đoạn 1 — **255/250 nguyên liệu** đã biên soạn, engine calo đã
chạy và **qua kiểm chứng đầu tiên** (89,7% số món FNDDS trong ±20%; nhóm chiên chưa đo được), 61 món
gieo mầm từ Wikidata ở trạng thái nháp. Xem [ROADMAP](docs/05-tasks/ROADMAP.md) và
[nhật ký đo](docs/06-nutrition/ACCURACY-SPEC.md).

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

Laravel 13 · MySQL 8 (từ 2026-10-30, thay PostgreSQL — xem ARCHITECTURE §4) · Filament 3+ (GĐ2) ·
Livewire · Pest · Laragon trên Windows.

## Ghi công nguồn dữ liệu

- **USDA FoodData Central** — SR Legacy, FNDDS/SR Links, Table of Cooking Yields for Meat and
  Poultry R2. Public domain / CC0. *U.S. Department of Agriculture, Agricultural Research Service.
  FoodData Central.* [fdc.nal.usda.gov](https://fdc.nal.usda.gov)
- **Wikidata** — danh mục món Việt gieo mầm. CC0 1.0.
- **Nutrition5k** (Google Research) — dùng để kiểm tra số học của engine.
  CC BY 4.0 — *Thames, Q., Karpur, A., Norris, W., Xia, F., Panait, L., Weyand, T., Sim, J.
  "Nutrition5k: Towards Automatic Nutritional Understanding of Generic Food", CVPR 2021.*

## Giấy phép

Chưa chốt — xem [LICENSE-COMPLIANCE](docs/04-data-sources/LICENSE-COMPLIANCE.md) §6. Lưu ý: việc dùng
Open Food Facts (ODbL) và Wikibooks (CC BY-SA) có ràng buộc share-alike ảnh hưởng tới lựa chọn này,
nên phải quyết định **trước** khi nhập các nguồn đó.
