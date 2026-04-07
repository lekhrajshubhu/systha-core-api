<template>
    <div class="">
        <div class="mt-8" id="service_list">
            <div>
               <h4 class="">Products</h4> 
            </div>
            <div class="custom-bs pa-4">
                <!-- <div class="d-flex align-center justify-space-between">
                    <div v-if="!appointment.is_paid">
                        <v-btn small outlined rounded
                        color="primary"
                        v-if="action"
                        @click="openProductModal()"><v-icon>mdi-plus</v-icon>Add product</v-btn>
                    </div>
                </div> -->
                <div>
                    <div class="pt-2" v-if="appointment && (!appointment.is_paid)">
                        <CartList :appointment="appointment" 
                        :showTotal="true"
                        :action="action"
                        :showTitle="false"/>
                    </div>
                </div>
                <ModalProduct :appointment="appointment" :isOpen.sync="productModalOpen" @close="handleClose" />
            </div>
        </div>
    </div>
</template>
<script>
import { base_url } from '@/core/services/config'
import { ApiService } from '@/core/services/api.service'
import { mapGetters } from 'vuex';
export default {
    props: {
        appointment: {},
        action:{
            type:Boolean,
            defualt:true,
        }
    },
    data() {
        return {
            base_url,

            serviceModalOpen: false,
            productModalOpen: false,
            services: [], // List of services fetched from the server
            selectedServices: [], // Services added to the appointment

            // modal_service: false,
            // modal_product: false,
        }
    },
    computed: {
        ...mapGetters({
            cart: "cart/getCart",
            cart_total: "cart/getCartTotal",
        })
    },
    methods: {
        async openServiceModal() {
            this.serviceModalOpen = true;
            if (this.services.length === 0) {
                await this.fetchServices();
            }
        },
        async openProductModal() {
            this.productModalOpen = true;
        },
        async fetchServices() {
            try {
                // ApiService.post("/")
                // // const response = await ApiService('/api/services'); // Replace with your API endpoint
                // // this.services = await response.json();
                ApiService.post("/vendor-service-list", {
                    vendor_id: this.currentUser.vendor.id
                })
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
            // this.selectedselectedServicess.push(selectedServices);
            console.log({ selectedServices });
        },
        handleClose() {
            this.serviceModalOpen = false;
            this.productModalOpen = false;
        },

    },
    components: {
        // ModalService: () => import('@/modules/appointments/modal/ModalService'),
        ModalProduct: () => import('@/modules/appointments/modal/ModalProduct'),
        CartList:()=>import("@/modules/appointments/modal/CartList"),
    }
}
</script>