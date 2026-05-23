<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({ template: Object, isOwner: Boolean });
defineEmits(['use', 'duplicate', 'delete']);
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
        <div class="h-32 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center">
            <img v-if="template.thumbnail_url" :src="template.thumbnail_url" class="h-full w-full object-cover" alt="">
            <svg v-else class="h-12 w-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>

        <div class="p-4 flex flex-col gap-2 flex-1">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">{{ template.name }}</h3>
                <span v-if="template.is_public" class="shrink-0 text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">Public</span>
            </div>
            <p v-if="template.description" class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ template.description }}</p>
            <p class="text-xs text-gray-400">{{ template.config?.widgets?.length ?? 0 }} widgets</p>
        </div>

        <div class="border-t border-gray-100 dark:border-gray-700 px-4 py-3 flex gap-2">
            <button @click="$emit('use', template)" class="flex-1 rounded-lg bg-blue-600 text-white text-xs py-1.5 font-medium hover:bg-blue-700 transition-colors">Use</button>
            <button @click="$emit('duplicate', template)" class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 text-xs py-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Clone</button>
            <Link v-if="template.is_owner" :href="route('templates.edit', template.id)" class="rounded-lg border border-gray-200 dark:border-gray-600 px-3 text-xs py-1.5 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Edit</Link>
            <button v-if="template.is_owner" @click="$emit('delete', template)" class="rounded-lg border border-red-200 dark:border-red-900 px-3 text-xs py-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">Del</button>
        </div>
    </div>
</template>
