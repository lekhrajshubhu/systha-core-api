<template>
    <div id="stripePayment">
        <v-container fluid>
            <v-row>
                <v-col cols="12">
                    <div class="mb-3">
                        <v-card elevation="0" class="pa-4 position-relative">
                            <div v-if="selectedCard">
                                <div class="mb-4">
                                    <v-row>
                                        <v-col cols="3" class="d-flex align-center justify-center px-0">
                                            <div>
                                                <v-img :src="getCardLogo(selectedCard.card_brand)" width="46" contain />
                                            </div>
                                        </v-col>
                                        <v-col cols="9">
                                            <div class="text-center d-flex justify-space-between">
                                                <p class="mb-1">
                                                    {{ brandDisplayName(selectedCard.card_brand) }}
                                                </p>
                                                <p class="mb-1">
                                                    •••• {{ selectedCard.card_last4 }}
                                                </p>
                                            </div>
                                            <div class="d-flex justify-space-between">
                                                <div class="text-capitalize">{{ selectedCard.card_name }}</div>

                                                <div>
                                                    {{ selectedCard.exp_month }}/{{
                                                        selectedCard.exp_year.toString().slice(-2)
                                                    }}
                                                </div>
                                            </div>


                                        </v-col>
                                    </v-row>
                                </div>
                                <div class="text-center pt-2">
                                    <v-btn outlined block large color="primary"
                                        @click="changeCard()"><v-icon>mdi-credit-card-sync</v-icon> change card</v-btn>
                                </div>
                            </div>
                            <div class="mt-4">
                                <v-btn block color="primary" outlined large @click="openCardForm()">
                                    <v-icon>mdi-credit-card-plus</v-icon> Add new
                                    card</v-btn>
                            </div>
                        </v-card>

                    </div>
                </v-col>
            </v-row>
        </v-container>

        <!-- <div id="card-element" class="my-6"></div> -->

        <div>
            <div class="px-4">
                <v-btn rounded block color="success" size="large" :disabled="loading" @click="handleSubmit">
                    {{ loading ? "Processing..." : "Pay Now" }}
                </v-btn>
            </div>
        </div>
        <modal-template ref="globalModal" @close="handleClose"></modal-template>
    </div>
</template>



<script setup>
import $axios from '@shared/axios.config'
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import { useVendorClientStore } from '@/vendor_clients/stores/account'
import { useGlobalStore } from '@/global_clients/stores/account'

import visaLogo from "./payment/icons/visa.png";
import mastercardLogo from "./payment/icons/mastercard.png";
import amexLogo from "./payment/icons/amex.png";
import discoverLogo from "./payment/icons/discover.png";
import dinersLogo from "./payment/icons/diners.png";
import jcbLogo from "./payment/icons/jcb.png";
import unionpayLogo from "./payment/icons/unionpay.png";
import defaultLogo from "./payment/icons/default.png";

const getCardLogo = (brand) => {
    const logos = {
        visa: visaLogo,
        mastercard: mastercardLogo,
        amex: amexLogo,
        discover: discoverLogo,
        diners: dinersLogo,
        jcb: jcbLogo,
        unionpay: unionpayLogo,
    };
    return logos[brand] || defaultLogo;
};




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
const selectedCard = ref(null)

watch(selectedCard, (cardInfo, oldCard) => {
    if (cardInfo) {
        if (cardInfo == 'new-card') {
            openCardForm()
        } else {
            console.log({ cardInfo });
        }
    }
});

import ModalAddCard from './payment/ModalAddCard.vue';
import CardChange from './payment/CardChange.vue';

const globalModal = ref(null);

function changeCard() {
    if (globalModal.value) {
        globalModal.value.open({
            title: 'Card Change',
            component: CardChange,
            size: 'md',
            props: {
                publishableKey: props.publishableKey,
                card_list: cards.value
            }
        });
    }
}
function openCardForm() {
    if (globalModal.value) {
        globalModal.value.open({
            title: 'Add New Card',
            component: ModalAddCard,
            size: 'md',
            props: {
                publishableKey: props.publishableKey
            }
        });
    }
}

async function handleClose() {
    fetchCards();
}

onMounted(async () => {
    fetchCards();
    stripe = await loadStripe(props.publishableKey)
})



const brandDisplayName = (brand) => {
    const map = {
        visa: 'Visa',
        mastercard: 'MasterCard',
        'american-express': 'American Express',
        discover: 'Discover',
        jcb: 'JCB',
        unionpay: 'UnionPay',
        diners: 'Diners Club',
        bccard: 'BC Card',
        dinacard: 'DinaCard',
        unknown: 'Unknown',
    }

    return map[brand?.toLowerCase()] || 'Card'
}



const cards = ref([]);

async function fetchCards() {
    try {
        const resp = await $axios.get("/customers/payment-methods");
        // Check if response structure is valid
        if (resp?.data?.payment_methods) {
            cards.value = resp.data.payment_methods;

            // Set default card if any
            selectedCard.value = resp.data.default_payment_method;
        } else {
            cards.value = [];
            selectedCard.value = null;
        }
    } catch (error) {
        console.error("Failed to fetch payment methods:", error);
        cards.value = [];
        selectedCard.value = null;
    }
}

// ✅ Define profile as a computed getter
const profile = computed(() => userStore.profile);

onBeforeUnmount(() => {
    cardElement?.destroy()
})

async function handleSubmit() {
    try {

        let clientSecret = null;

        if (!selectedCard.value) return;

        errorMessage.value = null
        loading.value = true
        console.log(selectedCard);
        loading.value = false;


        if (selectedCard.value?.stripe_customer && selectedCard.value?.payment_method_id) {

            const response = await $axios.post(`/customers/appointments/${props.appointmentId}/payment-intent`, {
                customer_email: profile.value.email,
                customer_name: profile.value.fname,
                customer_phone: profile.value.phone_no,
                stripe_customer_id: selectedCard.value?.stripe_customer,
                payment_method_id: selectedCard.value?.payment_method_id
            });
            clientSecret = response.client_secret;
            confirmPayment(clientSecret, selectedCard.value?.payment_method_id);

        } else {
            console.log("error");
        }

    } catch (error) {
        console.error(error);
        errorMessage.value = error.message || 'Payment failed.';
        loading.value = false
    }
}

async function confirmPayment(clientSecret, paymentMethodId) {


    if (!clientSecret || !paymentMethodId) return;

    loading.value = true;
    try {
        const resp = await stripe.confirmCardPayment(clientSecret, { payment_method: paymentMethodId });
        console.log({ resp });
        savePayment(resp.paymentIntent);

    } catch (error) {
        loading.value = false
        console.log(error);
    }
}
async function savePayment(paymentIntent) {
    try {
        loading.value = true;
        const response = await $axios.post(`/customers/appointments/${props.appointmentId}/store-card-payment`, {
            payment_intent_id: paymentIntent.id,
            appointment_id: props.appointmentId,
            payment_method_id: paymentIntent.payment_method,
            amount: paymentIntent.amount / 100,
        })
        loading.value = false
    } catch (error) {
        loading.value = false
        console.log({ error })
    }
}
</script>

<style scoped>
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.font-mono {
    font-family: monospace;
}

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
