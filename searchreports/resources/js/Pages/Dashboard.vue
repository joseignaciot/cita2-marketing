<script setup>
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

onMounted(() => router.visit(route('dashboard'), { replace: true }));
</script>
<template><div /></template>
