import React, { useEffect, useState } from 'react';
import { Card, Col, Row, Statistic, Table, Typography, Spin } from 'antd';
import api from '../api';

const ACTION_LABELS = { create: 'Tạo mới', update: 'Cập nhật', delete: 'Xoá', approve: 'Duyệt', reopen: 'Mở lại', compute: 'Tính calo' };

export default function Dashboard() {
    const [data, setData] = useState(null);

    useEffect(() => {
        api.get('/dashboard').then(({ data }) => setData(data));
    }, []);

    if (!data) return <Spin style={{ display: 'block', marginTop: 80 }} />;

    return (
        <>
            <Typography.Title level={4}>Tổng quan dữ liệu</Typography.Title>
            <Row gutter={16}>
                <Col span={6}><Card><Statistic title="Nguyên liệu" value={data.ingredients_total} /></Card></Col>
                <Col span={6}><Card><Statistic title="Nguyên liệu chờ duyệt" value={data.ingredients_pending_review} valueStyle={{ color: '#cf1322' }} /></Card></Col>
                <Col span={6}><Card><Statistic title="Món ăn" value={data.dishes_total} /></Card></Col>
                <Col span={6}><Card><Statistic title="Thực phẩm tham chiếu (USDA)" value={data.reference_foods_total} /></Card></Col>
            </Row>
            <Row gutter={16} style={{ marginTop: 16 }}>
                <Col span={6}><Card><Statistic title="Món đã có nguyên liệu" value={data.dishes_with_ingredients} /></Card></Col>
                <Col span={6}><Card><Statistic title="Dòng nguyên liệu trong món" value={data.dish_ingredient_rows} /></Card></Col>
                <Col span={12}>
                    <Card title="Phân bố mức hoàn thiện món">
                        {Object.entries(data.dishes_by_completeness ?? {}).map(([level, n]) => (
                            <Typography.Text key={level} style={{ marginRight: 16 }}>Mức {level}: <b>{n}</b> món</Typography.Text>
                        ))}
                    </Card>
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
