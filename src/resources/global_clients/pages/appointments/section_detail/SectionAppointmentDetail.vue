<template>
    <div>
        <div>
            <div v-if="appointmentDetail && Object.keys(appointmentDetail).length">
                <div class="pb-2">
                    <h4 class="text-uppercase">{{ appointment.appointment_no }}</h4>
                    <p class="mb-0">
                        {{ formatDateToDay(appointmentDetail.start_date) }}
                        {{ formatTimeString(appointmentDetail.start_time) }} {{
                            formatTimeString(appointmentDetail.end_time) }}
                    </p>
                </div>
                <v-divider class="mb-4"></v-divider>
                <div class="custom-height-1 pl-6 pr-6">
                    <div v-if="appointmentDetail && Object.keys(appointmentDetail).length" class="">
                        <div class="pa-6 pl-0 pt-0">

                            <!-- CLIENT -->
                            <SectionClient :appointment="appointmentDetail" @refresh="handleRefresh()" />

                            <!-- SERVICE PROVIDER -->
                            <SectionProvider :appointment="appointmentDetail" />

                            <!-- SERVICES -->
                            <SectionServices :appointment="appointmentDetail" @refresh="handleRefresh"
                                v-if="appointmentDetail && appointmentDetail.services" />

                            <!-- SERVICES -->
                            <SectionProducts :appointment="appointmentDetail" :action="true"
                                v-if="!appointmentDetail.is_paid" />
                            <!-- SERVICES -->
                            <SectionOrders :appointment="appointmentDetail"
                                v-if="appointmentDetail && appointmentDetail.order" />

                            <div v-if="!appointmentDetail.is_paid" class="d-flex">
                                <!-- <v-radio label="Order Type" :value="oder_type" v-for="(oder_type,index) in ['pickup','delivery']" :key="index" class="custom-bs pa-4 mb-4 mr-4" style="width: 130px;">
                                    <template v-slot:label>
                                        <div>
                                            <div class="pt-2 pb-2 text-capitalize">{{oder_type}}</div>
                                        </div>
                                    </template>
</v-radio> -->
                                <div v-if="currentCart">
                                    <v-radio-group v-model="order_type" row>
                                        <v-radio label="Order Type" :value="gateway"
                                            v-for="(gateway, index) in ['pickup', 'delivery']" :key="index"
                                            class="custom-bs pa-4 mb-4 mr-4" style="width: 130px;">
                                            <template v-slot:label>
                                                <div>
                                                    <div class="pt-2 pb-2 text-capitalize">{{ gateway }}</div>
                                                </div>
                                            </template>
                                        </v-radio>
                                    </v-radio-group>
                                    <div v-if="order_type && order_type=='delivery'">
                                        <div>
                                            <p>Delivery Address</p>
                                            <div class="custom-bs pa-4">
                                                <v-form ref="formDeliveryAddress">
                                                    <v-row>
                                                        <v-col cols="12">
                                                            <v-text-field label="Address"
                                                            :rules="rulesRequired"
                                                            v-model="address.add1"
                                                            ></v-text-field>
                                                        </v-col>
                                                        <v-col cols="4">
                                                            <v-text-field label="City"
                                                            :rules="rulesRequired"
                                                            v-model="address.city"
                                                            ></v-text-field>
                                                        </v-col>
                                                        <v-col cols="4">
                                                            <v-text-field label="State"
                                                            :rules="rulesRequired"
                                                            v-model="address.state"
                                                            ></v-text-field>
                                                        </v-col>
                                                        <v-col cols="4">
                                                            <v-text-field label="ZIP"
                                                            :rules="rulesRequired"
                                                            v-model="address.zip"
                                                            ></v-text-field>
                                                        </v-col>
                                                    </v-row>
                                                </v-form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <v-divider class=""></v-divider>
                    <div v-if="appointmentDetail && appointmentDetail.is_paid">
                        <!-- <p>Checkout At: {{  }}</p> -->
                    </div>

                    <div v-else class="pt-4">
                        <div v-if="currentCart && order_type">
                            <v-btn color="primary" @click="handleContinue()">Continue</v-btn>
                        </div>
                        <div v-else>
                            <div v-if="!currentCart">
                                <v-btn color="primary" large rounded @click="handleCheckout()">checkout</v-btn>
                            </div>
                        </div>
                        <!-- <div class="pt-2">
                            <SectionPayment 
                            :appointment="appointmentDetail" 
                            @paymentSuccess="fetchAppointmentDetail" />
                        </div> -->
                    </div>
                </div>
            </div>
            <div class="unavailable" v-else>
                <p>No appointment</p>
            </div>
        </div>
    </div>
</template>
<script>
import { ApiService } from '@/core/services/api.service'
// import { mapGetters } from 'vuex'
import { base_url } from '@/core/services/config'
export default {
    name: 'AppointmentPage',
    props: {
        appointment: {}
    },
    data() {
        return {
            title: '',
            base_url,
            loading: false,
            appointmentDetail: null,
            order_type: null,
            rulesRequired: [(v) => !!v || "Required"],
            address:{
                add1:'',
                city:'',
                state:'',
                zip:'',
            }
        }
    },
    watch: {
        appointment(value) {
            if (value) {
                this.fetchAppointment();
            }
        },
        order_type(value){
            ApiService.post("/pos-cart-order-type",{
                appointment_id: this.appointment.id,
                order_type: value,
            },(resp)=>{
                console.log({resp});
            },(error) =>{
                console.log({error});
            })
        }
    },
    mounted() {
    },
    methods: {
        handleContinue(){
            if(!this.$refs.formDeliveryAddress.validate()) return;

            console.log("test", this.address);
        },
        handleCheckout() {
            ApiService.post('/pos-cart-appointment-checkout', {
                "appointment_id": this.appointment.id,

            })
                .then((resp) => {
                    // this.loaderHide();
                    // this.checkout_confirm = true;
                    console.log("tets", resp);
                })
                .catch((error) => {
                    this.loaderHide();
                    console.log({ error });
                })
        },
        handleRefresh() {
            // this.$emit('refresh');
            this.fetchAppointment();
        },
        fetchAppointment() {
            this.loading = true;
            localStorage.setItem('appt_id', this.appointment.id);
            ApiService.post('/pos-vendor-appointment',{appointment_id: this.appointment.id, event:true})
            .then((resp) => {
                this.loading = false;
                this.order_type = null;
                this.appointmentDetail = resp.data;
            })
            .catch((error) => {
                this.loading = false;
                console.log(error);
            })
        },
        handleTabSelect(item, index) {
            this.selected_tab = index;
            console.log("test", item);
        },
        loadMore() {
            if (this.last_page >= this.next_page) {
                if (!this.fetching_data)
                    this.fetchAppointments();
            }
        },
        handleActive(item, index) {
            console.log(item, index);
            this.activeType = index;
            this.enquiry_type = item.status.status;
            this.next_page = 1;
            this.appointment_list = [];
            this.fetchAppointments();

        },
        handleView(appointment, index) {
            this.active_index = index;
            this.fetchAppointmentDetail(appointment);
        },
        fetchAppointmentDetail(appointment) {
            ApiService.get("/vendor/appointment/" + appointment.id)
                .then((resp) => {
                    this.appointment = resp.data;
                    console.log("test", this.appointment)
                })
                .catch((error) => {
                    console.log(error);
                })
        },

        addService() {
            let service_info = {
                name: '',
                val1: '',
                val2: '',
                service_charge: '',
                vendor_id: '',
                status: 'publish',
                is_default: '',
            }
            this.$bus.$emit("DIALOG_VENDOR_SERVICE", {
                'is_edit': 0,
                'title': 'New Service',
                'service_info': service_info,
                'service_categories': this.service_categories
            });
        },
        fetchAppointments() {
            this.loading = true;
            ApiService.post("/vendor/appointment/list?page=" + this.next_page, {
                "status": this.enquiry_type
            })
                .then((resp) => {
                    this.loading = false;
                    this.appointment_types = resp.data;
                })
                .catch((error) => {
                    this.loading = false;
                    this.message = "Error!!"
                    console.log(error);
                })
        },
        handleBack() {
            this.$router.back();
        },
    },
    components: {
        SectionProvider: () => import('@/modules/appointments/components/section/SectionAppointmentServiceProvider.vue'),
        SectionClient: () => import('@/modules/appointments/components/section/SectionAppointmentClient.vue'),
        SectionServices: () => import('@/modules/appointments/components/section/SectionAppointmentServices.vue'),
        SectionProducts: () => import('@/modules/appointments/components/section/SectionAppointmentProducts.vue'),
        SectionOrders: () => import('@/modules/appointments/components/section/SectionAppointmentOrders.vue'),
        // SectionPayment: () => import('@/modules/payment/PaymentPage.vue'),
    },
    // computed: {
    //     ...mapGetters({
    //         currentUser: 'auth/user',
    //         currentCart: 'cart/getCart'
    //     })
    // }
}
</script>
<style lang="scss" scoped>
.custom-height {
    max-height: calc(100vh - 250px);
    overflow: auto;
}

.custom-height-1 {
    max-height: calc(100vh - 235px);
    overflow: auto;
}
</style>