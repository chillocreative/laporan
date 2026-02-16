import api from './axios';

export default {
    getStats() {
        return api.get('/dashboard');
    },
};
