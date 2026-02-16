import { useNotificationStore } from '../stores/notifications';

export function useNotification() {
    const store = useNotificationStore();

    return {
        success: (msg) => store.success(msg),
        error: (msg) => store.error(msg),
        warning: (msg) => store.warning(msg),
        info: (msg) => store.info(msg),
    };
}
