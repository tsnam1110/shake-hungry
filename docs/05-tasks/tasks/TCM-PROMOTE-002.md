# [TCM-PROMOTE-002] — Gán Mức 3 cho món đủ cổng + nâng bù đợt 2 (27 món)

**Mã task:** TCM-PROMOTE-002 · **Giai đoạn:** GĐ6 · **Loại:** Dữ liệu
**Status:** ✅ DONE · **Ngày:** 2026-11-01 · **Phụ thuộc:** TCM-000 (cơ chế mức + cổng V20 + `levelLabel`), TCM-001 (coverage), VN-001 đợt 1–3 (`cultural_note`), TCM-PROMOTE-001 (tiền lệ nâng bù đợt 1)

**Căn cứ:** cổng Mức 3 trong kế hoạch Mức 3 §1 (chốt của chủ dự án 2026-11-01): món **Mức 2 thường** có độ phủ tính vị ≥60% + ghi chú văn hoá → **Mức 3**. Món "Mức 2 (có note)" KHÔNG gán Mức 3 (nguyên tắc đã ghi ở báo cáo VN-001 đợt 2: Mức 3 đầy đủ đòi nền Mức 2 thường). Đường nâng bù áp tiếp cho món Mức 1 mới đủ 2 tiêu chí sau VN-001 đợt 3.

---

## Phần A — Gán Mức 3 (187 món)

Nhóm đích = món `completeness_level=2`, `level_basis` NULL, `coverage_pct ≥ 60` trong `dish_tcm_summary` (DB kiểm chứng `shake_hungry_verify`, seed lại từ `data/` + `lacdoi:recompute-tcm`) và đã có `cultural_note`. Đối chiếu với thống kê VN-001 đợt 2/đợt 3 (187/472 Mức 2 thường đủ cả hai tiêu chí; 365 Mức 2 thường đạt phủ ≥60%): **khớp tuyệt đối, không chênh lệch**. Thay đổi duy nhất ở mỗi file: `completeness_level` 2 → 3 (diff đúng +1/−1 dòng/file, kiểm bằng `git diff --numstat`).

| # | Slug | Tên món | Phủ tính vị (%) |
|---|---|---|---:|
| 1 | banh-beo | bánh bèo | 81.6 |
| 2 | banh-beo-tom-chay | Bánh bèo tôm chấy | 87.1 |
| 3 | banh-bo | bánh bò | 91.4 |
| 4 | banh-bo-thot-not | Bánh bò thốt nốt | 68.2 |
| 5 | banh-cam | Bánh cam | 100.0 |
| 6 | banh-can | bánh căn | 69.0 |
| 7 | banh-canh | Bánh canh | 82.2 |
| 8 | banh-canh-ca-loc-quang-tri | Bánh canh cá lóc Quảng Trị | 92.7 |
| 9 | banh-canh-cua | Bánh canh cua | 88.9 |
| 10 | banh-canh-gio-heo | Bánh canh giò heo | 97.9 |
| 11 | banh-canh-nam-pho | Bánh canh Nam Phổ | 83.1 |
| 12 | banh-cay | Bánh cáy | 96.3 |
| 13 | banh-chung | bánh chưng | 97.3 |
| 14 | banh-chung-chien | Bánh chưng chiên | 92.1 |
| 15 | banh-cuon-trung | Bánh cuốn trứng | 78.5 |
| 16 | banh-da-cua | Bánh đa cua | 65.9 |
| 17 | banh-da-lon | bánh da lợn | 75.1 |
| 18 | banh-dau-xanh | bánh đậu xanh | 100.0 |
| 19 | banh-deo | Bánh dẻo | 92.9 |
| 20 | banh-duc | Bánh đúc | 70.8 |
| 21 | banh-flan | Bánh flan | 83.3 |
| 22 | banh-giay | bánh giầy | 95.2 |
| 23 | banh-gio | Bánh giò | 72.9 |
| 24 | banh-khoai-tay-chien | Bánh khoai tây chiên | 93.8 |
| 25 | banh-khot | Bánh khọt | 87.1 |
| 26 | banh-nam | bánh nậm | 77.5 |
| 27 | banh-ran | bánh rán | 100.0 |
| 28 | banh-ran-duong | Bánh rán đường | 100.0 |
| 29 | banh-tam-bi | bánh tằm bì | 91.1 |
| 30 | banh-te | bánh tẻ | 92.7 |
| 31 | banh-tet | bánh tét | 98.6 |
| 32 | banh-to | Bánh tổ | 97.7 |
| 33 | banh-tom-ho-tay | bánh tôm Hồ Tây | 80.9 |
| 34 | banh-trang-cuon-thit-heo | Bánh tráng cuốn thịt heo | 79.5 |
| 35 | banh-troi | bánh trôi | 100.0 |
| 36 | banh-troi-tau | Bánh trôi tàu | 100.0 |
| 37 | banh-uot-long-ga | bánh ướt lòng gà | 69.6 |
| 38 | banh-xeo | Bánh xèo | 96.2 |
| 39 | banh-xeo-mien-tay | Bánh xèo miền Tây | 97.3 |
| 40 | banh-xu-xe | Bánh xu xê | 62.7 |
| 41 | bo-bia | Bò bía | 67.3 |
| 42 | bo-kho | bò kho | 81.0 |
| 43 | bo-luc-lac | Bò lúc lắc | 76.1 |
| 44 | bo-nuong-la-lot | Bò nướng lá lốt | 91.8 |
| 45 | bo-sot-vang | bò sốt vang | 71.4 |
| 46 | bot-chien | bột chiên | 72.2 |
| 47 | bun-bi | Bún bì | 96.3 |
| 48 | bun-bo-gio-heo | Bún bò giò heo | 92.2 |
| 49 | bun-bo-hue | Bún bò Huế | 92.5 |
| 50 | bun-bo-nam-bo | Bún bò Nam Bộ | 94.4 |
| 51 | bun-bung | Bún bung | 85.9 |
| 52 | bun-ca | Bún cá | 66.2 |
| 53 | bun-cha | Bún chả | 89.6 |
| 54 | bun-cha-ca | Bún chả cá | 70.4 |
| 55 | bun-chao-tom | Bún chạo tôm | 92.9 |
| 56 | bun-do | Bún đỏ | 93.8 |
| 57 | bun-doc-mung | Bún dọc mùng | 85.7 |
| 58 | bun-hen | Bún hến | 91.5 |
| 59 | bun-mam | Bún mắm | 93.9 |
| 60 | bun-moc | bún mọc | 84.2 |
| 61 | bun-nem | Bún nem | 87.6 |
| 62 | bun-nghe | Bún nghệ xào lòng | 93.5 |
| 63 | bun-oc | Bún ốc | 92.0 |
| 64 | bun-rieu-cua | Bún riêu cua | 87.1 |
| 65 | bun-thit-nuong | Bún thịt nướng | 96.8 |
| 66 | ca-kho | cá kho | 88.3 |
| 67 | ca-loc-nuong-trui | Cá lóc nướng trui | 94.0 |
| 68 | ca-nuong-pa-pinh-top | Cá nướng pa pỉnh tộp | 91.7 |
| 69 | ca-ro-kho-to | Cá rô kho tộ | 91.3 |
| 70 | ca-tram-kho-rieng | cá trắm kho riềng | 90.5 |
| 71 | canh-bau-tom | canh bầu nấu tôm | 95.7 |
| 72 | canh-bong | canh bóng thập cẩm | 82.7 |
| 73 | canh-bong-tha | Canh bóng thả | 65.3 |
| 74 | canh-cai-cuc-thit-bam | Canh cải cúc thịt băm | 90.2 |
| 75 | canh-chua | Canh chua | 83.6 |
| 76 | canh-chua-ca-loc | Canh chua cá lóc | 83.2 |
| 77 | canh-ga-la-giang | canh gà lá giang | 78.8 |
| 78 | canh-kho-qua-nhoi-thit | canh khổ qua nhồi thịt | 97.6 |
| 79 | canh-khoai-tay-suon | Canh khoai tây nấu sườn | 95.9 |
| 80 | canh-rau-ma-thit-bo | Canh rau má thịt bò | 88.8 |
| 81 | canh-tap-tang | canh tập tàng nấu tôm | 66.5 |
| 82 | cao-lau | Cao lầu | 60.4 |
| 83 | cha-ca-la-vong | chả cá Lã Vọng | 87.7 |
| 84 | cha-chia | Chả chìa | 92.1 |
| 85 | cha-com | Chả cốm | 67.4 |
| 86 | cha-la-lot | Chả lá lốt | 93.3 |
| 87 | cha-muc | Chả mực | 90.0 |
| 88 | cha-trung | chả trứng | 90.1 |
| 89 | chao-ca-loc | Cháo cá lóc rau đắng | 84.4 |
| 90 | chao-canh | Cháo canh Quảng Bình | 95.6 |
| 91 | chao-ga | cháo gà | 97.5 |
| 92 | chao-hau | Cháo hàu | 92.6 |
| 93 | chao-long | cháo lòng | 78.9 |
| 94 | chao-luon-nghe-an | Cháo lươn Nghệ An | 86.7 |
| 95 | chao-nam | cháo nấm chay | 80.3 |
| 96 | chao-ngheu | Cháo nghêu | 83.1 |
| 97 | chao-suon | cháo sườn | 93.6 |
| 98 | chao-tom | chạo tôm | 91.3 |
| 99 | chao-vit | Cháo vịt | 91.8 |
| 100 | che-ba-ba | Chè bà ba | 76.9 |
| 101 | che-banh-lot | chè bánh lọt | 69.6 |
| 102 | che-buoi | Chè bưởi | 92.1 |
| 103 | che-dau-den | Chè đậu đen | 92.8 |
| 104 | che-dau-do | Chè đậu đỏ | 99.0 |
| 105 | che-dau-xanh | chè đậu xanh | 98.9 |
| 106 | che-hat-sen | Chè hạt sen | 98.8 |
| 107 | che-hat-sen-long-nhan | Chè hạt sen long nhãn | 99.4 |
| 108 | che-lam | chè lam | 100.0 |
| 109 | che-nhan-boc-hat-sen | chè nhãn bọc hạt sen | 100.0 |
| 110 | che-troi-nuoc | chè trôi nước | 76.5 |
| 111 | chuoi-chien | Chuối chiên | 81.1 |
| 112 | chuoi-nuong | chuối nướng | 97.3 |
| 113 | com-am-phu | cơm âm phủ | 82.9 |
| 114 | com-chay-ninh-binh | cơm cháy Ninh Bình | 89.8 |
| 115 | com-ga-hoi-an | Cơm gà Hội An | 79.4 |
| 116 | com-ga-quang-nam | cơm gà Quảng Nam | 87.3 |
| 117 | com-ga-xoi-mo | cơm gà xối mỡ | 96.7 |
| 118 | com-hen | cơm hến | 86.5 |
| 119 | com-nam-muoi-vung | Cơm nắm muối vừng | 100.0 |
| 120 | com-tam | cơm tấm | 95.5 |
| 121 | com-tam-long-xuyen | Cơm tấm Long Xuyên | 97.2 |
| 122 | cua-rang-me | Cua rang me | 88.6 |
| 123 | dau-phu-nhoi-thit | Đậu phụ nhồi thịt sốt cà | 93.7 |
| 124 | ga-luoc | gà luộc | 94.8 |
| 125 | ga-sa-lua | Gà sa lửa | 95.4 |
| 126 | ghe-hap | Ghẹ hấp sả | 97.0 |
| 127 | gia-cay | giả cầy | 89.9 |
| 128 | goi-ga | gỏi gà xé phay | 92.7 |
| 129 | goi-sua | Gỏi sứa | 73.2 |
| 130 | goi-vit | Gỏi vịt | 64.0 |
| 131 | hau-nuong | Hàu nướng mỡ hành | 94.1 |
| 132 | hen-xao | Hến xào xúc bánh tráng | 81.9 |
| 133 | heo-quay | Heo quay | 99.5 |
| 134 | hu-tieu | Hủ tiếu | 68.5 |
| 135 | lau-ga-la-e | lẩu gà lá é | 95.9 |
| 136 | lau-mam | lẩu mắm | 79.5 |
| 137 | lau-rieu-cua | lẩu riêu cua bắp bò | 73.1 |
| 138 | lau-thai | lẩu Thái hải sản | 86.5 |
| 139 | lau-vit | lẩu vịt | 90.8 |
| 140 | luon-om-chuoi-dau | lươn om chuối đậu | 70.2 |
| 141 | mam-kho | mắm kho | 88.6 |
| 142 | mam-tep-chung-thit | mắm tép chưng thịt | 92.8 |
| 143 | mi-xao-bo | mì xào bò | 68.0 |
| 144 | mi-xao-gion | Mì xào giòn | 69.7 |
| 145 | mien-luon | Miến lươn | 69.6 |
| 146 | mien-mang-ga | Miến măng gà | 70.0 |
| 147 | mien-xao-cua | miến xào cua | 66.2 |
| 148 | muc-rim-me | Mực rim me | 81.3 |
| 149 | muoi-tom | Muối tôm | 98.2 |
| 150 | nem-cuon | Nem cuốn | 88.7 |
| 151 | nem-lui | Nem lụi Huế | 87.8 |
| 152 | nem-ran | Nem rán (chả giò) | 80.9 |
| 153 | ngao-hap | Ngao hấp sả | 99.4 |
| 154 | oc-luoc | Ốc luộc | 93.9 |
| 155 | oc-nau-chuoi-dau | ốc nấu chuối đậu | 76.4 |
| 156 | pho-bo | Phở bò | 96.7 |
| 157 | pho-bo-sot-vang | Phở bò sốt vang | 93.5 |
| 158 | pho-chay | Phở chay | 86.1 |
| 159 | pho-cuon | Phở cuốn | 93.6 |
| 160 | pho-ga | phở gà | 97.6 |
| 161 | pho-ga-tron | Phở gà trộn | 96.2 |
| 162 | pho-xao | Phở xào | 94.8 |
| 163 | rau-muong-xao-toi | rau muống xào tỏi | 93.8 |
| 164 | sam-bo-luong | Sâm bổ lượng | 100.0 |
| 165 | su-su-xao-toi | Su su xào tỏi | 93.6 |
| 166 | sui-din | sủi dìn | 100.0 |
| 167 | sup-cua | súp cua | 89.7 |
| 168 | sup-luon | Súp lươn Nghệ An | 82.8 |
| 169 | tao-pho | tào phớ | 96.2 |
| 170 | tep-rang-khe | Tép rang khế | 91.4 |
| 171 | thit-chua | Thịt chua | 100.0 |
| 172 | thit-dong | Thịt đông | 75.8 |
| 173 | thit-kho-tau | thịt kho Tàu | 91.9 |
| 174 | thit-ngam-mam | Thịt ngâm mắm | 84.7 |
| 175 | thit-rang-chay-canh | Thịt rang cháy cạnh | 89.8 |
| 176 | tom-cang-hap-nuoc-dua | tôm càng hấp nước dừa | 100.0 |
| 177 | trung-cut-kho-thit | Trứng cút kho thịt | 75.9 |
| 178 | vit-om-sau | vịt om sấu | 93.6 |
| 179 | xoi-bap | xôi bắp | 97.5 |
| 180 | xoi-chien | xôi chiên | 94.9 |
| 181 | xoi-ga | Xôi gà | 94.4 |
| 182 | xoi-gac | xôi gấc | 82.3 |
| 183 | xoi-khuc | Xôi khúc | 94.6 |
| 184 | xoi-mit | xôi mít | 60.7 |
| 185 | xoi-ngu-sac | xôi ngũ sắc | 93.4 |
| 186 | xoi-vo | xôi vò | 100.0 |
| 187 | xoi-xeo | xôi xéo | 95.4 |

## Phần B — Nâng bù đợt 2 (27 món)

Nhóm đích = món `completeness_level=1` có `coverage_pct ≥ 60` + đã có `cultural_note` (ghi chú viết ở VN-001 đợt 3). Đối chiếu: VN-001 đợt 3 thống kê **27 món Mức 1 mới đủ 2 tiêu chí nâng bù** (48 món Mức 1 đạt phủ ≥60% = 135 món đủ tiêu chí phủ của TCM-001 trừ 87 món đã nâng ở TCM-PROMOTE-001; trên nền 95 món Mức 1 còn lại sau đợt 1) — **khớp tuyệt đối, không chênh lệch**. Áp đúng quy trình TCM-PROMOTE-001: `completeness_level` 1 → 2, thêm `level_basis: "tcm_compensation"` + `level_note` soạn riêng từng món từ hồ sơ giữ Mức 1 trong `import_note` của chính món (diff đúng +3/−1 dòng/file). Kiểm chứng chéo cơ học: mọi note nhắc "429" đều có "429" trong hồ sơ của món; coverage trong note khớp DB kiểm chứng ở cả 27 món; note của `bun-mam-chay` khai nguyên liệu `tuong-ban`/`tuong-hat` kcal null — đối chiếu file nguyên liệu: cả hai đúng là null.

Phân bố lý do chưa đạt cổng Mức 2 thường: 11 thiếu nguồn/không đồng thuận · 7 biến thể/chờ quyết · 5 bị chặn 429 · 2 hạ mức sau AUDIT-FIX (link rot) · 1 trần nguyên liệu chưa có số · 1 không có công thức trên web.

### Bị chặn 429 / nguồn chưa đọc được hết (5)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| banh-canh-tom-cang | Bánh canh tôm càng | 95.0 | Chưa đạt cổng Mức 2 thường vì các lượt mở nguồn bị chặn 429 và tổng họ độc lập chỉ có 2 (Cookpad Hoàng Thị Tố Hà là một họ; cụm Điện Máy XANH ≈ Tripi sao chép lẫn nhau tính một họ) — chưa đủ 3 họ độc lập cho đúng món (LV2-009 đến LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.0% ≥ 60% và đã có ghi chú văn hoá. |
| khoai-lang-ken | Khoai lang kén | 84.2 | Chưa đạt cổng Mức 2 thường vì mới mở được 1 họ (Điện Máy XANH) thì lần mở họ thứ hai bị lỗi 429, phải dừng theo kỷ luật đợt — chưa đủ họ để đối chiếu (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 84.2% ≥ 60% và đã có ghi chú văn hoá. |
| khoai-lang-luoc-trung | Khoai lang luộc và trứng luộc | 100.0 | Chưa đạt cổng Mức 2 thường vì lần mở nguồn đầu tiên đã bị lỗi 429, dừng tra cứu theo kỷ luật đợt — chưa đối chiếu được nguồn nào (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| rau-den-luoc | Rau dền luộc | 100.0 | Chưa đạt cổng Mức 2 thường vì lần mở nguồn bị lỗi 429, dừng tra cứu theo kỷ luật đợt — chưa có họ nào được mở đọc thật để đối chiếu (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |
| rau-lang-luoc | Rau lang luộc | 100.0 | Chưa đạt cổng Mức 2 thường vì lần mở nguồn bị lỗi 429, dừng tra cứu theo kỷ luật đợt — chưa có họ nào được mở đọc thật để đối chiếu (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 100.0% ≥ 60% và đã có ghi chú văn hoá. |

### Trần nguyên liệu chưa có số dinh dưỡng (1)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| bun-mam-chay | Bún mắm chay | 72.0 | Chưa đạt cổng Mức 2 thường vì tương bản và tương hột — nguyên liệu định danh làm nền nước lèo — đều chưa có số dinh dưỡng trong kho (kcal null) nên phần nền lèo không tính được calo, dù 3 họ độc lập đã xác nhận công thức (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 72.0% ≥ 60% và đã có ghi chú văn hoá. |

### Thiếu nguồn công thức đúng định danh / không đồng thuận (11)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| bun-ca-me | Bún cá mè | 95.1 | Chưa đạt cổng Mức 2 thường vì không có họ biên tập định lượng nào cho đúng cá mè — các nguồn định lượng đã tra đều là bún cá rô phi/rô đồng/thu/lóc (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.1% ≥ 60% và đã có ghi chú văn hoá. |
| bun-ca-tram | Bún cá trắm | 94.1 | Chưa đạt cổng Mức 2 thường vì đúng biến thể Hà Nội chỉ có 1 họ độc lập và họ đó không ghi khẩu phần; nguồn còn lại là biến thể trộn cá thu khác định danh (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 94.1% ≥ 60% và đã có ghi chú văn hoá. |
| bun-tom-cang | Bún tôm càng | 97.8 | Chưa đạt cổng Mức 2 thường vì chưa đủ 3 họ độc lập cho đúng hồ sơ của file (tôm càng nguyên con + thịt ba chỉ luộc + nước dùng trong) — kết quả tra cứu chỉ là bún tôm Hải Phòng/Huế dùng tôm nhỏ và một ghi chép món nhà không định lượng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.8% ≥ 60% và đã có ghi chú văn hoá. |
| chao-tom-cang | Cháo tôm càng | 97.2 | Chưa đạt cổng Mức 2 thường vì mọi công thức tôm càng xanh nấu cháo có định lượng tìm được đều là cháo ăn dặm cho bé; bản người lớn của file không có nguồn biên tập nào công bố công thức (LV2-009, LV2-010). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.2% ≥ 60% và đã có ghi chú văn hoá. |
| dau-phu-kho-nam | đậu phụ kho nấm | 90.5 | Chưa đạt cổng Mức 2 thường vì biến thể nấm rơm tươi (đã chuẩn hoá theo quyết định A3) chỉ có 1 họ quy đổi được về khẩu phần — các họ còn lại hoặc không ghi khẩu phần, hoặc trùng bộ số cùng một họ (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 90.5% ≥ 60% và đã có ghi chú văn hoá. |
| lau-ca-me | lẩu cá mè nấu măng chua | 95.5 | Chưa đạt cổng Mức 2 thường vì chỉ có 1 họ định lượng cho biến thể cá mè + măng chua và họ đó không ghi số khẩu phần; trang đúng tên còn lại là SEO không định lượng — chưa đủ 3 họ (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.5% ≥ 60% và đã có ghi chú văn hoá. |
| mang-kho-chay | Măng tươi kho chay | 91.9 | Chưa đạt cổng Mức 2 thường vì chỉ có 1 họ định lượng độc lập (Điện Máy XANH) cho biến thể thuần chay tối giản không nấm của file — các bản còn lại là sao chép cùng bộ số; cần thêm ít nhất 2 họ độc lập (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 91.9% ≥ 60% và đã có ghi chú văn hoá. |
| mi-xao-tom-cang | Mì xào tôm càng | 72.8 | Chưa đạt cổng Mức 2 thường vì không có họ biên tập nào cho đúng định danh mì xào tôm càng — các họ đã mở đều là tôm thẻ/nõn sai định danh hoặc SEO vô danh (LV2-009 đến LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 72.8% ≥ 60% và đã có ghi chú văn hoá. |
| nam-bao-ngu-kho-sa | Nấm bào ngư kho sả ớt | 89.7 | Chưa đạt cổng Mức 2 thường vì đúng biến thể kho sả ớt chỉ có 1 họ và họ đó không định lượng; hai họ có định lượng đã mở đều lệch biến thể (xào, hoặc kho nước tương không sả) (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 89.7% ≥ 60% và đã có ghi chú văn hoá. |
| pho-xao-ga | Phở xào gà | 93.6 | Chưa đạt cổng Mức 2 thường vì chưa gom được họ biên tập nào có định lượng — nguồn đúng món duy nhất (Điện Máy XANH) không có định lượng trong bản đọc được, các nguồn có số đều là trang thương mại/SEO không đạt chuẩn (LV2-009 đến LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 93.6% ≥ 60% và đã có ghi chú văn hoá. |
| sup-tom-cang | Súp tôm càng | 93.4 | Chưa đạt cổng Mức 2 thường vì 3 họ súp tôm độc lập đã mở đều dùng tôm thường (không phải tôm càng xanh như định danh của file) và không họ nào ghi số khẩu phần của mẻ — không đối chiếu được định lượng cho chén 300 g (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 93.4% ≥ 60% và đã có ghi chú văn hoá. |

### Biến thể / chờ quyết chủ dự án (7)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| bi-do-kho-chay | Bí đỏ kho chay | 95.9 | Chưa đạt cổng Mức 2 thường vì biến thể của file (kho với nước cốt dừa và nước tương, không đậu/chả/nấm) không xuất hiện ở họ đạt chuẩn nào có khẩu phần — các họ đã mở hoặc kho trơn không cốt dừa, hoặc là canh chứ không phải kho (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.9% ≥ 60% và đã có ghi chú văn hoá. |
| bun-ca-troi | Bún cá trôi | 61.2 | Chưa đạt cổng Mức 2 thường vì biến thể cá trôi khúc chiên của file chưa được chứng thực — tra cứu chỉ gặp bún chả cá trôi (lần mở trang bị lỗi 429 phía dịch vụ) và bún cá Hà Nội dùng cá trắm/rô phi/chép; định danh cần chủ dự án xác nhận rồi mới nâng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 61.2% ≥ 60% và đã có ghi chú văn hoá. |
| chao-ca-tram | Cháo cá trắm | 96.5 | Chưa đạt cổng Mức 2 thường vì mọi nguồn đạt chuẩn mở được đều là biến thể cháo cá trắm đậu xanh, trong khi file là bản cháo trắng thuần (không đậu xanh) chỉ thấy ở trang tổng hợp không ghi khẩu phần — không đủ họ cho đúng biến thể (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 96.5% ≥ 60% và đã có ghi chú văn hoá. |
| dau-phu-kho-gung | Đậu phụ kho gừng | 90.7 | Chưa đạt cổng Mức 2 thường vì biến thể chay kho gừng miếng của file là sáng tạo biên tập của DISH-005 đã được chủ dự án chốt giữ định danh (quyết định A3) — nguồn đúng tên duy nhất đã mở là biến thể mặn, không có nguồn độc lập đúng biến thể chay (LV2-007, LV2-010 Phần C). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 90.7% ≥ 60% và đã có ghi chú văn hoá. |
| kho-chay | kho chay thập cẩm | 90.8 | Chưa đạt cổng Mức 2 thường vì 'kho chay thập cẩm' là tên gọi chung không có định danh công thức cố định — các nguồn có định lượng dùng tổ hợp nguyên liệu khác hẳn nhau và khác file; cần chủ dự án chốt một biến thể chuẩn mới biên soạn Mức 2 được (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 90.8% ≥ 60% và đã có ghi chú văn hoá. |
| lau-ca-tram | lẩu cá trắm | 95.1 | Chưa đạt cổng Mức 2 thường vì file là biến thể nhẹ tự biên (chua bằng chanh, không cơm mẻ/nghệ/tương hột, không đồ nhúng kèm) không được họ nào xác nhận, và định lượng cá nguyên con cả nồi ở mọi họ đều không quy đổi được về khẩu phần (LV2-011). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 95.1% ≥ 60% và đã có ghi chú văn hoá. |
| so-diep-nuong | Sò điệp nướng mỡ hành | 97.7 | Chưa đạt cổng Mức 2 thường vì các họ đã mở đều tính theo kilôgam nguyên vỏ không cỡ con và lệch nhau rất lớn ở nguyên liệu phụ, không đối chiếu được về đĩa 6 con — cần quyết định chuẩn hoá cỡ sò và cách cân trước khi nâng (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 97.7% ≥ 60% và đã có ghi chú văn hoá. |

### Không có công thức của món trên web (1)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| goi-ca-doi | gỏi cá đối | 73.3 | Chưa đạt cổng Mức 2 thường vì không có công thức gỏi cá đối định lượng nào trên các trang biên tập đã tra — chỉ có đoạn giới thiệu không số liệu (LV2-012). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 73.3% ≥ 60% và đã có ghi chú văn hoá. |

### Hạ mức sau AUDIT-FIX vì nguồn chết link (link rot) (2)

| Slug | Tên món | Phủ tính vị (%) | `level_note` đã ghi |
|---|---|---:|---|
| canh-khoai-so | Canh khoai sọ thịt băm | 93.2 | Chưa đạt cổng Mức 2 thường vì món từng đạt Mức 2 nhưng bị hạ xuống sau rà soát AUDIT-002: hai nguồn công thức đúng món đã chết link 404 và không tìm được nguồn thay thế đạt chuẩn, chỉ còn 1 nguồn sống — dưới cổng 3 nguồn (AUDIT-FIX, PX-02). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 93.2% ≥ 60% và đã có ghi chú văn hoá. |
| canh-rau-lang-tom | Canh rau lang nấu tôm | 89.9 | Chưa đạt cổng Mức 2 thường vì món từng đạt Mức 2 nhưng bị hạ xuống sau rà soát AUDIT-002: một nguồn đúng món đã chết link 404, hai nguồn còn lại qua 4 lượt thử vẫn chưa kết luận được (lỗi fetch phía công cụ, không mã HTTP) — nguồn sống đã xác nhận mới đạt 2, dưới cổng 3 nguồn (AUDIT-FIX, PX-16). Được nâng theo đường nâng bù Mức 3 (quyết định chủ dự án 2026-11-01): độ phủ tính vị 89.9% ≥ 60% và đã có ghi chú văn hoá. |

## Tác động code — không cần sửa

Grep toàn bộ nơi dùng `completeness_level` trước khi áp: `Dish::levelLabel()` sinh nhãn động "Mức " + level (+ hậu tố "(có note)" theo `level_basis`) → trả "Mức 3" tự động; CHECK constraint DB cho phép 1–3 và ràng buộc `level_basis` chỉ khi level=2 — hợp lệ cho cả hai phần; validator V12 (tầng file) yêu cầu ≥3 `recipe_sources` cho level ≥2 dạng thường — nhóm Phần A vốn là Mức 2 thường nên đã đạt; V20 kiểm cổng Mức 3 (note + phủ ≥60) ở cả tầng file và DB; admin `Dishes.jsx` hiển thị "Mức {v}" dạng động và bộ lọc mức **đã có sẵn** tuỳ chọn Mức 3; Dashboard admin nhóm theo mức dạng động; trang món chỉ hiển thị khối `level_note` khi basis là `tcm_compensation` (nhóm Phần A không bị ảnh hưởng). **Kết luận: 0 dòng code phải sửa.**

Kiểm hiển thị thật trên server tạm gắn DB kiểm chứng (cổng 8123, đã tắt sau khi kiểm): API `/api/dishes/{slug}` — `banh-can`/`banh-beo`/`pho-ga` → `level_label` "Mức 3"; `canh-khoai-so` → "Mức 2 (có note)" kèm basis đúng; `bau-xao-tep` → "Mức 2"; `mam-ruoi` → "Mức 1"; trang món `/mon/banh-can` trả 200.

## Thống kê mức toàn kho (khớp 3 cách: file · DB · recompute)

| Trạng thái | Trước đợt | Sau đợt |
|---|---:|---:|
| Mức 3 | 0 | **187** |
| Mức 2 thường | 472 | **285** |
| Mức 2 (có note) | 87 | **114** |
| Mức 1 | 95 | **68** |
| **Tổng** | **654** | **654** |

Toàn vẹn cổng sau đợt (truy vấn DB kiểm chứng): 0 món Mức 3 thiếu `cultural_note`; 0 món Mức 3 có phủ <60%; 0 món nâng bù thiếu `level_note`; `level_note` của 27 món mới khớp ký tự tuyệt đối giữa file và DB.

## Kiểm chứng cổng đợt (DB kiểm chứng riêng, không đụng DB dev)

- `lacdoi:validate-data`: **0 🔴**, 24 🟡 — đều là các lớp cảnh báo đã biết (V9 kcal/khẩu phần ngoài 50–2000 ở món khẩu phần rất nhỏ, V10 mỡ lợn, V8 nguyên liệu chưa dùng, V7 tên gần trùng). So với 22 🟡 ở TCM-PROMOTE-001 có **+2 V9 mới** (`rau-den-luoc`, `rau-lang-luoc`): phát sinh vì khi lên Mức 2 seed mới tính dinh dưỡng/khẩu phần cho hai món rau luộc khẩu phần rất nhẹ (kcal/khẩu phần <50 — đúng bản chất món, cùng lớp với các V9 sẵn có), không phải lỗi dữ liệu.
- **Pest: 162/162 xanh toàn bộ** (629 assertions) — gồm cả 2 test `SwapTrayTest` từng đỏ ở các đợt trước (tính năng đổi món trong mâm của phiên song song nay đã hoàn thiện; không còn test đỏ nào, không test nào thuộc đợt này bị ảnh hưởng).
- **ExportStabilityTest đạt** (3/3): vòng seed→export vẫn là điểm cố định trên toàn cây `data/` sau khi sửa 214 file — khuôn file do script sửa giữ đúng định dạng exporter.
- Phạm vi file: đúng 214 file món thay đổi (187 Phần A + 27 Phần B); 7 file món đang dở của phiên song song (media diff) không nằm trong cả hai danh sách và không bị đụng.

## Còn lại sau đợt

- 68 món Mức 1: 21 món đạt phủ ≥60% nhưng chưa có `cultural_note` (nhóm chờ của VN-001) + 47 món chưa đạt tiêu chí phủ — nâng bù tiếp chỉ khi có ghi chú văn hoá đạt chuẩn ở các đợt VN-001 sau.
- 285 món Mức 2 thường chưa lên Mức 3: 178 món đạt phủ ≥60% nhưng chưa có `cultural_note` (hàng đợi VN-001 nhóm A — toàn bộ 187 món có ghi chú đã lên Mức 3, kiểm DB: 0 món Mức 2 thường phủ ≥60% còn ghi chú) + 107 món phủ <60% (phụ thuộc gieo nhãn TCM bổ sung).
- AUDIT-003 (rà soát toàn bộ lớp Mức 3) vẫn là chặng cuối của chương trình theo kế hoạch.
