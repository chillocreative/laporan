import api from './axios';

export default {
    getAll() {
        return api.get('/settings');
    },
    getPublic() {
        return api.get('/settings/public');
    },
    updateGroup(group, data) {
        if (group === 'branding') {
            const formData = new FormData();
            formData.append('logo', data.logo);
            formData.append('_method', 'PUT');
            return api.post(`/settings/${group}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
        }
        return api.put(`/settings/${group}`, data);
    },
};
