import { createRouter, createWebHistory } from 'vue-router';
import { setupGuards } from './guards';

const routes = [
    // Auth (guest only)
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/auth/Login.vue'),
        meta: { guest: true },
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../pages/auth/Register.vue'),
        meta: { guest: true },
    },

    // Forgot / Reset Password (guest only)
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../pages/auth/ForgotPassword.vue'),
        meta: { guest: true },
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../pages/auth/ResetPassword.vue'),
        meta: { guest: true },
    },

    // Authenticated layout
    {
        path: '/',
        component: () => import('../components/layout/AppLayout.vue'),
        meta: { auth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../pages/dashboard/DashboardPage.vue'),
            },

            // Reports
            {
                path: 'reports',
                name: 'reports.index',
                component: () => import('../pages/reports/ReportList.vue'),
            },
            {
                path: 'reports/create',
                name: 'reports.create',
                component: () => import('../pages/reports/ReportCreate.vue'),
                meta: { permission: 'reports.create' },
            },
            {
                path: 'reports/:id',
                name: 'reports.show',
                component: () => import('../pages/reports/ReportShow.vue'),
                props: true,
            },
            {
                path: 'reports/:id/edit',
                name: 'reports.edit',
                component: () => import('../pages/reports/ReportEdit.vue'),
                props: true,
            },

            // Users (admin+)
            {
                path: 'users',
                name: 'users.index',
                component: () => import('../pages/users/UserList.vue'),
                meta: { roles: ['super-admin', 'admin'] },
            },
            {
                path: 'users/create',
                name: 'users.create',
                component: () => import('../pages/users/UserCreate.vue'),
                meta: { roles: ['super-admin', 'admin'] },
            },
            {
                path: 'users/:id/edit',
                name: 'users.edit',
                component: () => import('../pages/users/UserEdit.vue'),
                meta: { roles: ['super-admin', 'admin'] },
                props: true,
            },

            // Categories (admin+)
            {
                path: 'categories',
                name: 'categories.index',
                component: () => import('../pages/categories/CategoryList.vue'),
                meta: { roles: ['super-admin', 'admin'] },
            },

            // Roles (super-admin only)
            {
                path: 'roles',
                name: 'roles.index',
                component: () => import('../pages/roles/RoleList.vue'),
                meta: { roles: ['super-admin'] },
            },
            {
                path: 'roles/create',
                name: 'roles.create',
                component: () => import('../pages/roles/RoleCreate.vue'),
                meta: { roles: ['super-admin'] },
            },
            {
                path: 'roles/:id/edit',
                name: 'roles.edit',
                component: () => import('../pages/roles/RoleEdit.vue'),
                meta: { roles: ['super-admin'] },
                props: true,
            },

            // Profile (all authenticated users)
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../pages/profile/ProfilePage.vue'),
            },

            // Settings (super-admin only)
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../pages/settings/SettingsPage.vue'),
                meta: { roles: ['super-admin'] },
            },

            // Logs (super-admin only)
            {
                path: 'logs/activity',
                name: 'logs.activity',
                component: () => import('../pages/logs/ActivityLogs.vue'),
                meta: { roles: ['super-admin'] },
            },
            {
                path: 'logs/security',
                name: 'logs.security',
                component: () => import('../pages/logs/SecurityLogs.vue'),
                meta: { roles: ['super-admin'] },
            },
            {
                path: 'logs/ai',
                name: 'logs.ai',
                component: () => import('../pages/logs/AILogs.vue'),
                meta: { roles: ['super-admin'] },
            },
        ],
    },

    // 404
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('../pages/auth/Login.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

setupGuards(router);

export default router;
