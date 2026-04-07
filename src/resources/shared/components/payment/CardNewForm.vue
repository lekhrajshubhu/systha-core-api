<template>
    <v-container class="pb-0">
        <div class="px-6">
            <div class="mb-4 text-left">
                <p class="text-body-2 text-medium-emphasis mb-0">
                    Enter card holder details and save your card securely.
                </p>
            </div>

            <v-form ref="formRef" v-model="valid" lazy-validation>
    
                <v-text-field v-model="customer.customer_name"
                prepend-inner-icon="mdi-account-box"
                label="Name on Card" :rules="nameRules" required
                    density="comfortable" variant="outlined" />
    
            </v-form>
    
            <div id="card-element" class="mt-4"></div>
    
            <div class="pt-2">
                <p v-if="errorMessage" class="text-error">{{ errorMessage }}</p>
    
                <div class="pt-10 text-center">
                    <v-btn color="primary" size="large" :disabled="loading" @click="handleSubmit">
                        {{ loading ? "Processing..." : "Submit" }}
                    </v-btn>
                </div>
            </div>
        </div>
    </v-container>

</template>

<script setup>
import $axios from '@shared/axios.config'
import { reactive, ref, onMounted } from "vue";
import { loadStripe } from "@stripe/stripe-js";
import { useVendorClientStore } from "@/vendor_clients/stores/account";
import { useGlobalStore } from "@/global_clients/stores/account";

const props = defineProps({
    publishableKey: { type: String, required: true },
});

const emit = defineEmits(["onClose", "onBack"]);

const loading = ref(false);
const errorMessage = ref(null);
const valid = ref(false);

let stripe = null;
let elements = null;
let cardElement = null;

const isAccounts = window.location.pathname.startsWith("/global-clients");
const userStore = isAccounts ? useGlobalStore() : useVendorClientStore();

const customer = reactive({
    customer_name: "",
    customer_email: "",
    customer_phone: "",
});

// Form ref for validation
const formRef = ref(null);
// Validation rules
const nameRules = [
    (v) => !!v || "Name on Card is required",
];

onMounted(async () => {

    console.log(userStore.profile);

    // Pre-fill customer info from user store
    Object.assign(customer, {
        customer_name: userStore.profile.name || "",
        customer_email: userStore.profile.email || "",
        customer_phone: userStore.profile.phone_no || "",
    });

    console.log(customer);

    stripe = await loadStripe(props.publishableKey);
    elements = stripe.elements();

    if (!userStore.profile) {
        await userStore.fetchProfile();
    }

    const style = {
        base: {
            color: "#32325d",
            fontSize: "16px",
            fontFamily: 'Poppins, sans-serif',
            "::placeholder": { color: "#a0aec0" },
        },
        invalid: {
            color: "#fa755a",
        },
    };

    cardElement = elements.create("card", { style });
    cardElement.mount("#card-element");
});

async function handleSubmit() {
    errorMessage.value = null;

    if (!formRef.value.validate()) {
        // Form validation failed
        return;
    }
    loading.value = true;


    // 1. Create payment method from card element
    const { paymentMethod, error: pmError } = await stripe.createPaymentMethod({
        type: 'card',
        card: cardElement,
        billing_details: {
            email: customer.customer_email,
            name: customer.customer_name,
            phone: customer.customer_phone,
            address: {
                line1: userStore.profile.address.add1,
                line2: userStore.profile.address.add2,    // Optional
                city: userStore.profile.address.city,
                state: userStore.profile.address.state,               // State or province
                postal_code: userStore.profile.address.zip,
                country: userStore.profile.address.country, //US - 2-letter country code, ISO standard
            }
        },
    });
    if (pmError) {
        loading.value = false
        errorMessage.value = pmError.message;
        return;
    }



    console.log({ paymentMethod })


    const response = await $axios.post(`/payment-methods/create`, {
        payment_method_id: paymentMethod.id,
        ...customer
    });
    console.log({ response });


    // Validate Stripe card element via createPaymentMethod
    //   const { error } = await stripe.createPaymentMethod({
    //     type: "card",
    //     card: cardElement,
    //     billing_details: {
    //       name: customer.name,
    //       email: customer.email,
    //       phone: customer.phone,
    //     },
    //   });

    // if (error) {
    //     errorMessage.value = error.message;
    //     return;
    // }

    loading.value = true;
    try {
        // Your payment submission logic here
        // await $axios.post(...)

        if (props.embedded) {
            emit("onBack");
            return;
        }
        emit("onClose");
    } catch (e) {
        errorMessage.value = e.message || "Payment failed.";
    } finally {
        loading.value = false;
    }
}
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

</style>
