<template>
    <v-card class="elevation-0 pa-4">
        <div class="section-title-bar">
            <p class="section-title">Preferred Date & Time</p>
            <p class="section-subtitle">When the service is needed</p>
        </div>
        <div class="address-tile">
            <p class="address-line">{{ formatDate(dateTime[0]) }}</p>
            <p class="address-line">{{ formatTime(dateTime[1]) }}</p>
        </div>
    </v-card>
</template>

<script setup>
const props = defineProps({
    dateTime: { type: Array, default: () => [] },
});

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    }).format(date);
}

function formatTime(value) {
    if (!value) return '—';
    const timeString = String(value).trim();
    const date = new Date(`1970-01-01T${timeString}`);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
    }).format(date);
}
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
