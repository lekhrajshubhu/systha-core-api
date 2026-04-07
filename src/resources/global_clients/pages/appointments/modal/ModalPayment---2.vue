<template>
    <v-card elevation="0">
        <div class="d-flex align-items-center justify-space-between">
            <v-card-title>Payment</v-card-title>
            <div class="pa-2">
                <v-btn size="small" @click="$emit('onClose')" variant="text" icon="mdi-close"></v-btn>
            </div>
        </div>
        <v-divider></v-divider>

        <v-card-text>
            <div class="mb-4 text-success text-center">
                <h1>{{ formatAmount(appointment?.total_amount) }}</h1>
            </div>
            <form @submit.prevent="handleSubmit">
                <v-row>
                    <v-col cols="12">
                        <label>Card Number</label>
                        <div id="card-number" class="StripeElement"></div>
                    </v-col>
                    <v-col cols="6">
                        <label>Expiry Date</label>
                        <div id="card-expiry" class="StripeElement"></div>
                    </v-col>
                    <v-col cols="6">
                        <label>CVC</label>
                        <div id="card-cvc" class="StripeElement"></div>
                    </v-col>
                    <v-col cols="12" class="text-center">
                        <p v-if="errorMessage" class="error-text">{{ errorMessage }}</p>
                        <v-btn type="submit" color="primary" rounded size="large" :disabled="loading">
                            <v-icon left class="mr-2">
                                {{ loading ? '' : 'mdi-check-circle' }}
                            </v-icon>
                            {{ loading ? 'Processing...' : 'Proceed' }}
                        </v-btn>
                    </v-col>
                </v-row>
            </form>
        </v-card-text>
    </v-card>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import { formatAmount } from '@utils/helpers'
import $axios from '@shared/axios.config'
// Define props
const props = defineProps({
    publishableKey: {
        type: String,
        required: true,
    },
    appointment: {
        type: Object,
        required: true,
    }
})

const emit = defineEmits(['onSuccess', 'onError'])

const loading = ref(false)
const errorMessage = ref(null)

let stripe = null
let elements = null
let cardNumber = null
let cardExpiry = null
let cardCvc = null

onMounted(async () => {
    stripe = await loadStripe(props.publishableKey)
    if (!stripe) {
        emit('onError', new Error('Stripe initialization failed.'))
        return
    }

    elements = stripe.elements()

    const style = {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#a0aec0',
            },
        },
        invalid: {
            color: '#fa755a',
        },
    }

    cardNumber = elements.create('cardNumber', { style })
    cardNumber.mount('#card-number')

    cardExpiry = elements.create('cardExpiry', { style })
    cardExpiry.mount('#card-expiry')

    cardCvc = elements.create('cardCvc', { style })
    cardCvc.mount('#card-cvc')
})

onBeforeUnmount(() => {
    cardNumber?.destroy()
    cardExpiry?.destroy()
    cardCvc?.destroy()
})

async function handleSubmit() {
    loading.value = true
    errorMessage.value = null

    const { token, error } = await stripe.createToken(cardNumber)

    if (error) {
        errorMessage.value = error.message
        emit('onError', error)
        loading.value = false
        return
    }

    try {
        const payment = {
            stripeToken: token.id,
            stripe_card_id: token.card.id,
            last4: token.card.last4,
            name: token.card.name || '',
            brand: token.card.brand,
            cr_exp_month: token.card.exp_month,
            cr_exp_year: token.card.exp_year,
            country: token.card.country,
            payment_type: token.card.brand,
            payment_due: props.appointment.total_amount,
            appointment_id: props.appointment.id,
        }

        console.log({ payment })

        const resp = await $axios.post(`/appointments/${props.appointment.id}/payment`, payment)
        // emit('onSuccess', resp.data)
        console.log({resp});

    } catch (err) {
        console.error(err)
        emit('onError', err.response?.data?.message || 'Payment failed.')
    }

    loading.value = false
}
</script>


<style scoped>
label {
    font-size: 0.9rem;
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}

.StripeElement {
    border: 1px solid #ccc;
    padding: 10px 12px;
    border-radius: 4px;
    margin-bottom: 12px;
    font-family: 'Poppins', sans-serif;
}

.error-text {
    color: red;
    margin-bottom: 12px;
}
</style>
