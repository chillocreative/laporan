import api from './axios';

export default {
    async getCsrfCookie() {
        await api.get('/sanctum/csrf-cookie', { baseURL: '' });
    },
    async login(credentials) {
        await this.getCsrfCookie();
        return api.post('/login', credentials);
    },
    async register(data) {
        await this.getCsrfCookie();
        return api.post('/register', data);
    },
    async logout() {
        await this.getCsrfCookie();
        return api.post('/logout');
    },
    getUser() {
        return api.get('/user');
    },
    async forgotPassword(data) {
        await this.getCsrfCookie();
        return api.post('/forgot-password', data);
    },
    async resetPassword(data) {
        await this.getCsrfCookie();
        return api.post('/reset-password', data);
    },
};
