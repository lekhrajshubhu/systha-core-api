<template>
    <v-container class="py-4" fluid>
        <v-snackbar v-model="showSuccess" color="success" timeout="2500">
            {{ successMessage }}
        </v-snackbar>

        <v-row>
            <card-primary-panel
                :default-card="defaultCard"
                :loading-cards="loadingCards"
                :card-brand="cardBrand"
                :masked-card="maskedCard"
                :card-expiry="cardExpiry"
                :card-holder="cardHolder"
                :copy-to-clipboard="copyToClipboard"
            />
            <card-list-panel
                :filtered-cards="filteredCards"
                :loading-cards="loadingCards"
                :card-brand="cardBrand"
                :masked-card="maskedCard"
                :card-expiry="cardExpiry"
                :card-holder="cardHolder"
                :copy-to-clipboard="copyToClipboard"
                :search-query="searchQuery"
                @update:searchQuery="searchQuery = $event"
                @edit-card="editCard"
                @delete-card="deleteCard"
            />
        </v-row>
    </v-container>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref } from 'vue';
import { useGlobalStore } from '@/global_clients/stores/account';
import { storeToRefs } from 'pinia';
import CardPrimaryPanel from '@shared/components/card/CardPrimaryPanel.vue';
import CardListPanel from '@shared/components/card/CardListPanel.vue';

const accountStore = useGlobalStore();
const { profile } = storeToRefs(accountStore);
const { proxy } = getCurrentInstance();
const cards = ref([]);
const defaultCard = ref(null);
const loadingCards = ref(false);
const searchQuery = ref('');
const showSuccess = ref(false);
const successMessage = ref('');

onMounted(async () => {
    if (!profile.value) {
        await accountStore.fetchProfile();
    }
    await fetchCards();
});

const copyToClipboard = async (text) => {
    if (!text) return;
    try {
        await navigator.clipboard.writeText(String(text));
    } catch (error) {
        console.error('Failed to copy', error);
    }
};

const cardBrand = (card) => {
    const brand = card?.card_brand || card?.brand || 'Card';
    return brand.toString().replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
};

const maskedCard = (card) => `•••• •••• •••• ${card?.card_last4 || '----'}`;

const cardExpiry = (card) => {
    const month = String(card?.exp_month || '').padStart(2, '0');
    const year = String(card?.exp_year || '');
    if (!month.trim() || !year.trim()) return 'N/A';
    return `${month}/${year.slice(-2)}`;
};

const cardHolder = (card) => card?.card_name || profile.value?.name || 'N/A';

const filteredCards = computed(() => {
    const query = (searchQuery.value || '').trim().toLowerCase();
    if (!query) return cards.value;

    return cards.value.filter((card) => {
        const brand = cardBrand(card).toLowerCase();
        const holder = cardHolder(card).toLowerCase();
        const last4 = String(card?.card_last4 || '');
        const expiry = cardExpiry(card).toLowerCase();
        return brand.includes(query) || holder.includes(query) || last4.includes(query) || expiry.includes(query);
    });
});

const parsePaymentMethodResponse = (response) => {
    const dataNode = response?.data?.data || response?.data || {};
    const methods = dataNode?.payment_methods || response?.data?.payment_methods || [];
    const defaultMethod = dataNode?.default_payment_method || response?.data?.default_payment_method || methods[0] || null;
    return { methods, defaultMethod };
};

const fetchCards = async () => {
    loadingCards.value = true;
    try {
        const response = await proxy.$axios.get('/payment-methods');
        const parsed = parsePaymentMethodResponse(response);
        cards.value = parsed.methods;
        defaultCard.value = parsed.defaultMethod;
    } catch (error) {
        cards.value = [];
        defaultCard.value = null;
        console.error('Failed to fetch payment methods', error);
    } finally {
        loadingCards.value = false;
    }
};

const deleteCard = async (card) => {
    if (!card?.id) return;

    try {
        await proxy.$axios.post(`/payment-methods/${card.id}/delete`);
        successMessage.value = 'Card deleted successfully.';
        showSuccess.value = true;
        await fetchCards();
    } catch (error) {
        console.error('Failed to delete payment method', error);
    }
};

const editCard = async (payload) => {
    if (!payload?.id) return;

    try {
        await proxy.$axios.post(`/payment-methods/${payload.id}/update`, {
            is_default: payload?.is_default,
            is_active: payload?.is_active,
        });
        successMessage.value = 'Card updated successfully.';
        showSuccess.value = true;
        await fetchCards();
    } catch (error) {
        console.error('Failed to update payment method', error);
        throw error;
    }
};
</script>
