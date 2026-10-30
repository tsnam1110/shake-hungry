import React from 'react';
import { createRoot } from 'react-dom/client';
import { ConfigProvider } from 'antd';
import viVN from 'antd/locale/vi_VN';
import App from './App';

createRoot(document.getElementById('admin-root')).render(
    <React.StrictMode>
        <ConfigProvider locale={viVN}>
            <App />
        </ConfigProvider>
    </React.StrictMode>,
);
