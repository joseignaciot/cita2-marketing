<script setup>
import { ref, onMounted } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const page = usePage();
const step = ref(1);
const loading = ref(true);
const submitting = ref(false);

const properties = ref([]);
const templates = ref([]);

const form = ref({
    name: '',
    property_id: null,
    template_id: null,
    date_from: new Date(Date.now() - 28 * 86400000).toISOString().split('T')[0],
    date_to: new Date().toISOString().split('T')[0],
    output_format: 'pdf',
});

async function load() {
    const [p, t] = await Promise.all([
        axios.get(route('api.properties.index')),
        axios.get(route('api.templates.index')),
    ]);
    properties.value = p.data.data;
    templates.value = t.data.data;
    loading.value = false;
}

async function submit() {
    if (!form.value.property_id) { alert('Please select a property.'); return; }
    submitting.value = true;
    try {
        await axios.post(route('api.reports.store'), form.value);
        router.visit(route('reports.index'));
    } catch (e) {
        alert('Failed to create report.');
        submitting.value = false;
    }
}

onMounted(load);
</script>

<template>
    <AppLayout>
        <Head title="Create Report" />
        <template #header>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">New Report</h1>
        </template>

        <div v-if="loading" class="flex justify-center py-20"><LoadingSpinner size="lg" /></div>

        <div v-else class="max-w-2xl mx-auto">
            <!-- Step indicator -->
            <div class="flex items-center gap-3 mb-8">
                <div v-for="s in [1,2,3]" :key="s" class="flex items-center gap-3">
                    <div :class="['h-8 w-8 rounded-full flex items-center justify-center text-sm font-semibold transition-colors', step >= s ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500']">{{ s }}</div>
                    <span :class="['text-sm', step >= s ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-400']">{{ ['Property', 'Template', 'Configure'][s-1] }}</span>
                    <div v-if="s < 3" class="flex-1 h-px bg-gray-200 dark:bg-gray-700 w-8" />
                </div>
            </div>

            <!-- Step 1: Select property -->
            <div v-if="step === 1" class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Select the Search Console property to generate the report for.</p>
                <div class="grid grid-cols-1 gap-3">
                    <button
                        v-for="prop in properties"
                        :key="prop.id"
                        @click="form.property_id = prop.id"
                        :class="['rounded-xl border-2 p-4 text-left transition-colors', form.property_id === prop.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 bg-white dark:bg-gray-800']"
                    >
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ prop.display_name }}</p>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ prop.site_url }}</p>
                    </button>
                </div>
                <div class="flex justify-end mt-6">
                    <button @click="step = 2" :disabled="!form.property_id" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors">Next →</button>
                </div>
            </div>

            <!-- Step 2: Select template -->
            <div v-if="step === 2" class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Choose a report template (optional — defaults to basic layout).</p>
                <div class="grid grid-cols-1 gap-3">
                    <button
                        @click="form.template_id = null"
                        :class="['rounded-xl border-2 p-4 text-left transition-colors', form.template_id === null ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 bg-white dark:bg-gray-800']"
                    >
                        <p class="font-semibold text-sm text-gray-900 dark:text-white">Default Layout</p>
                        <p class="text-xs text-gray-400 mt-0.5">Basic metrics summary + top queries</p>
                    </button>
                    <button
                        v-for="t in templates"
                        :key="t.id"
                        @click="form.template_id = t.id"
                        :class="['rounded-xl border-2 p-4 text-left transition-colors', form.template_id === t.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 bg-white dark:bg-gray-800']"
                    >
                        <div class="flex items-start justify-between">
                            <p class="font-semibold text-sm text-gray-900 dark:text-white">{{ t.name }}</p>
                            <span v-if="t.is_public" class="text-xs text-green-500">Public</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">{{ t.config?.widgets?.length ?? 0 }} widgets</p>
                    </button>
                </div>
                <div class="flex justify-between mt-6">
                    <button @click="step = 1" class="rounded-lg border border-gray-200 dark:border-gray-600 px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">← Back</button>
                    <button @click="step = 3" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">Next →</button>
                </div>
            </div>

            <!-- Step 3: Configure -->
            <div v-if="step === 3" class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Configure your report settings.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Report Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g. Monthly SEO Report — May 2026" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date From</label>
                            <input v-model="form.date_from" type="date" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Date To</label>
                            <input v-model="form.date_to" type="date" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Output Format</label>
                        <select v-model="form.output_format" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pdf">PDF</option>
                            <option value="html">HTML</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-between mt-6">
                    <button @click="step = 2" class="rounded-lg border border-gray-200 dark:border-gray-600 px-6 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">← Back</button>
                    <button @click="submit" :disabled="submitting || !form.name" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50 transition-colors">
                        {{ submitting ? 'Generating…' : 'Generate Report' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
