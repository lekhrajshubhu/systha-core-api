<template>
    <div>
        <div id="card-element"></div>

        <div class="pt-2">
            <p v-if="errorMessage" class="text-error text-center">{{ errorMessage }}</p>

            <div class="pt-4">
                <v-btn rounded block color="success" size="large" :disabled="loading" @click="handleSubmit">
                    {{ loading ? 'Processing...' : 'Pay Now' }}
                </v-btn>
            </div>
        </div>
    </div>
</template>

<script setup>
import $axios from '@shared/axios.config'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import { useVendorClientStore } from '@/vendor_clients/stores/account'
import { useGlobalStore } from '@/global_clients/stores/account'

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
let elements = null
let cardElement = null

const isAccounts = window.location.pathname.startsWith('/global-clients')
const userStore = isAccounts ? useGlobalStore() : useVendorClientStore()


onMounted(async () => {
    stripe = await loadStripe(props.publishableKey)
    elements = stripe.elements()

    if (!userStore.profile) {
        userStore.fetchProfile()
    }

    const style = {
        base: {
            color: '#32325d',
            fontSize: '16px',
            '::placeholder': {
                color: '#a0aec0',
            },
            // Stripe does not support border or padding inside style, handle those with CSS
        },
        invalid: {
            color: '#fa755a',
        },
    }

    cardElement = elements.create('card', { style })
    cardElement.mount('#card-element')
})
// ✅ Define profile as a computed getter
const profile = computed(() => userStore.profile);

onBeforeUnmount(() => {
    cardElement?.destroy()
})

async function handleSubmit() {
    errorMessage.value = null
    loading.value = true

    // try {
    //     // Request payment intent client secret from backend
    //     const response = await $axios.post(`/customers/appointments/${props.appointmentId}/payment-intent`, {
    //         customer_email: props.customerEmail
    //     })

    //     // const data = await res.json()
    //     const clientSecret = response.client_secret

    //     // Confirm card payment with Stripe
    //     const resp = await stripe.confirmCardPayment(
    //         clientSecret,
    //         {
    //             payment_method: {
    //                 card: cardElement,
    //                 billing_details: { email: props.customerEmail },
    //             },
    //         }
    //     )
    //     console.log({resp});

    //     // if (error) {
    //     //     errorMessage.value = error.message
    //     // } else if (paymentIntent.status === 'succeeded') {
    //     //     console.log('Payment successful!', { paymentIntent });
    //     //     await savePayment(paymentIntent);
    //     //     // Emit event or handle success as needed here
    //     // }
    // } catch (error) {
    //     console.error(error)
    //     errorMessage.value = error.message || 'Payment failed.'
    // }
    // try {
    //     // 1. Request payment intent client secret from backend
    //     const response = await $axios.post(`/customers/appointments/${props.appointmentId}/payment-intent`, {
    //         customer_email: props.customerEmail
    //     })

    //     const clientSecret = response.client_secret // assuming API returns { client_secret: '...' }

    //     // 2. Confirm card payment with Stripe
    //     const { paymentIntent, error } = await stripe.confirmCardPayment(
    //         clientSecret,
    //         {
    //             payment_method: {
    //                 card: cardElement,
    //                 billing_details: { email: props.customerEmail },
    //             },
    //         }
    //     )

    //     // 3. Handle error
    //     if (error) {
    //         errorMessage.value = error.message
    //         return
    //     }

    //     // 4. Handle success
    //     if (paymentIntent.status === 'succeeded') {
    //         const cardInfo = paymentIntent.charges.data[0].payment_method_details.card

    //         console.log('Payment successful!', {
    //             paymentIntentId: paymentIntent.id,
    //             last4: cardInfo.last4,
    //             brand: cardInfo.brand,
    //             exp_month: cardInfo.exp_month,
    //             exp_year: cardInfo.exp_year,
    //         })

    //         // Optional: Save to backend
    //         await $axios.post(`/customers/payments/store`, {
    //             appointment_id: props.appointmentId,
    //             payment_intent_id: paymentIntent.id,
    //             card_brand: cardInfo.brand,
    //             card_last4: cardInfo.last4,
    //             exp_month: cardInfo.exp_month,
    //             exp_year: cardInfo.exp_year,
    //             amount: paymentIntent.amount,
    //         })

    //         // success event/redirect
    //     }
    // } catch (error) {
    //     console.error(error)
    //     errorMessage.value = error.message || 'Payment failed.'
    // }

    try {
        // 1. Create payment method from card element
        const { paymentMethod, error: pmError } = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
            billing_details: {
                email: "lekhraj@systha.com"
            },
        });

        if (pmError) {
            loading.value = false
            errorMessage.value = pmError.message;
            return;
        }

        // 2. Request payment intent from backend with customer + payment method
        const response = await $axios.post(`/customers/appointments/${props.appointmentId}/payment-intent`, {
            customer_email: profile.value.email,
            customer_name: profile.value.fname,
            customer_phone: profile.value.phone_no,
            payment_method_id: paymentMethod.id,
        });

        const clientSecret = response.client_secret;

        // 3. Confirm card payment
        const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(
            clientSecret,
            {
                payment_method: paymentMethod.id,
            }
        );

        if (confirmError) {
            loading.value = false
            errorMessage.value = confirmError.message;
            return;
        }

        // 4. On success
        if (paymentIntent.status === 'succeeded') {

            console.log({paymentIntent});
            // const cardInfo = paymentIntent.charges.data[0].payment_method_details.card;

            // console.log('Payment successful!', {
            //     paymentIntentId: paymentIntent.id,
            //     last4: cardInfo.last4,
            //     brand: cardInfo.brand,
            //     exp_month: cardInfo.exp_month,
            //     exp_year: cardInfo.exp_year,
            // });

            await $axios.post(`/customers/appointments/${props.appointmentId}/store-card-payment`, {
                appointment_id: props.appointmentId,
                payment_intent_id: paymentIntent.id,
                payment_method_id: paymentIntent.payment_method,
                amount: paymentIntent.amount / 100,
            });

            // success event or redirect
        }

    } catch (error) {
        console.error(error);
        errorMessage.value = error.message || 'Payment failed.';
    }



    loading.value = false
}

async function confirmPayment(clientSecret) {
    if (!clientSecret) return;

    // 3. Confirm card payment
    const { paymentIntent, error: confirmError } = await stripe.confirmCardPayment(
        clientSecret,
        {
            payment_method: paymentMethod.id,
        }
    );

    if (confirmError) {
        errorMessage.value = confirmError.message;
        return;
    }

    // 4. On success
    if (paymentIntent.status === 'succeeded') {
        const cardInfo = paymentIntent.charges.data[0].payment_method_details.card;

        console.log('Payment successful!', {
            paymentIntentId: paymentIntent.id,
            last4: cardInfo.last4,
            brand: cardInfo.brand,
            exp_month: cardInfo.exp_month,
            exp_year: cardInfo.exp_year,
        });

        await $axios.post(`/customers/payments/store`, {
            appointment_id: props.appointmentId,
            payment_intent_id: paymentIntent.id,
            card_brand: cardInfo.brand,
            card_last4: cardInfo.last4,
            exp_month: cardInfo.exp_month,
            exp_year: cardInfo.exp_year,
            amount: paymentIntent.amount,
        });

        // success event or redirect
    }
}


// async function savePayment(paymentIntent) {
//     try {
//         const response = await $axios.post(`/customers/appointments/${props.appointmentId}/card-payment`, {
//             payment_intent_id: paymentIntent.id,
//             appointment_id: props.appointmentId,
//         })
//         console.log({ response });
//     } catch (error) {
//         console.log({ error })
//     }
// }
</script>

<style scoped>
#card-element {
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 12px;
    box-sizing: border-box;
    background-color: white;
    margin: 0 auto;
}

.text-error {
    color: #fa755a;
    margin-top: 8px;
    font-weight: 600;
}
</style>
