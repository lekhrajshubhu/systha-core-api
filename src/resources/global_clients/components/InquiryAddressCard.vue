<template>
    <v-card class="elevation-0 pa-4">
        <div class="section-title-bar">
            <p class="section-title">Service Address</p>
            <p class="section-subtitle">Where the service is needed</p>
        </div>
        <div class="address-tile">
            <p v-for="(line, idx) in lines" :key="idx" class="address-line">{{ line }}</p>
        </div>
    </v-card>
</template>

<script setup>
defineProps({
    lines: { type: Array, default: () => [] },
});
</script>

<style scoped>
.section-title-bar {
    margin-bottom: 12px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.address-tile {
    padding: 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: #f9fafb;
}

.address-line {
    font-size: 13px;
    color: #111827;
    margin: 2px 0;
}
</style>
