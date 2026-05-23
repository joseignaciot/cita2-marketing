<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const loading = ref(true);
const reports = ref([]);
let pollTimer = null;

async function load() {
    const { data } = await axios.get(route('api.reports.index'));
    reports.value = data.data;
    loading.value = false;
}

async function del(id) {
    if (!confirm('Delete this report?')) return;
    await axios.delete(route('api.reports.destroy', id));
    load();
}

async function share(id) {
    const { data } = await axios.post(route('api.reports.share', id));
    navigator.clipboard.writeText(data.share_url);
    alert('Share link copied to clipboard!');
}

const hasPending = () => reports.value.some(r => ['pending', 'generating'].includes(r.status));

onMounted(() => {
    load();
    pollTimer = setInterval(() => { if (hasPending()) load(); }, 5000);
});

onUnmounted(() => clearInterval(pollTimer));
</script>

<template>
    <AppLayout>
        <Head title="Reports" />
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Reports</h1>
                <Link :href="route('reports.create')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Report
                </Link>
            </div>
        </template>

        <div v-if="loading" class="flex justify-center py-20"><LoadingSpinner size="lg" /></div>

        <EmptyState
            v-else-if="!reports.length"
            title="No reports yet"
            description="Create your first report to start analyzing your search performance."
        />

        <div v-else class="space-y-3">
            <div
                v-for="r in reports"
                :key="r.id"
                class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-4 shadow-sm"
            >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ r.name }}</h3>
                        <StatusBadge :status="r.status" />
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded-full uppercase">{{ r.output_format }}</span>
                    </div>
                    <div class="mt-1 flex items-center gap-4 text-xs text-gray-400">
                        <span>{{ r.property?.display_name }}</span>
                        <span>{{ r.date_from }} – {{ r.date_to }}</span>
                        <span v-if="r.template">Template: {{ r.template.name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a
                        v-if="r.status === 'ready'"
                        :href="route('api.reports.download', r.id)"
                        class="rounded-lg bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-3 py-1.5 text-xs font-medium hover:bg-green-100 transition-colors"
                    >Download</a>
                    <button
                        v-if="r.status === 'ready'"
                        @click="share(r.id)"
                        class="rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >Share</button>
                    <button
                        @click="del(r.id)"
                        class="rounded-lg border border-red-100 dark:border-red-900 text-red-500 px-3 py-1.5 text-xs font-medium hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                    >Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
