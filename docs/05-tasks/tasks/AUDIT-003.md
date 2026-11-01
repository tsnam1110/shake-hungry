# AUDIT-003 — RÀ SOÁT CENSUS TOÀN LỚP MỨC 3 (nguồn sống/chết, tổng hợp, cổng mức, ghi chú, components, hiển thị)

**Trạng thái:** ✅ HOÀN TẤT (2026-11-01) — **không sửa dữ liệu/code trong đợt**; đầu ra: nhóm 🔴 trích dẫn AX3-01…03 (4 URL / 6 dòng), nhóm 🟡 AX3-05…10, danh sách khắc phục AUDIT-003-FIX (báo cáo §9). Đã tiếp nhận và phân xử ca chuyển **FIX-TCM4-4** từ AUDIT-004 (ca-doi — xem AX3-02).
**Phụ thuộc:** TCM-PROMOTE-002 (kho 654 món / 434 nguyên liệu / 227 NL có nhãn / 413 dòng nhãn tại baseline rà).
**Căn cứ:** chủ dự án giao 2026-11-01 — chặng cuối chương trình Mức 3, chuẩn AUDIT-002: census 100%, hai lượt, phát hiện 🔴 kiểm chứng độc lập lượt 2 trước khi kết luận.
**Báo cáo đầy đủ:** `~/workspace/goals/l-c-i-shake-hungry-development/files/bao-cao-audit-003-ra-soat-lop-muc-3-2026-11-01.md`.

---

## 1. Bao phủ & phương pháp

- **Baseline:** trích xuất census trên trạng thái 413 dòng nhãn. Giữa đợt, FIX-TCM4 (`a0414e2`/`a7fcb56`) nâng corpus lên 436 dòng (TCM-001d) — phán quyết tổng hợp/cổng gắn với baseline 413; 23 dòng mới chưa qua census nguồn của đợt này (báo cáo §0b).
- **Môi trường:** DB kiểm chứng riêng `shake_hungry_audit3` (migrate:fresh --seed từ `data/` + recompute-tcm → 648 tổng hợp: 500/98/50). Validate 0 🔴 / 24 🟡 / 3.344 bản ghi. Pest tại thời điểm chạy: 162/162 (gồm ExportStabilityTest + TcmFoundationTest đạt).
- **Tổng hợp độc lập:** Python viết lại từ tài liệu thiết kế (không dịch từ PHP) đối chiếu DB: **0/648 lệch** ở cả 5 trường. API: món <30% → `tcm: null`; món 59,67% → `is_partial`, confidence low — quy tắc ngưỡng đạt.
- **Cổng mức hai chiều (coverage độc lập):** 187/187 Mức 3 đạt; 114/114 nâng bù vẫn ≥60% (0 trôi), số % trong level_note khớp 114/114; 0 ca sót đủ điều kiện ở cả hai chiều ngược.
- **Census URL hai kênh:** curl 596/596 URL (131 nhãn + 465 ghi chú); mở trang đọc nội dung bởi 7 kiểm chứng viên độc lập — nhãn 131/131 có lần mở, ghi chú đọc được 359/465 (106 URL bị dịch vụ mở trang chặn 429/lỗi fetch → chưa kết luận, có lượt curl phủ trạng thái).
- **Ghi chú:** rà khung y tế bị cấm (2 bộ mẫu) trên 463/463 ghi chú: **0 vi phạm thật**. Phân tích hợp tập nguồn theo ghi chú: ≥12 cụm phủ chéo xác nhận.
- **Components:** 39/39 liên kết tồn tại + hợp lý ngữ nghĩa; traits∩components = ∅.
- **Hiển thị:** render DOM thật 55/55 ca đạt chuẩn TCM-003 (3 dòng màu/icon + popup) trên mọi trạng thái.

## 2. Kết quả chính

- **Giá trị tính vị: 0 sai** (census nguồn khớp nguyên văn; hội tụ với AUDIT-004: 413/413 xác minh, sai 0/413).
- **🔴 AX3-01 `ca-chim`:** URL trích dẫn sai loài — ký tự 鯇 (cá trắm cỏ, 溫/甘) thay 鯧 (cá chim, 平/甘).
- **🔴 AX3-02 `ca-doi` ×2 (= FIX-TCM4-4):** URL trượt ký tự （鱸魚 thay 鯔魚） trả 404 hai kênh; bản phồn thể đúng ký tự cũng rot (AUDIT-004); bản giản thể cùng mục còn nguyên văn.
- **🔴 AX3-03 lớp "URL dính dấu chấm cuối" (3 dòng):** dau-do-hat ×2 (URL tcmfans tại census; FIX-TCM4-1 đã đổi sang A+ 赤小豆 nhưng URL mới vẫn dính dấu chấm) + rau-diep-ca #2 (zhongyifangji `id/394.`). Token trích ra được → 404; đích không-dấu-chấm tồn tại đúng nội dung; giá trị gieo đều đúng.
- **🟡 AX3-05…10:** 8 dòng không-URL (nội dung kiểm chứng 8/8 tại trang mục); hat-tieu-den chưa kết luận nội dung; 106 URL ghi chú chưa kết luận (6 ghi chú có nguồn then chốt: thit-rang-chay-canh, bun-ken, canh-cua-mong-toi, lekima, che-ba-ba, bun-hen); chi tiết phụ chưa có căn cứ đã mở trong một số ghi chú (AX3-08); diễn đạt chè Thái (AX3-09); corroboration định danh mam-ca-ro từ nguồn của chính ghi chú (AX3-10).

## 3. Khắc phục đề xuất (AUDIT-003-FIX — chưa thực thi)

Theo báo cáo §9: sửa ký tự URL ca-chim; thay URL ca-doi bằng bản giản thể 鯔魚； tách dấu chấm cuối ở 3 token; bổ sung URL cho 8 dòng không-URL; kiểm lại hat-tieu-den; census bổ sung 106 URL ghi chú; xử lý chi tiết dư AX3-08 + diễn đạt chè Thái; quét lại cổng + census cho 23 dòng TCM-001d. Mọi thay đổi chỉ trong `source_ref`/diễn đạt ghi chú — không đụng giá trị, định lượng, mức.

## 4. Kiểm chứng cuối

- Đối chiếu phân loại cuối: nhãn 413/413 dòng có phán quyết; ghi chú 463/463 có phán quyết ở tầng cấu trúc + diễn đạt, 359/465 URL có phán quyết nội dung (phần còn lại ghi rõ chưa kết luận + lý do công cụ).
- Không sửa dữ liệu/code trong đợt; DB kiểm chứng `shake_hungry_audit3` giữ để tái kiểm.
