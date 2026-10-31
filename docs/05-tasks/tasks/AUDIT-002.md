# AUDIT-002 — Rà soát toàn bộ (census) sau chương trình Mức 2

**Trạng thái:** ✅ HOÀN TẤT phần rà soát — phát hiện đã gom thành danh sách phân xử PX-01…PX-35 chờ chủ dự án quyết (báo cáo §4); chỉ 6 sửa cơ học thuộc ngoại lệ đã duyệt được áp trong đợt (§3 dưới đây). **Cập nhật 2026-11-01: pha khắc phục AUDIT-FIX đã xử lý xong nhóm A (PX-01…PX-16), PX-17, PX-34 và PX-28 — xem §6; các PX còn lại giữ nguyên trạng chờ quyết.**
**Phụ thuộc:** LV2-012 (HEAD `f8a368f`; kho 654 món — 475 Mức 2 / 179 Mức 1; 434 nguyên liệu; 82 hồ sơ quả; 1.087 ảnh).
**Căn cứ:** task chủ dự án xếp hàng 2026-10-31 ~23:49 — rà soát lại TOÀN BỘ sản phẩm, **không lấy mẫu ngẫu nhiên**, theo chuẩn tầm soát 2026-10-31 (kiểm 2 lượt độc lập loại dương tính giả; phân loại 🔴/🟡/tồn đọng đã biết). Đợt này **cơ bản chỉ đọc**: ngoại lệ duy nhất là lỗi cơ học thuần tuý vi phạm quy ước đã chốt, sửa kèm truy vết; mọi phát hiện còn lại chờ phân xử, không tự sửa hàng loạt.
**Báo cáo đầy đủ:** `~/workspace/goals/l-c-i-shake-hungry-development/files/bao-cao-ra-soat-toan-bo-sau-lv2-2026-10-31.md` (bao phủ từng luồng, bằng chứng từng mục PX, bảng calo trước/sau của các sửa cơ học).

---

## 1. Bao phủ

654/654 món · 434/434 nguyên liệu · 82/82 hồ sơ quả (1.387 dòng giá) · 1.087/1.087 ảnh đối chiếu 3 chiều · **2.185 URL duy nhất** census bằng curl, trong đó **407 URL** không-200/không-FDC được mở lại bằng trình duyệt ở lượt 2 (333 sống đúng · 10 sống nhưng sai nội dung · 24 chết hẳn · 40 không mở được do lỗi công cụ fetch — giữ trạng thái chưa kết luận). Số liệu FDC được kiểm chứng trên bản trích xuất USDA SR Legacy cục bộ (270/278 mã khớp tuyệt đối) thay cho mở trang, vì FDC đã đổi cấu trúc site.

## 2. Kết quả chính

- **Cấu trúc toàn kho sạch:** 0 món thiếu tên chính VI, 0 biến thể mồ côi, 0 dòng nguyên liệu trùng/trỏ file không tồn tại; media khớp tuyệt đối 1.087 = 1.087 = 1.087; kiểm cứng qua DB: **0 món `diet:chay` chứa nguyên liệu `origin:dong-vat`**; quy ước A4/Phương án A sạch sau khi loại dương tính giả (mọi ca quét máy nêu tên đều có phân xử trong file hoặc thuộc ngoại lệ đã ghi).
- **Mức 1:** 179/179 hồ sơ giữ mức còn đúng theo Phụ lục A của LV2-012; **không món nào bị bỏ sót đủ điều kiện nâng** (`com-tho`, `bun-suong` có ≥3 nguồn nhưng bị giữ đúng theo hồ sơ).
- **Phát hiện 🔴 chính (chi tiết + bằng chứng: báo cáo §4):**
  - **Nhóm A (PX-01…PX-16) — link rot làm rớt cổng nguồn:** 16 món Mức 2 còn <3 nguồn mở lại được; nặng nhất `bau-xao-tep` **0/3** (cả 3 nguồn 404), `canh-rau-lang-tom` 0 nguồn xác nhận sống; 3 món còn 1/3 (`canh-khoai-so`, `banh-canh-nam-pho`, `canh-khoai-tay-suon`).
  - **PX-17 — `ca-ro-dong-chien-xu`:** nguồn cá rô phi (sai loài) **không công khai** trong ghi chú, và tỉ lệ bột chiên xù của file đi theo nguồn sai loài này. Ca sai loài duy nhất không công khai; các ca sai loài/họ khác (PX-18…PX-24) đều đã ghi rõ trong file và gộp thành **một câu hỏi quy ước chung PX-25** (nguồn họ kỹ thuật có tính vào cổng 3 nguồn không; "độc lập" theo bài hay theo domain — 23 món có ≥2 bài cùng ĐMX).
  - **PX-26 — cặp `canh-hen` / `canh-hen-nau-chua`:** trùng tên chính tiếng Việt, công thức gần như một, cả hai Mức 2 — ứng viên gộp/biến thể theo tiền lệ tầm soát.
- **🟡 đáng chú ý:** PX-28 link rot FDC (260 URL 404 do FDC đổi site — số đã kiểm chứng độc lập, đề xuất đợt sửa URL riêng); PX-34 nhóm 23 món chờ đúng 1 URL chưa mở được (nặng nhất `canh-bong-tha`: 0/3 nguồn xác nhận sống nhưng 0 nguồn chết); PX-29 `qua-va`/`qua-sau` tổng macro >100 (tầng 6, đã gắn cờ chưa kiểm định); PX-32 ba ca A4.3 thiếu ghi chú ngoại lệ; PX-33 `dau-phu-kho-gung` còn schema cũ.

## 3. Sửa cơ học đã áp (ngoại lệ theo nhiệm vụ — truy vết đầy đủ ở báo cáo §5)

6 file nguyên liệu, mỗi file 1 dòng `oil_absorption_pct` theo nhóm CALC-004 đã hiệu chuẩn: `thit-bo-xay` 8,0→4,0 (số dư trước hiệu chuẩn); `ca-thu` null→9 (hải sản); `thit-bo-than`, `thit-bo-nam` null→4 (thịt); `trung-vit-muoi` null→4 (tiền lệ trứng); `su-hao` null→9 (sợi chiên). Kiểm chứng sau sửa trên DB kiểm chứng riêng: đúng 5 món đổi khoảng kcal theo hướng đúng (`banh-tom-ho-tay`, `com-chien-ca-man`, `com-chien-trung-muoi`, `com-dua-bo`, `com-rang-dua`), 475/475 vẫn tính đủ. Các ca nhóm không rõ ràng **không** tự sửa — chuyển PX-31.

## 4. Kiểm chứng cuối

Validate trên DB kiểm chứng riêng `shake_hungry_verify_audit`: **0 🔴 / 11 🟡** (toàn bộ thuộc lớp đã biết: V9 món nhỏ, V10 mỡ nguyên chất, V8 nguyên liệu chưa dùng, V7 hai cặp tên — PX-26/PX-27). Recompute **475/475 Mức 2 tính đủ**. Pest chạy lại sau sửa cơ học: **127/127 (494 assertions)**. Không seed DB dev. Commit chỉ pathspec của đợt (6 file nguyên liệu + AUDIT-002.md + ROADMAP + CLAUDE §0), không chạm 9 file món của phiên song song đang dở.

## 5. Việc chờ chủ dự án (từ báo cáo §8)

Đợt thay nguồn cho nhóm A · chốt quy ước họ nguồn PX-25 rồi xử lý nhóm B · lượt mở lại nhịp chậm cho PX-34/PX-16 · đợt sửa URL FDC PX-28 · phân xử cặp canh hến PX-26.

## 6. Trạng thái sau pha khắc phục AUDIT-FIX (2026-11-01)

Chi tiết đầy đủ: [AUDIT-FIX.md](./AUDIT-FIX.md). Tóm tắt trạng thái từng mục:

- **PX-01…PX-16 (nhóm A): ✅ ĐÃ XỬ LÝ** — 13 món khôi phục cổng 3 nguồn bằng nguồn thay thế đã mở kiểm chứng (đối chiếu lại định lượng từng món, không món nào phải sửa số); **3 món hạ Mức 2 → 1 có hồ sơ**: `canh-khoai-so` (PX-02 — không tìm được nguồn thay thế đạt chuẩn), `dau-phu-kho-chao` (PX-14 — không có nguồn đúng món đạt chuẩn), `canh-rau-lang-tom` (PX-16 — 2 URL cũ vẫn chưa kết luận, tổng xác nhận sống 2 < 3). `bau-xao-tep` (PX-01) khôi phục từ 0/3, bộ định lượng tép khô (A3) được 2 nguồn độc lập ủng hộ.
- **PX-17: ✅ ĐÃ XỬ LÝ** — loại nguồn sai loài cá rô phi (ghi công khai trong `import_note`), sửa tỉ lệ bột theo nguồn đúng loài Memart (bột xù 25 → 50 g, thêm bột mì 25 g/khẩu phần); món giữ Mức 2.
- **PX-34: ✅ ĐÃ MỞ LẠI TOÀN BỘ** — 9 URL kết luận sống đúng món, **0 URL xác nhận chết**, không món nào rớt cổng; phần còn lại giữ chưa kết luận kèm ngày thử lại trong hồ sơ từng món (`canh-bong-tha` và `banh-xu-xe` là hồ sơ yếu, ưu tiên thử lại đợt sau).
- **PX-28: ✅ ĐÃ SỬA** — 286 URL (260 FDC ID) chuyển sang mẫu API chính thức của USDA NAL; mẫu mới kiểm chứng 11/11 ID trước khi sửa hàng loạt và mở lại 260/260 sau sửa (HTTP 200, đúng thực phẩm); số dinh dưỡng không đổi. 8 mã NDB đời cũ không nằm trong tập URL này — giữ nguyên, ngoài phạm vi đợt.
- **PX-18…PX-27, PX-29…PX-33, PX-35: giữ nguyên trạng** — không thay đổi nào ở pha khắc phục tác động tới chúng; PX-25 và PX-26 vẫn chờ chủ dự án chốt.
- **Tổng Mức 2 sau khắc phục: 472/654.** Kiểm chứng cuối của AUDIT-FIX trên DB kiểm chứng riêng: validate 0 🔴, recompute 472/472, đối chiếu calo cách ly chỉ đúng 1 món đổi (`ca-ro-dong-chien-xu`, do PX-17), Pest 127/127, rà độc lập mở lại 100% nguồn thay thế mới (19/22 mở được đúng món, 3 URL ghi truy vết chưa kết luận/chống bot).
