<script setup>
defineProps({
    label: String,
    value: [String, Number],
    change: Number,
    suffix: { type: String, default: '' },
    loading: Boolean,
});
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
        <p class="text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ label }}</p>
        <div v-if="loading" class="mt-2 h-8 w-24 animate-pulse rounded bg-gray-200 dark:bg-gray-700" />
        <div v-else class="mt-1 flex items-end gap-2">
            <span class="text-2xl font-bold text-gray-900 dark:text-white font-mono">{{ value }}{{ suffix }}</span>
            <span v-if="change !== undefined" :class="['mb-0.5 text-xs font-medium', change >= 0 ? 'text-green-500' : 'text-red-500']">
                {{ change >= 0 ? '▲' : '▼' }} {{ Math.abs(change) }}%
            </span>
        </div>
    </div>
</template>
