<template>
    <v-card class="elevation-0 pa-4">
        <div class="section-title-bar">
            <p class="section-title">Service List</p>
            <p class="section-subtitle">Requested services</p>
        </div>
        <div class="service-list">
            <div v-for="service in services" :key="service.id" class="service-row">
                <div>
                    <p class="service-title">{{ service.title }}</p>
                    <p class="service-meta">{{ service.meta }}</p>
                </div>
                <v-chip size="x-small" variant="flat" class="service-chip">{{ service.tag }}</v-chip>
            </div>
        </div>
    </v-card>
</template>

<script setup>
defineProps({
    services: { type: Array, default: () => [] },
});
</script>

<style scoped>
.section-title-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
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

.service-list {
    display: grid;
    gap: 10px;
}

.service-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: #f9fafb;
}

.service-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.service-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}

.service-chip {
    background: #e0f2fe;
    color: #0369a1;
}
</style>
