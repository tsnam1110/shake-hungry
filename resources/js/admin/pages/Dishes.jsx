import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
    Alert, Button, Checkbox, Drawer, Form, Input, InputNumber, Popconfirm, Select,
    Space, Table, Tag, Typography, message,
} from 'antd';
import api from '../api';

const SLOT_OPTIONS = [
    { value: 'sang', label: 'Sáng' },
    { value: 'trua', label: 'Trưa' },
    { value: 'chieu', label: 'Chiều' },
    { value: 'toi', label: 'Tối' },
    { value: 'an-vat', label: 'Ăn vặt' },
];

export default function Dishes() {
    const [rows, setRows] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, per_page: 20, total: 0 });
    const [loading, setLoading] = useState(false);
    const [q, setQ] = useState('');
    const [drawer, setDrawer] = useState(null);
    const [form] = Form.useForm();
    const [regions, setRegions] = useState([]);
    const [similar, setSimilar] = useState([]);
    const [nutrition, setNutrition] = useState(null);
    const [ingredientOptions, setIngredientOptions] = useState([]);
    const [preview, setPreview] = useState(null);
    const timers = useRef({});
    const watchedIngredients = Form.useWatch('ingredients', form);
    const watchedMethod = Form.useWatch('cooking_method', form);
    const parentDishId = Form.useWatch('parent_dish_id', form);

    // ADM-004: calo cập nhật trực tiếp khi gõ — engine chính thức chạy trên dữ liệu form
    useEffect(() => {
        const rowsIn = (watchedIngredients ?? []).filter((r) => r?.ingredient_id && r?.quantity);
        if (rowsIn.length === 0) { setPreview(null); return; }
        clearTimeout(timers.current.preview);
        timers.current.preview = setTimeout(async () => {
            try {
                const { data } = await api.post('/dishes/preview-nutrition', {
                    cooking_method: watchedMethod ?? null,
                    ingredients: rowsIn,
                });
                setPreview(data);
            } catch { setPreview(null); }
        }, 500);
    }, [watchedIngredients, watchedMethod]);

    const load = useCallback(async (page = 1) => {
        setLoading(true);
        try {
            const { data } = await api.get('/dishes', { params: { page, per_page: 20, ...(q ? { q } : {}) } });
            setRows(data.data);
            setMeta(data);
        } finally {
            setLoading(false);
        }
    }, [q]);

    useEffect(() => { load(1); }, [load]);
    useEffect(() => {
        api.get('/regions').then(({ data }) => setRegions(data.map((r) => ({ value: r.id, label: r.name }))));
    }, []);

    const searchIngredients = (value) => {
        clearTimeout(timers.current.ing);
        timers.current.ing = setTimeout(async () => {
            const { data } = await api.get('/ingredients', { params: { q: value, per_page: 20 } });
            setIngredientOptions(data.data.map((i) => ({ value: i.id, label: i.name_vi })));
        }, 300);
    };

    // ADM-005: cảnh báo trùng tên theo trigram ngay khi gõ
    const checkSimilar = (name, excludeId) => {
        clearTimeout(timers.current.sim);
        timers.current.sim = setTimeout(async () => {
            if (!name || name.trim().length < 3) { setSimilar([]); return; }
            const { data } = await api.get('/dishes/similar', { params: { name, exclude_id: excludeId ?? 0 } });
            setSimilar(data);
        }, 400);
    };

    const openEdit = async (record) => {
        setSimilar([]);
        setNutrition(null);
        if (record) {
            const { data } = await api.get(`/dishes/${record.id}`);
            setDrawer(data);
            setNutrition(data.nutrition ?? null);
            setIngredientOptions((data.dish_ingredients ?? []).map((di) => ({ value: di.ingredient_id, label: di.ingredient?.name_vi ?? `#${di.ingredient_id}` })));
            form.setFieldsValue({
                primary_name: data.names?.find((n) => n.is_primary)?.name ?? '',
                alias_names: (data.names ?? []).filter((n) => n.is_alias).map((n) => n.name),
                english_name: (data.names ?? []).find((n) => n.locale === 'en')?.name ?? '',
                country_code: data.country_code, region_id: data.region_id,
                is_eat_out: data.is_eat_out, is_home_cook: data.is_home_cook,
                cooking_method: data.cooking_method, serving_desc: data.serving_desc,
                serving_grams: data.serving_grams, parent_dish_id: data.parent_dish_id,
                recipe_steps: data.recipe_steps ?? [],
                meal_slots: (data.meal_slots ?? []).map((s) => s.slot),
                ingredients: (data.dish_ingredients ?? []).map((di) => ({
                    ingredient_id: di.ingredient_id, quantity: di.quantity,
                    unit_name: di.unit_name, note: di.note, is_optional: di.is_optional,
                })),
            });
        } else {
            setDrawer('new');
            form.resetFields();
            form.setFieldsValue({ country_code: 'VN', is_eat_out: true, is_home_cook: true, ingredients: [], meal_slots: [], recipe_steps: [] });
        }
    };

    const save = async () => {
        const values = await form.validateFields();
        try {
            let resp;
            if (drawer === 'new') {
                resp = await api.post('/dishes', values);
                message.success('Đã tạo món (bản nháp, chờ duyệt)');
            } else {
                resp = await api.put(`/dishes/${drawer.id}`, values);
                message.success('Đã lưu món');
            }
            setDrawer(resp.data);
            setNutrition(resp.data.nutrition ?? null);
            load(meta.current_page);
        } catch (e) {
            message.error(e.response?.data?.message ?? 'Lưu thất bại');
        }
    };

    const compute = async () => {
        try {
            const { data } = await api.post(`/dishes/${drawer.id}/compute`);
            setNutrition(data);
            message.success('Đã tính dinh dưỡng bằng engine chính thức');
            load(meta.current_page);
        } catch (e) {
            message.error(e.response?.data?.message ?? 'Chưa tính được');
        }
    };

    const review = async (record, approve) => {
        await api.post(`/dishes/${record.id}/review`, { approve });
        message.success(approve ? 'Đã duyệt món' : 'Đã mở lại món');
        load(meta.current_page);
    };

    return (
        <>
            <Space style={{ marginBottom: 16, width: '100%', justifyContent: 'space-between' }}>
                <Typography.Title level={4} style={{ margin: 0 }}>Món ăn</Typography.Title>
                <Button type="primary" onClick={() => openEdit(null)}>+ Nhập món mới</Button>
            </Space>
            <Input.Search placeholder="Tìm món theo tên…" allowClear style={{ width: 300, marginBottom: 12 }}
                onSearch={setQ} onChange={(e) => { if (!e.target.value) setQ(''); }} />
            <Table
                rowKey="id" loading={loading} dataSource={rows}
                pagination={{ current: meta.current_page, pageSize: meta.per_page, total: meta.total, onChange: load }}
                columns={[
                    { title: 'Tên món', render: (_, r) => r.names?.[0]?.name ?? r.slug },
                    { title: 'Vùng miền', render: (_, r) => r.region?.name ?? '—' },
                    { title: 'Mức hoàn thiện', dataIndex: 'completeness_level', render: (v) => <Tag>Mức {v}</Tag> },
                    { title: 'Trạng thái', dataIndex: 'status' },
                    {
                        title: 'Duyệt', dataIndex: 'needs_review',
                        render: (v) => v ? <Tag color="red">Chờ duyệt</Tag> : <Tag color="green">Đã duyệt</Tag>,
                    },
                    {
                        title: 'Thao tác', width: 200,
                        render: (_, r) => (
                            <Space>
                                <Button size="small" onClick={() => openEdit(r)}>Mở / Sửa</Button>
                                {r.needs_review
                                    ? <Popconfirm title="Duyệt món này?" onConfirm={() => review(r, true)}><Button size="small" type="primary">Duyệt</Button></Popconfirm>
                                    : <Popconfirm title="Mở lại món này?" onConfirm={() => review(r, false)}><Button size="small">Mở lại</Button></Popconfirm>}
                            </Space>
                        ),
                    },
                ]}
            />
            <Drawer title={drawer === 'new' ? 'Nhập món mới (Mức 1)' : 'Sửa món'} open={drawer !== null}
                onClose={() => setDrawer(null)} width={760}
                extra={<Space>
                    {drawer && drawer !== 'new' && <Button onClick={compute}>Tính dinh dưỡng</Button>}
                    <Button type="primary" onClick={save}>Lưu món</Button>
                </Space>}>
                {nutrition && (
                    <Alert type="success" style={{ marginBottom: 12 }}
                        message={`Dinh dưỡng đã tính: ${nutrition.kcal_low ?? '?'}–${nutrition.kcal_high ?? '?'} kcal/khẩu phần (điểm giữa ${nutrition.kcal_point ?? '?'})`}
                        description={nutrition.confidence_note ?? undefined} />
                )}
                <Form form={form} layout="vertical">
                    <Form.Item label="Tên món" name="primary_name" rules={[{ required: true, message: 'Nhập tên món' }]}>
                        <Input onChange={(e) => checkSimilar(e.target.value, drawer && drawer !== 'new' ? drawer.id : 0)} />
                    </Form.Item>
                    {similar.length > 0 && (
                        <Alert type="warning" style={{ marginBottom: 12 }}
                            message="Có món gần giống đã tồn tại — chọn cách xử lý:"
                            description={similar.map((s) => (
                                <div key={s.dish_id} style={{ marginTop: 4 }}>
                                    <b>{s.name}</b>{s.region ? ` (${s.region})` : ''} — giống {(s.similarity * 100).toFixed(0)}%
                                    <Space style={{ marginLeft: 8 }}>
                                        <Button size="small" onClick={() => openEdit({ id: s.dish_id })}>Sửa món cũ</Button>
                                        <Button size="small" onClick={() => { form.setFieldValue('parent_dish_id', s.dish_id); setSimilar([]); message.info(`Sẽ tạo biến thể của "${s.name}"`); }}>Tạo biến thể</Button>
                                        <Button size="small" type="text" onClick={() => setSimilar((prev) => prev.filter((x) => x.dish_id !== s.dish_id))}>Vẫn là món độc lập</Button>
                                    </Space>
                                </div>
                            ))} />
                    )}
                    {parentDishId ? (
                        <Alert type="info" style={{ marginBottom: 12 }} message={`Món này sẽ là biến thể của món #${parentDishId}`}
                            action={<Button size="small" onClick={() => form.setFieldValue('parent_dish_id', null)}>Huỷ biến thể</Button>} />
                    ) : null}
                    <Form.Item name="parent_dish_id" hidden><Input /></Form.Item>
                    <Form.Item label="Tên gọi khác" name="alias_names">
                        <Select mode="tags" placeholder="Gõ rồi Enter để thêm tên khác" />
                    </Form.Item>
                    <Space style={{ width: '100%' }} size={12}>
                        <Form.Item label="Tên tiếng Anh" name="english_name" style={{ flex: 1 }}><Input /></Form.Item>
                        <Form.Item label="Vùng miền" name="region_id" style={{ flex: 1 }}>
                            <Select options={regions} showSearch optionFilterProp="label" allowClear placeholder="Chọn vùng" />
                        </Form.Item>
                        <Form.Item label="Quốc gia" name="country_code"><Input maxLength={2} style={{ width: 70 }} /></Form.Item>
                    </Space>
                    <Space size={24}>
                        <Form.Item name="is_eat_out" valuePropName="checked"><Checkbox>Ăn ngoài</Checkbox></Form.Item>
                        <Form.Item name="is_home_cook" valuePropName="checked"><Checkbox>Tự nấu</Checkbox></Form.Item>
                    </Space>
                    <Form.Item label="Bữa ăn" name="meal_slots">
                        <Checkbox.Group options={SLOT_OPTIONS} />
                    </Form.Item>
                    <Space style={{ width: '100%' }} size={12}>
                        <Form.Item label="Cách chế biến" name="cooking_method" style={{ flex: 1 }}>
                            <Input placeholder="luoc, xao, chien, nuong…" />
                        </Form.Item>
                        <Form.Item label="Khẩu phần" name="serving_desc" style={{ flex: 1 }}>
                            <Input placeholder="1 tô vừa, 1 đĩa…" />
                        </Form.Item>
                        <Form.Item label="Gram/khẩu phần" name="serving_grams">
                            <InputNumber min={0} />
                        </Form.Item>
                    </Space>
                    <Form.Item label="Các bước nấu (mỗi dòng một bước)" name="recipe_steps">
                        <Select mode="tags" placeholder="Gõ bước rồi Enter" />
                    </Form.Item>
                    {preview && (
                        <Alert type={preview.missing?.length ? 'warning' : 'success'} style={{ marginBottom: 12 }}
                            message={`Calo trực tiếp (toàn bộ công thức như đang nhập): ${preview.kcal_min != null ? `${Math.round(preview.kcal_min)}–${Math.round(preview.kcal_max)} kcal` : 'chưa đủ dữ liệu'} · đạm ${preview.protein_g ?? '?'}g · béo ${preview.fat_g ?? '?'}g · bột ${preview.carb_g ?? '?'}g · tin cậy: ${preview.confidence}`}
                            description={preview.missing?.length ? `Chưa tra được dinh dưỡng: ${preview.missing.join(', ')}` : undefined} />
                    )}
                    <Typography.Text strong>Nguyên liệu của món</Typography.Text>
                    <Typography.Paragraph type="secondary" style={{ margin: 0 }}>
                        Mẹo bàn phím: nhấn Enter ở ô số lượng của dòng cuối để thêm dòng mới.
                    </Typography.Paragraph>
                    <Form.List name="ingredients">
                        {(fields, { add, remove }) => (
                            <>
                                {fields.map((field) => (
                                    <Space key={field.key} style={{ display: 'flex', marginTop: 8 }} align="baseline">
                                        <Form.Item {...field} name={[field.name, 'ingredient_id']} rules={[{ required: true, message: 'Chọn nguyên liệu' }]} style={{ minWidth: 240 }}>
                                            <Select showSearch filterOption={false} onSearch={searchIngredients}
                                                options={ingredientOptions} placeholder="Tìm nguyên liệu…" />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'quantity']}>
                                            <InputNumber placeholder="Số lượng" min={0}
                                                onPressEnter={(e) => {
                                                    e.preventDefault();
                                                    const current = form.getFieldValue('ingredients') ?? [];
                                                    if (field.name === current.length - 1) {
                                                        document.querySelector('[data-add-ingredient]')?.click();
                                                    }
                                                }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'unit_name']}>
                                            <Input placeholder="g / quả / muỗng" style={{ width: 110 }} />
                                        </Form.Item>
                                        <Form.Item {...field} name={[field.name, 'is_optional']} valuePropName="checked">
                                            <Checkbox>Tuỳ chọn</Checkbox>
                                        </Form.Item>
                                        <Button danger size="small" onClick={() => remove(field.name)}>Xoá</Button>
                                    </Space>
                                ))}
                                <Button type="dashed" data-add-ingredient onClick={() => add({ unit_name: 'g' })} block style={{ marginTop: 8 }}>
                                    + Thêm nguyên liệu
                                </Button>
                            </>
                        )}
                    </Form.List>
                </Form>
            </Drawer>
        </>
    );
}
