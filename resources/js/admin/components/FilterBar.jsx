import React, { useLayoutEffect, useRef, useState } from 'react';
import { Badge, Button, Popover } from 'antd';
import { FilterOutlined } from '@ant-design/icons';

/**
 * Thanh bộ lọc co giãn (ADM-008 đợt 2).
 *
 * Các ô lọc được xếp theo độ quan trọng từ trái sang phải (mảng `items` theo
 * thứ tự ưu tiên của trang). Khi tổng chiều rộng vượt quá MỘT hàng, các ô thừa
 * ở cuối được gom vào "nhóm bộ lọc" — một nút icon bộ lọc; badge đỏ trên nút
 * là số bộ lọc trong nhóm ĐANG được bật, để người dùng không quên mình đang
 * lọc theo tiêu chí ẩn. Bấm nút mở panel chứa các ô lọc thừa (vẫn thao tác
 * bình thường vì trạng thái lọc nằm ở trang, không nằm trong ô lọc).
 *
 * items: [{ key, active: boolean, node }] — node là ô lọc đã cấu hình sẵn.
 */
export default function FilterBar({ items }) {
    const containerRef = useRef(null);
    const measureRef = useRef(null);
    const [visibleCount, setVisibleCount] = useState(items.length);
    const [open, setOpen] = useState(false);

    useLayoutEffect(() => {
        const container = containerRef.current;
        const measurer = measureRef.current;
        if (!container || !measurer) return undefined;

        const measure = () => {
            const available = container.clientWidth;
            const widths = [...measurer.children].map((el) => el.offsetWidth + 8);
            const BTN = 52; // chỗ chừa cho nút nhóm bộ lọc khi còn ô thừa
            let used = 0;
            let count = 0;
            for (let i = 0; i < widths.length; i++) {
                const reserve = i < widths.length - 1 ? BTN : 0;
                if (used + widths[i] + reserve <= available) {
                    used += widths[i];
                    count++;
                } else {
                    break;
                }
            }
            setVisibleCount((prev) => (prev === count ? prev : count));
        };

        measure();
        const ro = new ResizeObserver(measure);
        ro.observe(container);
        return () => ro.disconnect();
    }, [items.length]);

    const visible = items.slice(0, visibleCount);
    const overflow = items.slice(visibleCount);
    const activeOverflow = overflow.filter((i) => i.active).length;

    return (
        <div ref={containerRef} style={{ marginBottom: 12, position: 'relative' }}>
            {/* Hàng đo ẩn: dựng mọi ô lọc một lần để lấy chiều rộng thật. */}
            <div ref={measureRef} aria-hidden
                style={{ position: 'absolute', top: 0, left: 0, visibility: 'hidden', pointerEvents: 'none', display: 'flex', gap: 8 }}>
                {items.map((i) => <div key={i.key}>{i.node}</div>)}
            </div>
            <div style={{ display: 'flex', gap: 8, alignItems: 'flex-start' }}>
                {visible.map((i) => <div key={i.key}>{i.node}</div>)}
                {overflow.length > 0 && (
                    <Popover
                        open={open}
                        onOpenChange={setOpen}
                        trigger="click"
                        placement="bottomRight"
                        title="Thêm bộ lọc"
                        content={(
                            <div style={{ display: 'flex', flexDirection: 'column', gap: 8, minWidth: 230 }}>
                                {overflow.map((i) => <div key={i.key}>{i.node}</div>)}
                            </div>
                        )}
                    >
                        <Badge count={activeOverflow} size="small" offset={[-2, 2]}>
                            <Button
                                icon={<FilterOutlined />}
                                type={activeOverflow > 0 ? 'primary' : 'default'}
                                title={activeOverflow > 0 ? `Có ${activeOverflow} bộ lọc đang bật trong nhóm` : 'Thêm bộ lọc'}
                            />
                        </Badge>
                    </Popover>
                )}
            </div>
        </div>
    );
}
