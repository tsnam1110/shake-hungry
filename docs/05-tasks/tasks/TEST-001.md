# [TEST-001] — Test các ràng buộc toàn vẹn R1–R9

**Mã task:** TEST-001 · **Giai đoạn:** GĐ1 · **Loại:** Test
**Status:** ✅ DONE (2026-08-31) — CI xem Ghi chú · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** SETUP-003

---

## Mục tiêu

Chứng minh 9 ràng buộc toàn vẹn ở tầng database **thực sự chặn được** dữ liệu sai. Đây là hiện thực
kỹ thuật của các quy tắc tuyệt đối — không dựa vào kỷ luật con người.

## Bối cảnh

Danh sách ràng buộc: [DATA-MODEL §6](../../03-data-model/DATA-MODEL.md).

| # | Ràng buộc | Bảo vệ |
|---|---|---|
| R1 | `dish_nutrition.source <> 'llm_suggested'` | **QT-1** |
| R2 | `kcal_min <= kcal_max` | QT-5 |
| R3 | `missing_ingredients` khác rỗng ⇒ `confidence <> 'high'` | QT-5 |
| R4 | `dish_media.license` NOT NULL | QT-2 |
| R5 | `ingredient_tcm_traits.source_ref` NOT NULL | QT-6 |
| R6 | `completeness_level >= 2` ⇒ có ≥1 nguyên liệu | PRD §7 |
| R7 | `mapping_confidence <> 'high'` ⇒ `mapping_note` NOT NULL | truy nguyên |
| R8 | `completeness_level >= 2` ⇒ có ≥3 dòng `dish_recipe_sources` | truy nguyên công thức |
| R9 | `dish_ingredients.ingredient_id` trỏ tới nguyên liệu có thật | chống slug lỗi thời |

## Việc cần làm

Với **mỗi** ràng buộc, viết một test cố tình vi phạm và khẳng định database **từ chối**:

```php
it('chan ban ghi dinh duong do LLM sinh ra', function () {
    expect(fn () => DishNutrition::create([
        'dish_id' => $dish->id,
        'source' => 'llm_suggested',   // vi phạm R1
        'kcal_min' => 300, 'kcal_max' => 400,
    ]))->toThrow(QueryException::class);
});
```

## Definition of Done

- [x] 9 test vi phạm + 1 test "bản ghi hợp lệ đi qua" (chống cổng luôn-đỏ) —
      `tests/Feature/IntegrityConstraintsTest.php`
- [x] **Tất cả pass** trên MySQL thật (phpunit.xml ép `mysql`, không dùng sqlite —
      sqlite không tái hiện được CHECK/trigger)
- [x] Workflow CI đã viết (`.github/workflows/tests.yml`, service MySQL 8) —
      **chưa kiểm chứng chạy trên GitHub** vì repo chưa có remote; xem Ghi chú
- [x] Test R1 có comment trỏ về QT-1 trong CLAUDE.md

## Cạm bẫy

> **Quy tắc chung về cổng chất lượng:** thêm cổng nào cũng phải kiểm chứng nó **đỏ được**. Nếu ràng
> buộc chưa được tạo trong migration mà test vẫn "pass" vì lý do khác, bạn có một cổng chất lượng
> luôn xanh — bằng chứng giả, tệ hơn không có gì. Chạy thử test **trước khi** tạo ràng buộc, xác nhận
> nó đỏ, rồi mới tạo ràng buộc và xác nhận nó xanh.

- Test phải kiểm ở tầng **database**, không phải tầng validation của Laravel. Mục đích là chặn cả khi
  code có lỗi.

## Ghi chú

**Hoàn thành 2026-08-31.**

- R6/R8 hiện thực bằng trigger MySQL (AFTER UPDATE/DELETE — migration 100008). Test ép
  kiểm bằng `forceCompletenessCheck()` trong `tests/Pest.php`: chạm một UPDATE giữ
  nguyên giá trị vào món để trigger AFTER UPDATE chạy ngay trong transaction test.
- Tính "đỏ được" chứng minh bằng chính cơ chế `toThrow`: nếu ràng buộc không tồn tại,
  insert thành công → không có exception → test fail. Kèm test thứ 10 chứng minh bản ghi
  hợp lệ đi qua — cổng không luôn-đỏ.
- **CI:** workflow `.github/workflows/tests.yml` dùng service `mysql:8.0`.
  Repo hiện chưa có remote GitHub nên chưa thấy nó chạy thật —
  khi push lần đầu phải mở tab Actions xác nhận xanh, và bật branch protection để nó
  chặn merge được.
