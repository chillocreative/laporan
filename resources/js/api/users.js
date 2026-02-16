import api from './axios';

export default {
    list(params = {}) {
        return api.get('/users', { params });
    },
    get(id) {
        return api.get(`/users/${id}`);
    },
    create(data) {
        return api.post('/users', data);
    },
    update(id, data) {
        return api.put(`/users/${id}`, data);
    },
    delete(id) {
        return api.delete(`/users/${id}`);
    },
    toggleActive(id) {
        return api.patch(`/users/${id}/toggle-active`);
    },
    approve(id, data) {
        return api.post(`/users/${id}/approve`, data);
    },
    pendingCount() {
        return api.get('/users/pending-count');
    },
};
