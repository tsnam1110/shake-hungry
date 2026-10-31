# Engine gợi ý — nút Lắc

**Cập nhật lần cuối:** 2026-10-31
**Trạng thái:** ✅ **ĐÃ HIỆN THỰC** — engine gợi ý 3 tầng (task [REC-001](../05-tasks/tasks/REC-001.md)
✅ DONE) đang chạy ở trang chủ, dị ứng là lọc cứng không bao giờ nới, có test chứng minh.

---

## 1. Đặt kỳ vọng đúng

**Đây là phần nhỏ nhất của dự án.** Không phải phần khó, không phải lợi thế cạnh tranh.

Khảo sát mã nguồn mở ngày 2026-08-31: toàn bộ danh mục dự án "meal recommender" là đồ án đã ngừng
phát triển (`meal-planner` dừng 2016, `yumme` của Cornell dừng 2020). Bản triển khai đạt chuẩn sản
phẩm duy nhất nằm trong Mealie, và nó chỉ là: các luật khớp `(thứ trong tuần, loại bữa)`, AND các
chuỗi filter lại, rồi truy vấn `order_by=random` với seed để phân trang ổn định. **Khoảng 60 dòng.**

⇒ Đừng dành 3 tháng cho engine gợi ý. Dành 3 tháng cho dữ liệu. Engine tốt trên dữ liệu nghèo vẫn cho
gợi ý nghèo; engine đơn giản trên dữ liệu tốt đã đủ dùng.

**Cột mốc:** một bản nút Lắc thô sơ nên được làm ngay ở GĐ1 (xấu cũng được) để có thứ nhìn thấy được
mà giữ động lực. Bản chính thức ở GĐ4.

---

## 2. Kiến trúc ba tầng

```
Toàn bộ món đã published
        │
        ▼
┌──────────────────────────────┐
│ TẦNG 1 — LỌC CỨNG            │  loại bỏ, không thương lượng
│ • bộ lọc người dùng chọn     │
│ • dị ứng / không ăn được     │
│ • chế độ ăn (chay…)          │
│ • completeness_level ≥ 1     │
└──────────────┬───────────────┘
               ▼
┌──────────────────────────────┐
│ TẦNG 2 — LOẠI TRỪ GẦN ĐÂY    │  chống lặp
│ • N món vừa ăn (mặc định 10) │
│ • món cùng nguyên liệu chính │
│   trong 2 bữa gần nhất       │
└──────────────┬───────────────┘
               ▼
┌──────────────────────────────┐
│ TẦNG 3 — CHẤM ĐIỂM           │  xếp hạng mềm
│ • phù hợp bữa (weight)       │
│ • phù hợp calo còn lại       │
│ • đa dạng so với gần đây     │
│ • ưu tiên dữ liệu đầy đủ     │
└──────────────┬───────────────┘
               ▼
   Chọn ngẫu nhiên có trọng số
   trong nhóm 20% điểm cao nhất
```

**Vì sao tầng 3 không chọn thẳng món điểm cao nhất:** vì đây là **nút Lắc** — người dùng mong đợi sự
bất ngờ. Luôn ra cùng một món là hỏng trải nghiệm. Ngẫu nhiên có trọng số trong nhóm dẫn đầu giữ được
cả tính phù hợp lẫn tính bất ngờ.

---

## 3. Tầng 1 — Lọc cứng

| Bộ lọc | Nguồn | Ghi chú |
|---|---|---|
| Quốc gia | Người dùng chọn | Mặc định VN ở v1 |
| Vùng miền | Người dùng chọn | Theo cây `regions` |
| Ăn ngoài / tự nấu | Người dùng chọn | Có thể chọn cả hai |
| Bữa | Tự suy từ giờ hiện tại, người dùng đổi được | <10h→sáng; 10–14h→trưa; 14–17h→chiều; >17h→tối |
| Dị ứng / không ăn | `user_profiles.excluded_ingredients` | **Không bao giờ bỏ qua** |
| Chế độ ăn | `user_profiles.diet_type` | Chay → loại món có nguyên liệu động vật |
| Trạng thái | Hệ thống | Chỉ `status='published'` |

> ⚠️ **Lọc dị ứng là ràng buộc an toàn, không phải tuỳ chọn.** Món có `missing_ingredients` khác rỗng
> **không được** gợi ý cho người dùng có khai báo dị ứng — vì ta không biết chắc món đó chứa gì.
> Thà không gợi ý còn hơn gợi ý sai.

---

## 4. Tầng 2 — Loại trừ

```
Loại bỏ món có mặt trong meal_logs của N bữa gần nhất  (N mặc định = 10)
Loại bỏ món cùng parent_dish_id với món vừa ăn
Hạ điểm mạnh món có cùng nguyên liệu chính với 2 bữa gần nhất
```

Yêu cầu gốc *"dựa theo món ăn bữa trước để gợi ý món ăn cho bây giờ"* được hiện thực ở đây và ở
tầng 3, chứ không phải bằng một mô hình học máy. Ăn phở sáng thì trưa không gợi ý bún bò — vì cùng
nhóm nước dùng/sợi, không phải vì mô hình học được điều đó.

**Trường hợp biên phải xử lý:** nếu sau khi lọc và loại trừ không còn món nào, **nới lỏng dần** theo
thứ tự: (1) giảm N xuống 5 → (2) giảm N xuống 0 → (3) bỏ lọc vùng miền → (4) báo "chưa có món phù
hợp, hãy nới bộ lọc". **Không bao giờ nới lỏng lọc dị ứng/chế độ ăn.**

---

## 5. Tầng 3 — Chấm điểm

```
điểm = w1·phù_hợp_bữa
     + w2·phù_hợp_calo
     + w3·đa_dạng
     + w4·đầy_đủ_dữ_liệu
```

| Thành phần | Cách tính | Trọng số khởi đầu |
|---|---|---|
| `phù_hợp_bữa` | `dish_meal_slots.weight` của bữa hiện tại, chuẩn hoá về 0–1 | 0,40 |
| `phù_hợp_calo` | Khoảng cách giữa calo món và calo còn lại trong ngày của người dùng | 0,25 |
| `đa_dạng` | 1 − độ tương tự cosine giữa `dishes.embedding` của món và trung bình embedding 5 bữa gần nhất | 0,25 |
| `đầy_đủ_dữ_liệu` | `completeness_level`/3 — nhẹ nhàng ưu tiên món có công thức và calo | 0,10 |

> ⚠️ Trọng số là **giá trị khởi đầu chưa hiệu chuẩn**. Hiệu chỉnh sau khi có dữ liệu người dùng thật.
> Đừng tinh chỉnh chúng khi kho món còn dưới 150 — không đủ dữ liệu để biết là tốt hơn hay chỉ là
> ngẫu nhiên.

### `phù_hợp_calo` tính thế nào

```
calo_còn_lại = TDEE_ước_tính − tổng_calo_đã_ăn_hôm_nay
calo_mục_tiêu_bữa = calo_còn_lại × tỷ_lệ_bữa
   (sáng 25%, trưa 35%, chiều 10%, tối 30% — điều chỉnh được)
phù_hợp_calo = 1 − min(1, |calo_món − calo_mục_tiêu_bữa| / calo_mục_tiêu_bữa)
```

Vì TDEE là **khoảng** ([CALORIE-ENGINE §8](../06-nutrition/CALORIE-ENGINE.md)), dùng giá trị giữa
khoảng và **không hiển thị phép tính này như một chỉ dẫn dinh dưỡng chính xác**. Nó chỉ để xếp hạng
nội bộ.

### `đa_dạng` và embedding

`dishes.embedding` sinh từ: tên món + nguyên liệu chính + cách chế biến + vùng miền
(xem [REC-002](../05-tasks/tasks/REC-002.md)).

Nếu chưa có embedding (giai đoạn đầu), thay bằng heuristic: khác `cooking_method` và khác nguyên liệu
chính so với bữa trước. Engine phải chạy được **không cần** embedding — đừng để GĐ4 bị chặn bởi việc
sinh embedding.

---

## 6. Tính lặp lại

Nút Lắc bấm lại phải ra món khác, nhưng cùng một trạng thái phải tái lập được để debug.

```
seed = hash(user_id, ngày, bữa, số_lần_lắc)
```

Ghi `seed` vào log mỗi lần gợi ý. Khi người dùng báo "nó gợi ý món kỳ quặc", có seed là dựng lại được
đúng tình huống đó.

---

## 7. Những gì KHÔNG làm

| Không làm | Vì sao |
|---|---|
| Collaborative filtering ("người giống bạn cũng thích…") | Cần hàng nghìn người dùng mới có tín hiệu. Ở v1 là vô nghĩa |
| Mô hình học máy để xếp hạng | Không có dữ liệu huấn luyện, không có cách đánh giá |
| Gợi ý theo thời tiết / mùa | Ý tưởng hay, nhưng để v2. Có thể làm rẻ qua lớp tính vị (GĐ6) |
| Cá nhân hoá theo mục tiêu sức khoẻ ("giảm cân") | Chạm vào tuyên bố y tế — xem [ACCURACY-SPEC §5](../06-nutrition/ACCURACY-SPEC.md) |
| A/B test thuật toán | Chưa đủ người dùng để có ý nghĩa thống kê |

---

## 8. Đo chất lượng gợi ý

Không có "độ chính xác" cho gợi ý món ăn. Đo bằng hành vi:

| Chỉ số | Ý nghĩa | Ngưỡng khởi đầu |
|---|---|---|
| Tỷ lệ chấp nhận | % lần bấm Lắc mà người dùng đánh dấu "đã ăn" | ≥25% |
| Số lần lắc lại trung bình | Lắc bao nhiêu lần mới ưng | ≤3 |
| Tỷ lệ lặp món trong 7 ngày | Cùng món xuất hiện lại quá sớm | ≤15% |
| Độ phủ kho món | % số món từng được gợi ý trong 30 ngày | ≥60% |

**Chỉ số độ phủ quan trọng hơn vẻ ngoài của nó:** nếu engine chỉ gợi ý đi gợi ý lại 30 món trong khi
kho có 500, thì 470 món kia là công sức biên soạn bị lãng phí.
