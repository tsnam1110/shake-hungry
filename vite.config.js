import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

// Font phía người dùng: Inter (subset tiếng Việt) nạp qua Google Fonts trong
// layouts/app.blade.php, chồng font hệ thống Apple đứng sau trong app.css.
// Không dùng bunny fonts nữa (UI-004 — Instrument Sans hỗ trợ tiếng Việt kém).
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin/main.jsx'],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
