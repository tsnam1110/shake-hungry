<?php

declare(strict_types=1);

/**
 * Đăng ký từ vựng nhãn — NGUỒN SỰ THẬT DUY NHẤT của hệ phân loại (TAXONOMY.md).
 *
 * Mỗi nhãn: slug đầy đủ dạng "<facet>:<value>" → tên hiển thị + facet.
 * File dữ liệu chỉ được dùng nhãn có trong đăng ký này (validate-data V16 chặn
 * nhãn lạ). Thêm nhãn mới = thêm vào đây + ghi định nghĩa vào TAXONOMY.md
 * trong cùng commit (QT-7).
 */
return [
    'tags' => [
        // — Nguồn gốc nguyên liệu (đúng 1/nguyên liệu) —
        'origin:dong-vat' => ['name' => 'Động vật', 'facet' => 'origin'],
        'origin:thuc-vat' => ['name' => 'Thực vật', 'facet' => 'origin'],
        'origin:nam' => ['name' => 'Nấm', 'facet' => 'origin'],
        'origin:khoang-chat' => ['name' => 'Khoáng chất', 'facet' => 'origin'],
        'origin:tong-hop' => ['name' => 'Tổng hợp / công nghiệp', 'facet' => 'origin'],

        // — Trạng thái nguyên liệu (đúng 1/nguyên liệu) —
        'state:tuoi' => ['name' => 'Tươi / sống', 'facet' => 'state'],
        'state:kho' => ['name' => 'Khô', 'facet' => 'state'],
        'state:len-men' => ['name' => 'Lên men', 'facet' => 'state'],
        'state:dong-hop' => ['name' => 'Đóng hộp / đóng gói', 'facet' => 'state'],
        'state:che-bien' => ['name' => 'Chế biến sẵn', 'facet' => 'state'],

        // — Chất gây dị ứng (nguyên liệu: nguồn gán; món: suy từ thành phần) —
        'allergen:dau-phong' => ['name' => 'Đậu phộng', 'facet' => 'allergen'],
        // QD-03 (2026-11-02): nhóm Hạt cây — chủ dự án duyệt thêm vào từ vựng dị ứng.
        'allergen:hat-cay' => ['name' => 'Hạt cây', 'facet' => 'allergen'],
        'allergen:tom' => ['name' => 'Tôm · tép', 'facet' => 'allergen'],
        'allergen:cua' => ['name' => 'Cua', 'facet' => 'allergen'],
        'allergen:dong-vat-co-vo' => ['name' => 'Động vật có vỏ · thân mềm', 'facet' => 'allergen'],
        'allergen:ca' => ['name' => 'Cá', 'facet' => 'allergen'],
        'allergen:trung' => ['name' => 'Trứng', 'facet' => 'allergen'],
        'allergen:sua' => ['name' => 'Sữa', 'facet' => 'allergen'],
        'allergen:dau-nanh' => ['name' => 'Đậu nành', 'facet' => 'allergen'],
        'allergen:lua-mi' => ['name' => 'Lúa mì (gluten)', 'facet' => 'allergen'],
        'allergen:me' => ['name' => 'Mè · vừng', 'facet' => 'allergen'],
        'allergen:mam-tom' => ['name' => 'Mắm tôm · ruốc', 'facet' => 'allergen'],

        // — Loại món (biên tập, đúng 1/món) —
        'loai:bun-pho-mi' => ['name' => 'Bún · Phở · Mì', 'facet' => 'loai'],
        'loai:com' => ['name' => 'Cơm', 'facet' => 'loai'],
        'loai:xoi' => ['name' => 'Xôi', 'facet' => 'loai'],
        'loai:banh' => ['name' => 'Bánh', 'facet' => 'loai'],
        'loai:chao-sup' => ['name' => 'Cháo · Súp', 'facet' => 'loai'],
        'loai:canh' => ['name' => 'Canh', 'facet' => 'loai'],
        'loai:lau' => ['name' => 'Lẩu', 'facet' => 'loai'],
        'loai:goi-nom' => ['name' => 'Gỏi · Nộm', 'facet' => 'loai'],
        'loai:che-trang-mieng' => ['name' => 'Chè · Tráng miệng', 'facet' => 'loai'],
        'loai:nuoc-cham' => ['name' => 'Nước chấm · Mắm pha', 'facet' => 'loai'],
        'loai:mon-chinh' => ['name' => 'Món chính', 'facet' => 'loai'],

        // — Đặc sản địa phương (biên tập, tối đa 1/món) — DATA-010:
        // vùng miền của món chỉ còn 3 miền chính ở region_code; món gắn với
        // một tỉnh/thành cụ thể giữ thông tin đó ở facet này để vẫn lọc/tìm được.
        'dac-san:ha-noi' => ['name' => 'Đặc sản Hà Nội', 'facet' => 'dac-san'],
        'dac-san:hai-phong' => ['name' => 'Đặc sản Hải Phòng', 'facet' => 'dac-san'],
        'dac-san:nam-dinh' => ['name' => 'Đặc sản Nam Định', 'facet' => 'dac-san'],
        'dac-san:thanh-hoa' => ['name' => 'Đặc sản Thanh Hoá', 'facet' => 'dac-san'],
        'dac-san:nghe-an' => ['name' => 'Đặc sản Nghệ An', 'facet' => 'dac-san'],
        'dac-san:hue' => ['name' => 'Đặc sản Huế', 'facet' => 'dac-san'],
        'dac-san:da-nang' => ['name' => 'Đặc sản Đà Nẵng', 'facet' => 'dac-san'],
        'dac-san:quang-nam' => ['name' => 'Đặc sản Quảng Nam', 'facet' => 'dac-san'],
        'dac-san:tp-hcm' => ['name' => 'Đặc sản TP. Hồ Chí Minh', 'facet' => 'dac-san'],
        'dac-san:tien-giang' => ['name' => 'Đặc sản Tiền Giang', 'facet' => 'dac-san'],
        'dac-san:an-giang' => ['name' => 'Đặc sản An Giang', 'facet' => 'dac-san'],
        'dac-san:can-tho' => ['name' => 'Đặc sản Cần Thơ', 'facet' => 'dac-san'],
        'dac-san:soc-trang' => ['name' => 'Đặc sản Sóc Trăng', 'facet' => 'dac-san'],
        'dac-san:vung-tau' => ['name' => 'Đặc sản Bà Rịa – Vũng Tàu', 'facet' => 'dac-san'],

        // — Chế độ ăn của món (suy từ thành phần, đúng 1/món) —
        'diet:chay' => ['name' => 'Chay (thuần)', 'facet' => 'diet'],
        'diet:chay-trung-sua' => ['name' => 'Chay trứng · sữa', 'facet' => 'diet'],
        'diet:man' => ['name' => 'Mặn', 'facet' => 'diet'],

        // — Đạm chính của món (suy từ thành phần, đúng 1/món) —
        'protein:thit-bo' => ['name' => 'Thịt bò', 'facet' => 'protein'],
        'protein:thit-lon' => ['name' => 'Thịt lợn', 'facet' => 'protein'],
        'protein:gia-cam' => ['name' => 'Gia cầm', 'facet' => 'protein'],
        'protein:ca' => ['name' => 'Cá', 'facet' => 'protein'],
        'protein:tom-cua' => ['name' => 'Tôm · Cua', 'facet' => 'protein'],
        'protein:muc' => ['name' => 'Mực', 'facet' => 'protein'],
        'protein:oc' => ['name' => 'Ốc · Hến · Sò', 'facet' => 'protein'],
        'protein:trung' => ['name' => 'Trứng', 'facet' => 'protein'],
        'protein:dau-phu-dau' => ['name' => 'Đậu · Đậu phụ', 'facet' => 'protein'],
        'protein:hon-hop' => ['name' => 'Đạm hỗn hợp', 'facet' => 'protein'],
        'protein:khong' => ['name' => 'Không có đạm chính', 'facet' => 'protein'],
    ],
];
