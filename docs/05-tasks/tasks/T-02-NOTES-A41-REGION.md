# [T-02 / T-03 / T-13] — Giải toả 7 file món bị giữ + ghi chú văn hoá + A4.1 + region_code

**Mã task:** T-02 (ghi chú 7 món) · T-03 (A4.1 `dau-bap-xao-toi`) · T-13 (`region_code` `mam-ruot-an-giang`)
**Giai đoạn:** GĐ6 · **Loại:** Dữ liệu
**Status:** ✅ DONE · **Ngày:** 2026-11-02
**Căn cứ:** phiếu tồn đọng T-01…T-13 + chỉ đạo chủ dự án 2026-11-02 "xử lý toàn bộ việc tồn đọng". Baseline: `a310c22` + 7 file món đang sửa dở (chỉ diff `media`) của phiên song song: `banh-canh-ca-loc`, `bun-thang`, `canh-bap-cai-cuon-thit`, `com-bo-kho`, `com-ga-tam-ky`, `dau-bap-xao-toi`, `rau-muong-luoc` — chính là 7 file VN-001 đợt 2/đợt 3 phải bỏ qua hai lần.

---

## Bước 1 — Kiểm và commit media (commit `9ea4ad3`)

Cả 7 file: diff chỉ gồm khối `media` (+9/−1 dòng/file); file ảnh đích tồn tại thật tại `data/media/dishes/<slug>.webp` (WebP hợp lệ, đã tracked), license CC + attribution đầy đủ trong file:

| Món | License | Attribution |
|---|---|---|
| banh-canh-ca-loc | CC BY-SA 4.0 | Phương Huy |
| bun-thang | CC BY-SA 3.0 | Nguyễn Thanh Quang |
| canh-bap-cai-cuon-thit | CC BY 3.0 | Nguyen Thi Quynh Tram |
| com-bo-kho | CC BY-SA 4.0 | Gody Mago |
| com-ga-tam-ky | CC BY-SA 3.0 | Đông Sơn |
| dau-bap-xao-toi | CC BY-SA 3.0 | Kham Tran |
| rau-muong-luoc | CC BY-SA 3.0 | Buileducanh |

→ commit pathspec đúng 7 file, giải toả hoàn toàn. Không file nào phải dừng.

## Bước 2 — Ghi chú văn hoá cho 7 món (commit `62e48f5`) — chuẩn VN-001 nguyên vẹn

Đã viết **6/7** (mỗi ghi chú 3 câu, ≥1 nguồn biên tập đã **mở kiểm chứng tận nơi** tại thời điểm viết — census của đợt chính là lượt mở này: 6/6 URL sống và khớp nội dung; 0 vi phạm khung y tế; diff thuần chèn +9/−0 dòng/file, khối `cultural_note` đặt ngay sau `import_note` theo khuôn exporter):

| Món | Nguồn (đã mở) |
|---|---|
| banh-canh-ca-loc | Wikipedia tiếng Việt — *Bánh canh* (viết ở cấp họ món: nguồn gốc Đông Nam Bộ đầu TK XX, sợi bột gạo, nước lèo tôm/cá/giò heo, cá róc xương; không gắn khẳng định xuất xứ Quảng Bình/Quảng Trị của wiki cho biến thể cá lóc vì file định danh miền Tây và đã có slug riêng `banh-canh-ca-loc-quang-tri`) |
| bun-thang | Sức khỏe & Đời sống (Gia đình) — *Bún thang Hà Nội - món ăn bình dân trong lớp vỏ quý tộc* (gốc "tổng kết Tết" tận dụng đồ thừa, nước gà + sá sùng, mắm tôm/cà cuống) |
| canh-bap-cai-cuon-thit | Thanh Niên (2016) — *Món dụ trẻ không thích ăn rau: Thơm ngọt bắp cải cuốn* (cuốn cột bằng hành lá trụng, nấu nước trong; ký ức tuổi thơ/bữa cơm nhà) |
| com-bo-kho | Wikipedia tiếng Việt — *Bò kho* (miền Nam; tên "kho" nhưng nấu chính là hầm; dọn với cơm nóng hoặc bánh mì) |
| com-ga-tam-ky | Thương hiệu & Công luận — *Cơm gà Tam Kỳ - Hương vị đậm đà từ xứ Quảng* (Tam Kỳ từ ~những năm 1950; gà ta thả vườn; cơm nước luộc gà + nghệ; bỏ qua khẳng định chỉ dẫn địa lý chưa kiểm chứng chéo trong bài) |
| rau-muong-luoc | VnExpress Cooking — *Mâm cơm ngày hè xưa Bắc Bộ* (mâm hè Bắc Bộ: rau luộc + nước rau đánh sấu + cà dầm tương; ca dao "Nhớ canh rau muống, nhớ cà dầm tương"; đã cắt sạch đoạn khung Đông y của bài) |

**Để chờ — `dau-bap-xao-toi` (nhóm A):** tra cứu nhiều vòng chỉ gặp trang công thức thương mại/SEO (cholaithieu, memart…) và bài về nguyên liệu đậu bắp theo khung sức khoẻ — không có nguồn biên tập đạt chuẩn viết cho chính món. Không hạ chuẩn; món giữ Mức 2 (phủ TCM 93,15% — chỉ thiếu ghi chú là đủ cổng Mức 3 ở lần quét sau).

## Bước 3 — T-03: A4.1 cho `dau-bap-xao-toi` (commit `555f04c`) — đóng tồn đọng LV2-012 §5

Áp quy ước cấp kho A4.1 (tiền lệ LV2-010 Phần C — `dau-que-xao-toi`, `rau-muong-xao-toi`): bỏ dòng `nuoc-mam-truyen-thong` 4 g; thêm `muoi` 1 g + `hat-nem` 2 g (giữ nguyên khuôn dòng: `llm_suggested`/low); bước 4 của `recipe_steps` đổi sang nêm muối + hạt nêm; `import_note` appended đoạn phân xử (họ ToyenXin có nước mắm 1 muỗng = 1/3 họ; họ Bếp Mina nêm muối + hạt nêm làm nền; món thuộc nhóm "áp bổ sung", không thuộc 2 ngoại lệ chính thức `kho-qua-xao-trung`/`bau-xao-tep`; phân xử cũ của LV2-004 được quy ước cấp kho thay thế).

Calo/khẩu phần (engine 1.1.0, recompute trên 2 DB kiểm chứng riêng seed từ file trước/sau):

| | Trước A4.1 | Sau A4.1 |
|---|---:|---:|
| kcal (min–max) | 101,00 – 149,20 | **98,50 – 147,20** |
| đạm (g) | 3,70 | 2,80 |
| béo / bột đường (g) | 8,30 / 11,30 | 8,30 / 11,30 (không đổi) |

Δ ≈ −2,0…−2,5 kcal — đúng kỳ vọng cho thay đổi dòng nêm. Lưu ý trung thực của engine: `hat-nem` chưa có số dinh dưỡng trong kho → engine liệt kê vào `missing_ingredients` và tính 0 cho dòng đó thay vì bịa số (QT-1).

## Bước 4 — T-13: `region_code` cho `mam-ruot-an-giang` (commit `1f5d9a1`)

Món là file duy nhất toàn kho có `region_code: null` (hàng đợi định danh đợt 3 của VN-001, mục 5). Định danh: mắm ruột Châu Đốc, An Giang → gán **`vn-south`** theo quy ước kho (mọi món An Giang khác đều dùng `vn-south` kèm tag `dac-san:an-giang`; đợt này chỉ bổ sung `region_code` theo đúng phạm vi phiếu T-13, không thêm tag).

## Bước 5 — Quét lại cổng toàn kho → TCM-PROMOTE-003

Xem [TCM-PROMOTE-003](TCM-PROMOTE-003.md): 6 món ghi chú mới + `xoi-man` (đủ cổng nhờ T-05) lên Mức 3; nâng bù 0 món mới. Bức tranh cuối: **194 Mức 3 · 279 Mức 2 thường · 113 Mức 2 (có note) · 68 Mức 1 = 654**; cổng sạch hai chiều (0 vi phạm, 0 ứng viên sót) trên DB kiểm chứng tại HEAD cuối.

## Kiểm chứng gói

- `lacdoi:validate-data` (DB kiểm chứng `shake_hungry_t02`, worktree sạch): **0 🔴**.
- Pest tại checkout chính (DB test mặc định, cửa sổ sạch — đã kiểm tra không có tiến trình test của phiên song song): **cả hai lượt chạy toàn bộ đều xanh tuyệt đối — 177/177 và 187/187** (số test tăng giữa hai lượt do phiên song song bổ sung test mới), gồm ExportStabilityTest đạt. *Đính chính cùng ngày (2026-11-02):* bản đầu của mục này ghi lượt đầu 186/187 với “1 đỏ SwapTrayTest không tái lập” — đó là đọc nhầm đầu ra trung gian giữa lúc chạy; đầu ra cuối (terminal, exit 0) của cả hai lượt đều không có test đỏ nào. (Lượt Pest thử trong worktree bị lỗi hạ tầng do vendor symlink làm lệch namespace test — đã loại bỏ, không dùng làm bằng chứng.)
- Kỷ luật phối hợp: mọi commit đều pathspec riêng; không chạm file đang dở của T-04/T-05 (chỉ đọc trạng thái); seed/validate chạy trên worktree + DB riêng `shake_hungry_t02`/`shake_hungry_t02b`, không đụng DB dev/test.

## Các commit của gói

`9ea4ad3` (media) · `555f04c` (A4.1) · `1f5d9a1` (region) · `62e48f5` (ghi chú) · `7ad160d` + `61819bc` (TCM-PROMOTE-003) · commit tài liệu QT-7 đi kèm (VN-001, LV2-012 §5, ROADMAP, CLAUDE §0 lần 40).
