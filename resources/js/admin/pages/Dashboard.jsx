import React, { useEffect, useState } from 'react';
import { Card, Col, List, Row, Statistic, Table, Tag, Typography, Spin } from 'antd';
import api from '../api';

// Trang tổng quan (ADM-008): số lượng bản ghi của mọi loại dữ liệu + các
// chỉ số chất lượng/hàng đợi cần xử lý, thay cho vài con số rời rạc trước đây.
const ACTION_LABELS = { create: 'Tạo mới', update: 'Cập nhật', delete: 'Xoá', approve: 'Duyệt', reopen: 'Mở lại', compute: 'Tính calo' };
const CATEGORY_LABELS = {
    'thit': 'Thịt', 'thuy-san': 'Thuỷ sản', 'tinh-bot': 'Tinh bột', 'rau-cu': 'Rau củ',
    'rau-gia-vi': 'Rau gia vị', 'gia-vi': 'Gia vị', 'dau-mo': 'Dầu mỡ', 'trai-cay': 'Trái cây', 'khac': 'Khác',
};
const FRUIT_GROUP_LABELS = { 'co-mui': 'Có múi', mong: 'Quả mọng', hach: 'Quả hạch', 'nhiet-doi': 'Nhiệt đới', dua: 'Họ dưa', khac: 'Khác' };
const ORIGIN_LABELS = { 'trong-nuoc': 'Trong nước', 'nhap-khau': 'Nhập khẩu', 'ca-hai': 'Cả hai' };

function Breakdown({ title, entries }) {
    return (
        <Card title={title} style={{ height: '100%' }}>
            <List
                size="small"
                dataSource={entries}
                renderItem={([label, n]) => (
                    <List.Item style={{ padding: '4px 0' }}>
                        <Typography.Text>{label}</Typography.Text>
                        <Typography.Text strong>{n}</Typography.Text>
                    </List.Item>
                )}
            />
        </Card>
    );
}

export default function Dashboard() {
    const [data, setData] = useState(null);

    useEffect(() => {
        api.get('/dashboard').then(({ data }) => setData(data));
    }, []);

    if (!data) return <Spin style={{ display: 'block', marginTop: 80 }} />;

    const ing = data.ingredients;
    const dishes = data.dishes;
    const fruits = data.fruits;
    const contrib = data.contributions;
    const fruitsNoPrice = fruits.total - fruits.with_prices;

    const queueItems = [
        ['Nguyên liệu chờ duyệt', ing.pending_review],
        ['Món ăn chờ duyệt', dishes.pending_review],
        ['Hồ sơ quả chờ duyệt', fruits.pending_review],
        ['Đóng góp cộng đồng chờ xử lý', contrib.pending],
        ['Nguyên liệu thiếu số dinh dưỡng', ing.missing_nutrition],
        ['Món chưa tính được calo', dishes.missing_nutrition],
        ['Món có số chưa kiểm định (nguồn thứ cấp)', dishes.unverified],
        ['Nguyên liệu chưa kiểm định (nguồn thứ cấp)', ing.unverified],
        ['Hồ sơ quả chưa có dữ liệu giá', fruitsNoPrice],
    ];

    return (
        <>
            <Typography.Title level={4}>Tổng quan dữ liệu</Typography.Title>
            <Row gutter={16}>
                <Col span={4}><Card><Statistic title="Nguyên liệu" value={ing.total} /></Card></Col>
                <Col span={4}><Card><Statistic title="Món ăn" value={dishes.total} /></Card></Col>
                <Col span={4}><Card><Statistic title="Hồ sơ hoa quả" value={fruits.total} /></Card></Col>
                <Col span={4}><Card><Statistic title="Dòng giá quả" value={fruits.price_rows} /></Card></Col>
                <Col span={4}><Card><Statistic title="Đóng góp chờ duyệt" value={contrib.pending} valueStyle={{ color: contrib.pending > 0 ? '#cf1322' : undefined }} /></Card></Col>
                <Col span={4}><Card><Statistic title="Người dùng" value={data.users.total} suffix={<Typography.Text type="secondary" style={{ fontSize: 12 }}>({data.users.admins} quản trị)</Typography.Text>} /></Card></Col>
            </Row>
            <Row gutter={16} style={{ marginTop: 16 }}>
                <Col span={8}>
                    <Card title="⏳ Hàng đợi & chất lượng dữ liệu" style={{ height: '100%' }}>
                        <List
                            size="small"
                            dataSource={queueItems}
                            renderItem={([label, n]) => (
                                <List.Item style={{ padding: '4px 0' }}>
                                    <Typography.Text>{label}</Typography.Text>
                                    {n > 0
                                        ? <Tag color="red" style={{ marginRight: 0 }}>{n}</Tag>
                                        : <Tag color="green" style={{ marginRight: 0 }}>0 ✓</Tag>}
                                </List.Item>
                            )}
                        />
                    </Card>
                </Col>
                <Col span={8}>
                    <Breakdown title="Món ăn theo chế độ & mức hoàn thiện" entries={[
                        ['Chay', dishes.by_diet.chay],
                        ['Chay trứng–sữa', dishes.by_diet['chay-trung-sua']],
                        ['Mặn', dishes.by_diet.man],
                        ['Đã tính được dinh dưỡng', dishes.computable],
                        ['Có tên gọi khác (vùng miền)', dishes.with_aliases],
                        ...Object.entries(dishes.by_completeness ?? {}).map(([level, n]) => [`Mức hoàn thiện ${level}`, n]),
                        ['Dòng nguyên liệu trong món', dishes.ingredient_rows],
                    ]} />
                </Col>
                <Col span={8}>
                    <Breakdown title="Nguyên liệu theo nhóm" entries={
                        Object.entries(ing.by_category ?? {}).map(([c, n]) => [CATEGORY_LABELS[c] ?? c, n])
                    } />
                </Col>
            </Row>
            <Row gutter={16} style={{ marginTop: 16 }}>
                <Col span={8}>
                    <Breakdown title="Hoa quả theo nhóm" entries={
                        Object.entries(fruits.by_group ?? {}).map(([g, n]) => [FRUIT_GROUP_LABELS[g] ?? g, n])
                    } />
                </Col>
                <Col span={8}>
                    <Breakdown title="Hoa quả theo nguồn gốc" entries={
                        Object.entries(fruits.by_origin ?? {}).map(([o, n]) => [ORIGIN_LABELS[o] ?? o, n])
                    } />
                </Col>
                <Col span={8}>
                    <Breakdown title="Khác" entries={[
                        ['Thực phẩm tham chiếu (USDA…)', data.reference_foods_total],
                        ['Tên gọi khác của nguyên liệu', ing.aliases],
                        ['Đóng góp đã duyệt', contrib.approved],
                        ['Đóng góp đã từ chối', contrib.rejected],
                        ['Tổng đóng góp cộng đồng', contrib.total],
                    ]} />
                </Col>
            </Row>
            <Card title="Hoạt động quản trị gần đây" style={{ marginTop: 16 }}>
                <Table
                    rowKey="id"
                    pagination={false}
                    dataSource={data.recent_activity}
                    locale={{ emptyText: 'Chưa có hoạt động nào được ghi log' }}
                    columns={[
                        { title: 'Thời gian', dataIndex: 'created_at', render: (v) => new Date(v).toLocaleString('vi-VN') },
                        { title: 'Người thực hiện', render: (_, r) => r.user?.name ?? r.user?.email ?? '—' },
                        { title: 'Thao tác', dataIndex: 'action', render: (v) => ACTION_LABELS[v] ?? v },
                        { title: 'Đối tượng', render: (_, r) => `${(r.model_type ?? '').split('\\').pop()} #${r.model_id ?? ''}` },
                    ]}
                />
            </Card>
        </>
    );
}
