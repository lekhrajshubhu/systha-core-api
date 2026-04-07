<template>
    <v-container class="px-0">
        <div class="mt-5">
            <div id="service_list" v-if="appointment && appointment.order">
                <div>
                    <div class="d-flex align-center justify-space-between mb-2">
                        <h2 class="text-uppercase mb-0 ">
                            {{ appointment.order.order_no }}
                        </h2>
                        <v-btn v-if="!appointment.order.is_paid" large color="primary" rounded
                            @click="handleProductAdd()"><v-icon>mdi-plus</v-icon> Add Product</v-btn>
                    </div>
                    <div class="custom-bs pa-4" >
                        <div class="d-flex justify-space-between pa-4 rounded mb-2"
                        style="border: 1px solid #dadada;"
                            v-for="(orderItem, index) in appointment.order.items" :key="index">
                            <div class="d-flex" >
                                <v-img class="mr-3" height="60" width="60" contain
                                    :src="orderItem.thumbnail_url"></v-img>
                                <div>
                                    <p class=" mb-0">{{ orderItem.inventory.name }}</p>
                                    <p class="mb-0">
                                        {{ formatAmount(orderItem.item_price) }} x
                                        {{ orderItem.quantity }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <h4 class="mb-0 ">
                                    {{ formatAmount(orderItem.item_price * orderItem.quantity) }}
                                </h4>
                                <v-btn v-if="!appointment.order.is_paid" small outlined color="error" class="mt-2"
                                @click="handleDelete(orderItem)"
                                >Remove</v-btn>
                            </div>
                        </div>
                        <v-divider class="mt-5"></v-divider>
                        <div>
                            <v-row>
                                <v-col cols="6"></v-col>
                                <v-col cols="6">
                                    <div v-if="appointment.order.payments" class="mt-2">
                                        <table class="w-100">
                                            <tbody>
                                                <tr>
                                                    <td class="text-left">
                                                        <h4>Sub Total</h4>
                                                    </td>
                                                    <td class="text-right">
                                                        <h4>{{ formatAmount(appointment.order.amount) }}</h4>
                                                    </td>
                                                </tr>
                                                <tr
                                                    v-if="appointment.order.tax && appointment.order.tax.applied_amount">
                                                    <td class="text-left">
                                                        <h4>
                                                            Tax
                                                            <span v-if="appointment.order.tax.type === 'percentage'">
                                                                ({{ appointment.order.tax.tax_value }}%)
                                                            </span>
                                                            <span v-else-if="appointment.order.tax.type === 'flat'">
                                                                (flat)
                                                            </span>
                                                        </h4>
                                                    </td>
                                                    <td class="text-right">
                                                        <h4>{{ formatAmount(appointment.order.tax.applied_amount) }}
                                                        </h4>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left">
                                                        <h4>Total Amount</h4>
                                                    </td>
                                                    <td class="text-right">
                                                        <h4>{{ formatAmount(appointment.order.total_amount) }}</h4>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-else class="mt-2">
                                        <table class="w-100">
                                            <tbody>
                                                <tr>
                                                    <td class="text-left">
                                                        <p class="mb-0">Sub Total</p>
                                                    </td>
                                                    <td class="text-right">
                                                        <p class="mb-0">{{ formatAmount(appointment.order.sub_total) }}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr v-if="appointment.order && appointment.order.applied_tax">
                                                    <td class="text-left">
                                                        <p class="mb-0">
                                                            Tax

                                                        </p>
                                                    </td>
                                                    <td class="text-right">
                                                        <p class="mb-0">{{ formatAmount(appointment.order.applied_tax) }}
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left">
                                                        <h4>Total Amount</h4>
                                                    </td>
                                                    <td class="text-right">
                                                        <h4>{{ formatAmount(appointment.order.grand_total) }}</h4>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-right">
                                        <div v-if="appointment.order.is_paid || appointment.order.payment" class="pt-4">
                                            <p v-if="appointment.order.payment.payment_type == 'cash'">Cash Payment</p>
                                            <p v-else class="text-capitalize info--text f8 mb-0">{{
                                                appointment.order.payment.payment_type }}
                                                <span v-if="appointment.order.payment.cr_last4">****{{
                                                    appointment.order.payment.cr_last4 }}</span>
                                            </p>
                                            <p class="mb-0 f8 info--text">{{
                                                formatDateTime(appointment.order.payment.created_at) }}</p>
                                        </div>
                                        <h4><v-chip :color="appointment.order.is_paid ? 'success' : 'error'">{{
                                            appointment.order.is_paid ? 'Paid' : 'Due Payment' }}</v-chip></h4>
                                    </div>
                                </v-col>
                            </v-row>
                        </div>
                    </div>


                </div>
            </div>
            <div id="order_list" v-else-if="appointment && !appointment.is_paid || !appointment.payment">
                <div class="mb-2">
                    <h2>Product Items</h2>
                </div>
                <div class="custom-bs pa-4 d-flex align-center justify-space-around">
                    <div class="unavailbable py-8">
                        <v-btn large color="primary" outlined rounded
                            @click="handleProductAdd()"><v-icon>mdi-plus</v-icon>
                            Add Product</v-btn>
                    </div>
                </div>
            </div>
            <div>
                <v-container>
                    <v-row>
                        <v-col cols="12">
                            <div>
                                <ModalProduct @onClose="handleCloseModal()" 
                                @updateData="updateData"
                                :appointment="appointment" :modal_product="modal_product" />
                            </div>
                            <div>
                                <DialogConfirm :dialogConfirm="modal_confirm"
                                @handleConfirm="deleteConfirm()"
                                @close="handleCloseModal"
                                />
                            </div>
                        </v-col>
                    </v-row>
                </v-container>
            </div>
        </div>
    </v-container>
</template>

<script>
import { base_url } from '@/core/services/config';

export default {
    props: {
        appointment: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            base_url,
            modal_product: false,
            modal_confirm:false,
            selected_item:null,
        };
    },
    components: {
        ModalProduct: () => import('@/views/app/service/appointment/modal/ModalProduct'),
        DialogConfirm: () => import('@components/layout/DialogConfirm.vue'),
        // ModalProduct'),
    },
    methods: {
        handleDelete(param){
            this.selected_item = param;
            this.modal_confirm = true;
        },
        async deleteConfirm(){
            this.loaderShow();
            const resp = await this.$axios.delete(`/order-items/${this.selected_item.id}/delete`);
            this.loaderHide();
            this.messageSuccess(resp.message);
            this.handleCloseModal();
            this.$emit("updateData");
        },
        handleCloseModal() {
            this.modal_confirm = false;
            this.modal_product = false;
            this.$emit("updateData");
        },
        updateData(){
            this.$emit("updateData");
        },

        handleProductAdd() {
            this.modal_product = true;
        },
        getImageUrl(fileName) {
            return `${this.base_url}/isw?f=product/inventory/thumbnail&fn=${fileName || 'noimage.webp'}`;
        },
    },
};
</script>