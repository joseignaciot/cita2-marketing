<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import EmptyState from '@/Components/EmptyState.vue';
import axios from 'axios';

const loading = ref(true);
const summary = ref(null);

async function load() {
    const { data } = await axios.get(route('api.dashboard.summary'));
    summary.value = data;
    loading.value = false;
}

onMounted(load);
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        </template>

        <div v-if="loading" class="flex justify-center py-20">
            <LoadingSpinner size="lg" />
        </div>

        <template v-else-if="summary">
            <!-- Metrics row -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <MetricCard label="Total Clicks (28d)" :value="summary.total_clicks.toLocaleString()" />
                <MetricCard label="Total Impressions (28d)" :value="summary.total_impressions.toLocaleString()" />
                <MetricCard label="Connected Properties" :value="summary.properties_count" />
                <MetricCard label="Reports" :value="summary.reports_count" />
            </div>

            <!-- Quick actions -->
            <div class="flex flex-wrap gap-3 mb-6">
                <Link :href="route('reports.create')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Report
                </Link>
                <Link :href="route('properties.index')" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Manage Properties
                </Link>
            </div>

            <!-- Properties list -->
            <div>
                <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Your Properties</h2>
                <EmptyState v-if="!summary.properties.length" title="No properties connected" description="Connect with Google and sync your Search Console properties.">
                    <Link :href="route('properties.index')" class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Go to Properties</Link>
                </EmptyState>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    <div
                        v-for="prop in summary.properties"
                        :key="prop.id"
                        class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 flex items-center gap-3 shadow-sm hover:shadow transition-shadow"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ prop.display_name }}</p>
                            <p class="text-xs text-gray-400 font-mono truncate">{{ prop.site_url }}</p>
                        </div>
                        <Link :href="route('properties.show', prop.id)" class="shrink-0 text-blue-500 hover:text-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </Link>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>
