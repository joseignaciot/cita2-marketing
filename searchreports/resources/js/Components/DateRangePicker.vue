<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Object,
});
const emit = defineEmits(['update:modelValue']);

const presets = [
    { label: 'Last 7d', days: 7 },
    { label: 'Last 28d', days: 28 },
    { label: 'Last 90d', days: 90 },
    { label: 'Last 6m', days: 180 },
];

function applyPreset(days) {
    const end = new Date().toISOString().split('T')[0];
    const start = new Date(Date.now() - days * 86400000).toISOString().split('T')[0];
    emit('update:modelValue', { start, end });
}

const from = computed({
    get: () => props.modelValue?.start ?? '',
    set: v => emit('update:modelValue', { ...props.modelValue, start: v }),
});

const to = computed({
    get: () => props.modelValue?.end ?? '',
    set: v => emit('update:modelValue', { ...props.modelValue, end: v }),
});
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <button
            v-for="p in presets"
            :key="p.days"
            @click="applyPreset(p.days)"
            class="rounded-full border border-gray-200 dark:border-gray-600 px-3 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >{{ p.label }}</button>
        <input type="date" v-model="from" class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <span class="text-gray-400 text-xs">–</span>
        <input type="date" v-model="to" class="rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
</template>
