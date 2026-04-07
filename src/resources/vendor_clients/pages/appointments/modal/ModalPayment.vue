<template>
    <div>
        <div id="card-element"></div>
        <button :disabled="loading" @click="handleSubmit">
            {{ loading ? 'Processing...' : 'Pay Now' }}
        </button>
        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { loadStripe } from '@stripe/stripe-js'

const props = defineProps({
    appointmentId: {
        type: Number,
        required: true,
    },
    publishableKey: {
        type: String,
        required: true,
    },
    customerEmail: {
        type: String,
        required: true,
    },
})

const loading = ref(false)
const errorMessage = ref(null)

let stripe = null
let cardElement = null
let elements = null

onMounted(async () => {
    stripe = await loadStripe(props.publishableKey)
    elements = stripe.elements()

    const style = {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': { color: '#a0aec0' },
        },
        invalid: { color: '#fa755a' },
    }

    cardElement = elements.create('card', { style })
    cardElement.mount('#card-element')
})

onBeforeUnmount(() => {
    cardElement?.destroy()
})

async function handleSubmit() {
    errorMessage.value = null
    loading.value = true

    try {
        // 1. Request payment intent client secret from your backend
        const res = await fetch(`/api/appointments/${props.appointmentId}/payment-intent`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ customer_email: props.customerEmail }),
        })

        if (!res.ok) {
            throw new Error('Failed to create payment intent')
        }

        const data = await res.json()
        const clientSecret = data.client_secret

        // 2. Confirm card payment on Stripe side
        const { paymentIntent, error } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: { email: props.customerEmail },
            },
        })

        if (error) {
            errorMessage.value = error.message
        } else if (paymentIntent.status === 'succeeded') {
            alert('Payment successful!')
            // You can emit event or redirect here
        }
    } catch (error) {
        errorMessage.value = error.message || 'Payment failed.'
    }

    loading.value = false
}
</script>

<style>
#error {
    color: red;
    margin-top: 10px;
}
</style>
