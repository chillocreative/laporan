import { defineStore } from 'pinia';
import settingsApi from '../api/settings';

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        systemName: 'Sistem Pelaporan',
        systemLogo: null,
        recaptchaSiteKey: null,
        loaded: false,
    }),

    actions: {
        async fetchPublic() {
            try {
                const { data } = await settingsApi.getPublic();
                this.systemName = data.system_name || 'Sistem Pelaporan';
                this.systemLogo = data.system_logo;
                this.recaptchaSiteKey = data.recaptcha_site_key;
                this.loaded = true;
            } catch {
                // Use defaults
            }
        },
    },
});
