import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

export function useAuth() {
    const store = useAuthStore();

    return {
        user: computed(() => store.user),
        isAuthenticated: computed(() => store.isAuthenticated),
        isSuperAdmin: computed(() => store.isSuperAdmin),
        isAdmin: computed(() => store.isAdmin),
        isUser: computed(() => store.isUser),
        loading: computed(() => store.loading),
        hasRole: (slug) => store.hasRole(slug),
        hasAnyRole: (slugs) => store.hasAnyRole(slugs),
        hasPermission: (slug) => store.hasPermission(slug),
    };
}
