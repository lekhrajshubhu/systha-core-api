<template>
    <v-container class="ma-0 pa-0">
        <div class="pt-4">
            <v-container>
                <v-row>
                    <v-col cols="12" md="7">
                        <div>

                            <!-- Loading Spinner -->
                            <div v-if="loading" class="text-center">
                                <v-progress-circular indeterminate color="primary" />
                            </div>

                            <!-- Appointment Details -->
                            <div v-else-if="appointment && Object.keys(appointment).length">

                                <!-- Appointment Header -->
                                <v-card class="pa-4 mb-4" elevation="0">
                                    <h4 class="primary--text mb-2 text-uppercase">{{ appointment.appointment_no }}</h4>
                                    <div class="d-flex">
                                        <v-icon large color="primary">mdi-calendar-check-outline</v-icon>
                                        <div class="pl-2">
                                            <p class="mb-0 primary--text">{{ formatDateTime(appointment.start_date) }}
                                            </p>
                                            <p class="mb-0 f9">{{ formatTime(appointment.start_time) }} - {{
                                                formatTime(appointment.end_time) }}</p>
                                        </div>
                                    </div>
                                </v-card>

                                <!-- Service Provider -->
                                <v-card class="pa-4 mb-4" elevation="0">
                                    <h3>Service Provider</h3>
                                    <v-divider class="my-2" />
                                    <div v-if="appointment.provider" class="d-flex">
                                        <v-avatar size="40" class="pt-2">
                                            <v-img :src="appointment.provider.provider.avatar" />
                                        </v-avatar>
                                        <div class="pl-2">
                                            <h4 class="mb-0 primary--text">{{ appointment.provider.provider.fullName }}
                                            </h4>
                                            <p class="mb-0">{{ formatPhoneNumber(appointment.provider.provider.phone_no)
                                                }}</p>
                                            <p class="mb-0">{{ appointment.provider.provider.email }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="text-center">
                                        <p>Not Assigned</p>
                                    </div>
                                </v-card>

                                <!-- Services List -->
                                <v-card class="pa-4 mb-4" elevation="0">
                                    <h3>Services</h3>
                                    <v-divider class="my-2" />

                                    <div v-for="(service, index) in appointment.services" :key="service.id || index"
                                        class="d-flex align-center justify-space-between text-capitalize mb-2">
                                        <p>{{ index + 1 }}. {{ service.service_name }}</p>
                                        <p>{{ formatAmount(service.price) }}</p>
                                    </div>

                                    <v-divider class="my-2" />

                                    <table class="w-100 text-right">
                                        <tr>
                                            <td style="min-width: 20%"></td>
                                            <td>Item Total</td>
                                            <td>
                                                <p>{{ formatAmount(appointment.sub_total) }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>Tax</td>
                                            <td>
                                                <p>{{ formatAmount(appointment.total_tax) }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td style="border-top: 1px solid #dadada;">
                                                <h4 class="py-2">Grand Total</h4>
                                            </td>
                                            <td style="border-top: 1px solid #dadada;">
                                                <h4>{{ formatAmount(appointment.total_amount) }}</h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td style="border-top: 1px solid #dadada;"><h4 class="pt-2">Paid By</h4></td>
                                            <td style="border-top: 1px solid #dadada; vertical-align: top;">
                                                <h4 class="pt-2"> ({{ formatAmount(appointment.total_amount) }})</h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td colspan="2">

                                                <div v-if="appointment.payment" class="pt-1">
                                                    <p class="info--text text-capitalize mb-0" style="font-size: small;">
                                                        {{ appointment.payment.payment_type }}
                                                        <span v-if="appointment.payment.cr_last4"> ****{{
                                                            appointment.payment.cr_last4 }}</span>
                                                        {{ formatDateTime(appointment.payment.created_at) }}
                                                    </p>

                                                </div>
                                            </td>

                                        </tr>

                                    </table>

                                   
                                </v-card>

                                <!-- Order Details (if any) -->
                                <div v-if="appointment.order" class="custom-bs pa-4">
                                    <h3>Order: {{ appointment.order.order_no }}</h3>
                                    <v-divider class="my-2" />

                                    <div v-for="(orderItem, index) in appointment.order.items"
                                        :key="orderItem.id || index" class="d-flex justify-space-between rounded mb-2">
                                        <div class="d-flex">
                                            <v-img class="mr-3" height="60" width="60" contain
                                                :src="orderItem.thumbnail_url" />
                                            <div>
                                                <p class="mb-0">{{ orderItem.inventory.name }}</p>
                                                <p class="mb-0">{{ formatAmount(orderItem.item_price) }} x {{
                                                    orderItem.quantity }}</p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <h4 class="mb-0">{{ formatAmount(orderItem.item_price * orderItem.quantity)
                                                }}</h4>
                                        </div>
                                    </div>

                                    <v-divider class="mt-5" />

                                    <table class="w-100 text-right">
                                        <tbody>
                                            <tr>
                                                <td class="text-left">Total</td>
                                                <td class="text-right">{{ formatAmount(appointment.order.sub_total) }}
                                                </td>
                                            </tr>
                                            <tr v-if="appointment.order.applied_tax">
                                                <td class="text-left">Tax</td>
                                                <td class="text-right">{{ formatAmount(appointment.order.applied_tax) }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-left"><strong>Total Amount</strong></td>
                                                <td class="text-right"><strong>{{
                                                    formatAmount(appointment.order.grand_total) }}</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="text-right pt-4"
                                        v-if="appointment.order.is_paid || appointment.order.payment">
                                        <p v-if="appointment.order.payment.payment_type === 'cash'">Cash Payment</p>
                                        <p class="text-capitalize info--text mb-0" v-else>
                                            {{ appointment.payment.payment_type }}
                                            <span v-if="appointment.payment.cr_last4">****{{
                                                appointment.payment.cr_last4 }}</span>
                                        </p>
                                        <p class="info--text">{{ formatDateTime(appointment.order.payment.created_at) }}
                                        </p>
                                    </div>
                                    <div v-else>
                                    </div>
                                </div>
                                <div class="pa-4 bg-white"
                                    v-if="(appointment && !appointment.is_paid) || !appointment.payment">
                                    <h3>Payment</h3>
                                    <v-divider></v-divider>
                                    <div class="pt-4">
                                        <StripePayment :appointmentId="appointment?.id" :publishableKey="publicKey"
                                            :customerEmail="appointment?.client?.email" @onSuccess="handleSuccess"
                                            @onError="handleError" @close="handleClose" />
                                    </div>
                                </div>

                                <!-- Payment Button -->
                                <!-- <div v-if="appointment && (!appointment.is_paid || !appointment.payment)"
                                    class="text-center mt-4">
                                    <v-btn rounded block color="success" size="x-large" @click="handlePay">Pay
                                        Now</v-btn>
                                </div> -->
                            </div>

                            <!-- No Appointment Found -->
                            <div v-else class="unavailable text-center">
                                <p>No appointment found.</p>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="12" md="5">
                        <div v-if="appointment && appointment.payments.length">
                            <v-card elevation="0" class="pa-4">
                                <v-card-title>
                                    <div>
                                        <h4>Transactions</h4>
                                    </div>
                                </v-card-title>
                                <v-card-text>
                                    <div v-if="appointment && appointment.payments">
                                        <v-card v-for="(transaction, index) in appointment.payments" :key="index"
                                            class="mb-3" elevation="1" rounded="lg">
                                            <v-card-text>
                                                <div class="d-flex justify-space-between align-center mb-2">
                                                    <div class="text-grey text-caption">Payment Code</div>
                                                    <div class="font-weight-medium">{{ transaction.payment_code }}</div>
                                                </div>

                                                <div class="d-flex justify-space-between align-center mb-2">
                                                    <div class="text-grey text-caption">Amount</div>
                                                    <div class="font-weight-medium text-success">
                                                        {{ formatAmount(transaction.amount) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-space-between align-center mb-2">
                                                    <div class="text-grey text-caption">payment Type</div>
                                                    <div class="font-weight-medium text-success text-capitalize">
                                                        {{ transaction.payment_type }} <span
                                                            v-if="transaction.cr_last4">*****{{ transaction.cr_last4
                                                            }}</span>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-space-between align-center">
                                                    <div class="text-grey text-caption">Date</div>
                                                    <div class="font-weight-medium">
                                                        {{ formatDateTime(transaction.created_at) }}
                                                    </div>
                                                </div>
                                            </v-card-text>
                                        </v-card>

                                    </div>
                                </v-card-text>
                                <div>

                                </div>
                            </v-card>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </div>

        <!-- StripePayment always shown -->


        <modal-template ref="globalModal" @close="handleClose" @onSuccess="handleSuccess" />
    </v-container>
</template>

<script setup>
import $axios from '@shared/axios.config'
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { formatDateTime, formatTime, formatAmount, formatPhoneNumber } from '@utils/helpers'
import StripePayment from '@components/StripePayment.vue'

const route = useRoute()

const appointment = ref(null)
const loading = ref(false)

const appointment_id = computed(() => route.params.id || null)

const publicKey = computed(() =>
    appointment.value?.vendor?.payment_credential?.val1 ||
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
</style>
