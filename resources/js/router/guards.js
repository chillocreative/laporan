import { useAuthStore } from '../stores/auth';

export function setupGuards(router) {
    router.beforeEach(async (to, from, next) => {
        const authStore = useAuthStore();

        // Initialize user on first load
        if (!authStore.initialized) {
            await authStore.fetchUser();
        }

        const isAuth = authStore.isAuthenticated;

        // Guest-only routes (login, register)
        if (to.meta.guest) {
            return isAuth ? next({ name: 'dashboard' }) : next();
        }

        // Protected routes
        if (to.meta.auth && !isAuth) {
            return next({ name: 'login' });
        }

        // Role check
        if (to.meta.roles) {
            const hasRole = to.meta.roles.some(r => authStore.hasRole(r));
            if (!hasRole) {
                return next({ name: 'dashboard' });
            }
        }

        // Permission check
        if (to.meta.permission) {
            if (!authStore.hasPermission(to.meta.permission)) {
                return next({ name: 'dashboard' });
            }
        }

        next();
    });
}
