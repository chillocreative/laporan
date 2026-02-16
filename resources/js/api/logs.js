import api from './axios';

export default {
    activity(params = {}) {
        return api.get('/logs/activity', { params });
    },
    security(params = {}) {
        return api.get('/logs/security', { params });
    },
    ai(params = {}) {
        return api.get('/logs/ai', { params });
    },
    aiTodayUsage() {
        return api.get('/logs/ai/today-usage');
    },
    systemHealth() {
        return api.get('/system-health');
    },
};
