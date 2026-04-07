<template>

    <v-card-text class="pt-0">
        <div v-if="cards.length">
            <v-tabs v-model="selectedTab" density="comfortable" color="primary" class="mb-3 sticky-tabs">
                <v-tab value="active">Active ({{ activeCards.length }})</v-tab>
                <v-tab value="expired">Expired ({{ expiredCards.length }})</v-tab>
                <v-tab value="new">New Card</v-tab>
            </v-tabs>
            <v-row>
                <v-col cols="12">
                    <div class="mb-3">
                        <div v-if="selectedTab === 'new'" class="text-center pt-6">
                            <CardNewForm :publishable-key="publishableKey"/>
                        </div>
                        <div
                            v-else
                            v-for="(card, index) in filteredCards"
                            :key="index"
                            class="w-100 mb-2 rounded card-item"
                            :class="{ 'card-item--selected': isSelectedCard(card) }" @click="selectCard(card)">
                            <div class="card-item-content">
                                <div class="card-meta-row">
                                    <span class="card-meta text-truncate">{{ brandDisplayName(card.card_brand) }}</span>
                                    <span class="card-meta card-last4 text-truncate">{{ maskedCard(card) }}</span>
                                    <span class="card-meta-divider">•</span>
                                    <span class="card-meta text-truncate">{{ cardExpiry(card) }}</span>
                                    <v-chip v-if="Number(card?.is_default) === 1" size="x-small" color="success"
                                        variant="tonal" class="default-card-label">
                                        Current default card
                                    </v-chip>
                                   
                                </div>

                               
                                <span class="card-holder-primary text-truncate">{{ cardHolder(card) }}</span>
                            </div>
                        </div>
                        <div v-if="selectedTab !== 'new' && !filteredCards.length" class="text-medium-emphasis text-center py-4">
                            No {{ selectedTab }} cards found.
                        </div>
                    </div>
                </v-col>
            </v-row>
        </div>

    </v-card-text>
    <v-divider v-if="selectedTab !== 'new'"></v-divider>
    <v-card-actions v-if="selectedTab !== 'new'">
        <div class="w-100 text-center">
            <v-btn
                color="primary"
                variant="outlined"
                :disabled="selectedTab === 'new' || !selectedCard || Number(selectedCard?.is_default) === 1 || isCardExpired(selectedCard)"
                @click="handleChangeCard"
            >
                <v-icon>mdi-credit-card-check</v-icon>
                Use this card
            </v-btn>
        </div>
    </v-card-actions>

</template>

<script setup>
import { computed, ref, watch } from 'vue';
import $axios from '@shared/axios.config'
import { useGlobalModalStore } from '@shared/stores/globalModal'
import CardNewForm from './CardNewForm.vue'

const props = defineProps({
    card_list: {
        type: Array,
        default: () => [],
    },
    onUpdated: {
        type: Function,
        default: null,
    },
    onSuccess: {
        type: Function,
        default: null,
    },
    onAddCard: {
        type: Function,
        default: null,
    },
    publishableKey: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['onClose']);
const globalModal = useGlobalModalStore();

const loading = ref(false);
const selectedCard = ref(null);
const cards = ref([]);
const selectedTab = ref('active');

const activeCards = computed(() =>
    cards.value.filter((card) => !isCardExpired(card) && Number(card?.is_active ?? 1) === 1)
);

const expiredCards = computed(() =>
    cards.value.filter((card) => isCardExpired(card) || Number(card?.is_active ?? 1) !== 1)
);

const filteredCards = computed(() => (selectedTab.value === 'expired' ? expiredCards.value : activeCards.value));

watch(
    () => props.card_list,
    (list) => {
        cards.value = Array.isArray(list) ? [...list] : [];
        setDefaultSelection();
    },
    { immediate: true }
);

watch(selectedTab, () => {
    if (selectedTab.value === 'new') {
        selectedCard.value = null;
        return;
    }
    if (!selectedCard.value) return;
    const existsInTab = filteredCards.value.some((card) => Number(card?.id) === Number(selectedCard.value?.id));
    if (!existsInTab) {
        selectedCard.value = filteredCards.value[0] || null;
    }
});

function brandDisplayName(brand) {
    const map = {
        visa: 'Visa',
        mastercard: 'MasterCard',
        'american-express': 'American Express',
        discover: 'Discover',
        jcb: 'JCB',
        unionpay: 'UnionPay',
        diners: 'Diners Club',
        unknown: 'Unknown',
    };
    return map[brand?.toLowerCase()] || 'Card';
}

function maskedCard(card) {
    return `•••• ${card?.card_last4 || '----'}`;
}

function cardExpiry(card) {
    const month = String(card?.exp_month || '').padStart(2, '0');
    const year = String(card?.exp_year || '').slice(-2);
    if (!month || !year) return '--/--';
    return `${month}/${year}`;
}

function cardHolder(card) {
    return card?.card_name || 'Card Holder';
}

function isCardExpired(card) {
    const month = Number(card?.exp_month);
    const year = Number(card?.exp_year);
    if (!month || !year) return false;
    const endOfExpiryMonth = new Date(year, month, 0, 23, 59, 59, 999);
    return endOfExpiryMonth < new Date();
}

function selectCard(card) {
    selectedCard.value = card;
}

function isSelectedCard(card) {
    return Number(selectedCard.value?.id) === Number(card?.id);
}

function setDefaultSelection() {
    selectedCard.value = cards.value.find((item) => Number(item?.is_default) === 1) || cards.value[0] || null;
    if (!selectedCard.value) {
        selectedTab.value = 'active';
        return;
    }
    selectedTab.value = isCardExpired(selectedCard.value) || Number(selectedCard.value?.is_active ?? 1) !== 1
        ? 'expired'
        : 'active';
}

async function handleAddNewCard() {
    if (typeof props.onAddCard === 'function') {
        await props.onAddCard();
    }
}

function closeDialog(payload = null) {
    emit('onClose', payload);
    globalModal.close();
}

async function handleChangeCard() {
    if (!selectedCard.value?.id) {
        return;
    }

    try {
        loading.value = true;
        const response = await $axios.post(`/payment-methods/${selectedCard.value.id}/make-default`);
        if (typeof props.onUpdated === 'function') {
            await props.onUpdated();
        }
        if (typeof props.onSuccess === 'function') {
            await props.onSuccess({ response });
            return;
        }
        closeDialog({ response });
    } catch (error) {
        console.log({ error });
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped lang="scss">
.sticky-tabs {
    background: #fff;
    position: sticky;
    top: 0;
    z-index: 2;
    border-bottom: 1px solid #dadada;
}

.card-item-content {
    align-items: center;
    column-gap: 10px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    width: 100%;
}

.card-item {
    cursor: pointer;
    padding: 8px 10px;
    background: linear-gradient(0deg, rgb(255, 255, 255) 0%, rgb(242, 247, 251) 100%);
    border: 1px solid #f1f1f1;
    cursor: pointer;
}

.card-item--selected {
    border-color: rgb(var(--v-theme-primary)) !important;
    box-shadow: inset 0 0 0 1px rgba(var(--v-theme-primary), 0.25);
    .card-item-content{
        color: rgb(var(--v-theme-primary)) !important;
    }
}

.card-holder-primary {
    color: #374151;
    font-size: 0.78rem;
    letter-spacing: 0.01em;
    line-height: 1.1rem;
    text-align: left;
}

.card-meta-row {
    align-items: center;
    display: grid;
    grid-auto-flow: column;
    grid-auto-columns: max-content;
    column-gap: 8px;
    justify-content: start;
    overflow: hidden;
    width: 100%;
}

.card-meta {
    color: #6b7280;
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.01em;
    line-height: 1.1rem;
}

.card-meta-divider {
    color: #9ca3af;
    font-size: 0.72rem;
    line-height: 1rem;
}

.default-card-label {
    color: #065f46;
    font-size: 0.72rem;
    font-weight: 600;
    justify-self: end;
    margin-left: auto;
    white-space: nowrap;
}

.card-last4 {
    text-align: left;
}
</style>
