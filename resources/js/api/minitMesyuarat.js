import api from './axios';

export default {
    list() {
        return api.get('/minit-mesyuarat');
    },
    create(formData) {
        return api.post('/minit-mesyuarat', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/minit-mesyuarat/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    delete(id) {
        return api.delete(`/minit-mesyuarat/${id}`);
    },
};
