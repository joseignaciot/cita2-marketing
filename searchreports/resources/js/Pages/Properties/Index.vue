<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PropertyCard from '@/Components/PropertyCard.vue';
import EmptyState from '@/Components/EmptyState.vue';
import LoadingSpinner from '@/Components/LoadingSpinner.vue';
import axios from 'axios';

const loading = ref(true);
const syncing = ref(false);
const syncingId = ref(null);
const properties = ref([]);
let pollTimer = null;

async function load() {
    const { data } = await axios.get(route('api.properties.index'));
    properties.value = data.data;
    loading.value = false;
}

async function syncAll() {
    syncing.value = true;
    await axios.post(route('api.properties.sync'));
    setTimeout(load, 3000);
    syncing.value = false;
}

async function syncOne(id) {
    syncingId.value = id;
    await axios.post(route('api.properties.sync'));
    setTimeout(() => { load(); syncingId.value = null; }, 3000);
}

onMounted(() => {
    load();
    pollTimer = setInterval(load, 30000);
});

onUnmounted(() => clearInterval(pollTimer));
</script>

<template>
    <AppLayout>
        <Head title="Properties" />
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Properties</h1>
                <button
                    @click="syncAll"
                    :disabled="syncing"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-60 transition-colors"
                >
                    <svg :class="['h-4 w-4', syncing ? 'animate-spin' : '']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    {{ syncing ? 'Syncing…' : 'Sync All' }}
                </button>
            </div>
        </template>

        <div v-if="loading" class="flex justify-center py-20"><LoadingSpinner size="lg" /></div>

        <EmptyState
            v-else-if="!properties.length"
            title="No properties found"
            description="Click 'Sync All' to import your Google Search Console properties."
        />

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <PropertyCard
                v-for="prop in properties"
                :key="prop.id"
                :property="prop"
                :syncing="syncingId === prop.id"
                @sync="syncOne"
            />
        </div>
    </AppLayout>
</template>
