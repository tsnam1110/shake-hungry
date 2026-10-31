import React, { useCallback, useEffect, useState } from 'react';
import {
    Button, Checkbox, Drawer, Form, Input, InputNumber, Popconfirm, Select, Space, Table, Tag, Typography, message,
} from 'antd';
import api from '../api';

// Module quản trị hồ sơ hoa quả (FRUIT-005) — mục riêng của nhánh hoa quả.
// Dinh dưỡng/dị ứng không sửa ở đây: thuộc nguyên liệu liên kết (QT-3/FRUIT-MODULE §1).
const GROUPS = [
    { value: 'co-mui', label: 'Có múi' },
    { value: 'mong', label: 'Quả mọng' },
    { value: 'hach', label: 'Quả hạch' },
    { value: 'nhiet-doi', label: 'Nhiệt đới' },
    { value: 'dua', label: 'Họ dưa' },
    { value: 'khac', label: 'Khác' },
];
const ORIGINS = [
    { value: 'trong-nuoc', label: 'Trong nước' },
    { value: 'nhap-khau', label: 'Nhập khẩu' },
    { value: 'ca-hai', label: 'Cả hai' },
];
const TYPES = [
    { value: 'theo-mua', label: 'Theo mùa' },
    { value: 'quanh-nam', label: 'Quanh năm' },
    { value: 'quanh-nam-nhap-khau', label: 'Quanh năm (nhập khẩu)' },
];
const REGIONS = [
    { value: 'bac', label: 'Miền Bắc' },
    { value: 'trung', label: 'Miền Trung' },
    { value: 'nam', label: 'Miền Nam' },
];
const MONTHS = Array.from({ length: 12 }, (_, i) => ({ value: i + 1, label: `Th ${i + 1}` }));
const groupLabel = (v) => GROUPS.find((g) => g.value === v)?.label ?? v;
const originLabel = (v) => ORIGINS.find((g) => g.value === v)?.label ?? v;

export default function Fruits() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, per_page: 20, total: 0 });
    const [loading, setLoading] = useState(false);
    const [q, setQ] = useState('');
    const [reviewFilter, setReviewFilter] = useState(undefined);
    const [drawer, setDrawer] = useState(null); // null | 'new' | fruit object
    const [ingredientOptions, setIngredientOptions] = useState([]);
    const [form] = Form.useForm();

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const params = { page, per_page: 20 };
            if (q) params.q = q;
            if (reviewFilter !== undefined) params.needs_review = reviewFilter;
            const { data } = await api.get('/fruits', { params });
            setRows(data.data);
            setMeta({ current_page: data.current_page, per_page: data.per_page, total: data.total });
        } finally {
            setLoading(false);
        }
    }, [q, reviewFilter]);

    useEffect(() => { load(1); }, [load]);

    const openEdit = async (record) => {
        if (record) {
            const { data } = await api.get(`/fruits/${record.id}`);
            setDrawer(data);
            form.setFieldsValue({
                classification_group: data.classification_group,
                family: data.family,
                origin: data.origin,
                grown_in: data.grown_in ?? [],
                availability_months: data.availability_months ?? [],
                main_season_months: data.main_season_months ?? [],
                availability_type: data.availability_type,
                best_nutrition_months: data.best_nutrition_months ?? [],
                best_nutrition_note: data.best_nutrition_note,
                best_value_months: data.best_value_months ?? [],
                best_value_note: data.best_value_note,
                pairing_cautions: data.pairing_cautions ?? [],
                selection_note: data.selection_note,
                storage_note: data.storage_note,
                prices: (data.prices ?? []).map((p) => ({ ...p, collected_at: p.collected_at?.slice(0, 10) })),
            });
        } else {
            const { data } = await api.get('/ingredients', { params: { category: 'trai-cay', per_page: 100 } });
            setIngredientOptions(data.data.map((i) => ({ value: i.id, label: `${i.name_vi} (${i.slug})` })));
            setDrawer('new');
            form.resetFields();
            form.setFieldsValue({ availability_months: [], main_season_months: [], grown_in: [], pairing_cautions: [], prices: [] });
        }
    };

    const save = async () => {
        const values = await form.validateFields();
        try {
            if (drawer === 'new') {
                await api.post('/fruits', values);
                message.success('Đã tạo hồ sơ quả (đang chờ duyệt)');
            } else {
                await api.put(`/fruits/${drawer.id}`, values);
                message.success('Đã lưu hồ sơ quả');
            }
            setDrawer(null);
            load(meta.current_page);
        } catch (e) {
            const errors = e.response?.data?.errors;
            message.error(errors ? Object.values(errors).flat()[0] : (e.response?.data?.message ?? 'Lưu thất bại'));
        }
    };

    const review = async (record, approve) => {
        await api.post(`/fruits/${record.id}/review`, { approve });
        message.success(approve ? 'Đã duyệt hồ sơ' : 'Đã mở lại để duyệt lại');
        load(meta.current_page);
    };

    return (
        <>
            <Space style={{ marginBottom: 16, width: '100%', justifyContent: 'space-between' }}>
                <Typography.Title level={4} style={{ margin: 0 }}>Hoa quả</Typography.Title>
                <Button type="primary" onClick={() => openEdit(null)}>+ Thêm hồ sơ quả</Button>
            </Space>
            <Space style={{ marginBottom: 12 }}>
                <Input.Search placeholder="Tìm theo tên (kể cả tên gọi khác)…" allowClear style={{ width: 300 }}
                    onSearch={(v) => setQ(v)} onChange={(e) => { if (!e.target.value) setQ(''); }} />
                <Select placeholder="Trạng thái duyệt" allowClear style={{ width: 190 }} value={reviewFilter}
                    onChange={setReviewFilter}
                    options={[{ value: true, label: 'Chờ duyệt' }, { value: false, label: 'Đã duyệt' }]} />
            </Space>
            <Table
                rowKey="id" loading={loading} dataSource={rows}
                pagination={{ current: meta.current_page, pageSize: meta.per_page, total: meta.total, onChange: load }}
                columns={[
                    { title: 'Tên quả', render: (_, r) => r.ingredient?.name_vi ?? r.slug },
                    { title: 'Nhóm', dataIndex: 'classification_group', render: groupLabel },
                    { title: 'Nguồn gốc', dataIndex: 'origin', render: originLabel },
                    {
                        title: 'Tháng có hàng',
                        render: (_, r) => `${(r.availability_months ?? []).length} tháng${(r.main_season_months ?? []).length ? ` · vụ chính ${(r.main_season_months).join(', ')}` : ''}`,
                    },
                    { title: 'Dòng giá', render: (_, r) => (r.prices ?? []).length },
                    {
                        title: 'Duyệt', dataIndex: 'needs_review',
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
                                    ? <Popconfirm title="Duyệt hồ sơ này?" onConfirm={() => review(r, true)}><Button size="small" type="primary">Duyệt</Button></Popconfirm>
                                    : <Popconfirm title="Mở lại để duyệt lại?" onConfirm={() => review(r, false)}><Button size="small">Mở lại</Button></Popconfirm>}
                                <Popconfirm title="Xoá hồ sơ quả này?" onConfirm={async () => {
                                    try { await api.delete(`/fruits/${r.id}`); message.success('Đã xoá'); load(meta.current_page); }
                                    catch (e) { message.error(e.response?.data?.message ?? 'Không xoá được'); }
                                }}>
                                    <Button size="small" danger>Xoá</Button>
                                </Popconfirm>
                            </Space>
                        ),
                    },
                ]}
            />
            <Drawer title={drawer === 'new' ? 'Thêm hồ sơ quả' : 'Sửa hồ sơ quả'} open={drawer !== null}
                onClose={() => setDrawer(null)} width={760}
                extra={<Button type="primary" onClick={save}>Lưu</Button>}>
                <Form form={form} layout="vertical">
                    {drawer === 'new' && (
                        <Form.Item label="Nguyên liệu trái cây" name="ingredient_id" rules={[{ required: true, message: 'Chọn nguyên liệu' }]}>
                            <Select showSearch optionFilterProp="label" options={ingredientOptions} placeholder="Chọn quả (nguyên liệu trai-cay chưa có hồ sơ)" />
                        </Form.Item>
                    )}
                    <Space style={{ display: 'flex' }} align="start">
                        <Form.Item label="Nhóm phân loại" name="classification_group" style={{ flex: 1 }} rules={[{ required: true }]}>
                            <Select options={GROUPS} />
                        </Form.Item>
                        <Form.Item label="Nguồn gốc" name="origin" style={{ flex: 1 }} rules={[{ required: true }]}>
                            <Select options={ORIGINS} />
                        </Form.Item>
                        <Form.Item label="Kiểu mùa vụ" name="availability_type" style={{ flex: 1 }} rules={[{ required: true }]}>
                            <Select options={TYPES} />
                        </Form.Item>
                    </Space>
                    <Form.Item label="Họ thực vật" name="family"><Input placeholder="VD: Rutaceae (họ Cam)" /></Form.Item>
                    <Form.Item label="Tháng có hàng" name="availability_months" rules={[{ required: true, message: 'Chọn ít nhất 1 tháng' }]}>
                        <Checkbox.Group options={MONTHS} />
                    </Form.Item>
                    <Form.Item label="Vụ chính" name="main_season_months">
                        <Checkbox.Group options={MONTHS} />
                    </Form.Item>
                    <Form.Item label="Tháng dinh dưỡng tốt nhất" name="best_nutrition_months">
                        <Checkbox.Group options={MONTHS} />
                    </Form.Item>
                    <Form.Item label="Lý do (cửa sổ dinh dưỡng)" name="best_nutrition_note"><Input.TextArea rows={2} /></Form.Item>
                    <Form.Item label="Tháng giá hời nhất" name="best_value_months">
                        <Checkbox.Group options={MONTHS} />
                    </Form.Item>
                    <Form.Item label="Lý do (cửa sổ giá)" name="best_value_note"><Input.TextArea rows={2} /></Form.Item>

                    <Typography.Text strong>Vùng trồng tiêu biểu</Typography.Text>
                    <Form.List name="grown_in">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8 }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'region']} rules={[{ required: true, message: 'Vùng trồng' }]}>
                                            <Input placeholder="Tỉnh/vùng, VD: Bến Tre" style={{ width: 200 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'note']}>
                                            <Input placeholder="Ghi chú (giống, đặc sản…)" style={{ width: 320 }} />
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" onClick={() => add()} block style={{ marginTop: 8, marginBottom: 16 }}>+ Thêm vùng trồng</Button>
                            </>
                        )}
                    </Form.List>

                    <Typography.Text strong>Lưu ý kết hợp thực phẩm</Typography.Text>
                    <Form.List name="pairing_cautions">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8 }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'with']} rules={[{ required: true, message: 'Không nên kết hợp với' }]}>
                                            <Input placeholder="Kết hợp với gì…" style={{ width: 180 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'level']} rules={[{ required: true }]}>
                                            <Select style={{ width: 130 }} options={[{ value: 'co-so', label: 'Có cơ sở' }, { value: 'dan-gian', label: 'Dân gian' }]} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'note']} rules={[{ required: true, message: 'Giải thích' }]}>
                                            <Input placeholder="Giải thích ngắn" style={{ width: 260 }} />
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" onClick={() => add()} block style={{ marginTop: 8, marginBottom: 16 }}>+ Thêm lưu ý kết hợp</Button>
                            </>
                        )}
                    </Form.List>

                    <Form.Item label="Ghi chú chọn quả" name="selection_note"><Input.TextArea rows={2} /></Form.Item>
                    <Form.Item label="Ghi chú bảo quản" name="storage_note"><Input.TextArea rows={2} /></Form.Item>

                    <Typography.Text strong>Giá tham khảo mùa vụ (VND/kg — bắt buộc nguồn + ngày thu thập)</Typography.Text>
                    <Form.List name="prices">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8, flexWrap: 'wrap' }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'region_code']} rules={[{ required: true }]}>
                                            <Select style={{ width: 110 }} options={REGIONS} placeholder="Miền" />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'month']} rules={[{ required: true }]}>
                                            <Select style={{ width: 80 }} options={MONTHS} placeholder="Tháng" />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'price_min']} rules={[{ required: true }]}>
                                            <InputNumber min={1} step={1000} placeholder="Giá từ" style={{ width: 110 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'price_max']} rules={[{ required: true }]}>
                                            <InputNumber min={1} step={1000} placeholder="Giá đến" style={{ width: 110 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'source_name']} rules={[{ required: true, message: 'Nguồn giá' }]}>
                                            <Input placeholder="Nguồn giá" style={{ width: 200 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'source_url']}>
                                            <Input placeholder="URL nguồn (nếu có)" style={{ width: 200 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'collected_at']} rules={[{ required: true, message: 'Ngày thu thập' }]}>
                                            <Input placeholder="YYYY-MM-DD" style={{ width: 110 }} />
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" onClick={() => add({ collected_at: new Date().toISOString().slice(0, 10) })} block style={{ marginTop: 8 }}>+ Thêm dòng giá</Button>
                            </>
                        )}
                    </Form.List>
                </Form>
            </Drawer>
        </>
    );
}
