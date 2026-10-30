import axios from 'axios';

// API nội bộ của khu vực quản trị: dùng phiên web + cookie XSRF của Laravel.
const api = axios.create({
    baseURL: '/admin/api',
    withCredentials: true,
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
});

let onUnauthorized = null;
export function setUnauthorizedHandler(fn) {
    onUnauthorized = fn;
}

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && onUnauthorized) {
            onUnauthorized();
        }
        return Promise.reject(error);
    },
);

export default api;
