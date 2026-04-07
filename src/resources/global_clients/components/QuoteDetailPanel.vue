<template>
    <div class="quote-panel">
        <div class="panel-body">
            <div class="panel-row">
                <span class="panel-label">Quote #</span>
                <span class="panel-value">{{ quote?.code || '—' }}</span>
            </div>
            <div class="panel-row">
                <span class="panel-label">Status</span>
                <span class="panel-value text-capitalize">{{ quote?.status || '—' }}</span>
            </div>
            <div class="panel-row">
                <span class="panel-label">Meta</span>
                <span class="panel-value">{{ quote?.meta || '—' }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    quote: { type: Object, default: null },
});
</script>

<style scoped>
.quote-panel {
    padding: 16px;
}

.panel-body {
    display: grid;
    gap: 10px;
}

.panel-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.panel-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.panel-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}
</style>
