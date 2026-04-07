<template>

    <v-card-text class="px-6 py-10 position-relative">
        <div v-if="selectedCard">
            <div class="d-flex align-center justify-space-around">
                <CardPreview :card="selectedCard" :card-brand="cardBrand" :masked-card="maskedCard"
                    :card-expiry="cardExpiry" :card-holder="cardHolder" width="360px" />
            </div>
            <div v-if="isSelectedCardExpired" class="expired-hint mt-3">
                <v-icon size="18" color="warning" class="mr-2">mdi-alert-circle-outline</v-icon>
                <p class="mb-0 expired-hint-text">
                    Selected card is expired. Please
                    <span class="action-highlight action-link" role="button" tabindex="0" @click="changeCard"
                        @keydown.enter="changeCard">
                        change card
                    </span>
                    or add a new card before proceeding.
                </p>
            </div>
            <div class="action-hint mt-3" v-else>
                <v-icon size="18" class="mr-2">mdi-information-outline</v-icon>
                <p class="mb-0 action-hint-text">
                    <span class="count-highlight">{{ cards.length }}</span>
                    <span>{{ cards.length === 1 ? ' saved card available.' : ' saved cards available.' }}</span>
                    <span class="action-highlight action-link" role="button" tabindex="0" @click="changeCard"
                        @keydown.enter="changeCard">
                        Change card
                    </span>
                    <span> if needed before proceeding.</span>
                </p>
            </div>

        </div>
        <div v-else class="text-center py-6 text-medium-emphasis">No saved card found.</div>
    </v-card-text>
    <v-divider></v-divider>
    <v-card-actions>
        <div class="w-100 text-center py-2">
            <v-btn
                color="primary"
                variant="outlined"
                :loading="submitting"
                :disabled="!selectedCard || submitting || isSelectedCardExpired"
                @click="proceedWithCard"
            >
                <span class="btn-amount-text text-success">{{ formattedAmountToPay }} - </span>
                <span class="btn-action-text text-primary">Pay Now</span>
            </v-btn>
        </div>
    </v-card-actions>

</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import $axios from '@shared/axios.config'
import CardPreview from '@shared/components/card/CardPreview.vue'

const props = defineProps({
    appointmentId: { type: Number, default: null },
    publishableKey: { type: String, required: true },
    customerEmail: { type: String, default: '' },
    amountToPay: { type: [Number, String], default: null },
    onProceed: { type: Function, default: null },
    onChangeCard: { type: Function, default: null },
})

const emit = defineEmits(['selectedCard'])

const cards = ref([])
const selectedCard = ref(null)
const submitting = ref(false)

const formattedAmountToPay = computed(() => {
    const amount = Number(props.amountToPay)
    if (Number.isNaN(amount)) return '—'
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(amount)
})

const isSelectedCardExpired = computed(() => {
    if (!selectedCard.value) return false
    const month = Number(selectedCard.value?.exp_month)
    const year = Number(selectedCard.value?.exp_year)
    if (!month || !year) return false
    const endOfExpiryMonth = new Date(year, month, 0, 23, 59, 59, 999)
    return endOfExpiryMonth < new Date()
})

function resolveDefaultCard(list = [], apiDefault = null) {
    if (apiDefault) return apiDefault
    return list.find((card) => card?.is_default == true) || null
}

function cardBrand(card) {
    const map = {
        visa: 'Visa',
        mastercard: 'MasterCard',
        'american-express': 'American Express',
        discover: 'Discover',
        jcb: 'JCB',
        unionpay: 'UnionPay',
        diners: 'Diners Club',
        unknown: 'Unknown',
    }
    return map[card?.card_brand?.toLowerCase()] || 'Card'
}

function maskedCard(card) {
    return `•••• •••• •••• ${card?.card_last4 || '----'}`
}

function cardExpiry(card) {
    const month = String(card?.exp_month || '').padStart(2, '0')
    const year = String(card?.exp_year || '').slice(-2)
    if (!month || !year) return '--/--'
    return `${month}/${year}`
}

function cardHolder(card) {
    return card?.card_name || 'Card Holder'
}

async function fetchCards() {
    try {
        const resp = await $axios.get('/payment-methods')
        cards.value = resp?.data?.payment_methods || []

        selectedCard.value = resolveDefaultCard(
            cards.value,
            resp?.data?.default_payment_method
        )
        console.log('Selected card:', selectedCard.value)
        emit('selectedCard', selectedCard.value)
    } catch (err) {
        cards.value = []
        selectedCard.value = null
        console.error('Error fetching cards:', err)
    }
}

async function proceedWithCard() {
    if (!selectedCard.value) return
    submitting.value = true
    if (typeof props.onProceed === 'function') {
        const isSuccess = await props.onProceed(selectedCard.value)
        if (isSuccess) {
            submitting.value = false
        }
    }
}

async function changeCard() {
    if (typeof props.onChangeCard === 'function') {
        await props.onChangeCard(cards.value, selectedCard.value)
    }
}

watch(selectedCard, (val) => {
    emit('selectedCard', val)
})

onMounted(() => {
    fetchCards()
})
</script>

<style scoped>
.action-hint {
    align-items: center;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    color: #1e3a8a;
    display: flex;
    justify-content: center;
    padding: 10px 12px;
}

.action-hint-text {
    font-size: 0.88rem;
    line-height: 1.2rem;
}

.count-highlight {
    color: #1d4ed8;
    font-weight: 700;
}

.action-highlight {
    color: #b45309;
    font-weight: 700;
}

.action-link {
    cursor: pointer;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.expired-hint {
    align-items: center;
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 10px;
    color: #92400e;
    display: flex;
    justify-content: center;
    padding: 10px 12px;
}

.expired-hint-text {
    font-size: 0.88rem;
    line-height: 1.2rem;
}

.amount-summary {
    /* background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); */
    /* border: 1px solid #e2e8f0; */
    /* border-radius: 12px; */
    margin: 0 auto;
    max-width: 280px;
    padding: 12px 14px;
    text-align: center;
}

.amount-label {
    color: #64748b;
    font-size: 0.76rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.amount-value {
    color: #0b172a;
    font-size: 1.3rem;
    font-weight: 800;
    line-height: 1.35rem;
}

.payment-actions-row {
    align-items: center;
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.amount-inline {
    text-align: left;
}

.amount-inline-label {
    color: #64748b;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.amount-inline-value {
    color: #0f172a;
    font-size: 1.05rem;
    font-weight: 800;
    line-height: 1.2rem;
}

.btn-amount-text {
    font-weight: 800;
    margin-right: 6px;
}

.btn-action-text {
    font-weight: 600;
}
</style>
