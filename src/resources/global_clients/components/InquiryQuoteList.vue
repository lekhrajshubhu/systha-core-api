<template>
    <v-card class="elevation-0 pa-4">
        <div class="section-title-bar">
            <p class="section-title">Quotation List</p>
            <p class="section-subtitle">Related quotes</p>
        </div>
        <v-table density="comfortable" class="quote-table">
            <thead>
                <tr>
                    <th class="text-left">Quote #</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Date</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="quote in quotes" :key="quote.id">
                    <td class="quote-id">{{ quote.code }}</td>
                    <td>
                        <v-chip size="x-small" variant="flat" class="status-chip" :class="quote.status">
                            {{ quote.status }}
                        </v-chip>
                    </td>
                    <td class="quote-meta">{{ quote.meta }}</td>
                    <td class="text-right">
                        <v-btn size="small" variant="text" color="primary" icon 
                        :to="{ name: 'globalQuotationDetailPage', params: { id: quote.id } }"
                        >
                            <v-icon>mdi-eye</v-icon>
                        </v-btn>
                    </td>
                </tr>
                <tr v-if="quotes.length === 0">
                    <td colspan="4" class="text-center text-grey">No quotations</td>
                </tr>
            </tbody>
        </v-table>
    </v-card>
</template>

<script setup>
import { useGlobalModalStore } from '@shared/stores/globalModal';
import QuoteDetailPanel from '@/global_clients/components/QuoteDetailPanel.vue';

defineProps({
    quotes: { type: Array, default: () => [] },
});

// const globalModal = useGlobalModalStore();

// function openQuote(quote) {
//     globalModal.open(QuoteDetailPanel, { quote }, { title: 'Quotation Detail', width: 'lg' });
// }
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

.quote-id {
    /* font-size: 1rem; */
    /* font-weight: 600; */
    color: #111827;
    margin: 0;
}

.quote-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}

.quote-table {
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.quote-table thead th {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
}

.status-chip {
    background: #f3f4f6;
    color: #374151;
    font-weight: 600;
    text-transform: capitalize;
}

.status-chip.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-chip.confirmed {
    background: #dcfce7;
    color: #166534;
}
</style>
