<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    property: Object,
    syncing: Boolean,
});

defineEmits(['sync']);

function faviconUrl(siteUrl) {
    const clean = siteUrl.replace('sc-domain:', 'https://');
    try {
        const url = new URL(clean);
        return `https://www.google.com/s2/favicons?domain=${url.hostname}&sz=32`;
    } catch {
        return null;
    }
}
</script>

<template>
    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm p-5 flex flex-col gap-3">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-3">
                <img v-if="faviconUrl(property.site_url)" :src="faviconUrl(property.site_url)" class="h-6 w-6 rounded" alt="">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm truncate max-w-[180px]">{{ property.display_name }}</h3>
                    <p class="text-xs text-gray-400 font-mono truncate max-w-[180px]">{{ property.site_url }}</p>
                </div>
            </div>
            <span :class="['shrink-0 inline-flex h-2 w-2 rounded-full mt-1.5', property.is_active ? 'bg-green-400' : 'bg-gray-300']" />
        </div>

        <div class="text-xs text-gray-400">
            Last synced: {{ property.last_synced_at ? new Date(property.last_synced_at).toLocaleDateString() : 'Never' }}
        </div>

        <div class="flex gap-2">
            <Link :href="route('properties.show', property.id)" class="flex-1 text-center rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs py-1.5 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors">
                View
            </Link>
            <button
                @click="$emit('sync', property.id)"
                :disabled="syncing"
                class="flex-1 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-xs py-1.5 font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
            >
                {{ syncing ? 'Syncing…' : 'Sync' }}
            </button>
        </div>
    </div>
</template>
