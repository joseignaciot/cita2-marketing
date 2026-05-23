<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const report = computed(() => page.props.report?.data ?? {});
</script>

<template>
    <Head :title="report.name ?? 'Shared Report'" />

    <div class="min-h-screen bg-gray-50 p-6">
        <div class="max-w-4xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ report.name }}</h1>
                <p class="text-gray-500 text-sm mt-1">{{ report.property?.display_name }} · {{ report.date_from }} – {{ report.date_to }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <p class="text-gray-500 text-sm">This report was shared with you via SearchReports.</p>
                <div v-if="report.status === 'ready'" class="mt-4">
                    <a :href="'/api/reports/' + report.id + '/download'" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        Download Report
                    </a>
                </div>
                <p v-else class="mt-4 text-sm text-yellow-600">Report is still being generated. Please check back shortly.</p>
            </div>
        </div>
    </div>
</template>
