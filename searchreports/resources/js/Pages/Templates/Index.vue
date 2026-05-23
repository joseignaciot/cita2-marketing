<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TemplateCard from '@/Components/TemplateCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const loading = ref(true);
const templates = ref([]);

async function load() {
    const { data } = await axios.get(route('api.templates.index'));
    templates.value = data.data;
    loading.value = false;
}

async function duplicate(template) {
    await axios.post(route('api.templates.duplicate', template.id));
    load();
}

async function del(template) {
    if (!confirm(`Delete "${template.name}"?`)) return;
    await axios.delete(route('api.templates.destroy', template.id));
    load();
}

function useTemplate(template) {
    router.visit(route('reports.create', { template_id: template.id }));
}

onMounted(load);
</script>

<template>
    <AppLayout>
        <Head title="Templates" />
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Report Templates</h1>
                <a :href="route('templates.builder')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    New Template
                </a>
            </div>
        </template>

        <div v-if="loading" class="flex justify-center py-20"><LoadingSpinner size="lg" /></div>

        <EmptyState
            v-else-if="!templates.length"
            title="No templates yet"
            description="Create your first report template to get started."
        />

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <TemplateCard
                v-for="t in templates"
                :key="t.id"
                :template="t"
                :is-owner="t.is_owner"
                @use="useTemplate"
                @duplicate="duplicate"
                @delete="del"
            />
        </div>
    </AppLayout>
</template>
