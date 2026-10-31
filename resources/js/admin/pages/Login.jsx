import React, { useEffect, useState } from 'react';
import { Button, Card, Form, Input, Typography, message } from 'antd';
import api from '../api';

export default function Login({ onSuccess }) {
    const [loading, setLoading] = useState(false);
    const [quickLogin, setQuickLogin] = useState(false);
    const [quickLoading, setQuickLoading] = useState(false);

    useEffect(() => {
        api.get('/login-options').then(({ data }) => setQuickLogin(data.quick_login === true)).catch(() => {});
    }, []);

    const enterQuick = async () => {
        setQuickLoading(true);
        try {
            const { data } = await api.post('/quick-login');
            onSuccess(data.user);
        } catch {
            message.error('Đăng nhập nhanh đang tắt hoặc chưa có tài khoản admin.');
        } finally {
            setQuickLoading(false);
        }
    };

    const submit = async (values) => {
        setLoading(true);
        try {
            const { data } = await api.post('/login', values);
            onSuccess(data.user);
        } catch (e) {
            const errors = e.response?.data?.errors;
            message.error(errors?.email?.[0] ?? 'Đăng nhập thất bại, thử lại sau.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', background: '#f5f5f5' }}>
            <Card style={{ width: 380 }}>
                <Typography.Title level={3} style={{ textAlign: 'center', marginBottom: 4 }}>Lắc Đói — Quản trị</Typography.Title>
                <Typography.Paragraph type="secondary" style={{ textAlign: 'center' }}>
                    Công cụ biên soạn dữ liệu món ăn
                </Typography.Paragraph>
                <Form layout="vertical" onFinish={submit}>
                    <Form.Item label="Email" name="email" rules={[{ required: true, type: 'email', message: 'Nhập email hợp lệ' }]}>
                        <Input autoComplete="username" placeholder="admin@lacdoi.local" />
                    </Form.Item>
                    <Form.Item label="Mật khẩu" name="password" rules={[{ required: true, message: 'Nhập mật khẩu' }]}>
                        <Input.Password autoComplete="current-password" />
                    </Form.Item>
                    <Button type="primary" htmlType="submit" block loading={loading}>Đăng nhập</Button>
                </Form>
                {quickLogin && (
                    <>
                        <Typography.Paragraph type="warning" style={{ textAlign: 'center', margin: '16px 0 8px', fontSize: 12 }}>
                            ⚠️ Chế độ kiểm thử đang bật: nút dưới đây vào thẳng quản trị cấp cao nhất mà không cần mật khẩu. Đừng bật ở môi trường thật.
                        </Typography.Paragraph>
                        <Button danger block loading={quickLoading} onClick={enterQuick}>
                            ⚡ Vào thẳng quản trị (kiểm thử)
                        </Button>
                    </>
                )}
            </Card>
        </div>
    );
}
