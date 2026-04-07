<template>
    <form @submit.prevent="handleSubmit">
        <v-row>
            <v-col cols="12">
                <div>
                    <label>Card Number</label>
                    <div id="card-number" class="StripeElement"></div>
                </div>
            </v-col>
            <v-col cols="6">
                <div>
                    <label>Expiry Date</label>
                    <div id="card-expiry" class="StripeElement"></div>
                </div>
            </v-col>
            <v-col cols="6">
                <div>
                    <label>CVC</label>
                    <div id="card-cvc" class="StripeElement"></div>
                </div>
            </v-col>
            <v-col cols="12">
                <div class="text-center">
                    <p v-if="errorMessage" style="color:red;" class="mb-4">{{ errorMessage }}</p>
                    <v-btn type="submit" color="primary" rounded size="large" :disabled="loading">
                        <v-icon left>
                            {{ loading ? '' : 'mdi-credit-card' }}
                        </v-icon>
                        {{ loading ? 'Processing...' : 'Pay Now' }}
                    </v-btn>

                </div>
            </v-col>
        </v-row>
    </form>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { loadStripe } from '@stripe/stripe-js'

const loading = ref(false)
const errorMessage = ref(null)

let stripe = null
let elements = null
let cardNumber = null
let cardExpiry = null
let cardCvc = null

onMounted(async () => {
    stripe = await loadStripe('pk_test_51NcU65FCkDOH9dhP0FoxxZFNkp8i7VOf5468fmuBqCwt9r4nPtQY8SIu1qIDKwaI6gVReyAhYo0OQqGjo7bCGa1h0048eTMvfH')
    elements = stripe.elements()

    const style = {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#a0aec0'
            }
        },
        invalid: {
            color: '#fa755a'
        }
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
    } else {
        // Use the token.id to send to your server for payment processing
        console.log('Received Stripe Token:', token)
        // You can emit event or call API here
    }

    loading.value = false
}
</script>

<style scoped>
label {
    font-size: 0.9rem;
}

.StripeElement {
    border: 1px solid #ccc;
    padding: 10px 12px;
    border-radius: 4px;
    margin-bottom: 12px;
    font-family: 'Poppins', sans-serif;
}
</style>