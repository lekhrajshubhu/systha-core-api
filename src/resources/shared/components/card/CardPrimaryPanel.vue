<template>
    <v-col cols="12" md="4">
        <v-card class="pa-5 h-100 panel-card" elevation="0">
            <div class="d-flex align-center justify-space-between mb-4">
                <h3 class="mb-0">Primary Card</h3>
                <v-chip v-if="defaultCard" size="small" color="success" variant="tonal">Default</v-chip>
            </div>
            <v-divider></v-divider>
            <div  v-if="defaultCard" class="d-flex align-center justify-center py-6">
                <CardPreview
                   
                    :card="defaultCard"
                    :card-brand="cardBrand"
                    :masked-card="maskedCard"
                    :card-expiry="cardExpiry"
                    :card-holder="cardHolder"
                    width="350px"
                />
            </div>
            <div v-else-if="loadingCards" class="text-medium-emphasis py-6">Loading default card...</div>
            <div v-else class="text-medium-emphasis py-6">No default card found.</div>

             <v-divider></v-divider>

            <div v-if="defaultCard" class="holder-info pt-4">
                <p class="holder-title mb-3">Card Holder Information</p>
                <div class="holder-row">
                    <span class="holder-label">Expiry</span>
                    <div class="holder-value-wrap">
                        <span class="holder-text">{{ cardExpiry(defaultCard) }}</span>
                        <v-btn icon variant="text" size="x-small" title="Copy Expiry" @click="copyToClipboard(cardExpiry(defaultCard))">
                            <v-icon size="16">mdi-content-copy</v-icon>
                        </v-btn>
                    </div>
                </div>
                <div class="holder-row">
                    <span class="holder-label">Card Number</span>
                    <div class="holder-value-wrap">
                        <span class="holder-text">{{ maskedCard(defaultCard) }}</span>
                        <v-btn icon variant="text" size="x-small" title="Copy Card Number" @click="copyToClipboard(defaultCard?.card_last4 || '')">
                            <v-icon size="16">mdi-content-copy</v-icon>
                        </v-btn>
                    </div>
                </div>
                <div class="holder-row">
                    <span class="holder-label">Name on Card</span>
                    <div class="holder-value-wrap">
                        <span class="holder-text">{{ cardHolder(defaultCard) }}</span>
                        <v-btn icon variant="text" size="x-small" title="Copy Holder Name" @click="copyToClipboard(cardHolder(defaultCard))">
                            <v-icon size="16">mdi-content-copy</v-icon>
                        </v-btn>
                    </div>
                </div>
                <div class="holder-row">
                    <span class="holder-label">Card Type</span>
                    <div class="holder-value-wrap">
                        <span class="holder-text">{{ cardBrand(defaultCard) }}</span>
                        <v-btn icon variant="text" size="x-small" title="Copy Card Type" @click="copyToClipboard(cardBrand(defaultCard))">
                            <v-icon size="16">mdi-content-copy</v-icon>
                        </v-btn>
                    </div>
                </div>
            </div>
        </v-card>
    </v-col>
</template>

<script setup>
import CardPreview from './CardPreview.vue';

defineProps({
    defaultCard: {
        type: Object,
        default: null,
    },
    loadingCards: {
        type: Boolean,
        default: false,
    },
    cardBrand: {
        type: Function,
        required: true,
    },
    maskedCard: {
        type: Function,
        required: true,
    },
    cardExpiry: {
        type: Function,
        required: true,
    },
    cardHolder: {
        type: Function,
        required: true,
    },
    copyToClipboard: {
        type: Function,
        required: true,
    },
});
</script>

<style scoped>
.panel-card {
    min-height: calc(100vh - 100px);
    max-height: calc(100vh - 100px);
    overflow: auto;
}

.d-flex {
    display: flex;
}

.holder-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.holder-title {
    font-weight: 600;
    color: #0f172a;
}

.holder-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.holder-label {
    color: #64748b;
    font-size: 0.85rem;
}

.holder-text {
    color: #111827;
    font-weight: 500;
    text-align: right;
}

.holder-value-wrap {
    align-items: center;
    display: inline-flex;
    gap: 2px;
}
</style>
