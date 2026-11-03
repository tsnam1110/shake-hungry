# AGENTS.md — Lắc Đói (shake-hungry)

Hướng dẫn cho mọi AI agent (Claude Code, Codex, Cursor, Copilot Agent...) làm việc trên repo này.

> **Toàn bộ quy tắc nằm ở [CLAUDE.md](CLAUDE.md). Đọc file đó trước, không có ngoại lệ.**
> File này chỉ là bản tóm tắt cực ngắn để agent không đọc `CLAUDE.md` vẫn không phá hỏng dự án.

---

## Tóm tắt 60 giây

**Đây là dự án biên soạn cơ sở dữ liệu món ăn, có kèm web app ở đầu ra.** Không phải dự án web app.
Nút "Lắc" gợi ý món chỉ là vài chục dòng code; giá trị nằm ở dữ liệu.

**Trạng thái 2026-11-03 (kiểm từ file + seed + validate, thay số cũ 2026-10-31): ứng dụng đã hoàn thiện end-to-end** (Laravel 13 + MySQL 8, admin antd 6
tại `/admin`, 648 món / 436 nguyên liệu / 82 quả / 38 cặp kết hợp / 99 rau theo mùa / 230 nước uống,
toàn bộ đã duyệt theo lệnh chủ dự án 2026-11-03; validate 3.368 bản ghi — 0 lỗi, 22 cảnh báo),
engine gợi ý + tài khoản người dùng + đóng góp cộng đồng đều chạy thật, cộng thêm trang riêng Bảng kết hợp cần lưu ý,
Rau theo mùa và Thư viện nước uống. Trạng thái chi tiết: [CLAUDE.md](CLAUDE.md) §0 và
[docs/05-tasks/ROADMAP.md](docs/05-tasks/ROADMAP.md).

## Tám quy tắc tuyệt đối (bản rút gọn — bản đầy đủ ở CLAUDE.md §3)

1. **QT-1** — LLM không bao giờ được sinh con số dinh dưỡng. Chỉ được đề xuất ánh xạ nguyên liệu,
   định lượng gram, và chuẩn hoá tên. Calo do hàm thuần tuý tính từ bảng tham chiếu.
2. **QT-2** — Không nhập dữ liệu license bẩn. Cấm: RecipeNLG, Recipe1M, Food.com/Epicurious trên
   Kaggle, FAO/INFOODS, 30VNFoods. Không fork Tandoor/Mealie/KitchenOwl/RecipeSage.
3. **QT-3** — Mọi dữ liệu phải nằm trong `data/` dưới dạng file có version. Database chỉ là bản dựng
   lại; không phải nguồn sự thật.
4. **QT-4** — Một bảng thành phần thực phẩm duy nhất cho mỗi phép tính. Không trộn nguồn.
5. **QT-5** — Không hiển thị con số chính xác giả. Dinh dưỡng luôn có `min`, `max`, `confidence`.
6. **QT-6** — Ngũ hành/âm dương gán ở tầng nguyên liệu, là lớp phụ, khung văn hoá chứ không phải
   khung y tế.
7. **QT-7** — Sửa code phải sửa tài liệu trong cùng commit. Tài liệu sai thì chèn khối "Đính chính",
   không xoá lặng lẽ.
8. **QT-8** — Trước khi thực thi phải phân tích kỹ yêu cầu và kiểm tra điều kiện ngoại vi + mức
   phù hợp với dự án (QT-1…QT-7, kiến trúc, dữ liệu, phụ thuộc, rủi ro). Việc lớn / khó đảo ngược /
   đổi hướng phải trình phân tích và chờ chủ dự án chốt, không tự ý làm.

## Bốn việc dễ làm sai nhất

- Làm nút Lắc trước khi có dữ liệu → dự án chết vì DB rỗng.
- Nhập dữ liệu theo món thay vì theo nguyên liệu → làm lại cùng một việc hàng trăm lần.
- Để LLM điền calo cho nhanh → phá thẳng mục tiêu chính của dự án.
- Bắt tay làm ngay khi chưa phân tích yêu cầu và chưa kiểm tra điều kiện ngoại vi (QT-8) → làm sai hướng, phải làm lại.

## Trước khi báo cáo hoàn thành

- [ ] Đã cập nhật status ở **cả** file task **và** [ROADMAP](docs/05-tasks/ROADMAP.md)
- [ ] Đã đối chiếu [DEFINITION-OF-DONE](docs/05-tasks/DEFINITION-OF-DONE.md)
- [ ] Nếu đổi schema: đã sửa [DATA-MODEL](docs/03-data-model/DATA-MODEL.md)
- [ ] Nếu thêm dữ liệu: file đã nằm trong `data/`, và `migrate:fresh --seed` dựng lại được
