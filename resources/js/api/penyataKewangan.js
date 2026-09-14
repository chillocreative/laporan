import api from './axios';

export default {
    list() {
        return api.get('/penyata-kewangan');
    },
    create(formData) {
        return api.post('/penyata-kewangan', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/penyata-kewangan/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    delete(id) {
        return api.delete(`/penyata-kewangan/${id}`);
    },
};
