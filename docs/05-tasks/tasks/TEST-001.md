# [TEST-001] — Test các ràng buộc toàn vẹn R1–R9

**Mã task:** TEST-001 · **Giai đoạn:** GĐ1 · **Loại:** Test
**Status:** ⬜ TODO · **Ngày tạo:** 2026-08-31 · **Phụ thuộc:** SETUP-003

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

- [ ] 9 test, mỗi test cố tình vi phạm một ràng buộc
- [ ] **Tất cả đều pass** — nghĩa là database từ chối được
- [ ] Chạy trong CI, chặn merge nếu đỏ
- [ ] Test R1 có comment trỏ về QT-1 trong CLAUDE.md

## Cạm bẫy

> **Quy tắc chung về cổng chất lượng:** thêm cổng nào cũng phải kiểm chứng nó **đỏ được**. Nếu ràng
> buộc chưa được tạo trong migration mà test vẫn "pass" vì lý do khác, bạn có một cổng chất lượng
> luôn xanh — bằng chứng giả, tệ hơn không có gì. Chạy thử test **trước khi** tạo ràng buộc, xác nhận
> nó đỏ, rồi mới tạo ràng buộc và xác nhận nó xanh.

- Test phải kiểm ở tầng **database**, không phải tầng validation của Laravel. Mục đích là chặn cả khi
  code có lỗi.

## Ghi chú
