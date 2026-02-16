import { ref, computed } from 'vue';

export function usePagination() {
    const currentPage = ref(1);
    const lastPage = ref(1);
    const perPage = ref(15);
    const total = ref(0);

    const hasPages = computed(() => lastPage.value > 1);

    function setFromResponse(meta) {
        if (!meta) return;
        currentPage.value = meta.current_page || 1;
        lastPage.value = meta.last_page || 1;
        perPage.value = meta.per_page || 15;
        total.value = meta.total || 0;
    }

    function goToPage(page) {
        if (page >= 1 && page <= lastPage.value) {
            currentPage.value = page;
        }
    }

    return {
        currentPage,
        lastPage,
        perPage,
        total,
        hasPages,
        setFromResponse,
        goToPage,
    };
}
