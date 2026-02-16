import api from './axios';

export default {
    list(params = {}) {
        return api.get('/reports', { params });
    },
    get(id) {
        return api.get(`/reports/${id}`);
    },
    create(formData) {
        return api.post('/reports', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    update(id, formData) {
        formData.append('_method', 'PUT');
        return api.post(`/reports/${id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    },
    delete(id) {
        return api.delete(`/reports/${id}`);
    },
    deleteAttachment(attachmentId) {
        return api.delete(`/attachments/${attachmentId}`);
    },
    triggerAnalysis(id) {
        return api.post(`/reports/${id}/analyze`);
    },
    analysisStatus(id) {
        return api.get(`/reports/${id}/analysis-status`);
    },
};
