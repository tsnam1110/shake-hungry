import React, { useEffect, useState } from 'react';
import { Layout, Menu, Button, Space, Spin, Typography } from 'antd';
import api, { setUnauthorizedHandler } from './api';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Ingredients from './pages/Ingredients';
import Dishes from './pages/Dishes';

const { Header, Sider, Content } = Layout;

export default function App() {
    const [user, setUser] = useState(undefined); // undefined = đang kiểm tra phiên
    const [page, setPage] = useState('dashboard');

    useEffect(() => {
        setUnauthorizedHandler(() => setUser(null));
        api.get('/me')
            .then(({ data }) => setUser(data.user))
            .catch(() => setUser(null));
    }, []);

    if (user === undefined) return <Spin style={{ display: 'block', marginTop: 120 }} />;
    if (user === null) return <Login onSuccess={setUser} />;

    const logout = async () => {
        await api.post('/logout');
        setUser(null);
    };

    return (
        <Layout style={{ minHeight: '100vh' }}>
            <Sider breakpoint="lg" collapsedWidth={0}>
                <div style={{ color: '#fff', padding: '16px', fontWeight: 700, fontSize: 16 }}>🥗 Lắc Đói</div>
                <Menu theme="dark" mode="inline" selectedKeys={[page]} onClick={(e) => setPage(e.key)}
                    items={[
                        { key: 'dashboard', label: 'Tổng quan' },
                        { key: 'ingredients', label: 'Nguyên liệu' },
                        { key: 'dishes', label: 'Món ăn' },
                    ]} />
            </Sider>
            <Layout>
                <Header style={{ background: '#fff', display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingInline: 20 }}>
                    <Typography.Text strong>Công cụ biên soạn dữ liệu</Typography.Text>
                    <Space>
                        <Typography.Text>{user.name} · {user.role === 'admin' ? 'Quản trị viên' : 'Biên tập viên'}</Typography.Text>
                        <Button size="small" onClick={logout}>Đăng xuất</Button>
                    </Space>
                </Header>
                <Content style={{ margin: 20 }}>
                    {page === 'dashboard' && <Dashboard />}
                    {page === 'ingredients' && <Ingredients />}
                    {page === 'dishes' && <Dishes />}
                </Content>
            </Layout>
        </Layout>
    );
}
