# Lớp âm dương – ngũ hành

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Trạng thái:** ⚠️ **CHƯA HIỆN THỰC.** Thuộc GĐ6, là **lớp tuỳ chọn**, không phải chức năng lõi.

---

## 1. Vị trí trong hệ thống

Đây là **lớp phụ, tắt được, nằm trên cùng**. Nó:

- **Không** ảnh hưởng tới engine tính calo.
- **Không** được phép lọc bỏ món ở tầng lọc cứng của engine gợi ý.
- Chỉ tham gia ở tầng chấm điểm, và chỉ khi người dùng **chủ động bật**.
- Mặc định **tắt**.

Lý do thiết kế như vậy: nếu để nhãn tính vị ngấm vào lõi, sau này không tắt được, và mọi con số dinh
dưỡng sẽ bị nghi ngờ lây. Tách bạch từ đầu là cách duy nhất giữ được cả hai.

---

## 2. Phát hiện quan trọng: không có dữ liệu mở nào

Khảo sát ngày 2026-08-31:

> **Không tồn tại bộ dữ liệu mở, có cấu trúc, ánh xạ thực phẩm → âm dương / ngũ hành / tứ khí / ngũ
> vị — bằng tiếng Anh, tiếng Trung, hay tiếng Việt.**

| Nơi đã tìm | Kết quả |
|---|---|
| GitHub (tìm "TCM food nature cold hot dataset") | **0 kết quả** |
| Wikidata | **Không có property nào** cho tính, vị, hay quy kinh |
| Kaggle | Chỉ có tập NER văn bản y học cổ truyền, không phải bảng tính vị thực phẩm |
| ETCM v2.0 (2.005 vị thuốc, có tính/vị/quy kinh) | ✅ Có dữ liệu — nhưng là **dược liệu, không phải thực phẩm** |
| SymMap, TCMSP | Như trên — dược liệu |
| `TCM_knowledge_graph` (GitHub) | **Chỉ có code, không có dữ liệu, không có license** |
| Liu et al. 2012, *Nutrition & Dietetics* 69(1):64–68 — 284 thực phẩm gán hàn/nhiệt | ✅ Là **thực phẩm** — nhưng **không phát hành dữ liệu**, bảng nằm trong bài báo |

⇒ **Lớp này phải tự biên soạn hoàn toàn.** Đó là lý do nó xếp cuối, sau khi dữ liệu lõi đã ổn.

---

## 3. Hệ thống phân loại

### Tứ khí (四气) — bốn tính, cộng "bình"

| Tính | Hán Việt | Ý nghĩa |
|---|---|---|
| Hàn | 寒 | Rất mát/lạnh |
| Lương | 凉 | Hơi mát |
| Bình | 平 | Trung tính |
| Ôn | 温 | Hơi ấm |
| Nhiệt | 热 | Nóng |

Đây là **thuộc tính dược tính của thực phẩm**, xác định theo tác dụng quan sát được trên cơ thể —
**không phải nhiệt độ khi ăn**. Nước đá vẫn là nước đá; tính hàn của đậu xanh không đổi khi nấu nóng.

### Ngũ vị (五味)

Toan (chua) · Khổ (đắng) · Cam (ngọt) · Tân (cay) · Hàm (mặn) — một số sách thêm Đạm (nhạt).

**Đây là vị mang tính năng lượng, không phải vị giác.** Vị được gán thường không trùng với vị nếm
được. Đừng suy vị từ hương vị thực tế.

### Ngũ hành (五行) — nối hai hệ trên

| Hành | Vị | Tạng | Màu |
|---|---|---|---|
| Mộc | Toan | Can | Xanh |
| Hoả | Khổ | Tâm | Đỏ |
| Thổ | Cam | Tỳ | Vàng |
| Kim | Tân | Phế | Trắng |
| Thuỷ | Hàm | Thận | Đen |

### Cách trình bày trong văn hoá Việt

Sách *Cơ sở văn hoá Việt Nam* (Trần Ngọc Thêm) — giáo trình đại học chuẩn — trình bày năm mức âm
dương ánh xạ thẳng vào ngũ hành:

| Mức | Âm dương | Hành |
|---|---|---|
| Hàn | Rất âm | Thuỷ |
| Lương | Hơi âm | Kim |
| Bình | Trung tính | Thổ |
| Ôn | Hơi dương | Mộc |
| Nhiệt | Rất dương | Hoả |

Ví dụ kinh điển về **cân bằng trong một món**: chén nước mắm chấm — mặn (Thuỷ) + đắng (Hoả) + chua
(Mộc) + cay (Kim) + ngọt (Thổ).

---

## 4. Quyết định thiết kế: gán ở nguyên liệu, không gán ở món

**Đây là phát hiện quan trọng nhất về mặt kỹ thuật.**

Tài liệu có thẩm quyền — Cơ quan Quản lý Y học Cổ truyền Hồng Kông (CMRO) — phân loại **từng thực
phẩm riêng lẻ**: đậu xanh tính hàn, hẹ tính ôn, gạo tính bình, lê, rau chân vịt, gạo nếp… **Không**
phân loại món đã chế biến.

Cách chế biến được coi là **yếu tố điều chỉnh**: chiên/nướng làm ấm lên; luộc/hấp làm mát đi.

⇒ Mô hình dữ liệu:

```
ingredient_tcm_traits          (nhãn gốc, có nguồn, cho phép nhiều dòng mâu thuẫn)
        │
        │  tổng hợp theo trọng số khối lượng
        │  + hệ số điều chỉnh theo cooking_method
        ▼
dish_tcm_summary               (do máy tính, không nhập tay)
```

### Công thức tổng hợp

```
nature_score = Σ(khối_lượng_i × điểm_tính_i) / Σ(khối_lượng_i)   với i là nguyên liệu CÓ nhãn

điểm_tính:  hàn = −2 · lương = −1 · bình = 0 · ôn = +1 · nhiệt = +2

nature_score += hệ_số_chế_biến
   chiên/nướng/kho: +0,5    ·    xào: +0,25    ·    luộc/hấp: −0,25    ·    sống: −0,5

coverage_pct = Σ(khối_lượng nguyên liệu có nhãn) / Σ(tổng khối lượng) × 100
```

**Ràng buộc:** `coverage_pct < 60` ⇒ `confidence` tối đa `low`. `coverage_pct < 30` ⇒ **không hiển
thị kết quả nào**. Tổng hợp từ một phần ba nguyên liệu rồi tuyên bố "món này tính hàn" là bịa.

> ⚠️ **Hệ số chế biến ở trên là quy ước do dự án đặt ra**, dựa trên nguyên tắc định tính "chiên làm
> ấm, luộc làm mát" của y văn. **Không có nguồn nào cho con số cụ thể** vì y học cổ truyền không định
> lượng theo cách này. Phải ghi rõ điều đó trên giao diện, và đừng trình bày như một phép tính khoa học.

---

## 5. Ranh giới pháp lý — bắt buộc tuân thủ

### Phân loại này KHÔNG được chuẩn hoá

- **Được chuẩn hoá:** *từ vựng* (4 tính + bình, 5 vị, 5 hành, quy kinh) ổn định qua mọi trường phái.
  Với ~548 vị thuốc trong Dược điển Trung Quốc, tính/vị đã được pháp điển hoá chính thức.
- **KHÔNG được chuẩn hoá:** nhãn cho **thực phẩm thông thường** khác nhau đáng kể theo nguồn, trường
  phái, vùng miền và thời đại. Nhãn mát/ấm cho cùng một loại rau quả khác nhau giữa truyền thống
  Trung, Việt, Hàn, và Kampo Nhật, và khác nhau giữa các thầy thuốc. **Không có chuẩn ISO/SNOMED hay
  chuẩn quốc gia nào** cho tính của thực phẩm.
- Bản thân giới học thuật coi "tính hàn/nhiệt" là thứ cần **dự đoán bằng thống kê**, chứ không phải
  tra cứu — đó là thừa nhận rằng nó không cố định.

⇒ **Bắt buộc:** mỗi nhãn có `source_ref` (ràng buộc R5) và `confidence`. Khi các nguồn mâu thuẫn,
**hiển thị mâu thuẫn**, không chọn một cái rồi giấu cái kia.

### Rủi ro tuyên bố y tế

| Khung | Rủi ro |
|---|---|
| Việt Nam | Quảng cáo thực phẩm có nội dung phòng/chữa bệnh bị hạn chế theo khung pháp luật về an toàn thực phẩm và quảng cáo. ⚠️ **Chưa xác minh điều khoản cụ thể — cần hỏi luật sư nếu thương mại hoá** |
| App Store / Google Play | Đều từ chối ứng dụng có tuyên bố y tế/chẩn đoán không có căn cứ |
| Hoa Kỳ (nếu phát hành) | Phần mềm có thể bị coi là thiết bị y tế nếu nhằm chẩn đoán, chữa, giảm nhẹ, điều trị bệnh **hoặc tác động tới cấu trúc/chức năng cơ thể**. FTC yêu cầu bằng chứng khoa học đáng tin cậy cho mọi tuyên bố về lợi ích sức khoẻ, và **quan điểm của FTC là dòng miễn trừ trách nhiệm KHÔNG hoá giải được một tuyên bố gây hiểu lầm** |

**Điều này không phải tư vấn pháp lý.** Nhưng nó đủ để rút ra quy tắc vận hành an toàn:

### Khung diễn đạt bắt buộc

✅ **Được phép** — khung văn hoá, mô tả, không cá nhân hoá:
> "Theo quan niệm ẩm thực truyền thống, món này được xem là **tính hàn** — thường được ăn vào mùa hè."
> *(nguồn: …)*

🚫 **Cấm** — khung y tế, cá nhân hoá theo tình trạng cơ thể:
> ~~"Thể trạng của bạn đang thiên nhiệt, nên tránh món này"~~
> ~~"Ăn món tính lương để giảm viêm"~~
> ~~"Món này giúp cân bằng âm dương, tốt cho người bị…"~~

**Ba lằn ranh không được vượt:**

1. Không gắn nhãn tính vị với **triệu chứng, bệnh, hay tình trạng sức khoẻ**.
2. Không có bài trắc nghiệm "thể trạng" sinh ra **danh sách món phải tránh** — đặc biệt nguy hiểm với
   phụ nữ mang thai, người đang bệnh, và trẻ em.
3. Không để lớp này **ghi đè hoặc lọc bỏ** kết quả của engine dinh dưỡng.

---

## 6. Kế hoạch gieo mầm dữ liệu (GĐ6)

Theo thứ tự rẻ → đắt:

| # | Nguồn | Số lượng | Ghi chú |
|---|---|---|---|
| 1 | **Danh mục "dược thực đồng nguyên" (药食同源)** của Bộ Y tế Trung Quốc | 106 chất (33 từ 1987 → 106 tính tới 2024) | ⭐ Nguồn **duy nhất** vừa có thẩm quyền, vừa do nhà nước công bố, vừa ghép được bằng máy. Mọi mục đều nằm trong Dược điển nên **đã có sẵn tính + vị chính thức**, join được từ ETCM/TCMSP |
| 2 | Danh mục thực phẩm của CMRO Hồng Kông | Nhỏ | Có thẩm quyền, song ngữ Anh–Trung |
| 3 | Bảng 284 thực phẩm của Liu et al. 2012 | 284 | Phải số hoá tay từ bài báo. Kiểm tra điều khoản tạp chí trước |
| 4 | Biên soạn từ y văn Việt Nam | Còn lại | Tốn công nhất. Phải trích dẫn sách cụ thể, không nhớ đâu ghi đó |

**Điều kiện tiên quyết để mở GĐ6:** dữ liệu lõi (nguyên liệu + món) đã ổn định, và tất cả GĐ1–GĐ5 đã
đóng. Không làm sớm hơn.

---

## 7. Nguồn tham khảo

| Nội dung | Nguồn |
|---|---|
| Tứ khí, ngũ vị, ngũ hành | Y văn cổ truyền; tổng quan tại PMC11068986 |
| Phân loại **thực phẩm** có thẩm quyền | Cơ quan Quản lý Y học Cổ truyền Hồng Kông (CMRO), chuyên đề dưỡng sinh bằng thực phẩm |
| Trình bày trong văn hoá Việt | Trần Ngọc Thêm, *Cơ sở văn hoá Việt Nam* |
| Dữ liệu tính/vị dược liệu | ETCM v2.0; SymMap; TCMSP |
| Danh mục dược thực đồng nguyên | Tổng quan tại *BMC Complement Med Ther* (2025), DOI 10.1186/s12906-025-04822-0 |
| 284 thực phẩm gán hàn/nhiệt | Liu et al., *Nutrition & Dietetics* 2012;69(1):64–68 |
| Quy định thiết bị y tế / tuyên bố sức khoẻ | FDA General Wellness Policy; FTC Health Products Compliance Guidance |
