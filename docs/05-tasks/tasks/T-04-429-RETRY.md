# T-04 — Thử lại nhóm 429: 33 món Mức 1 bị chặn kỹ thuật ở LV2-012

**Mã task:** T-04
**Phase:** GĐ3 — Sản xuất dữ liệu (chương trình mở rộng: nâng toàn bộ kho lên Mức 2)
**Trạng thái:** ✅ XONG 2026-11-02 — 7/33 món đạt cổng công thức thật; 26 món giữ nguyên mức + hồ sơ (xem §Kết quả)
**Phụ thuộc:** LV2-012 (HEAD nền `a310c22`; kho 654 món: 187 Mức 3 · 285 Mức 2 thường ·
114 Mức 2 (có note) · 68 Mức 1)
**Căn cứ:** chỉ đạo chủ dự án 2026-10-31 — nâng toàn bộ lên Mức 2, chất lượng trên hết, món
không đủ bằng chứng giữ Mức 1 + hồ sơ; danh sách tồn đọng T-04 trong báo cáo tổng hợp sau
Mức 3 (2026-11-01). Tiêu chí Mức 2 thật giữ NGUYÊN VẸN như LV2-001…LV2-012: ≥3 họ nguồn công
thức thật đã MỞ kiểm chứng/món, định lượng đối chiếu ≥2 họ (chỉ sửa khi lệch >25–30%), steps
6–8 bước viết bằng lời dự án, qua cổng V12; giữ `needs_review` + `source`/`confidence` từng
dòng cũ trừ dòng sửa theo phân xử; hồ sơ phân xử ghi vào `import_note`.

**Lưu ý trạng thái hiện tại của nhóm:** sau LV2-012, 22/33 món đã được nâng "Mức 2 (có note)"
theo đường nâng bù TCM (`level_basis='tcm_compensation'`) — đợt này xét cổng CÔNG THỨC thật:
món đạt cổng thì chuyển sang Mức 2 thường (`level_basis=null`, bỏ `level_note`); 11 món còn
lại đang Mức 1, đạt cổng thì lên Mức 2 thường.

## Danh sách 33 món (đối chiếu phụ lục A LV2-012 — mọi món "Giữ Mức 1 — 429")

1. `banh-bao-banh-vac` (hiện Mức 2 có note) · 2. `banh-beo-bi` (có note) · 3. `banh-bong-lan-trung-muoi` (Mức 1) ·
4. `banh-duc-nong` (có note) · 5. `banh-gai` (có note) · 6. `banh-gat-gu` (có note) ·
7. `banh-hoa-hong-trang` (có note — **định danh treo QD-15.24: chỉ lập hồ sơ, không nâng**) ·
8. `banh-khoai` (có note) · 9. `che-lac` (Mức 1) · 10. `che-rau-cau` (Mức 1) ·
11. `che-thach-den` (Mức 1) · 12. `com-xanh` (Mức 1) · 13. `don` (có note) ·
14. `goi-ca-me` (có note) · 15. `goi-rau-ma` (Mức 1) · 16. `hu-tieu-ca` (có note) ·
17. `kho-nhai` (có note) · 18. `khoai-lang-ken` (có note) · 19. `khoai-lang-luoc-trung` (có note) ·
20. `mam-ba-khia` (có note) · 21. `mam-ca-loc` (có note) · 22. `mam-ca-ro` (có note —
**định danh treo QD-11: chỉ lập hồ sơ, không nâng**) · 23. `mam-cay` (có note) ·
24. `mam-chua` (có note) · 25. `mam-cong` (có note) · 26. `mi-thanh-long` (Mức 1) ·
27. `mi-tron-kho` (Mức 1) · 28. `muc-kho-nuong` (có note) · 29. `muoi-ot-chanh` (Mức 1) ·
30. `nuoc-cham` (Mức 1) · 31. `rau-den-luoc` (có note) · 32. `rau-lang-luoc` (có note) ·
33. `suon-chay-rim` (Mức 1)

Không món nào trong 33 trùng 7 file đang sửa dở của phiên song song.

## Kỷ luật tra cứu đợt này

- Mở trang TUẦN TỰ, nhịp chậm; gặp 429 ở một họ thì chuyển họ khác hoặc để cuối lượt, không hammer.
- Nguồn tính cổng phải là trang công thức ĐÃ MỞ (không dùng trích xuất tìm kiếm), có định lượng,
  đúng biến thể trong file; 3 họ phải độc lập (ưu tiên khác site/họ).
- Món có dòng nguyên liệu chưa có số dinh dưỡng thì không nâng dù đủ nguồn (ghi rõ nguyên liệu null).
- Món định danh đang treo quyết (mục 7, 22): giữ nguyên mức/basis, chỉ ghi hồ sơ nguồn vào `import_note`.

## Kết quả (2026-11-02)

**Tổng: 7/33 ĐẠT cổng công thức thật · 26/33 giữ nguyên mức + hồ sơ phân xử trong `import_note` từng file.**

Nhóm đạt (6 món "Mức 2 (có note)" → Mức 2 thường, bỏ `level_basis`/`level_note`; 1 món Mức 1 → Mức 2):

| Món | Họ nguồn đã mở (độc lập) | Phân xử định lượng |
|---|---|---|
| `mam-ba-khia` | Điện Máy XANH (2 bài = 1 họ), Bếp Xưa, CKKTV | cua-bien 60→52, duong-trang 3→10, chanh 3→4 (giữ tổng 70g; đường đồng thuận 16–20% khối lượng ba khía) |
| `banh-duc-nong` | Cookpad Hoàng Ngọc, cụm Bửu Long (bản sao = 1 họ), aFamily (chỉ đối chiếu cấu trúc) + Bếp Xưa (điều phối bổ sung khi áp) | bot-gao 70→20, **thêm bot-nang 18**, thit-lon-xay 25→35, moc-nhi-kho 3→2, dau-an 4→8, nuoc-mam 5→9 |
| `banh-khoai` | Cookpad Hoàng Thị Tố Hà, tài liệu ĐH Duy Tân, MonNgonDonGian | thit-lon-ba-chi 30→50, trung-ga 25→35, gia-do 30→60 (đồng thuận 72–75, lấy cận dưới thận trọng), **thêm nam-rom 20** |
| `che-thach-den` | Hướng Nghiệp Á Âu, Eva (phần thạch độc lập), KinhDo Food | **không sửa** — mọi dòng trong đồng thuận (Mức 1 → Mức 2) |
| `khoai-lang-luoc-trung` | Hello Bacsi (qua Cục TDTT), PGR Việt Nam, My Auris | khoai-lang 180→130 (lệch +38%), serving_grams 230→180 |
| `rau-den-luoc` | Memart, Saigonese Baguette, Nông Sản Dũng Hà | **không sửa** — 140g trong mọi dải quy đổi đĩa gia đình |
| `rau-lang-luoc` | I Nấu Ăn Ngon, FPT Shop, Nông Sản Dũng Hà | **không sửa** — 140g trong dải đối chiếu theo tiền lệ rau luộc |

Nhóm giữ (26 món — chi tiết từng món trong `import_note` của file, tiền tố `[T-04 thử lại 429 2026-11-02]`):
- **Thiếu họ độc lập / chỉ 1 cụm sao chép:** banh-bao-banh-vac (2 họ xung đột), banh-beo-bi (1 họ), banh-gat-gu (1 cụm sao chép), muc-kho-nuong (định lượng theo "con").
- **Lệch biến thể / cần quyết biến thể:** goi-ca-me (chuẩn chấm chẻo/giấm mẻ), goi-rau-ma (bò+tôm chỉ 1 họ), hu-tieu-ca (không họ nào có khẩu phần; thiếu hẹ/tóp mỡ), suon-chay-rim (nước tương vs nước mắm chay; sốt phân tán ~18×), muoi-ot-chanh (giã khô 1 họ vs xay 3 họ), che-lac (2 họ bất đồng căn bản), che-rau-cau (rau câu 8g không họ nào xác nhận), mi-tron-kho (1 họ gần biến thể), mam-cay (biến thể trong/xổi), don (thiếu hành tây/hành lá ở 4/4 họ; don không quy đổi nguyên con → thịt).
- **Định danh treo quyết — chỉ lập hồ sơ:** mam-ca-ro (QD-11: nguồn VietKings/xedihue là 1 họ, đều ghi cá kình con), banh-hoa-hong-trang (QD-15.24: nguồn khớp bao–vạc Hội An, không khớp file), kho-nhai (QD-15.27 + nguồn chỉ 1 cụm sao chép, không gram).
- **Cần phân xử của chủ dự án:** banh-bong-lan-trung-muoi (phạm vi 3 phần vs chỉ-cốt, gắn QD-15.39), mam-ca-loc (quy đổi lô → thành phẩm; thính/đường phân kỳ 4–5×), nuoc-cham (kho không có nguyên liệu nước thường; serving là mô hình chưa pha loãng), mi-thanh-long (đổi định danh gói mì thương mại), mam-chua (file sai nguyên liệu chính — nguồn dùng cá nhỏ/tép, không dùng cá lóc; thiếu thính), mam-cong (1 họ gram mâu thuẫn Wikipedia về thời gian ủ).
- **Chờ mở khoá nguyên liệu (T-05):** banh-gai (la-gai chưa có kcal + file chưa có dòng lá gai), khoai-lang-ken (thiếu bot-chien-gion ở 4/4 họ nhưng nguyên liệu này null kcal ở lượng ~37g/khẩu phần; bột năng không đồng thuận).
- **Không có công thức định lượng trên web:** com-xanh (cốm ăn sống với chuối — 2 nguồn biên tập 0 gram; ghi nhận tổng dòng 150g > serving 100g cho đợt biên tập sau).

**Bức tranh mức toàn kho (đếm từ file, tại HEAD `7ad160d` — đã gồm T-05 và TCM-PROMOTE-003 của các phiên song song chạy cùng ngày):** trước đợt 194 Mức 3 · 279 Mức 2 thường · 113 Mức 2 (có note) · 68 Mức 1 → sau đợt **194 Mức 3 · 286 Mức 2 thường · 107 Mức 2 (có note) · 67 Mức 1** (6 món chuyển từ nâng bù sang Mức 2 thường + 1 món Mức 1 lên Mức 2; delta khớp đúng +7/−6/−1).

**Đối chiếu nguyên liệu null trước khi nâng:** không món nào trong 33 bị chặn bởi nguyên liệu chính thiếu số; các dòng gia vị thơm lượng vết có kcal null (la-dua, rau-ram, rieng, la-chanh) có tiền lệ ở nhiều món Mức 2/3 và engine vẫn tính được — không chặn cổng.

**Kiểm chứng (DB riêng `shake_hungry_t04`, seed từ cây làm việc đã áp T-04):** validate **0 🔴 / 23 🟡** (3.344 bản ghi; các 🟡 là lớp đã biết — V9 món rất ít kcal gồm cả rau-den-luoc/rau-lang-luoc, V7 cặp canh hến, V8 nguyên liệu chưa dùng); recompute **587/587 món ≥ Mức 2 tính đủ, 0 thiếu dữ liệu** (67 món Mức 1 chỉ có estimated_kcal như thiết kế); đối chiếu calo cách ly so với baseline `a310c22`: trong phạm vi T-04 **đúng 5 món đổi** — 4 món sửa định lượng (mam-ba-khia, banh-duc-nong, banh-khoai, khoai-lang-luoc-trung) + che-thach-den có dòng dinh dưỡng mới vì từ Mức 1 (chỉ estimated) lên Mức 2; rau-den-luoc/rau-lang-luoc **không đổi một số**; 6 món ngoài phạm vi đổi (bun-mam-chay, ca-kho, dau-bap-xao-toi, goi-tom-thit, nem-cuon, xoi-man) đều truy được về các commit song song cùng ngày (T-05 số nguyên liệu mới, A4.1, TCM-PROMOTE-003), không phải T-04. Pest toàn bộ ở cửa sổ sạch: **188 passed / 1 failed** — test đỏ duy nhất là `AllergenPresetTest` của phiên song song đang áp gói quyết dị ứng (QD-03/QD-04: họ đang sửa dở `TaggingRules`/`config/taxonomy`/test ngay trong cây làm việc, thêm nhóm hat-cay/mam-tom), **không liên quan T-04** (T-04 chỉ thêm bot-nang/nam-rom — không thuộc nhóm dị ứng nào); `ExportStabilityTest` đạt trong cùng lượt.

**Sự cố phối hợp ghi nhận:** bản áp T-04 lần đầu bị một phiên song song ghi đè mất (nghi export từ DB seed cây cũ) trước khi kịp commit; đã áp lại toàn bộ trên HEAD `7ad160d` và kiểm chứng lại từ đầu — số liệu trên là của bản áp lại.
