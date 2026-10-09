<template>
    <div class="card mb-6">
        <div class="card-header flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900">Senarai MPKK Mengikut DUN</h3>
            <button type="button" class="text-sm text-primary-600 hover:text-primary-700 font-medium" @click="toggleAll">
                {{ allOpen ? 'Tutup semua' : 'Kembangkan semua' }}
            </button>
        </div>
        <div class="card-body p-0 overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">DUN / Nama MPKK</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Laporan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Penyata Kewangan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Jumlah Minit Mesyuarat</th>
                    </tr>
                </thead>
                <tbody v-for="g in duns" :key="g.dun" class="border-b border-gray-100">
                    <tr class="bg-gray-50 cursor-pointer hover:bg-gray-100" @click="toggle(g.dun)">
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 text-gray-500 transition-transform" :class="{ 'rotate-180': isOpen(g.dun) }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                                DUN {{ g.dun }}
                                <span class="text-xs font-normal text-gray-500">({{ g.total_mpkk }} MPKK)</span>
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ g.report_count || 0 }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ g.penyata_kewangan_count || 0 }}</td>
                        <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ g.minit_mesyuarat_count || 0 }}</td>
                    </tr>
                    <template v-if="isOpen(g.dun)">
                        <tr v-for="m in g.users" :key="m.user_id" class="border-t border-gray-100">
                            <td class="pl-12 pr-6 py-3 text-sm text-gray-700">{{ m.user_name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right">{{ m.report_count || 0 }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right">{{ m.penyata_kewangan_count || 0 }}</td>
                            <td class="px-6 py-3 text-sm text-gray-900 text-right">{{ m.minit_mesyuarat_count || 0 }}</td>
                        </tr>
                        <tr v-if="!g.users.length">
                            <td colspan="4" class="pl-12 pr-6 py-3 text-sm text-gray-400">Tiada MPKK</td>
                        </tr>
                    </template>
                </tbody>
                <tbody v-if="!duns?.length">
                    <tr><td colspan="4" class="px-6 py-6 text-sm text-gray-400 text-center">Tiada pengguna MPKK</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    duns: { type: Array, default: () => [] },
});

// Collapsed by default; track which DUNs the user expanded.
const open = ref(new Set());

const isOpen = (dun) => open.value.has(dun);
const allOpen = computed(() => props.duns.length > 0 && props.duns.every(g => open.value.has(g.dun)));

function toggle(dun) {
    const next = new Set(open.value);
    next.has(dun) ? next.delete(dun) : next.add(dun);
    open.value = next;
}

function toggleAll() {
    open.value = allOpen.value ? new Set() : new Set(props.duns.map(g => g.dun));
}
</script>
