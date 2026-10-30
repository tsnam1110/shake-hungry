import React, { useState } from 'react';
import { Button, Card, Form, Input, Typography, message } from 'antd';
import api from '../api';

export default function Login({ onSuccess }) {
    const [loading, setLoading] = useState(false);

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
            </Card>
        </div>
    );
}
