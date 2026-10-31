import React, { useCallback, useEffect, useState } from 'react';
import { Button, Input, Modal, Popconfirm, Select, Space, Table, Tag, Typography, message } from 'antd';
import api from '../api';
import FilterBar from '../components/FilterBar';

const TYPE_LABELS = { new_dish: 'Món mới', fix_dish: 'Báo sửa món', new_place: 'Quán ăn mới' };

export default function Contributions() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, per_page: 20, total: 0 });
    const [status, setStatus] = useState('pending');
    const [type, setType] = useState(undefined);
    const [stats, setStats] = useState(null);
    const [selected, setSelected] = useState([]);
    const [sorter, setSorter] = useState({});
    const [loading, setLoading] = useState(false);
    const [rejecting, setRejecting] = useState(null);
    const [reason, setReason] = useState('');

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const { data } = await api.get('/contributions', { params: { page, ...(status ? { status } : {}), ...(type ? { type } : {}), ...(sorter.field ? { sort: sorter.field, dir: sorter.dir } : {}) } });
            setRows(data.data);
            setMeta(data);
        } finally {
            setLoading(false);
        }
    }, [status, type, sorter]);

    useEffect(() => { load(1); }, [load]);

    // ADM-008: số liệu tổng cho thanh thống kê đầu trang.
    const loadStats = useCallback(() => {
        api.get('/dashboard').then(({ data }) => setStats(data.contributions)).catch(() => {});
    }, []);
    useEffect(() => { loadStats(); }, [loadStats]);

    // ADM-008 đợt 2: sắp xếp theo cột + xử lý hàng loạt.
    const handleTableChange = (pag, _filters, sorterObj) => {
        const next = sorterObj?.order
            ? { field: sorterObj.field ?? sorterObj.columnKey, dir: sorterObj.order === 'descend' ? 'desc' : 'asc' }
            : {};
        if (next.field !== sorter.field || next.dir !== sorter.dir) {
            setSorter(next);
        } else {
            load(pag.current);
        }
    };
    const sortOrderFor = (field) => (sorter.field === field ? (sorter.dir === 'desc' ? 'descend' : 'ascend') : null);

    const doBulk = async (action) => {
        try {
            const { data } = await api.post('/contributions/bulk', { action, ids: selected });
            message.success(`Đã xử lý ${data.affected} đóng góp${data.skipped ? `, bỏ qua ${data.skipped} đã xử lý trước đó` : ''}`);
            setSelected([]);
            load(meta.current_page);
            loadStats();
        } catch (e) {
            message.error(e.response?.data?.message ?? 'Thao tác hàng loạt thất bại');
        }
    };

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
            </Space>
            <FilterBar items={[
                {
                    key: 'status', active: status !== 'pending',
                    node: <Select value={status} onChange={setStatus} style={{ width: 170 }}
                        options={[
                            { value: 'pending', label: 'Chờ duyệt' },
                            { value: 'approved', label: 'Đã chấp nhận' },
                            { value: 'rejected', label: 'Đã từ chối' },
                            { value: '', label: 'Tất cả' },
                        ]} />,
                },
                {
                    key: 'type', active: !!type,
                    node: <Select placeholder="Loại đóng góp" allowClear style={{ width: 170 }} value={type}
                        onChange={setType}
                        options={Object.entries(TYPE_LABELS).map(([value, label]) => ({ value, label }))} />,
                },
            ]} />
            {stats && (
                <Typography.Text type="secondary" style={{ display: 'block', marginBottom: 12 }}>
                    Tổng <b>{stats.total}</b> đóng góp · Chờ duyệt <b>{stats.pending}</b> · Đã chấp nhận <b>{stats.approved}</b> · Đã từ chối <b>{stats.rejected}</b> · Đang hiển thị <b>{meta.total}</b> theo bộ lọc
                </Typography.Text>
            )}
            {selected.length > 0 && (
                <Space style={{ marginBottom: 12 }}>
                    <Typography.Text strong>Đã chọn {selected.length} bản ghi:</Typography.Text>
                    <Button size="small" type="primary" onClick={() => doBulk('approve')}>Chấp nhận</Button>
                    <Popconfirm title={`Từ chối ${selected.length} đóng góp đã chọn?`} onConfirm={() => doBulk('reject')}>
                        <Button size="small" danger>Từ chối</Button>
                    </Popconfirm>
                    <Button size="small" type="text" onClick={() => setSelected([])}>Bỏ chọn</Button>
                </Space>
            )}
            <Table
                rowKey="id" loading={loading} dataSource={rows}
                rowSelection={{ selectedRowKeys: selected, onChange: setSelected }}
                onChange={handleTableChange}
                pagination={{
                    current: meta.current_page, pageSize: meta.per_page, total: meta.total,
                    showTotal: (total, range) => `Bản ghi ${range[0]}–${range[1]} · Theo bộ lọc: ${total} / Tổng: ${stats?.total ?? '—'}`,
                }}
                columns={[
                    { title: 'Loại', dataIndex: 'type', sorter: true, sortOrder: sortOrderFor('type'), render: (v) => TYPE_LABELS[v] ?? v },
                    { title: 'Tiêu đề', render: (_, r) => r.payload?.title ?? '—' },
                    { title: 'Chi tiết', render: (_, r) => <Typography.Text type="secondary">{(r.payload?.detail ?? '').slice(0, 90)}</Typography.Text> },
                    { title: 'Người gửi', render: (_, r) => r.payload?.submitted_by ?? '—' },
                    {
                        title: 'Trạng thái', dataIndex: 'status', sorter: true, sortOrder: sortOrderFor('status'),
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
