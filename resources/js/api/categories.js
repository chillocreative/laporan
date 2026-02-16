import api from './axios';

export default {
    list() {
        return api.get('/categories');
    },
    active() {
        return api.get('/categories/active');
    },
    store(data) {
        return api.post('/categories', data);
    },
    update(id, data) {
        return api.put(`/categories/${id}`, data);
    },
    delete(id) {
        return api.delete(`/categories/${id}`);
    },
};
