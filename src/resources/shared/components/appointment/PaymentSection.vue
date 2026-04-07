<template>
    <div>
        <div class="section-header mt-8">
            <div>
                <p class="section-title">Payments</p>
                <p class="section-subtitle">Transaction summary</p>
            </div>
        </div>
        <v-divider class="my-2"></v-divider>
        <div class="payment-left">
            <div class="payment-row">
                <p class="summary-label">Subtotal</p>
                <p class="summary-value">{{ formatCurrency(appointment?.total_info?.sub_total) }}</p>
            </div>
            <div class="payment-row">
                <p class="summary-label">Tax</p>
                <p class="summary-value">{{ formatCurrency(appointment?.total_info?.tax_amount) }}</p>
            </div>
            <div class="payment-row">
                <p class="summary-label">Total</p>
                <p class="total-amount">{{ formatCurrency(appointment?.total_info?.total_amount) }}</p>
            </div>
            <v-divider></v-divider>
            <div v-if="appointment?.is_paid" class="payment-row">
                <p class="summary-label">Payment By</p>
                <div class="payment-method">
                    <p class="summary-value">{{ paymentMethodLabel }}</p>
                    <p class="summary-meta">{{ paymentDateLabel }}</p>
                </div>
            </div>
            <div v-else class="payment-row payment-status-row">
                <v-chip size="small" variant="flat" class="unpaid-chip rounded">Unpaid</v-chip>
                <v-btn color="primary" variant="outlined" @click="openPaymentModal">
                    <v-icon start size="18">mdi-credit-card-outline</v-icon>
                    Make Payment
                </v-btn>
            </div>
        </div>
    </div>
</template>

<script setup>

import $axios from '@shared/axios.config'
import { computed, onMounted, ref } from 'vue';
import { useGlobalModalStore } from '@shared/stores/globalModal';
import { useGlobalStore } from '@/global_clients/stores/account';
import PaymentForm from '@shared/components/payment/PaymentForm.vue'
import CardChange from '@shared/components/payment/CardChange.vue'
import { loadStripe } from '@stripe/stripe-js'

const props = defineProps({
    appointment: {
        type: Object,
        default: null,
    },
});
const emit = defineEmits(['proceed']);

const globalModal = useGlobalModalStore();
const accountStore = useGlobalStore();
let stripe = null;
const loading = ref(false);
const profile = computed(() => accountStore.profile?.data ?? accountStore.profile ?? {});

const payment = computed(() => props.appointment?.payment || null);
const publishableKey = computed(() =>
    props.appointment?.vendor?.publishable_key ||
    'pk_test_51NcU65FCkDOH9dhP0FoxxZFNkp8i7VOf5468fmuBqCwt9r4nPtQY8SIu1qIDKwaI6gVReyAhYo0OQqGjo7bCGa1h0048eTMvfH'
);
const customerEmail = computed(() => props.appointment?.client?.email || '');
const amountToPay = computed(() => props.appointment?.total_info?.total_amount ?? null);

const paymentMethodLabel = computed(() => {
    if (!payment.value) return '—';
    const brand = payment.value.payment_type || 'Card';
    const last4 = payment.value.card_last4 ? `•••• ${payment.value.card_last4}` : '';
    return `${brand} ${last4}`.trim();
});

const paymentDateLabel = computed(() => {
    if (!payment.value?.created_at) return '—';
    return formatDateTime(payment.value.created_at);
});

async function initializeStripe() {
    stripe = await loadStripe(publishableKey.value)
}
onMounted(async () => {
    await initializeStripe()
    if (!accountStore.profile) {
        await accountStore.fetchProfile();
    }
})
function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function formatCurrency(value) {
    const amount = Number(value);
    if (Number.isNaN(amount)) return '—';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(amount);
}

function openPaymentModal() {
    const appointmentId = Number(props.appointment?.id);
    if (!appointmentId) return;

    openPaymentFormDialog(appointmentId);
}

function openPaymentFormDialog(appointmentId) {
    globalModal.open(
        PaymentForm,
        {
            appointmentId,
            publishableKey: publishableKey.value,
            customerEmail: customerEmail.value,
            amountToPay: amountToPay.value,
            onProceed: handleProceedWithCard,
            onChangeCard: handleChangeCard,
        },
        {
            title: 'Make Payment',
            width: 'sm',
        }
    );
}


function handleChangeCard(cards) {
    const appointmentId = Number(props.appointment?.id);
    if (!appointmentId) return;

    globalModal.open(
        CardChange,
        {
            card_list: Array.isArray(cards) ? cards : [],
            publishableKey: publishableKey.value,
            onSuccess: () => openPaymentFormDialog(appointmentId),
        },
        {
            title: 'Manage Cards',
            width: 'sm',
        }
    );
}


async function handleProceedWithCard(card) {
    if (!card) return false;


    if (card?.stripe_customer && card?.payment_method_id) {

        const response = await $axios.post(`/appointments/${props.appointment.id}/payment-intent`, {
            customer_email: profile.value?.email || customerEmail.value,
            customer_name: profile.value?.name || `${profile.value?.fname || ''} ${profile.value?.lname || ''}`.trim(),
            customer_phone: profile.value?.phone_no || '',
            stripe_customer_id: card?.stripe_customer,
            payment_method_id: card?.payment_method_id
        });

        const clientSecret = response?.data?.client_secret || response?.client_secret;

        return await confirmPayment(clientSecret, card?.payment_method_id);

    } else {
        console.log("error");
        return false;
    }

}
async function confirmPayment(clientSecret, paymentMethodId) {


    if (!clientSecret || !paymentMethodId) return false;

    loading.value = true;
    try {
        const resp = await stripe.confirmCardPayment(clientSecret, { payment_method: paymentMethodId });
        console.log({ resp });
        return await savePayment(resp.paymentIntent);

    } catch (error) {
        console.log(error);
        return false;
    }
}
async function savePayment(paymentIntent) {
    try {
        loading.value = true;
        const response = await $axios.post(`/appointments/${props.appointment.id}/store-card-payment`, {
            payment_intent_id: paymentIntent.id,
            appointment_id: props.appointment.id,
            payment_method_id: paymentIntent.payment_method,
            amount: paymentIntent.amount / 100,
        })
        globalModal.close();
        emit('proceed', response?.data || response);
        return true;
    } catch (error) {
        console.log({ error })
        return false;
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.payment-left {
    display: grid;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    background: transparent;
}

.payment-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    text-align: right;
}

.payment-method {
    text-align: right;
}

.summary-label {
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #6b7280;
    margin: 0 0 4px 0;
}

.summary-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    text-transform: uppercase;
    margin: 0;
}

.total-amount {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 4px 0 0 0;
}

.summary-meta {
    font-size: 12px;
    color: #6b7280;
    margin: 2px 0 0 0;
}

.payment-status-row {
    justify-content: space-between;
    align-items: center;
}

.unpaid-chip {
    background: #fee2e2;
    color: #991b1b;
    font-weight: 600;
}
</style>
