# FRUIT-002 — Bổ sung nguyên liệu quả còn thiếu (~78 loại)

- **Trạng thái:** ✅ (2026-10-31 — 53 nguyên liệu quả mới; tổng kho trai-cay 85: **79 có số, 6 để null có hồ sơ**)
- Kết quả: nguồn tầng 1–2 chiếm đa số (SR Legacy/Foundation/Survey ~58/79); tầng 6 cho quả vùng:
  măng cụt (MEXT), vú sữa + bòn bon (FNRI), dâu da + thanh trà (Thai FCT), dưa gang (MEXT),
  thốt nốt + quả điều (IFCT), mơ ta (MEXT), nho thân gỗ (TBCA), mây + cóc (TKPI), lêkima (Peru
  CENAN), thị/vả/sim/táo ta (văn liệu). 6 null có hồ sơ: cà na, nhót, mít tố nữ, quả xây, mắc
  cọp, táo mèo — đều là quả dại/vùng cao không bảng nào công bố đạt chuẩn.
- Kèm theo: đăng ký 2 mã nguồn tầng 6 mới (`tbca_br`, `tkpi_id` — bảng quốc gia của nước bản
  địa loài) vào SourceTier + DATA-PIPELINE §7.2; TaggingRules thêm state:kho cho chà là/la hán/
  quả xây; đồng bộ natri cui-dua-nao từ cùng bản ghi SR 170169.
- Lưu ý: như mọi nguyên liệu trong kho, 53 file giữ `needs_review=true` (trục duyệt nguyên liệu riêng).
- **Phụ thuộc:** —
- **Nhánh:** Hoa quả (FRUIT-MODULE)

Theo thang nguồn RESEARCH-PROTOCOL hiện hành (kể cả tầng 6 có nhãn). Gồm quả trong nước còn thiếu và quả nhập khẩu lưu hành tại VN. Danh sách chốt sau rà soát chéo thực tế lưu hành; mỗi file đạt chuẩn R7/R8 như kho hiện tại.
