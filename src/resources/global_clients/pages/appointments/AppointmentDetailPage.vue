<template>
    <v-container fluid>
       
            <v-row>
                <v-col cols="12" md="8">
                    <div>
                        <section-summary :appointment="appointment" @payment-success="handlePaymentSuccess" />
                    </div>
                </v-col>
                <v-col cols="12" md="4" class="notification-col">
                    <div>
                        <section-notification :appointment="appointment" />
                    </div>
                </v-col>
            </v-row>

    </v-container>
</template>

<script setup>
import $axios from '@shared/axios.config'
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatDateTime, formatTime, formatAmount, formatPhoneNumber } from '@utils/helpers'
import SectionNotification from '@shared/components/notification/NotificationList.vue'
import SectionSummary from '@shared/components/appointment/SummarySection.vue'

const route = useRoute()

const appointment = ref(null)
const loading = ref(false)

const appointment_id = computed(() => route.params.id || null)

const publishableKey = computed(() =>
    appointment.value?.vendor?.publishable_key ||
    'pk_test_51NcU65FCkDOH9dhP0FoxxZFNkp8i7VOf5468fmuBqCwt9r4nPtQY8SIu1qIDKwaI6gVReyAhYo0OQqGjo7bCGa1h0048eTMvfH'
)

async function fetchAppointment() {
    loading.value = true
    try {
        const response = await $axios.get(`/appointments/${appointment_id.value}`)
        appointment.value = response.data
    } catch (error) {
        console.error('Failed to fetch appointment:', error)
    } finally {
        loading.value = false
    }
}

function handlePay() {
    // Optionally do something on button click
}

function handleSuccess() {
    fetchAppointment()
}

function handlePaymentSuccess() {
    fetchAppointment()
}

function handleError(error) {
    console.error('Payment error:', error)
}

function handleClose() {
    // Optional: handle modal close
}

onMounted(() => {
    if (appointment_id.value) fetchAppointment()
})
</script>

<style scoped lang="scss">
.unavailable {
    margin-top: 50px;
    font-size: 1.2rem;
    color: #999;
}

.notification-col {
    position: sticky;
    top: 4px;
    align-self: flex-start;
}
</style>
