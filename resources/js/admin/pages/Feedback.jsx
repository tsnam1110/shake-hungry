import React, { useCallback, useEffect, useState } from 'react';
import { Button, Input, Modal, Select, Space, Table, Tag, Typography, message } from 'antd';
import api from '../api';

// UI-018: quản trị ticket góp ý / phản hồi của người dùng.
const STATUSES = {
    'da-ghi-nhan': 'Đã ghi nhận · Chờ duyệt',
    'da-duyet': 'Đã duyệt',
    'dang-xu-ly': 'Đang xử lý',
    'da-xu-ly': 'Đã xử lý',
    'da-dong': 'Đã đóng',
};
const STATUS_COLORS = { 'da-ghi-nhan': 'orange', 'da-duyet': 'blue', 'dang-xu-ly': 'gold', 'da-xu-ly': 'green', 'da-dong': 'default' };
const TYPES = { 'gop-y': 'Góp ý', 'ho-tro': 'Hỗ trợ', 'phat-trien': 'Phát triển', loi: 'Báo lỗi' };

export default function Feedback() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, total: 0 });
    const [status, setStatus] = useState('da-ghi-nhan');
    const [type, setType] = useState(undefined);
    const [loading, setLoading] = useState(false);
    const [viewing, setViewing] = useState(null);
    const [note, setNote] = useState('');
    const [viewStatus, setViewStatus] = useState(undefined);

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const { data } = await api.get('/feedback', {
                params: { page, ...(status ? { status } : {}), ...(type ? { type } : {}) },
            });
            setRows(data.data);
            setMeta(data);
        } finally {
            setLoading(false);
        }
    }, [status, type]);

    useEffect(() => { load(1); }, [load]);

    const changeStatus = async (row, next) => {
        await api.post(`/feedback/${row.id}/status`, { status: next });
        message.success('Đã cập nhật trạng thái ticket');
        load(meta.current_page);
    };

    const openView = (row) => {
        setViewing(row);
        setNote(row.admin_note ?? '');
        setViewStatus(row.status);
    };

    const saveView = async () => {
        await api.post(`/feedback/${viewing.id}/status`, { status: viewStatus, admin_note: note || null });
        message.success('Đã lưu ticket');
        setViewing(null);
        load(meta.current_page);
    };

    return (
        <div>
            <Space style={{ marginBottom: 16 }} wrap>
                <Select allowClear placeholder="Trạng thái" style={{ width: 220 }} value={status}
                    onChange={(v) => setStatus(v)}
                    options={Object.entries(STATUSES).map(([k, v]) => ({ value: k, label: v }))} />
                <Select allowClear placeholder="Loại phản hồi" style={{ width: 170 }} value={type}
                    onChange={(v) => setType(v)}
                    options={Object.entries(TYPES).map(([k, v]) => ({ value: k, label: v }))} />
                <Typography.Text type="secondary">Tổng: {meta.total} ticket</Typography.Text>
            </Space>
            <Table rowKey="id" loading={loading} dataSource={rows}
                pagination={{ current: meta.current_page, total: meta.total, pageSize: 30, onChange: (p) => load(p) }}
                columns={[
                    { title: 'Thời gian', dataIndex: 'created_at', width: 150, render: (v) => new Date(v).toLocaleString('vi-VN') },
                    { title: 'Người gửi', width: 170, render: (_, r) => <span>{r.user?.name}<br /><Typography.Text type="secondary">{r.user?.email}</Typography.Text></span> },
                    { title: 'Loại', dataIndex: 'type_label', width: 100, render: (v, r) => <Tag color={r.type === 'loi' ? 'red' : 'default'}>{v}</Tag> },
                    { title: 'Về', dataIndex: 'context_name', width: 150, render: (v, r) => v ?? <Typography.Text type="secondary">{r.page ?? '—'}</Typography.Text> },
                    {
                        title: 'Nội dung', render: (_, r) => (
                            <span>
                                {r.title && <strong>{r.title}<br /></strong>}
                                <Typography.Text type="secondary">{r.content.length > 120 ? r.content.slice(0, 120) + '…' : r.content}</Typography.Text>
                            </span>
                        ),
                    },
                    {
                        title: 'Trạng thái', dataIndex: 'status', width: 200, render: (v, r) => (
                            <Select size="small" style={{ width: 185 }} value={v} onChange={(next) => changeStatus(r, next)}
                                options={Object.entries(STATUSES).map(([k, label]) => ({ value: k, label }))} />
                        ),
                    },
                    { title: '', width: 70, render: (_, r) => <Button size="small" onClick={() => openView(r)}>Xem</Button> },
                ]} />
            <Modal open={viewing !== null} title={viewing ? `Ticket #${viewing.id} — ${viewing.type_label}` : ''}
                onCancel={() => setViewing(null)} onOk={saveView} okText="Lưu" cancelText="Đóng">
                {viewing && (
                    <div>
                        <Typography.Paragraph><strong>{viewing.title ?? '(Không có tiêu đề)'}</strong></Typography.Paragraph>
                        <Typography.Paragraph style={{ whiteSpace: 'pre-wrap' }}>{viewing.content}</Typography.Paragraph>
                        <Typography.Paragraph type="secondary">
                            {viewing.user?.name} · {viewing.user?.email}
                            {viewing.context_name ? ` · Về: ${viewing.context_name}` : ''}
                            {viewing.page ? ` · Trang: ${viewing.page}` : ''}
                            {' · '}{new Date(viewing.created_at).toLocaleString('vi-VN')}
                        </Typography.Paragraph>
                        <Select style={{ width: '100%', marginBottom: 12 }} value={viewStatus} onChange={setViewStatus}
                            options={Object.entries(STATUSES).map(([k, label]) => ({ value: k, label }))} />
                        <Input.TextArea rows={3} placeholder="Ghi chú xử lý (nội bộ)" value={note} onChange={(e) => setNote(e.target.value)} />
                        <div style={{ marginTop: 8 }}>
                            <Tag color={STATUS_COLORS[viewing.status]}>{viewing.status_label}</Tag>
                        </div>
                    </div>
                )}
            </Modal>
        </div>
    );
}
