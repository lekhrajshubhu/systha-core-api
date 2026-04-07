<template>
    <div>
        <section class="summary-panel">
            <div class="summary-toolbar">
                <div class="section-header-top">
                    <h4 class="section-title">Quotation Summary</h4>
                    <v-chip size="small" variant="flat" :class="statusClass">
                        {{ statusLabel }}
                    </v-chip>
                </div>
                <v-btn v-if="showConfirmButton" color="primary" variant="flat" @click="emit('confirm')">
                    Confirm
                </v-btn>
            </div>
            <div class="section-meta">
                <div class="meta-pill">
                    <span class="meta-label">Quotation #</span>
                    <span class="meta-value">{{ quotationNumber || '—' }}</span>
                </div>
                <div class="meta-pill">
                    <span class="meta-label">Date</span>
                    <span class="meta-value">{{ formatDate(quotationDate) }}</span>
                </div>
                <div class="meta-pill">
                    <span class="meta-label">Time</span>
                    <span class="meta-value">{{ formatTime(quotationTime) }}</span>
                </div>
            </div>
        </section>
        <div v-if="sections && sections.length">
            <v-card v-for="section in sections" :key="section.id" class="mb-6 section-block elevation-0 border">
                <div class="d-flex align-center justify-space-between mb-2">
                    <h5 class="mb-0">{{ section.title }}</h5>
                </div>
                <div v-if="section.description" v-html="section.description" class="mb-3"></div>
                <v-table dense class="section-table">
                    <thead>
                        <tr>
                            <th class="text-left">Item</th>
                            <th class="text-left">Qty</th>
                            <th class="text-left">Unit</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in section.items || []" :key="item.id">
                            <td>
                                <span :class="item.parent_id ? 'section-item-child' : 'section-item-parent'">
                                    {{ item.title }}
                                </span>
                            </td>
                            <td>{{ item.qty }}</td>
                            <td>{{ item.unit }}</td>
                            <td class="text-right">{{ formatAmount(item.unit_price) }}</td>
                            <td class="text-right">{{ formatAmount(item.line_total) }}</td>
                        </tr>
                    </tbody>
                </v-table>
            </v-card>
        </div>
        <div class="d-flex justify-end">
           
            <v-card class="totals-card elevation-0">
                <table class="totals-table">
                    <tbody>
                        <tr>
                            <td class="totals-label">Sub Total</td>
                            <td class="totals-value">{{ formatAmount(normalizedTotals.sub_total) }}</td>
                        </tr>
                        <tr>
                            <td class="totals-label">Tax</td>
                            <td class="totals-value">{{ formatAmount(normalizedTotals.tax) }}</td>
                        </tr>
                        <tr class="totals-row">
                            <td class="totals-label">Total</td>
                            <td class="totals-value">{{ formatAmount(normalizedTotals.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </v-card>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { formatAmount } from '@utils/helpers';

const props = defineProps({
    sections: {
        type: Array,
        default: () => [],
    },
    totals: {
        type: Object,
        required: true,
    },
    quotationNumber: {
        type: String,
        default: '',
    },
    quotationDate: {
        type: String,
        default: '',
    },
    quotationTime: {
        type: String,
        default: '',
    },
    status: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['confirm']);

const normalizedTotals = computed(() => {
    const totals = props.totals || {};
    return {
        sub_total: totals.sub_total ?? null,
        tax: totals.tax_amount ?? null,
        total: totals.total_amount ?? null,
    };
});

const statusLabel = computed(() => (props.status || 'Unknown').toString().replace(/_/g, ' ').trim());

const statusClass = computed(() => {
    const value = (props.status || '').toLowerCase();
    if (value === 'confirmed') return 'status-chip status-confirmed';
    if (value === 'pending') return 'status-chip status-pending';
    if (value === 'cancelled') return 'status-chip status-cancelled';
    if (value === 'completed') return 'status-chip status-completed';
    return 'status-chip status-default';
});

const showConfirmButton = computed(() => (props.status || '').toLowerCase() !== 'confirmed');

const formatDate = (value) => {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('en-US', { day: '2-digit', month: 'short', year: 'numeric' });
};

const formatTime = (value) => {
    if (!value) return '—';
    const isDateTime = typeof value === 'string' && (value.includes('T') || value.includes(' '));
    const date = isDateTime ? new Date(value) : new Date(`1970-01-01T${value}`);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};
</script>

<style scoped>
.section-item-parent {
    font-weight: 600;
}

.section-item-child {
    padding-left: 16px;
    display: inline-block;
    color: #666;
}

.section-block {
    padding: 16px;
}

.section-table {
    width: 100%;
    font-size: 14px;
    line-height: 1.4;
}

.section-table table {
    width: 100%;
}

.section-table thead th {
    font-size: 12px;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    font-weight: 600;
    color: #555;
}

.section-table tbody td {
    font-size: 14px;
    color: #222;
    padding-top: 10px;
    padding-bottom: 10px;
}

.section-table tbody tr:not(:last-child) td {
    border-bottom: 1px solid #eee;
}

.totals-table {
    border-collapse: collapse;
    min-width: 240px;
}

.totals-table td {
    padding: 8px 0;
    font-size: 14px;
}

.totals-label {
    color: #555;
    padding-right: 24px;
}

.totals-value {
    text-align: right;
    font-weight: 600;
    color: #222;
}

.totals-row td {
    border-top: 1px solid #eee;
    padding-top: 10px;
}

.totals-card {
    padding: 12px 16px;
}

.summary-panel {
    margin-bottom: 16px;
    padding: 12px 0 4px;
}

.summary-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 12px;
}

.section-header-top {
    display: flex;
    align-items: center;
    gap: 12px;
}

.section-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
}

.section-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.meta-pill {
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    min-width: 170px;
    padding: 10px 12px;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #e5e7eb;
    font-size: 12px;
}

.meta-label {
    color: #6b7280;
    font-weight: 600;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.meta-value {
    color: #111827;
    font-weight: 600;
}

.status-chip {
    font-size: 12px;
    font-weight: 600;
    text-transform: capitalize;
}

.status-confirmed {
    color: #0f766e;
    background: #ecfdf3;
}

.status-pending {
    color: #b45309;
    background: #fff7ed;
}

.status-cancelled {
    color: #b91c1c;
    background: #fef2f2;
}

.status-completed {
    color: #1d4ed8;
    background: #eff6ff;
}

.status-default {
    color: #4b5563;
    background: #f3f4f6;
}

@media (max-width: 640px) {
    .summary-toolbar {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>
