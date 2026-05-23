<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    columns: Array,
    rows: Array,
    loading: Boolean,
    perPage: { type: Number, default: 25 },
});

const sortKey = ref('');
const sortDir = ref('asc');
const page = ref(1);

function sort(key) {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'desc';
    }
    page.value = 1;
}

const sorted = computed(() => {
    if (!sortKey.value) return props.rows ?? [];
    return [...(props.rows ?? [])].sort((a, b) => {
        const av = a[sortKey.value];
        const bv = b[sortKey.value];
        const cmp = av < bv ? -1 : av > bv ? 1 : 0;
        return sortDir.value === 'asc' ? cmp : -cmp;
    });
});

const totalPages = computed(() => Math.ceil(sorted.value.length / props.perPage));
const paginated = computed(() => sorted.value.slice((page.value - 1) * props.perPage, page.value * props.perPage));
</script>

<template>
    <div>
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            v-for="col in columns"
                            :key="col.key"
                            @click="col.sortable !== false ? sort(col.key) : null"
                            :class="['px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap', col.sortable !== false ? 'cursor-pointer select-none hover:text-gray-700 dark:hover:text-gray-200' : '']"
                        >
                            {{ col.label }}
                            <span v-if="sortKey === col.key" class="ml-1">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    <tr v-if="loading">
                        <td :colspan="columns.length" class="py-12 text-center">
                            <div class="flex justify-center"><slot name="loading"><span class="text-gray-400">Loading…</span></slot></div>
                        </td>
                    </tr>
                    <tr v-else-if="!paginated.length">
                        <td :colspan="columns.length" class="py-12 text-center text-gray-400">No data</td>
                    </tr>
                    <tr v-for="(row, i) in paginated" :key="i" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td v-for="col in columns" :key="col.key" class="px-4 py-3 text-gray-700 dark:text-gray-200 font-mono text-xs">
                            <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                {{ row[col.key] ?? '—' }}
                            </slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="totalPages > 1" class="flex items-center justify-between mt-4 text-sm text-gray-500">
            <span>Page {{ page }} of {{ totalPages }}</span>
            <div class="flex gap-2">
                <button @click="page--" :disabled="page <= 1" class="rounded px-3 py-1 border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700">Prev</button>
                <button @click="page++" :disabled="page >= totalPages" class="rounded px-3 py-1 border border-gray-200 dark:border-gray-600 disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700">Next</button>
            </div>
        </div>
    </div>
</template>
