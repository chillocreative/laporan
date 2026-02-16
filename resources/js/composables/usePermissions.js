import { useAuth } from './useAuth';

export function usePermissions() {
    const { hasPermission, hasRole, hasAnyRole, isSuperAdmin } = useAuth();

    const can = (permission) => hasPermission(permission);

    const canAny = (permissions) => permissions.some(p => hasPermission(p));

    const isAdminOrAbove = () => hasAnyRole(['super-admin', 'admin']);

    return { can, canAny, isAdminOrAbove, isSuperAdmin, hasRole, hasAnyRole };
}
