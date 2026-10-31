import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
    Button, Drawer, Form, Input, InputNumber, Popconfirm, Select, Space, Switch,
    Table, Tag, Typography, message,
} from 'antd';
import api from '../api';
import FilterBar from '../components/FilterBar';

const CONFIDENCE = [
    { value: 'high', label: 'Cao (high)' },
    { value: 'medium', label: 'Trung bình (medium)' },
    { value: 'low', label: 'Thấp (low)' },
];

// ADM-008: 9 nhóm nguyên liệu theo phân loại dự án.
const CATEGORIES = [
    { value: 'thit', label: 'Thịt' },
    { value: 'thuy-san', label: 'Thuỷ sản' },
    { value: 'tinh-bot', label: 'Tinh bột' },
    { value: 'rau-cu', label: 'Rau củ' },
    { value: 'rau-gia-vi', label: 'Rau gia vị' },
    { value: 'gia-vi', label: 'Gia vị' },
    { value: 'dau-mo', label: 'Dầu mỡ' },
    { value: 'trai-cay', label: 'Trái cây' },
    { value: 'khac', label: 'Khác' },
];

export default function Ingredients() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, per_page: 20, total: 0 });
    const [loading, setLoading] = useState(false);
    const [q, setQ] = useState('');
    const [reviewFilter, setReviewFilter] = useState(undefined);
    const [categoryFilter, setCategoryFilter] = useState(undefined);
    const [confidenceFilter, setConfidenceFilter] = useState(undefined);
    const [nutritionFilter, setNutritionFilter] = useState(undefined);
    const [stats, setStats] = useState(null);
    const [selected, setSelected] = useState([]);
    const [sorter, setSorter] = useState({});
    const [drawer, setDrawer] = useState(null); // null | 'new' | ingredient object
    const [form] = Form.useForm();
    const confidence = Form.useWatch('mapping_confidence', form);
    const [refOptions, setRefOptions] = useState([]);
    const refTimer = useRef(null);

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const params = { page, per_page: 20 };
            if (q) params.q = q;
            if (reviewFilter !== undefined) params.needs_review = reviewFilter ? 1 : 0;
            if (categoryFilter) params.category = categoryFilter;
            if (confidenceFilter) params.mapping_confidence = confidenceFilter;
            if (nutritionFilter) params.nutrition = nutritionFilter;
            if (sorter.field) { params.sort = sorter.field; params.dir = sorter.dir; }
            const { data } = await api.get('/ingredients', { params });
            setRows(data.data);
            setMeta(data);
        } finally {
            setLoading(false);
        }
    }, [q, reviewFilter, categoryFilter, confidenceFilter, nutritionFilter, sorter]);

    useEffect(() => { load(1); }, [load]);

    // ADM-008: số liệu tổng cho thanh thống kê đầu trang.
    const loadStats = useCallback(() => {
        api.get('/dashboard').then(({ data }) => setStats(data.ingredients)).catch(() => {});
    }, []);
    useEffect(() => { loadStats(); }, [loadStats]);

    // ADM-008 đợt 2: đổi sắp xếp → tải lại từ trang 1; đổi trang → tải trang đó.
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

    // ADM-008 đợt 2: thao tác hàng loạt trên các bản ghi đã chọn.
    const doBulk = async (action) => {
        try {
            const { data } = await api.post('/ingredients/bulk', { action, ids: selected });
            message.success(`Đã xử lý ${data.affected} nguyên liệu`);
            setSelected([]);
            load(meta.current_page);
            loadStats();
        } catch (e) {
            message.error(e.response?.data?.message ?? 'Thao tác hàng loạt thất bại');
        }
    };

    const searchRef = (value) => {
        clearTimeout(refTimer.current);
        refTimer.current = setTimeout(async () => {
            const { data } = await api.get('/reference-foods', { params: { q: value } });
            setRefOptions(data.map((f) => ({
                value: f.id,
                label: `${f.name_vi || f.name_en} — ${f.kcal_per_100g ?? '?'} kcal/100g (${f.fct_source} ${f.external_id})`,
            })));
        }, 300);
    };

    const openEdit = async (record) => {
        if (record) {
            const { data } = await api.get(`/ingredients/${record.id}`);
            setDrawer(data);
            form.setFieldsValue({ ...data, units: data.units ?? [] });
            if (data.reference_food) {
                setRefOptions([{ value: data.reference_food.id, label: `${data.reference_food.name_en} — ${data.reference_food.kcal_per_100g ?? '?'} kcal/100g` }]);
            }
        } else {
            setDrawer('new');
            form.resetFields();
            form.setFieldsValue({ source: 'curated', units: [], mapping_confidence: 'medium' });
        }
    };

    const save = async () => {
        const values = await form.validateFields();
        try {
            if (drawer === 'new') {
                await api.post('/ingredients', values);
                message.success('Đã tạo nguyên liệu (đang chờ duyệt)');
            } else {
                await api.put(`/ingredients/${drawer.id}`, values);
                message.success('Đã lưu nguyên liệu');
            }
            setDrawer(null);
            load(meta.current_page);
        } catch (e) {
            message.error(e.response?.data?.message ?? 'Lưu thất bại');
        }
    };

    const review = async (record, approve) => {
        await api.post(`/ingredients/${record.id}/review`, { approve });
        message.success(approve ? 'Đã duyệt nguyên liệu' : 'Đã mở lại để duyệt lại');
        load(meta.current_page);
    };

    return (
        <>
            <Space style={{ marginBottom: 16, width: '100%', justifyContent: 'space-between' }}>
                <Typography.Title level={4} style={{ margin: 0 }}>Nguyên liệu</Typography.Title>
                <Button type="primary" onClick={() => openEdit(null)}>+ Thêm nguyên liệu</Button>
            </Space>
            <FilterBar items={[
                {
                    key: 'q', active: !!q,
                    node: <Input.Search placeholder="Tìm theo tên…" allowClear style={{ width: 280 }}
                        onSearch={(v) => setQ(v)} onChange={(e) => { if (!e.target.value) setQ(''); }} />,
                },
                {
                    key: 'review', active: reviewFilter !== undefined,
                    node: <Select placeholder="Trạng thái duyệt" allowClear style={{ width: 190 }} value={reviewFilter}
                        onChange={setReviewFilter}
                        options={[{ value: true, label: 'Chờ duyệt' }, { value: false, label: 'Đã duyệt' }]} />,
                },
                {
                    key: 'category', active: !!categoryFilter,
                    node: <Select placeholder="Nhóm nguyên liệu" allowClear style={{ width: 170 }} value={categoryFilter}
                        onChange={setCategoryFilter} options={CATEGORIES} />,
                },
                {
                    key: 'confidence', active: !!confidenceFilter,
                    node: <Select placeholder="Độ tin cậy ánh xạ" allowClear style={{ width: 180 }} value={confidenceFilter}
                        onChange={setConfidenceFilter} options={CONFIDENCE} />,
                },
                {
                    key: 'nutrition', active: !!nutritionFilter,
                    node: <Select placeholder="Số dinh dưỡng" allowClear style={{ width: 180 }} value={nutritionFilter}
                        onChange={setNutritionFilter}
                        options={[{ value: 'has', label: 'Đã có số' }, { value: 'missing', label: 'Thiếu số' }]} />,
                },
            ]} />
            {stats && (
                <Typography.Text type="secondary" style={{ display: 'block', marginBottom: 12 }}>
                    Tổng <b>{stats.total}</b> nguyên liệu · Chờ duyệt <b>{stats.pending_review}</b> · Thiếu số dinh dưỡng <b>{stats.missing_nutrition}</b> · Chưa kiểm định <b>{stats.unverified}</b> · Có tên gọi khác <b>{stats.aliases}</b> · Đang hiển thị <b>{meta.total}</b> theo bộ lọc
                </Typography.Text>
            )}
            {selected.length > 0 && (
                <Space style={{ marginBottom: 12 }}>
                    <Typography.Text strong>Đã chọn {selected.length} bản ghi:</Typography.Text>
                    <Button size="small" type="primary" onClick={() => doBulk('approve')}>Duyệt</Button>
                    <Button size="small" onClick={() => doBulk('reopen')}>Mở lại</Button>
                    <Popconfirm title={`Xoá ${selected.length} nguyên liệu đã chọn?`} onConfirm={() => doBulk('delete')}>
                        <Button size="small" danger>Xoá</Button>
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
                    { title: 'Tên tiếng Việt', dataIndex: 'name_vi', sorter: true, sortOrder: sortOrderFor('name_vi') },
                    { title: 'Tên tiếng Anh', dataIndex: 'name_en', sorter: true, sortOrder: sortOrderFor('name_en') },
                    { title: 'Nhóm', dataIndex: 'category', sorter: true, sortOrder: sortOrderFor('category') },
                    { title: 'Kcal/100g (tham chiếu)', render: (_, r) => r.reference_food?.kcal_per_100g ?? '—' },
                    {
                        title: 'Duyệt', dataIndex: 'needs_review', sorter: true, sortOrder: sortOrderFor('needs_review'),
                        render: (v, r) => v
                            ? <Tag color="red">Chờ duyệt</Tag>
                            : <Tag color="green">Đã duyệt{r.reviewed_by ? ` · ${r.reviewed_by}` : ''}</Tag>,
                    },
                    {
                        title: 'Thao tác', width: 220,
                        render: (_, r) => (
                            <Space>
                                <Button size="small" onClick={() => openEdit(r)}>Sửa</Button>
                                {r.needs_review
                                    ? <Popconfirm title="Duyệt nguyên liệu này?" onConfirm={() => review(r, true)}><Button size="small" type="primary">Duyệt</Button></Popconfirm>
                                    : <Popconfirm title="Mở lại để duyệt lại?" onConfirm={() => review(r, false)}><Button size="small">Mở lại</Button></Popconfirm>}
                                <Popconfirm title="Xoá nguyên liệu này?" onConfirm={async () => {
                                    try { await api.delete(`/ingredients/${r.id}`); message.success('Đã xoá'); load(meta.current_page); }
                                    catch (e) { message.error(e.response?.data?.message ?? 'Không xoá được'); }
                                }}>
                                    <Button size="small" danger>Xoá</Button>
                                </Popconfirm>
                            </Space>
                        ),
                    },
                ]}
            />
            <Drawer title={drawer === 'new' ? 'Thêm nguyên liệu' : 'Sửa nguyên liệu'} open={drawer !== null}
                onClose={() => setDrawer(null)} width={640}
                extra={<Button type="primary" onClick={save}>Lưu</Button>}>
                <Form form={form} layout="vertical">
                    <Form.Item label="Tên tiếng Việt" name="name_vi" rules={[{ required: true }]}><Input /></Form.Item>
                    <Form.Item label="Tên tiếng Anh" name="name_en"><Input /></Form.Item>
                    <Space style={{ width: '100%' }} size={12}>
                        <Form.Item label="Nhóm" name="category" style={{ flex: 1 }} rules={[{ required: true, message: 'Nhập nhóm nguyên liệu' }]}><Input placeholder="rau-cu, thit, gia-vi, khac…" /></Form.Item>
                        <Form.Item label="Nhóm con" name="subcategory" style={{ flex: 1 }}><Input /></Form.Item>
                    </Space>
                    <Form.Item label="Thực phẩm tham chiếu (USDA)" name="reference_food_id"
                        extra="Tìm để so sánh kcal/macro trước khi chọn ánh xạ.">
                        <Select showSearch filterOption={false} onSearch={searchRef} options={refOptions}
                            placeholder="Gõ để tìm trong 7.793 thực phẩm…" allowClear />
                    </Form.Item>
                    <Space style={{ width: '100%' }} size={12}>
                        <Form.Item label="Độ tin cậy ánh xạ" name="mapping_confidence" style={{ flex: 1 }} rules={[{ required: true }]}>
                            <Select options={CONFIDENCE} allowClear />
                        </Form.Item>
                        <Form.Item label="Nguồn dữ liệu" name="source" style={{ flex: 1 }}>
                            <Select options={[
                                { value: 'curated', label: 'Biên soạn (curated)' },
                                { value: 'llm_suggested', label: 'LLM đề xuất' },
                                { value: 'imported', label: 'Nhập khẩu' },
                                { value: 'user_submitted', label: 'Người dùng gửi' },
                            ]} />
                        </Form.Item>
                    </Space>
                    {confidence && confidence !== 'high' && (
                        <Form.Item label="Ghi chú ánh xạ (bắt buộc khi độ tin cậy không phải cao — R7)" name="mapping_note"
                            rules={[{ required: true, message: 'Phải ghi lý do ánh xạ' }]}>
                            <Input.TextArea rows={2} />
                        </Form.Item>
                    )}
                    <Space wrap size={12}>
                        <Form.Item label="% ăn được" name="edible_portion_pct"><InputNumber min={0} max={100} /></Form.Item>
                        <Form.Item label="Tỷ trọng g/ml" name="density_g_per_ml"><InputNumber min={0} step={0.01} /></Form.Item>
                        <Form.Item label="Hệ số hao hụt" name="default_yield_factor"><InputNumber min={0} max={1} step={0.01} /></Form.Item>
                        <Form.Item label="% hút dầu" name="oil_absorption_pct"><InputNumber min={0} max={100} /></Form.Item>
                        <Form.Item label="Ăn liền" name="is_ready_to_eat" valuePropName="checked"><Switch /></Form.Item>
                    </Space>
                    <Typography.Text strong>Tên gọi khác (tên vùng miền)</Typography.Text>
                    <Form.List name="aliases">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8 }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'name']} rules={[{ required: true, message: 'Tên gọi khác' }]}>
                                            <Input placeholder="VD: Khóm" />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'note']}>
                                            <Input placeholder="Nhãn vùng: miền Nam, tên cũ…" />
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" onClick={() => add()} block style={{ marginTop: 8, marginBottom: 16 }}>+ Thêm tên gọi khác</Button>
                            </>
                        )}
                    </Form.List>
                    <Typography.Text strong>Đơn vị quy đổi</Typography.Text>
                    <Form.List name="units">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8 }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'unit_name']} rules={[{ required: true, message: 'Tên đơn vị' }]}>
                                            <Input placeholder="quả, muỗng, chén…" />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'grams']}>
                                            <InputNumber placeholder="gram" min={0} />
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" onClick={() => add()} block style={{ marginTop: 8 }}>+ Thêm đơn vị</Button>
                            </>
                        )}
                    </Form.List>
                </Form>
            </Drawer>
        </>
    );
}
