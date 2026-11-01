# AUDIT-004 — Kiểm tra độc lập độ chính xác giá trị Tính / Vị / Ngũ hành (tái suy dẫn mù)

**Trạng thái:** ✅ HOÀN TẤT (2026-11-01) — **không sửa dữ liệu/code trong đợt**; đầu ra: 1 mục sửa trích dẫn FIX-TCM4-1, 1 đợt gieo bổ sung đề xuất FIX-TCM4-2 (TCM-001d), 1 quy ước tài liệu FIX-TCM4-3, 1 ca link chuyển AUDIT-003 (FIX-TCM4-4).
**Phụ thuộc:** TCM-PROMOTE-002 (kho 654 món / 434 nguyên liệu / 227 NL có nhãn / 413 dòng nhãn).
**Căn cứ:** chủ dự án giao 2026-11-01 ~16:50, chỉ đạo chất lượng cao nhất, không giới hạn thời gian. Phạm vi tách khỏi AUDIT-003 (sống/chết nguồn, cổng mức, ghi chú, hiển thị census): đợt này kiểm **giá trị có đúng không** bằng con đường độc lập.
**Báo cáo đầy đủ:** `~/workspace/goals/l-c-i-shake-hungry-development/files/bao-cao-audit-004-chinh-xac-tinh-vi-2026-11-01.md`.

---

## 1. Bao phủ & phương pháp

- **Giai đoạn 1 (mù):** 9 agent độc lập chỉ nhận định danh của 227 NL, cấm đọc dữ liệu/TCM-001; tra theo thang nguồn, ghi nguyên văn + URL. Lô thịt tra 2 lượt độc lập bằng 2 họ kênh (lượt 2 dùng mirror cổ thư theo chỉ đạo bổ sung). Bao phủ 223/227; điều phối viên kiểm mẫu quote của 4 agent — 4/4 khớp nguyên văn.
- **Giai đoạn 2 (đối chiếu):** 413 dòng phân loại tự động; 185 dòng chưa khớp cùng nguồn đi tiếp **lượt 2 mở đúng `source_ref`** (85 URL gồm PDF/trang EN) — khớp quote/câu tính vị tự động 153, phân xử thủ công 32 (30 đạt, 2 vào ca trích dẫn dưới đây).
- **Giai đoạn 3:** script Python độc lập (viết lại từ tài liệu, không dùng code engine): P-DERIVE, element 413 dòng, tính lại 648 tổng hợp món đối chiếu DB riêng `shake_hungry_audit4`.
- **Giai đoạn 4:** API thật trên DB kiểm chứng (cổng riêng), 227 NL + 654 món, quy tắc chọn dòng chính cài lại độc lập.

## 2. Kết quả chính

- **🔴 Sai giá trị: 0/413.** Đã xác minh 413/413 dòng (208 khớp mù cùng nguồn · 183 lượt 2 · 20 suy diễn qua gốc đã xác minh · 2 giá trị đúng nhưng trích dẫn khuyết điểm).
- **13 dòng bị cờ "mâu thuẫn" ở lượt 1 đều đúng theo trích dẫn của chính chúng** — nguyên nhân gốc: trang nguồn chép nhiều cổ thư lệch nhau; dòng nhãn trích một cổ thư cụ thể và quote đó nằm nguyên văn trên trang. Mâu thuẫn liên nguồn hợp lệ, đã gieo song song đúng thiết kế.
- **P-DERIVE 20/20 đạt** (gương đúng, trần low, đủ marker, 9/9 gốc đã xác minh độc lập, điều kiện đơn thành phần còn đúng).
- **Element 413/413** nhất quán theo basis. **Tổng hợp độc lập 0/648 lệch** (5 trường; phân bố phủ 500/98/50 khớp công bố). **Hiển thị API 0 lỗi/881 endpoint.**

## 3. Phát hiện & khắc phục đề xuất (đã thực thi — xem §5)

- **FIX-TCM4-1 🟠 (trích dẫn):** `dau-do-hat` — giá trị bình/cam/toan đúng theo 《中國藥典》 (đã kiểm chứng qua A+ 「性平，味甘、酸」), nhưng trang tcmfans được ref dẫn làm "bản tái bản kiểm chứng" lại ghi "Slightly cool" (vi lương). Thay link kiểm chứng hoặc sửa câu ghi chú trong ref.
- **FIX-TCM4-2 (gieo bổ sung TCM-001d):** ~14 ứng viên truyền thống có thật chưa gieo: bí đao lương (CMRO), cần tây hàn (CMRO), đậu bắp lương (SK&ĐS), ếch hàn, nghêu hàn, thịt vịt lương ×2 + tim lợn hàn (Tuệ Tĩnh), lòng lợn lương （綱目 — lượt mù thịt 2 bắt được), cà tím hàn, dưa leo hàn, củ sen bình (ghi chú sống/chín), rau mùi lương (rễ/lá), quả sấu bình/hàn (rà lại họ nguồn). Danh sách đầy đủ: báo cáo §7.
- **FIX-TCM4-3 (tài liệu):** quy ước — bản EN của zhongyifangji/tcmfans có thể dịch sai tính vị (tiền lệ `rau-diep-ca`: "bitter" thay 辛； ref đã tự ghi rõ); chỉ dùng làm biển chỉ đường.
- **FIX-TCM4-4 (chuyển AUDIT-003):** URL phồn thể mục 鯔魚 trên cht.a-hospital.com trả 404 qua crawler; bản giản thể cùng bộ sách còn nguyên văn đỡ giá trị.

## 4. Kiểm chứng cuối

- Đối chiếu phân loại cuối: 413/413 dòng có phán quyết, 0 dòng không kết luận được.
- DB kiểm chứng `shake_hungry_audit4` giữ nguyên để tái kiểm (seed từ `data/` + `lacdoi:recompute-tcm` = 648 món).
- Không chạy Pest trong đợt (không đổi code/dữ liệu; chỉ thêm tài liệu). Sự cố giữa phiên: VM thay mới — DB kiểm chứng seed lại sạch trên instance mới, kết quả không ảnh hưởng.

## 5. Khắc phục FIX-TCM4 — ĐÃ XONG (2026-11-01)

- **FIX-TCM4-1 ✅ (trích dẫn `dau-do-hat`):** giá trị KHÔNG đổi (bình · cam/toan theo Dược điển). Đã thay liên kết kiểm chứng tcmfans bằng trang tái bản **A+醫學百科 mục 赤小豆** — mở kiểm chứng tận nơi:【性味】「性平，味甘、酸。」khớp văn bản Dược điển (chuyên khảo chép「甘酸，平。」, cơ nguyên gồm *Phaseolus angularis* = *Vigna angularis*). Ref nay ghi rõ: biến thể「微寒」có ở 《食性本草》 (dẫn trên cùng trang A+), và chính bản tcmfans cũ tự mang biến thể này trong bản EN ("Slightly cool") nên không còn dùng làm liên kết kiểm chứng.
- **FIX-TCM4-2 ✅ = TCM-001d (gieo bổ sung):** **+23 dòng cho 13 nguyên liệu** → tổng 436 dòng (chi tiết từng dòng + nguồn tại [TCM-001.md](./TCM-001.md) mục TCM-001d). Mọi nguồn đều mở lại kiểm chứng first-hand tại lúc gieo. **`rau-mui` là ứng viên duy nhất CHỜ**: vế lương từ 孟詵「平、微寒」chỉ cho tính không kèm vị — lập dòng sẽ phải mượn vị của nguồn khác (trộn nguồn) hoặc mở tiền lệ dòng thiếu vị chưa có thiết kế; ghi hồ sơ chờ tại TCM-001d. Phân xử kèm theo: cổ thư ghi kép hai tính → gieo cả hai vế; `bi-dao` không có dòng 食療本草 vì trang nguồn không mang câu tính vị của sách đó (hồ sơ §7 ghi nhận lại tại TCM-001d).
- **FIX-TCM4-3 ✅ (quy ước nguồn):** đã ghi tại [NGU-HANH-AM-DUONG.md](../../08-cultural/NGU-HANH-AM-DUONG.md) §10 — bản EN của zhongyifangji/tcmfans chỉ làm biển chỉ đường; giá trị lấy theo văn bản gốc Hán/Việt hoặc CMRO/Dược điển; giá trị chỉ có ở bản EN = chưa có nguồn.
- **FIX-TCM4-4:** thuộc AUDIT-003 (không nằm trong đợt này).
- **Ảnh hưởng tổng hợp** (DB kiểm chứng riêng `shake_hungry_tcm4`, diff trước/sau trên 648 món): độ phủ **không đổi** (500/98/50; 0 món qua ngưỡng khoá <30%); `has_conflicts` 220 → **259** (+39); 24 món confidence medium → low; nhãn tính hiển thị đổi ở 11 món; hành chủ đạo đổi ở 2 món. Chi tiết: TCM-001d.
- **Kiểm chứng đợt:** validate **0 🔴**; Pest **167/167** (693 assertions), ExportStabilityTest đạt.
