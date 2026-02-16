import { defineStore } from 'pinia';

let nextId = 0;

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        items: [],
    }),

    actions: {
        add(notification) {
            const id = nextId++;
            const item = {
                id,
                type: notification.type || 'info',
                message: notification.message,
                timeout: notification.timeout ?? 5000,
            };
            this.items.push(item);

            if (item.timeout > 0) {
                setTimeout(() => this.remove(id), item.timeout);
            }

            return id;
        },

        success(message) {
            return this.add({ type: 'success', message });
        },

        error(message) {
            return this.add({ type: 'error', message, timeout: 8000 });
        },

        warning(message) {
            return this.add({ type: 'warning', message });
        },

        info(message) {
            return this.add({ type: 'info', message });
        },

        remove(id) {
            const index = this.items.findIndex(item => item.id === id);
            if (index > -1) {
                this.items.splice(index, 1);
            }
        },

        clear() {
            this.items = [];
        },
    },
});
