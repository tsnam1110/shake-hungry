import React, { useCallback, useEffect, useState } from 'react';
import { Button, Input, Modal, Popconfirm, Select, Space, Table, Tag, Typography, message } from 'antd';
import api from '../api';

const TYPE_LABELS = { new_dish: 'Món mới', fix_dish: 'Báo sửa món', new_place: 'Quán ăn mới' };

export default function Contributions() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, per_page: 20, total: 0 });
    const [status, setStatus] = useState('pending');
    const [loading, setLoading] = useState(false);
    const [rejecting, setRejecting] = useState(null);
    const [reason, setReason] = useState('');

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const { data } = await api.get('/contributions', { params: { page, ...(status ? { status } : {}) } });
            setRows(data.data);
            setMeta(data);
        } finally {
            setLoading(false);
        }
    }, [status]);

    useEffect(() => { load(1); }, [load]);

    const review = async (record, decision, rejectReason) => {
        const { data } = await api.post(`/contributions/${record.id}/review`, { decision, reject_reason: rejectReason });
        message.success(decision === 'approved'
            ? (data.created_dish_id ? `Đã chấp nhận và tạo món nháp #${data.created_dish_id} — vào mục Món ăn để biên soạn tiếp` : 'Đã chấp nhận đề xuất')
            : 'Đã từ chối đề xuất');
        setRejecting(null);
        setReason('');
        load(meta.current_page);
    };

    return (
        <>
            <Space style={{ marginBottom: 16 }}>
                <Typography.Title level={4} style={{ margin: 0 }}>Đóng góp cộng đồng</Typography.Title>
                <Select value={status} onChange={setStatus} style={{ width: 170 }}
                    options={[
                        { value: 'pending', label: 'Chờ duyệt' },
                        { value: 'approved', label: 'Đã chấp nhận' },
                        { value: 'rejected', label: 'Đã từ chối' },
                        { value: '', label: 'Tất cả' },
                    ]} />
            </Space>
            <Table
                rowKey="id" loading={loading} dataSource={rows}
                pagination={{ current: meta.current_page, pageSize: meta.per_page, total: meta.total, onChange: load }}
                columns={[
                    { title: 'Loại', dataIndex: 'type', render: (v) => TYPE_LABELS[v] ?? v },
                    { title: 'Tiêu đề', render: (_, r) => r.payload?.title ?? '—' },
                    { title: 'Chi tiết', render: (_, r) => <Typography.Text type="secondary">{(r.payload?.detail ?? '').slice(0, 90)}</Typography.Text> },
                    { title: 'Người gửi', render: (_, r) => r.payload?.submitted_by ?? '—' },
                    {
                        title: 'Trạng thái', dataIndex: 'status',
                        render: (v) => v === 'pending' ? <Tag color="orange">Chờ duyệt</Tag> : v === 'approved' ? <Tag color="green">Đã chấp nhận</Tag> : <Tag color="red">Đã từ chối</Tag>,
                    },
                    {
                        title: 'Thao tác', width: 190,
                        render: (_, r) => r.status === 'pending' ? (
                            <Space>
                                <Popconfirm title="Chấp nhận đề xuất này?" onConfirm={() => review(r, 'approved')}>
                                    <Button size="small" type="primary">Chấp nhận</Button>
                                </Popconfirm>
                                <Button size="small" danger onClick={() => setRejecting(r)}>Từ chối</Button>
                            </Space>
                        ) : <Typography.Text type="secondary">{r.reviewed_by ?? ''}</Typography.Text>,
                    },
                ]}
            />
            <Modal title="Từ chối đề xuất" open={rejecting !== null} onCancel={() => setRejecting(null)}
                onOk={() => review(rejecting, 'rejected', reason)} okText="Từ chối" okButtonProps={{ danger: true }}>
                <Input.TextArea rows={3} value={reason} onChange={(e) => setReason(e.target.value)}
                    placeholder="Lý do từ chối (người gửi sẽ thấy lý do này)" />
            </Modal>
        </>
    );
}
