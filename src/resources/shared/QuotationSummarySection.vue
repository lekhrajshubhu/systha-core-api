<template>
    <div class="mb-6">
        <div class="d-flex mt-3 vendor-info">
            <div class="pr-3">
                <v-avatar size="44" tile>
                    <img contain height="32" :src="quotation?.vendor?.logo" alt="Vendor Logo">
                </v-avatar>
            </div>
            <div>
                <p class="mb-0 vendor-name">{{ quotation?.vendor?.name || '' }}</p>
                <p v-if="vendorAddress" class="mb-0 vendor-address">{{ vendorAddress }}</p>
            </div>
        </div>
        <v-divider class="mt-2 mb-2"></v-divider>
        <div class="summary-grid">
            <div class="summary-row">
                <p class="summary-label">Quotation Number</p>
                <p class="summary-value">{{ quotation?.quote_number }}</p>
            </div>
            <div class="summary-row">
                <p class="summary-label">Status</p>
                <v-chip class="summary-chip text-capitalize" :class="statusClass" size="small" variant="flat"
                    rounded="pill">
                    {{ quotation?.status }}
                </v-chip>
            </div>
        </div>
        <div v-if="quotation?.description" class="summary-desc">
            <p class="summary-label mb-1">Description</p>
            <div class="summary-desc-body" :v-html="quotation.description"></div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({
    quotation: {
        type: Object,
        required: true,
    },
    vendorAddress: {
        type: String,
        default: '',
    },
});

const statusClass = computed(() =>
    props.quotation?.status === 'confirmed' ? 'status-confirmed' : 'status-pending'
);
</script>

<style scoped>
.vendor-info {
    align-items: center;
}

.vendor-name {
    font-weight: 600;
    color: #222;
}

.vendor-address {
    font-size: 13px;
    color: #6b7280;
}

.summary-grid {
    display: grid;
    gap: 10px;
    margin-top: 6px;
}

.summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
}

.summary-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin: 0;
}

.summary-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.summary-chip {
    font-size: 12px;
    font-weight: 600;
}

.summary-desc {
    margin-top: 12px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
}

.summary-desc-body {
    font-size: 14px;
    color: #111827;
}

.status-confirmed {
    background: #dcfce7;
    color: #166534;
}

.status-pending {
    background: #fef3c7;
    color: #92400e;
}
</style>
