<?php

declare(strict_types=1);

namespace App\Taxonomy;

/**
 * Quy tắc gán nhãn NGUYÊN LIỆU (TAXONOMY.md §3–§4).
 *
 * Mọi nguyên liệu nhận: đúng 1 nhãn origin (theo category + bảng ngoại lệ),
 * đúng 1 nhãn state (theo bảng trạng thái tường minh), 0..n nhãn allergen
 * (theo bảng ánh xạ tường minh). Các bảng ở đây là nơi DUY NHẤT định nghĩa
 * ngoại lệ — script gán nhãn và validate-data (V16) cùng đọc từ đây để kết
 * quả trong file dữ liệu luôn tái lập được 100% từ quy tắc.
 */
final class TaggingRules
{
    /** Origin mặc định theo category. */
    public const ORIGIN_BY_CATEGORY = [
        'thit' => 'dong-vat',
        'thuy-san' => 'dong-vat',
        'rau-cu' => 'thuc-vat',
        'rau-gia-vi' => 'thuc-vat',
        'trai-cay' => 'thuc-vat',
        'tinh-bot' => 'thuc-vat',
        'gia-vi' => 'thuc-vat',
        'dau-mo' => 'thuc-vat',
        'khac' => 'thuc-vat',
    ];

    /** Ngoại lệ origin: slug → origin (ghi đè mặc định theo category). */
    public const ORIGIN_EXCEPTIONS = [
        // Nấm — category rau-cu nhưng không phải thực vật
        'moc-nhi-kho' => 'nam', 'nam-rom' => 'nam', 'nam-huong-kho' => 'nam',
        'nam-kim-cham' => 'nam', 'nam-bao-ngu' => 'nam',
        'nam-dui-ga' => 'nam', // nấm đùi gà Pleurotus eryngii (đợt DISH-005 đợt 3)
        // Mắm và chế phẩm từ động vật (category gia-vi)
        'nuoc-mam-cong-nghiep' => 'dong-vat', 'nuoc-mam-truyen-thong' => 'dong-vat',
        'mam-ca-linh' => 'dong-vat', 'mam-ca-sac' => 'dong-vat', 'mam-nem' => 'dong-vat',
        'mam-ruoc' => 'dong-vat', 'mam-tom' => 'dong-vat',
        'mam-tom-chua' => 'dong-vat', // mắm tôm chua = tôm ủ chua nguyên con (đợt DISH-005 đợt 2)
        'dau-hao' => 'dong-vat', // dầu hào — chiết xuất hàu
        'mat-ong' => 'dong-vat', // mật ong — sản phẩm của ong
        'sa-te' => 'dong-vat', // sa tế thương mại VN phổ biến có tôm khô — lấy chiều an toàn cho nhãn chay
        // Mỡ động vật (category dau-mo)
        'bo-dong-vat' => 'dong-vat', 'mo-lon-nuoc' => 'dong-vat',
        // Sữa, trứng và chế phẩm (category khac)
        'pho-mai' => 'dong-vat', 'sua-chua' => 'dong-vat', 'sua-dac' => 'dong-vat',
        'gelatin' => 'dong-vat', 'kem-whipping' => 'dong-vat', // collagen động vật + kem sữa — LV2-007 Phần B
        'sua-tuoi' => 'dong-vat', 'trung-vit-lon' => 'dong-vat', 'trung-vit-muoi' => 'dong-vat',
        'long-do-trung-ga' => 'dong-vat', 'gelato-vani' => 'dong-vat', 'bot-trung-muoi' => 'dong-vat',
        // Sản phẩm công nghiệp phối trộn nhiều nguồn gốc
        'bot-canh' => 'tong-hop', 'hat-nem' => 'tong-hop', 'xot-mayonnaise' => 'tong-hop',
        'mi-trung-kho' => 'tong-hop', // mì trứng = bột mì + trứng
        'banh-phong-tom-chien' => 'tong-hop', 'banh-phong-tom-song' => 'tong-hop', // tôm + tinh bột sắn
        'nuoc-ngot-cola' => 'tong-hop', 'mi-chinh' => 'tong-hop',
        'vo-hoanh-thanh' => 'tong-hop', // bột mì + trứng (đợt DISH-003)
        // Khoáng chất
        'muoi' => 'khoang-chat',
        'muoi-tom' => 'tong-hop', // hỗn hợp muối + tôm khô + ớt — LV2-007
        'men-no' => 'tong-hop', 'bot-no' => 'khoang-chat', // men nuôi cấy công nghiệp + bột nở muối khoáng — LV2-007 Phần B
        'bot-rang-muoi' => 'tong-hop', // hỗn hợp muối + gạo nếp rang + đậu xanh rang xay, không thành phần động vật — LV2-010 Phần C
    ];

    /**
     * Nguyên liệu origin:tong-hop VẪN tính là động vật khi suy nhãn diet của món,
     * kèm mức ảnh hưởng: 'man' (như thịt) hoặc 'trung-sua' (như trứng/sữa).
     */
    public const COMPOSITE_ANIMAL = [
        'bot-canh' => 'man', 'hat-nem' => 'man',
        'muoi-tom' => 'man', // có bột tôm — LV2-007
        'banh-phong-tom-chien' => 'man', 'banh-phong-tom-song' => 'man',
        'xot-mayonnaise' => 'trung-sua',
        'mi-trung-kho' => 'trung-sua',
        'vo-hoanh-thanh' => 'trung-sua', // vỏ hoành thánh = bột mì + trứng
    ];

    /** State tường minh theo slug; slug không có trong bảng ⇒ 'tuoi'. */
    public const STATE_BY_SLUG = [
        // Khô
        'tom-kho' => 'kho', 'muc-kho' => 'kho', 'hat-sen-kho' => 'kho', 'rau-cau-kho' => 'kho',
        'mi-trung-kho' => 'kho', 'banh-pho-kho' => 'kho', 'bun-kho' => 'kho', 'hu-tieu-kho' => 'kho',
        'moc-nhi-kho' => 'kho', 'nam-huong-kho' => 'kho', 'banh-trang' => 'kho', 'banh-da-do' => 'kho', 'tau-hu-ky' => 'kho',
        'suon-chay' => 'kho', // sườn non chay = đạm đậu nành ép dạng khô, ngâm nở trước khi nấu (đợt DISH-005 đợt 3)
        'banh-phong-tom-song' => 'kho', 'mang-kho' => 'kho', 'la-gai' => 'kho',
        'xa-bau' => 'kho', 'mien-dong' => 'kho', 'mi-an-lien' => 'kho',
        'vo-hoanh-thanh' => 'kho', 'nui-kho' => 'kho', 'bot-chien-xu' => 'kho',
        'hanh-nhan' => 'kho', 'bong-bi' => 'kho',
        'dau-den-hat' => 'kho', 'dau-do-hat' => 'kho', 'dau-nanh-hat' => 'kho', 'dau-xanh-hat' => 'kho',
        'dau-ngu' => 'kho', 'dau-trang' => 'kho', 'hat-sen-tuoi' => 'tuoi',
        'gao-nep' => 'kho', 'gao-te' => 'kho', 'y-di' => 'kho',
        'bot-bang' => 'kho', 'bot-gao' => 'kho', 'bot-mi' => 'kho', 'bot-nang' => 'kho',
        'bot-nep' => 'kho', 'bot-ngo' => 'kho', 'bot-san-day' => 'kho', // tinh bột sắn dây khô — QD-15 (2026-11-02, biên soạn slug mới từ vn_nin 02021)
        'bot-chien-gion' => 'kho', // bột trộn khô đóng gói — LV2-004
        'bot-canh' => 'kho', 'bot-cari' => 'kho', 'bot-nghe' => 'kho', 'bot-que' => 'kho',
        'ngu-vi-huong' => 'kho', 'ot-bot' => 'kho', 'hat-tieu-den' => 'kho', 'hat-dieu-mau' => 'kho',
        'hoa-hoi' => 'kho', 'thao-qua' => 'kho', 'ky-tu' => 'kho', 'la-nguyet-que' => 'kho',
        'mac-khen' => 'kho', 'hat-doi' => 'kho', // gia vị khô Tây Bắc — đợt mở rộng kho 2026-10-31
        'thinh-gao' => 'kho', 'duong-phen' => 'kho', 'duong-thot-not' => 'kho', 'duong-trang' => 'kho',
        'muoi' => 'kho', 'mi-chinh' => 'kho', 'hat-nem' => 'kho',
        'muoi-tom' => 'kho', 'tran-chau' => 'kho', // gia vị khô + hạt tinh bột sắn khô — LV2-007
        'bot-khoai' => 'kho', 'gelatin' => 'kho', // sợi tinh bột sắn khô + bột gelatin khô — LV2-007 Phần B
        'kem-whipping' => 'dong-hop', 'cha-bong' => 'che-bien', // kem hộp lạnh + ruốc thịt thành phẩm — LV2-007 Phần B
        'men-no' => 'kho', 'bot-no' => 'kho', // chất nở dạng bột khô — LV2-007 Phần B
        'bot-rang-muoi' => 'kho', // bột gia vị khô tự phối — LV2-010 Phần C
        'lac-song' => 'kho', 'lac-rang' => 'kho', 'vung' => 'kho', 'vung-rang' => 'kho',
        'hat-dieu-rang' => 'kho',
        'tao-tau-kho' => 'kho', 'nhan-kho' => 'kho', 'qua-danh-danh' => 'kho',
        'la-han' => 'kho', 'qua-xay' => 'kho', // quả lưu hành dạng khô (FRUIT-002)
        'cha-la' => 'kho', // chà là lưu hành dạng khô (FRUIT-002)
        // Lên men
        'mam-ca-linh' => 'len-men', 'mam-ca-sac' => 'len-men', 'mam-nem' => 'len-men',
        'mam-ruoc' => 'len-men', 'mam-tom' => 'len-men', 'nuoc-mam-cong-nghiep' => 'len-men',
        'nuoc-mam-truyen-thong' => 'len-men', 'tuong-ban' => 'len-men',
        'chao' => 'len-men', 'nem-chua' => 'len-men', 'sua-chua' => 'len-men',
        'mang-chua' => 'len-men', // măng muối chua lên men — đợt DISH-005
        'mam-tom-chua' => 'len-men', // tôm ủ chua lên men — đợt DISH-005 đợt 2
        'ruou-trang' => 'len-men', 'ruou-vang-do' => 'len-men', 'bia' => 'len-men',
        'dam-gao' => 'len-men', 'nuoc-tuong' => 'len-men', 'tuong-den' => 'len-men',
        'me' => 'len-men', 'giam-bong' => 'len-men', // cơm mẻ + giấm bỗng lên men — LV2-004
        'dua-cai-muoi' => 'len-men',
        // Đóng hộp / đóng gói dạng lỏng
        'sua-dac' => 'dong-hop', 'sua-tuoi' => 'dong-hop', 'sua-dau-nanh' => 'dong-hop',
        'nuoc-ngot-cola' => 'dong-hop', 'nuoc-cot-dua' => 'dong-hop',
        'dau-an' => 'dong-hop', 'dau-me' => 'dong-hop', 'sua-bien' => 'dong-hop',
        // Chế biến sẵn
        'gio-lua' => 'che-bien', 'lap-xuong' => 'che-bien', 'xuc-xich' => 'che-bien',
        'pate-gan' => 'che-bien', 'cha-bong' => 'che-bien', // ruốc thịt lợn thành phẩm — LV2-007 Phần B
        'thit-xong-khoi' => 'che-bien', 'cha-ca-vien' => 'che-bien',
        'dau-phu' => 'che-bien', 'dau-phu-chien' => 'che-bien', 'mi-can' => 'che-bien',
        'cha-lua-chay' => 'che-bien', // chả chay thành phẩm hấp chín, bán dạng cây (đợt DISH-005 đợt 3)
        'banh-mi' => 'che-bien', 'com-trang' => 'che-bien',
        // tương cà/tương ớt công nghiệp không lên men; chân gà đã luộc; trà/cà phê trong kho là NƯỚC PHA (theo tên + kcal ≈ 1)
        'tuong-ca' => 'che-bien', 'tuong-ot' => 'che-bien', 'chan-ga' => 'che-bien',
        'ca-phe-den' => 'che-bien', 'tra-xanh' => 'che-bien',
        // Nhóm bar / nước uống (hoàn thiện lõi nước uống 2026-11-03): trà đen
        // và espresso là nước pha như tra-xanh/ca-phe-den; soda/tonic/nước ép
        // cà chua/ginger beer/grenadine/orgeat là đồ chế biến sẵn; rượu mạnh,
        // rượu mùi, vermouth, prosecco xếp len-men cùng bia/rượu trong kho.
        'tra-den' => 'che-bien', 'ca-phe-espresso' => 'che-bien',
        'soda' => 'che-bien', 'tonic' => 'che-bien', 'nuoc-ep-ca-chua' => 'che-bien',
        'nuoc-ep-cam' => 'che-bien', 'gelato-vani' => 'che-bien', 'sot-socola' => 'che-bien', 'bot-trung-muoi' => 'che-bien', 'long-do-trung-ga' => 'tuoi', // nước ép cam vắt tươi — chế biến sẵn như nước ép cà chua, không phải nguyên liệu tươi nguyên quả
        'siro-duong' => 'che-bien', // siro đường tự pha — chế biến sẵn, không phải nguyên liệu tươi
        'tran-chau-da-nau' => 'che-bien', // trân châu đã nấu ướp đường — chế biến sẵn, không phải nguyên liệu tươi
        'ginger-beer' => 'che-bien', 'grenadine' => 'che-bien', 'orgeat' => 'che-bien',
        'vodka' => 'len-men', 'gin' => 'len-men', 'rum' => 'len-men',
        'tequila' => 'len-men', 'whiskey' => 'len-men', 'triple-sec' => 'len-men',
        'kahlua' => 'len-men', 'campari' => 'len-men', 'aperol' => 'len-men',
        'vermouth-ngot' => 'len-men', 'vermouth-kho' => 'len-men', 'prosecco' => 'len-men',
        'hat-chia' => 'kho', 'matcha' => 'kho',
        'hanh-phi' => 'che-bien', 'thach-suong-sao' => 'che-bien', 'pho-mai' => 'che-bien',
        'bo-dong-vat' => 'che-bien', 'bo-thuc-vat' => 'che-bien', 'mo-lon-nuoc' => 'che-bien',
        'trung-vit-muoi' => 'che-bien', 'banh-phong-tom-chien' => 'che-bien',
        'dau-hao' => 'che-bien', 'sa-te' => 'che-bien', 'xot-mayonnaise' => 'che-bien',
        'heo-quay' => 'che-bien', // thành phẩm quay sẵn — đợt DISH-005 đợt 2
        'gio-song' => 'che-bien', 'nuoc-mau' => 'che-bien', // nhũ tương sống đóng gói + nước thắng đóng chai — LV2-004
        'cha-com' => 'che-bien', // chả cốm thành phẩm hấp/chiên sẵn — LV2-009 Phần A
        'bo-vien' => 'che-bien', // bò viên thành phẩm đóng gói — LV2-010 Phần A
        'tuong-hat' => 'len-men', // tương hạt/hột = đậu nành nguyên hạt lên men — LV2-010 Phần A
        'vani' => 'dong-hop', // chiết xuất vani đóng chai — LV2-010 Phần A
    ];

    /** Nhãn dị ứng theo slug nguyên liệu (TAXONOMY.md §4). */
    public const ALLERGENS_BY_SLUG = [
        'lac-rang' => ['dau-phong'], 'lac-song' => ['dau-phong'],
        'tom-su' => ['tom'], 'tom-kho' => ['tom'], 'tep' => ['tom'], 'be-be' => ['tom'],
        'tom-the' => ['tom'], 'tom-cang-xanh' => ['tom'], // đợt mở rộng kho 2026-10-31
        'mam-tom' => ['tom', 'mam-tom'], 'mam-ruoc' => ['tom', 'mam-tom'],
        'mam-tom-chua' => ['tom', 'mam-tom'], // tôm nguyên con ủ chua — đợt DISH-005 đợt 2
        'muoi-tom' => ['tom'], // muối tôm Tây Ninh có bột tôm — LV2-007
        'sa-te' => ['tom'], // QD-04 (2026-11-02): chủ dự án CHỐT sa tế có tôm khô — khớp ngoại lệ origin dong-vat đã gán vì đúng lý do này
        'oc-hut' => ['dong-vat-co-vo'], // ốc biển nhỏ — đợt DISH-005 đợt 2
        'banh-phong-tom-chien' => ['tom'], 'banh-phong-tom-song' => ['tom'],
        'cua-bien' => ['cua'], 'cua-dong' => ['cua'], 'ghe' => ['cua'],
        'ngheu' => ['dong-vat-co-vo'], 'hau' => ['dong-vat-co-vo'], 'so-diep' => ['dong-vat-co-vo'],
        'oc-buou' => ['dong-vat-co-vo'], 'hen' => ['dong-vat-co-vo'], 'don' => ['dong-vat-co-vo'],
        'oc-len' => ['dong-vat-co-vo'], 'trai' => ['dong-vat-co-vo'],
        'muc-ong' => ['dong-vat-co-vo'], 'muc-kho' => ['dong-vat-co-vo'], 'bach-tuoc' => ['dong-vat-co-vo'],
        'dau-hao' => ['dong-vat-co-vo'],
        'luon' => ['ca'], 'cha-ca-vien' => ['ca', 'lua-mi'], 'gio-lua' => ['ca'],
        'gio-song' => ['ca'], // có nước mắm trong thành phần — LV2-004
        'cha-com' => ['ca'], // ướp nước mắm trong thành phần — LV2-009 Phần A
        'cha-bong' => ['ca'], // thành phần có nước mắm (hồ sơ + components tự ghi) — AUDIT-ALLERGEN FIX-A2
        'bo-vien' => ['trung'], // sản phẩm tham chiếu USDA Branded 2623419 có lòng trắng trứng — LV2-010 Phần A
        'tuong-hat' => ['dau-nanh'], // đậu nành nguyên hạt lên men — LV2-010 Phần A
        'nuoc-mam-cong-nghiep' => ['ca'], 'nuoc-mam-truyen-thong' => ['ca'],
        'mam-ca-linh' => ['ca'], 'mam-ca-sac' => ['ca'], 'mam-nem' => ['ca'],
        'trung-ga' => ['trung'], 'trung-vit' => ['trung'], 'trung-cut' => ['trung'],
        'trung-vit-lon' => ['trung'], 'trung-vit-muoi' => ['trung'], 'trung-ga-non' => ['trung'],
        'mi-trung-kho' => ['trung', 'lua-mi'], 'xot-mayonnaise' => ['trung'],
        'sua-tuoi' => ['sua'], 'sua-dac' => ['sua'], 'sua-chua' => ['sua'],
        'kem-whipping' => ['sua'], // kem sữa tươi đánh bông — LV2-007 Phần B
        'long-do-trung-ga' => ['trung'], 'gelato-vani' => ['sua'], 'bot-trung-muoi' => ['sua', 'trung'],
        'pho-mai' => ['sua'], 'bo-dong-vat' => ['sua'],
        'dau-nanh-hat' => ['dau-nanh'], 'sua-dau-nanh' => ['dau-nanh'], 'dau-phu' => ['dau-nanh'],
        'dau-phu-chien' => ['dau-nanh'], 'tau-hu-ky' => ['dau-nanh'], 'chao' => ['dau-nanh'],
        'tuong-ban' => ['dau-nanh'], 'nuoc-tuong' => ['dau-nanh'], 'tuong-den' => ['dau-nanh', 'lua-mi'],
        'xuc-xich' => ['dau-nanh', 'lua-mi'], 'pate-gan' => ['sua', 'lua-mi'], 'bia' => ['lua-mi'],
        'bot-mi' => ['lua-mi'], 'bot-chien-gion' => ['lua-mi'], 'banh-mi' => ['lua-mi'], 'mi-an-lien' => ['lua-mi'],
        'nui-kho' => ['lua-mi'], 'bot-chien-xu' => ['lua-mi'], 'vo-hoanh-thanh' => ['trung', 'lua-mi'],
        'mi-can' => ['lua-mi'],
        'suon-chay' => ['dau-nanh', 'lua-mi'], 'cha-lua-chay' => ['dau-nanh', 'lua-mi'], // đạm đậu nành + gluten/bột lúa mì — đợt DISH-005 đợt 3
        'vung' => ['me'], 'vung-rang' => ['me'], 'dau-me' => ['me'],
        // QD-03 (2026-11-02): nhóm Hạt cây (tree nuts) — census toàn catalog:
        // chỉ hai slug này là hạt cây thật (hạt điều màu là annatto, hạt sen/
        // hạt dổi không thuộc nhóm hạt cây theo chuẩn FDA/EU).
        'hat-dieu-rang' => ['hat-cay'], 'hanh-nhan' => ['hat-cay'],
    ];

    /**
     * Mọi cá theo tiền tố slug (bổ sung cho bảng trên) — chỉ áp cho nhóm thuỷ sản,
     * tránh nhầm 'ca-phe-den' (cà phê) thành cá.
     */
    public static function allergensFor(string $slug, string $category = ''): array
    {
        $tags = self::ALLERGENS_BY_SLUG[$slug] ?? [];
        if ($category === 'thuy-san' && str_starts_with($slug, 'ca-') && ! in_array('ca', $tags, true)) {
            $tags[] = 'ca';
        }

        return $tags;
    }

    public static function originFor(string $slug, string $category): string
    {
        return self::ORIGIN_EXCEPTIONS[$slug] ?? self::ORIGIN_BY_CATEGORY[$category] ?? 'thuc-vat';
    }

    public static function stateFor(string $slug): string
    {
        if (isset(self::STATE_BY_SLUG[$slug])) {
            return self::STATE_BY_SLUG[$slug];
        }
        // Quy ước hậu tố: slug kết thúc bằng '-kho' là hàng khô (măng khô, mực khô...)
        // — chặn lỗi hệ thống "mặc định tươi nuốt mất hàng khô" (rà soát 2026-10-31).
        if (str_ends_with($slug, '-kho')) {
            return 'kho';
        }

        return 'tuoi';
    }

    /** Bộ nhãn đầy đủ của một nguyên liệu theo quy tắc. */
    public static function tagsFor(string $slug, string $category): array
    {
        return [
            'origin:'.self::originFor($slug, $category),
            'state:'.self::stateFor($slug),
            ...array_map(fn ($a) => 'allergen:'.$a, self::allergensFor($slug, $category)),
        ];
    }
}
