<template>
    <div>
        <div class="mt-8" id="service_list">
            <div class="d-flex align-center justify-space-between">
                <div class="mb-2 w-100">
                    <div v-if="appointment && appointment.services && appointment.services.length" class="w-100">
                        <div class="d-flex align-center justify-space-between w-100">
                            <div>
                                <h2 class="">Services ({{ appointment.services.length }})</h2>
                            </div>
                            <div v-if="appointment && !appointment.payment">
                                <v-btn color="primary"
                                rounded
                                large
                                    @click="openServiceModal()"> <v-icon>mdi-plus</v-icon> Add Service</v-btn>
                            </div>
                        </div>

                    </div>
                    <div v-else>
                        <h2 class="">Service</h2>
                    </div>
                </div>
                <div>
                    <v-btn class="mr-2" small outlined rounded color="primary" v-if="action"
                        @click="openServiceModal()"><v-icon>mdi-plus</v-icon>Add service</v-btn>
                    <!-- <v-btn small outlined rounded color="primary"
                        @click="openProductModal()"><v-icon>mdi-plus</v-icon>Add product</v-btn> -->
                </div>
            </div>

            <!-- <v-divider class="mb-3 mt-3"></v-divider> -->
            <div class="pt-2 custom-bs pa-4 pt-6">
                <div v-if="appointment && appointment.services && appointment.services.length">
                    <div class="d-flex service_item justify-space-between"
                        v-for="(service, index) in appointment.services" :key="index">
                        <div class="d-flex">
                            <div>
                                <p class="mb-0">{{ index + 1 }}.</p>
                            </div>
                            <div>
                                <p class="mb-1 text-capitalize">{{ service.service_name }}</p>
                            </div>
                        </div>
                        <div>
                            <p class="mb-1">{{ formatAmount(service.price) }}</p>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center">
                    <p>Service Not Available</p>
                </div>
                <v-divider class="mt-3 mb-1"></v-divider>
                <div>
                    <v-row>
                        <v-col cols="6"></v-col>
                        <v-col cols="6">
                            <div>
                                <table class="w-100">
                                    <tbody>
                                        <tr>
                                            <td class="text-left">
                                                <p class="mb-0">Sub Total</p>
                                            </td>
                                            <td class="text-right">
                                                <p class="mb-0">{{ formatAmount(appointment.sub_total) }}</p>
                                            </td>
                                        </tr>
                                        <tr v-if="appointment.applied_tax">
                                            <td class="text-left">
                                                <p class="mb-0">Tax</p>
                                            </td>
                                            <td class="text-right">
                                                <p class="mb-0">{{ formatAmount(appointment.applied_tax) }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-left">
                                                <h4>Total Amount</h4>
                                            </td>
                                            <td class="text-right">
                                                <h4>{{ formatAmount(appointment.total_amount) }}</h4>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-right" colspan="2">
                                                <div v-if="appointment.is_paid || appointment.payment" class="pt-4">
                                                    <p v-if="appointment.payment.payment_type == 'cash'">Cash Payment</p>
                                                    <p v-else class="text-capitalize info--text f8 mb-0">{{
                                                        appointment.payment.payment_type}}
                                                        <span v-if="appointment.payment.cr_last4">****{{
                                                            appointment.payment.cr_last4 }}</span>
                                                    </p>
                                                    <p class="mb-0 f8 info--text">{{
                                                        formatDateTime(appointment.payment.created_at) }}</p>
                                                </div>
                                                <h4><v-chip :color="appointment.is_paid ? 'success' : 'error'">{{
                                                        appointment.is_paid?'Paid':'Due Payment'}}</v-chip></h4>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>

                            </div>
                        </v-col>
                    </v-row>

                    <!-- <ModalService 
                        @close="handleClose"
                        :modalService="modal_service"/> -->
                    <!-- Service Modal -->
                    <!-- <ModalService :modalService="modal_service" :services="services"
                        :appointment="appointment"
                        @onClose="handleClose"/> -->

                    <!-- <ModalProduct :appointment="appointment" :isOpen.sync="productModalOpen" @close="handleClose" /> -->
                </div>
            </div>
        </div>
    </div>
</template>
<script>
// import { mapGetters } from 'vuex';
export default {
    props: {
        action: {}
    },
    data() {
        return {
            serviceModalOpen: false,
            modal_service:false,
            productModalOpen: false,
            services: [], // List of services fetched from the server
            selectedServices: [], // Services added to the appointment
        }
    },
    // computed: {
    //     ...mapGetters({
    //         appointment: "appointments/getCurrentAppointment",
    //         currentUser: "auth/getCurrentUser",
    //     }),
    // },
    methods: {
        async openServiceModal() {
                await this.fetchServices();
                this.modal_service = true;
        },
        async openProductModal() {
            this.productModalOpen = true;
        },
        async fetchServices() {
            try {
                // this.$axios.post("/")
                // // const response = await this.$axios('/api/services'); // Replace with your API endpoint
                // // this.services = await response.json();
                this.$axios.get(`/vendors/${this.currentUser.vendor.id}/services`)
                    .then((resp) => {
                        this.loading = false;
                        this.services = resp.data;
                    })
                    .catch((error) => {
                        this.loading = false;
                        this.message = "Error!!"
                        console.log(error);
                    })

            } catch (error) {
                console.error('Error fetching services:', error);
            }
        },
        addServiceToAppointment(selectedServices) {
            this.$axios.post("/appointment-service-update", {
                service_ids: selectedServices.map((item) => item.id),
                appointment_id: this.appointment.id
            })
                .then(() => {
                    this.serviceModalOpen = false;
                    this.$emit('refresh');
                })
                .catch((error) => {
                    console.log({ error });
                })
        },
        handleClose() {
            this.modal_service = false;
        },

    },
    components: {
        // ModalService: () => import('@/views/app/service/appointment/modal/ModalService'),
        // ModalProduct: () => import('@/modules/appointments/modal/ModalProduct')
    }
}
</script>