import api from './axios';

export default {
    list() {
        return api.get('/roles');
    },
    get(id) {
        return api.get(`/roles/${id}`);
    },
    create(data) {
        return api.post('/roles', data);
    },
    update(id, data) {
        return api.put(`/roles/${id}`, data);
    },
    delete(id) {
        return api.delete(`/roles/${id}`);
    },
    permissions() {
        return api.get('/permissions');
    },
    assignable() {
        return api.get('/roles/assignable');
    },
};
