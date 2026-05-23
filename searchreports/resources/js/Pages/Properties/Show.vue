<script setup>
import { ref, onMounted, watch } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/MetricCard.vue';
import DataTable from '@/Components/DataTable.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const page = usePage();
const propertyId = page.url.split('/').pop();

const loading = ref(true);
const activeTab = ref('queries');
const summary = ref(null);
const queries = ref([]);
const pages = ref([]);

const dateRange = ref({
    start: new Date(Date.now() - 28 * 86400000).toISOString().split('T')[0],
    end: new Date().toISOString().split('T')[0],
});

async function loadMetrics() {
    loading.value = true;
    const [m, q, p] = await Promise.all([
        axios.get(route('api.properties.metrics', propertyId), { params: { start_date: dateRange.value.start, end_date: dateRange.value.end } }),
        axios.get(route('api.properties.queries', propertyId), { params: { start_date: dateRange.value.start, end_date: dateRange.value.end } }),
        axios.get(route('api.properties.pages', propertyId), { params: { start_date: dateRange.value.start, end_date: dateRange.value.end } }),
    ]);
    summary.value = m.data.summary;
    queries.value = q.data.data;
    pages.value = p.data.data;
    loading.value = false;
}

onMounted(loadMetrics);
watch(dateRange, loadMetrics, { deep: true });

const queryColumns = [
    { key: 'query', label: 'Query', sortable: false },
    { key: 'clicks', label: 'Clicks' },
    { key: 'impressions', label: 'Impressions' },
    { key: 'ctr', label: 'CTR' },
    { key: 'position', label: 'Position' },
];
const pageColumns = [
    { key: 'page', label: 'Page', sortable: false },
    { key: 'clicks', label: 'Clicks' },
    { key: 'impressions', label: 'Impressions' },
    { key: 'ctr', label: 'CTR' },
    { key: 'position', label: 'Position' },
];
</script>

<template>
    <AppLayout>
        <Head title="Property Details" />
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Property Details</h1>
        </template>

        <div class="space-y-6">
            <!-- Date picker -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <DateRangePicker v-model="dateRange" />
            </div>

            <!-- Summary metrics -->
            <div v-if="loading" class="flex justify-center py-10"><LoadingSpinner /></div>
            <div v-else-if="summary" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <MetricCard label="Clicks" :value="summary.clicks.toLocaleString()" />
                <MetricCard label="Impressions" :value="summary.impressions.toLocaleString()" />
                <MetricCard label="Avg CTR" :value="summary.ctr" suffix="%" />
                <MetricCard label="Avg Position" :value="summary.position" />
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="flex gap-6">
                    <button
                        v-for="tab in ['queries', 'pages']"
                        :key="tab"
                        @click="activeTab = tab"
                        :class="['py-2 text-sm font-medium capitalize border-b-2 transition-colors', activeTab === tab ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400']"
                    >{{ tab }}</button>
                </nav>
            </div>

            <DataTable
                v-if="activeTab === 'queries'"
                :columns="queryColumns"
                :rows="queries.map(r => ({ query: r.keys?.[0], clicks: r.clicks, impressions: r.impressions, ctr: (r.ctr * 100).toFixed(2) + '%', position: r.position?.toFixed(1) }))"
                :loading="loading"
            />
            <DataTable
                v-else
                :columns="pageColumns"
                :rows="pages.map(r => ({ page: r.keys?.[0], clicks: r.clicks, impressions: r.impressions, ctr: (r.ctr * 100).toFixed(2) + '%', position: r.position?.toFixed(1) }))"
                :loading="loading"
            />
        </div>
    </AppLayout>
</template>
