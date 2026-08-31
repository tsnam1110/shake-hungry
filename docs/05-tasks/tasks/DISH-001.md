# [DISH-001] — 50 món đầu tiên

**Mã task:** DISH-001 · **Giai đoạn:** GĐ3 · **Loại:** Dữ liệu
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** ADM-007

---

## Mục tiêu

Mẻ món đầu tiên. Ngoài việc tạo dữ liệu, mẻ này còn để **kiểm chứng quy trình trong thực tế** trước
khi tăng tốc.

## Việc cần làm

Chọn 50 món phổ biến nhất, cân đối:

- Theo bữa: sáng ~15, trưa ~20, tối ~15
- Theo hình thức: ăn ngoài ~30, tự nấu ~20 (nhiều món thuộc cả hai)
- Theo vùng: Bắc / Trung / Nam đều có
- Theo chế biến: **bắt buộc có món chiên/rán** — để phát hiện sớm lỗi hệ số dầu

Món ăn ngoài dừng ở Mức 1. Món tự nấu làm tới Mức 2.

## Definition of Done

- [ ] 50 món, tất cả `status = 'published'`, không còn `needs_review`
- [ ] **Món Mức 2 đều có ≥3 `recipe_sources`** (ràng buộc R8) — xem
      [RESEARCH-PROTOCOL §11](../../09-standards/RESEARCH-PROTOCOL.md)
- [ ] **`recipe_steps` viết bằng lời của mình**, không chép từ nguồn nào
- [ ] Mọi `ingredient_slug` trỏ tới nguyên liệu có thật (ràng buộc R9)
- [ ] Món Mức 2 đều có calo tính được (hoặc `missing_ingredients` được giải thích)
- [ ] `lacdoi:validate-data` sạch lỗi 🔴
- [ ] `lacdoi:export-data` → commit → `migrate:fresh --seed` dựng lại đủ 50 món
- [ ] Đã chạy rà soát chất lượng [QA-001](./QA-001.md) lần đầu
- [ ] Thời gian trung bình mỗi món ghi vào Ghi chú (đối chiếu với ADM-007)

## Cạm bẫy

- **Mẻ đầu là lúc phát hiện sai sót có hệ thống** — nhầm đơn vị nguyên liệu hay dùng, hệ số dầu sai,
  quy tắc đặt tên không nhất quán. Dừng lại sửa gốc ngay, đừng để lan sang 450 món sau.
- Nếu thời gian thực tế cao hơn ADM-007 nhiều, quay lại sửa công cụ chứ đừng cắn răng nhập tiếp.

## Ghi chú
