# Lớp âm dương – ngũ hành

**Cập nhật lần cuối:** 2026-08-31 — bản đầu tiên
**Trạng thái:** ⚠️ **CHƯA HIỆN THỰC.** Thuộc GĐ6, là **lớp tuỳ chọn**, không phải chức năng lõi.

> ## ⚠️ Đính chính + cập nhật 2026-11-01 — chương trình Mức 3 đã duyệt, nền kỹ thuật TCM-000 đã dựng
>
> Chủ dự án đã duyệt kế hoạch nâng dữ liệu lên Mức 3 (toàn bộ món + nguyên liệu + quả) và
> **TCM-000 đã hiện thực phần nền** — các câu dưới đây trong bản 2026-08-31 nay phải đọc kèm
> đính chính này:
>
> 1. **"Mặc định tắt / chỉ bật khi người dùng chủ động bật" (§1) — ĐÃ ĐẢO NGƯỢC.** Theo quyết
>    định của chủ dự án 2026-11-01, lớp tính vị **mặc định HIỂN THỊ** ở mọi nơi nó đủ căn cứ
>    để hiển thị (phần nào cần ẩn, chủ dự án cân nhắc sau). Điều KHÔNG đổi: ngưỡng trung thực
>    theo độ phủ (§4) — coverage <30% vẫn không hiển thị kết luận nào, 30–<60% vẫn bị chặn
>    độ tin cậy ở mức thấp. "Mặc định hiển thị" không có nghĩa là hiển thị thay cho dữ liệu
>    chưa có. Lớp này vẫn **không** ảnh hưởng engine calo và **không** lọc bỏ món ở tầng
>    lọc cứng.
> 2. **Thẩm quyền nguồn (§6):** chủ dự án chốt **Dược điển Trung Quốc + CMRO Hồng Kông là
>    nguồn chính** (TQ có truyền thống nghiên cứu đông y lâu đời nhất), bổ sung nguồn chính
>    thống/học thuật của các nước khác và y văn Việt Nam. Thứ tự rẻ → đắt ở §6 giữ nguyên
>    làm thứ tự gieo mầm (TCM-001a trở đi).
> 3. **Chính sách lưu trữ nguồn hạn chế:** dự án tuy mã nguồn mở nhưng chủ dự án chỉ dùng
>    cho **lưu trữ thông tin và lưu hành nội bộ**. Nguồn **uy tín** mà điều khoản ghi hạn
>    chế sử dụng/tái phân phối vẫn được **lưu vào thư viện**, kèm cờ `usage_restricted` +
>    ghi chú **"không được dùng"** gắn ngay tại dòng nhãn (và ghi vào LICENSE-COMPLIANCE).
>    Chính sách hiển thị công khai cho dữ liệu mang cờ này chốt riêng trước go-live; trong
>    giai đoạn nội bộ, ghi chú phải đi kèm dữ liệu ở mọi nơi nó xuất hiện.
> 4. **Cổng Mức 3** (PRD §7 + quyết định 2026-11-01): món đạt Mức 3 khi **coverage ≥60%**
>    **và có ghi chú văn hoá có nguồn**. Ghi chú văn hoá cũng áp cho hồ sơ quả.
> 5. **Đường nâng bù:** sản phẩm đang Mức 1, không đạt cổng Mức 2 nhưng **đủ thông tin
>    Mức 3** (coverage ≥60% + ghi chú văn hoá) thì nâng lên **"Mức 2 (có note)"** —
>    `completeness_level` giữ nguyên là số 2, căn cứ nằm ở `dishes.level_basis =
>    'tcm_compensation'` + `level_note` bắt buộc ghi rõ cổng chưa đạt. Trigger R8 và
>    cổng V12 miễn trừ đúng nhóm này; cổng phủ do V20 kiểm ở tầng DB.
> 6. **Nguyên liệu hỗn hợp** khai báo `components` (slug các nguyên liệu thành phần);
>    trang nguyên liệu hiển thị **liên kết thành phần** để người dùng tự vào xem từng
>    thành phần — đây là lối xử lý chính cho nhóm không có nhãn tính vị trực tiếp.
> 7. **Nhãn hiển thị** của khối là đúng chữ **"Tính vị:"** (chốt của chủ dự án) — khung
>    văn hoá/pháp lý do trang `/mien-tru-y-te` và đoạn nguồn/độ phủ đi kèm đảm nhiệm.
> 8. **Quy ước tổng hợp bổ sung ở TCM-000** (chi tiết ở §8 dưới đây): ánh xạ mọi mã
>    `cooking_method` của dự án vào nhóm hệ số; xử lý mâu thuẫn khi tổng hợp; cách tính
>    `dominant_element` và `confidence` của món.
>
> Chi tiết triển khai: [TCM-000](../05-tasks/tasks/TCM-000.md). Dữ liệu nhãn thật bắt đầu
> gieo từ TCM-001a — TCM-000 chỉ dựng nền, **không gieo nhãn nào**.

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

---

## 8. Quy ước tổng hợp của TCM-000 (hiện thực hoá §4)

Công thức gốc ở §4 chỉ định nghĩa hệ số cho 7 nhóm cách chế biến và điểm tính. Engine
`App\Cultural\TcmAggregator` (lệnh `lacdoi:recompute-tcm`) áp thêm các quy ước sau —
**tất cả đều là quy ước của dự án**, phải đọc cùng cảnh báo ở §4 (hệ số chế biến không
có nguồn định lượng):

| Mã `cooking_method` | Hệ số | Thuộc nhóm gốc |
|---|---|---|
| `chien`, `chien-ngap-dau`, `ran` | +0,5 | chiên |
| `nuong`, `kho` | +0,5 | nướng/kho |
| `xao` | +0,25 | xào |
| `luoc`, `hap`, `nau` | −0,25 | luộc/hấp (`nau` = nấu trong nước: canh, súp) |
| `song`, `tron` | −0,5 | sống (`tron` chủ yếu là thành phần sống trộn, không qua nhiệt) |
| null / mã lạ | 0 | — |

- **Điểm bị kẹp trong [−2, +2]** sau khi cộng hệ số (thang điểm gốc chỉ chạy −2…+2).
- **"Có nhãn"** nghĩa là có ≥1 dòng trait với `nature` khác null. Điểm của một nguyên
  liệu có nhiều dòng nature = **trung bình các dòng** (mỗi nguồn một phiếu); nguyên liệu
  có ≥2 nature phân biệt ⇒ món mang cờ `has_conflicts` và `confidence` bị hạ xuống `low`.
- **`confidence` của món** = mắt xích yếu nhất trong các dòng trait tham gia, rồi áp
  trần: coverage <60% ⇒ tối đa `low`; có mâu thuẫn ⇒ tối đa `low`.
- **`dominant_element`**: mỗi nguyên liệu có nhãn bỏ phiếu bằng khối lượng của nó cho
  hành hiệu lực của từng dòng trait (element ghi tường minh, hoặc suy từ vị theo bảng
  §3; vị `dam` không suy ra hành). Nhiều hành phân biệt trong cùng nguyên liệu ⇒ chia
  đều khối lượng. Hoà phiếu ⇒ `null`.
- **Khối lượng** lấy từ chính hàm quy đổi gram của engine dinh dưỡng
  (`DishNutritionService::resolveGrams`) — một định nghĩa khối lượng duy nhất. Dòng
  `is_discarded` không tham gia; dòng không quy đổi được gram bị loại khỏi cả tử số
  lẫn mẫu số của coverage.
- Món **không có nguyên liệu có nhãn nào thì không có dòng `dish_tcm_summary`** —
  không ghi một tổng hợp 0 trung tính để giả vờ đã tính.
- **Trần cứng `medium` cho confidence của món (TCM-001a):** sau mọi trần trên,
  `high` bị hạ xuống `medium` — hệ số chế biến là quy ước dự án không có nguồn định
  lượng, nên kết luận cấp món không bao giờ mang `high`. Nhãn nguyên liệu giữ nguyên
  thang confidence riêng của chúng.
- **`element_basis` (TCM-001a):** mỗi dòng nhãn khai căn cứ của `element` —
  `flavor` (mặc định, suy từ vị) | `nature` (suy từ tính theo bảng trình bày Việt ở
  §3) | `direct` (nguồn ghi trực tiếp). Chỉ dòng basis=`flavor` bỏ phiếu
  `dominant_element` khi tổng hợp món; dòng basis khác vẫn hiển thị ở tầng nguyên
  liệu và vẫn tham gia tổng hợp nature/flavor như thường.

## 9. Chính sách P-DERIVE — nhãn suy diễn cho dạng chế biến đơn thành phần (TCM-001c)

> Quyết định của phiên chính theo uỷ quyền tự chủ của chủ dự án (2026-11-01),
> xử lý điểm nghẽn ghi ở TCM-001b: các nguồn chỉ phân loại nguyên liệu gốc
> (hạt gạo, củ khoai), không phân loại dạng đã chế biến (bột, sợi) — mà suy
> nhãn lặng lẽ từ gốc sang dạng chế biến là điều kỷ luật các đợt trước cấm.
> P-DERIVE hợp thức hoá việc suy diễn đó thành một loại dòng nhãn RIÊNG,
> bị khoá chặt và luôn tự khai báo là suy diễn.

**Phạm vi áp dụng — chỉ khi ĐỒNG THỜI đủ ba điều kiện:**

1. Nguyên liệu là dạng chế biến **cơ học** (xay thành bột, ngâm nở thành
   sợi/bánh, sấy khô, ép) của **đúng một** nguyên liệu gốc;
2. nguyên liệu gốc **đã có nhãn nguồn thật** trong kho;
3. chế biến không đưa thêm thành phần nào khác và không đổi bản chất
   nguyên liệu (lên men, muối chua, rang kèm gia vị, pha trộn — đều NGOÀI
   phạm vi; lên men đa yếu tố như nước mắm tuyệt đối không).

**Hình thức:** dòng nhãn mang `derived_from` = slug của gốc; nature/flavor/
element/`element_basis` **gương nguyên vẹn một dòng nguồn thật của gốc**;
`source_ref` trích lại nguồn của nhãn gốc và bắt đầu bằng dấu `P-DERIVE`;
nếu dòng gốc mang cờ hạn chế sử dụng thì dòng suy diễn mang theo cờ + ghi
chú đó.

**Các khoá cứng (V20 tầng file + CHECK ở DB, migration 100012):**

- `confidence` của dòng suy diễn **khoá ở `low`** — không ngoại lệ;
- **cấm suy diễn chuỗi:** gốc phải có dòng nguồn thật, dòng suy diễn của
  gốc không làm căn cứ được;
- **cấm cho nguyên liệu đa thành phần/hỗn hợp** (có `components`) và
  nguyên liệu không định danh (dầu ăn generic…);
- hiển thị phân biệt rõ ở trang nguyên liệu/quả ("Nhãn suy diễn từ …"),
  không bao giờ trình bày như nhãn nguồn trực tiếp.

**Tổng hợp món:** dòng suy diễn tham gia như nhãn thường (nó vẫn là một
phiếu có căn cứ truy vết về nguồn thật của gốc); trần `medium`/`low` của
confidence món theo §8 tự động giữ kết luận ở mức thận trọng.

## 10. Quy ước đọc bản tái bản và bản dịch của nguồn tính vị (FIX-TCM4-3)

> Rút ra từ AUDIT-004 (2026-11-01): các trang tái bản **tiếng Anh** của
> zhongyifangji.net và tcmfans.club (và các site tương tự) dịch 性味 bằng
> máy, và bản dịch **có thể sai tính vị**. Hai tiền lệ đã gặp trong kho:
>
> - `rau-diep-ca`: bản EN của zhongyifangji dịch 辛 (tân) thành "bitter"
>   (đắng) — lấy theo bản EN sẽ gieo sai sang vị khổ.
> - `dau-do-hat` (FIX-TCM4-1): trang tcmfans từng được dẫn làm "bản tái
>   bản kiểm chứng" của Dược điển lại ghi 赤小豆 là "Slightly cool" (vi
>   lương), trong khi văn bản Dược điển là 平 — biến thể 微寒 có thật ở
>   《食性本草》 nhưng không phải giá trị của Dược điển.

Quy ước bắt buộc khi gieo hoặc đối chiếu nhãn tính vị:

1. Bản tiếng Anh của zhongyifangji/tcmfans **chỉ dùng làm biển chỉ đường**
   (tìm chuyên khảo, tra mã số, định vị trang gốc) — không bao giờ làm
   căn cứ chốt giá trị `nature`/`flavor`.
2. Giá trị chỉ lấy từ: văn bản gốc chữ Hán của cổ thư/Dược điển (bản toàn
   văn số hoá như Wikisource, hoặc bản tái bản chữ Hán như A+醫學百科）,
   ấn phẩm nguyên bản của CMRO, hoặc y văn tiếng Việt — luôn mở kiểm
   chứng tận nơi tại lúc gieo.
3. Nếu một giá trị chỉ có ở bản EN, coi như **chưa có nguồn**: đưa vào
   danh sách chờ, không gieo.
