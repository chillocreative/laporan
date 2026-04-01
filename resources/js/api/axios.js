import axios from 'axios';
import router from '../router';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
    withXSRFToken: true,
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const currentRoute = router.currentRoute.value;
        if (error.response?.status === 401 && !currentRoute.meta.guest) {
            router.push({ name: 'login' });
        }
        if (error.response?.status === 403 && !currentRoute.meta.guest) {
            router.push({ name: 'dashboard' });
        }
        return Promise.reject(error);
    }
);

export default api;
