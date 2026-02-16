import { defineStore } from 'pinia';
import authApi from '../api/auth';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        loading: false,
        initialized: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
        userName: (state) => state.user?.name || '',
        userRoles: (state) => state.user?.roles?.map(r => r.slug) || [],
        userPermissions: (state) => state.user?.permissions || [],
        isSuperAdmin: (state) => state.user?.roles?.some(r => r.slug === 'super-admin') || false,
        isAdmin: (state) => state.user?.roles?.some(r => r.slug === 'admin') || false,
        isUser: (state) => state.user?.roles?.some(r => r.slug === 'user') || false,
    },

    actions: {
        async fetchUser() {
            try {
                this.loading = true;
                const { data } = await authApi.getUser();
                this.user = data.user;
            } catch {
                this.user = null;
            } finally {
                this.loading = false;
                this.initialized = true;
            }
        },

        async login(credentials) {
            const { data } = await authApi.login(credentials);
            this.user = data.user;
            return data;
        },

        async register(formData) {
            const { data } = await authApi.register(formData);
            this.user = data.user;
            return data;
        },

        async logout() {
            try {
                await authApi.logout();
            } catch {
                // Always clear state even if API fails
            }
            this.user = null;
            this.initialized = false;
        },

        hasRole(slug) {
            return this.userRoles.includes(slug);
        },

        hasAnyRole(slugs) {
            return slugs.some(s => this.userRoles.includes(s));
        },

        hasPermission(slug) {
            if (this.isSuperAdmin) return true;
            return this.userPermissions.includes(slug);
        },
    },
});
