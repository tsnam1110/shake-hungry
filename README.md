# Lắc Đói

> Bấm một nút, hệ thống gợi ý món nên ăn.

Ứng dụng mã nguồn mở gợi ý món ăn theo quốc gia, vùng miền, bữa trong ngày, hình thức (ăn ngoài hay
tự nấu), món đã ăn bữa trước, và thể trạng người dùng. Món tự nấu có công thức và calo tính từ bảng
thành phần thực phẩm; món ăn ngoài có calo ước lượng kèm độ tin cậy.

**Trạng thái (2026-11-03, kiểm từ file + seed + validate):** ✅ Ứng dụng hoàn chỉnh hai phía — trang người dùng (Lắc món 3 tầng,
lọc theo nhãn phân loại và dị ứng, tài khoản + TDEE + nhật ký bữa ăn, đóng góp cộng đồng) và trang
quản trị `/admin` (Ant Design 6 + React 19). Kho dữ liệu: **648 món, 478 nguyên liệu** (415 có số dinh dưỡng có nguồn),
**82 hồ sơ quả, 38 cặp kết hợp cần lưu ý, 99 hồ sơ rau theo mùa, 230 entry nước uống** (169 pha chế + 61 đóng gói);
toàn bộ đã duyệt theo lệnh chủ dự án ngày 2026-11-03 (mục nào lỗi chủ dự án tự lật lại thành chưa duyệt).
Validate: **3.392 bản ghi — 0 lỗi, 22 cảnh báo** (`php artisan lacdoi:validate-data` sau `migrate:fresh --seed`).
Số cũ 496/386 (2026-10-31) đã lỗi thời — xem [ROADMAP](docs/05-tasks/ROADMAP.md)
và [nhật ký đo](docs/06-nutrition/ACCURACY-SPEC.md).

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

## Chạy dự án

Local/dev: [SETUP](docs/10-deployment/SETUP.md). VPS: [DEPLOY](docs/10-deployment/DEPLOY.md), chạy `bash deploy.sh`; MySQL riêng, web `127.0.0.1:1113`.

## Stack

Laravel 13 · MySQL 8 · Ant Design (antd) 6 + React 19 cho trang quản trị (GĐ2) ·
Blade + JavaScript thuần · Pest · Laragon trên Windows.

> ⚠️ **Đính chính 2026-11-07:** dòng stack cũ ghi Livewire là sai; dự án dùng Blade + JavaScript thuần (UI-010).

## Ghi công nguồn dữ liệu

- **USDA FoodData Central** — SR Legacy, FNDDS/SR Links, Table of Cooking Yields for Meat and
  Poultry R2. Public domain / CC0. *U.S. Department of Agriculture, Agricultural Research Service.
  FoodData Central.* [fdc.nal.usda.gov](https://fdc.nal.usda.gov)
- **Wikidata** — danh mục món Việt gieo mầm. CC0 1.0.
- **Nutrition5k** (Google Research) — dùng để kiểm tra số học của engine.
  CC BY 4.0 — *Thames, Q., Karpur, A., Norris, W., Xia, F., Panait, L., Weyand, T., Sim, J.
  "Nutrition5k: Towards Automatic Nutritional Understanding of Generic Food", CVPR 2021.*

## Giấy phép

Đã chốt ngày 2026-10-31 tại [LEGAL-002](docs/05-tasks/tasks/LEGAL-002.md):

- **Mã nguồn:** [MIT](LICENSE).
- **Dữ liệu (`data/`):** cơ sở dữ liệu tập hợp (collective database). Phần do dự án tự biên soạn
  theo **CC BY 4.0** (ghi công "Lắc Đói"). Một số ít dòng chép từ nhãn sản phẩm tra qua
  Open Food Facts giữ **ODbL 1.0** kèm ghi công Open Food Facts — các dòng này nhận diện được
  qua trường `license_note` trong từng file nguyên liệu. Dữ liệu USDA là public domain (CC0);
  các nguồn thứ cấp (Bảng TPTP Việt Nam, FNRI, MEXT, IFCT…) chỉ tra cứu từng mục kèm trích dẫn,
  không sao chép hàng loạt — xem [LICENSE-COMPLIANCE](docs/04-data-sources/LICENSE-COMPLIANCE.md).

## Trang quản trị

Công cụ biên soạn dữ liệu chạy tại `/admin` (React 19 + antd 6, đăng nhập
email/mật khẩu, hai vai trò admin/editor). Tài khoản quản trị được tạo riêng
tại từng máy phát triển — mật khẩu không nằm trong repo. Xem
[ADM-001](docs/05-tasks/tasks/ADM-001.md).


> **Đăng nhập nhanh kiểm thử (chỉ dev cục bộ):** đặt `ADMIN_QUICK_LOGIN=true`
> trong `.env` để trang đăng nhập quản trị hiện nút "Vào thẳng quản trị" —
> vào thẳng tài khoản admin đầu tiên **không cần mật khẩu**, phục vụ kiểm thử.
> Mặc định tắt, mỗi lần dùng đều ghi log cảnh báo. **Tuyệt đối không bật ở
> môi trường thật.**
