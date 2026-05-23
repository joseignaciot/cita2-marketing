<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { v4 as uuidv4 } from 'uuid';
import axios from 'axios';

const page = usePage();
const saving = ref(false);

const availableWidgets = [
    { type: 'metrics_summary', label: 'Metrics Summary', defaultSize: { w: 12, h: 2 } },
    { type: 'clicks_chart', label: 'Clicks Chart', defaultSize: { w: 6, h: 4 } },
    { type: 'impressions_chart', label: 'Impressions Chart', defaultSize: { w: 6, h: 4 } },
    { type: 'top_queries', label: 'Top Queries', defaultSize: { w: 6, h: 6 } },
    { type: 'top_pages', label: 'Top Pages', defaultSize: { w: 6, h: 6 } },
    { type: 'queries_by_device', label: 'By Device', defaultSize: { w: 4, h: 4 } },
    { type: 'clicks_by_country', label: 'By Country', defaultSize: { w: 4, h: 4 } },
    { type: 'position_distribution', label: 'Position Dist.', defaultSize: { w: 4, h: 4 } },
    { type: 'ctr_vs_position', label: 'CTR vs Position', defaultSize: { w: 6, h: 4 } },
    { type: 'date_comparison', label: 'Period Comparison', defaultSize: { w: 12, h: 3 } },
];

const templateName = ref('My Template');
const templateDesc = ref('');
const isPublic = ref(false);
const selectedWidget = ref(null);

const config = reactive({
    layout: 'grid',
    color_scheme: { primary: '#3b82f6', accent: '#8b5cf6', bg: '#ffffff' },
    logo_url: null,
    date_range: { type: 'last_28', days: 28 },
    widgets: [],
    filters: { country: null, device: null, query_regex: null },
    show_branding: true,
});

function addWidget(wDef) {
    config.widgets.push({
        id: uuidv4(),
        type: wDef.type,
        title: wDef.label,
        position: { col: 1, row: config.widgets.length + 1, w: wDef.defaultSize.w, h: wDef.defaultSize.h },
        config: {},
    });
}

function removeWidget(id) {
    config.widgets = config.widgets.filter(w => w.id !== id);
    if (selectedWidget.value?.id === id) selectedWidget.value = null;
}

function selectWidget(w) {
    selectedWidget.value = selectedWidget.value?.id === w.id ? null : w;
}

async function save() {
    saving.value = true;
    try {
        await axios.post(route('api.templates.store'), {
            name: templateName.value,
            description: templateDesc.value,
            is_public: isPublic.value,
            config: { ...config },
        });
        router.visit(route('templates.index'));
    } catch (e) {
        alert('Failed to save template.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Template Builder" />
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Template Builder</h1>
                <button @click="save" :disabled="saving" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 transition-colors">
                    {{ saving ? 'Saving…' : 'Save Template' }}
                </button>
            </div>
        </template>

        <div class="flex gap-4 h-[calc(100vh-8rem)]">
            <!-- Left: Widget library -->
            <div class="w-56 shrink-0 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Widgets</p>
                <div class="space-y-1">
                    <button
                        v-for="w in availableWidgets"
                        :key="w.type"
                        @click="addWidget(w)"
                        class="w-full text-left rounded-lg px-3 py-2 text-xs text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-300 transition-colors"
                    >
                        + {{ w.label }}
                    </button>
                </div>
            </div>

            <!-- Center: Canvas -->
            <div class="flex-1 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-4">
                <div v-if="!config.widgets.length" class="flex items-center justify-center h-full text-gray-400 text-sm">
                    ← Add widgets from the panel
                </div>
                <div v-else class="grid grid-cols-12 gap-3 auto-rows-[minmax(60px,auto)]">
                    <div
                        v-for="w in config.widgets"
                        :key="w.id"
                        :style="{ gridColumn: `span ${w.position.w}`, gridRow: `span ${w.position.h}` }"
                        @click="selectWidget(w)"
                        :class="['rounded-xl border-2 p-3 cursor-pointer transition-colors', selectedWidget?.id === w.id ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800']"
                    >
                        <div class="flex items-start justify-between">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ w.title }}</span>
                            <button @click.stop="removeWidget(w.id)" class="text-gray-300 hover:text-red-500 transition-colors ml-2">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ w.type }}</p>
                        <p class="text-xs text-gray-300 mt-1">{{ w.position.w }}×{{ w.position.h }} grid</p>
                    </div>
                </div>
            </div>

            <!-- Right: Config panel -->
            <div class="w-64 shrink-0 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Template</p>
                    <input v-model="templateName" type="text" placeholder="Template name" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <textarea v-model="templateDesc" rows="2" placeholder="Description…" class="mt-2 w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
                    <label class="mt-2 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        <input type="checkbox" v-model="isPublic" class="rounded">
                        Public template
                    </label>
                </div>

                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Colors</p>
                    <div class="space-y-2">
                        <label class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                            Primary <input type="color" v-model="config.color_scheme.primary" class="h-6 w-10 rounded cursor-pointer">
                        </label>
                        <label class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                            Accent <input type="color" v-model="config.color_scheme.accent" class="h-6 w-10 rounded cursor-pointer">
                        </label>
                    </div>
                </div>

                <template v-if="selectedWidget">
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Widget Config</p>
                        <input v-model="selectedWidget.title" type="text" placeholder="Widget title" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-3 py-2 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-500">Width (cols)</label>
                                <input v-model.number="selectedWidget.position.w" type="number" min="1" max="12" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-2 py-1 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">Height (rows)</label>
                                <input v-model.number="selectedWidget.position.h" type="number" min="1" max="12" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm px-2 py-1 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
