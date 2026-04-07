<template>
    <div>
        <div class="">
            <h5 class="text-uppercase mb-0 primary--text">Invoice</h5>
            <v-divider class="mb-3 mt-3"></v-divider>
            <div class="d-flex align-center justify-space-between">
                <p class="mb-0">Sub Total</p>
                <p class="mb-0">{{ formatAmount(appointment.price_without_tax) }}</p>
            </div>
            <div class="d-flex align-center justify-space-between">
                <p class="mb-0">Tax</p>
                <p class="mb-0">{{ formatAmount(appointment.price_without_tax) }}</p>
            </div>
            <v-divider class="mb-3 mt-3"></v-divider>
            <div class="d-flex align-center justify-space-between">
                <h4 class="mb-0 primary--text">Total</h4>
                <h4 class="mb-0 primary--text">{{ formatAmount(appointment.price) }}</h4>
            </div>
            <div v-if="appointment.payment" class="">
                <div class="text-right">
                    <v-chip color="success" small>(Paid)</v-chip>
                </div>
            </div>
            <div v-else>
                <div class="text-right">
                    <v-chip color="error" small>Payment Due</v-chip>
                </div>
                <v-divider class="mt-5 mb-4"></v-divider>
                <div class="mt-4 text-center" v-if="appointment.sent_payment_link">
                    <p class="info--text">Payment link has been sent</p>
                    <v-btn outlined rounded color="primary" @click="handleSendPaymentLink()">resend email</v-btn>
                </div>
                <div v-else class="mt-4">
                    <v-btn rounded color="primary" block large @click="handleSendPaymentLink()">Send payment link</v-btn>
                </div>
            </div>
        </div>
        <ModalConfirm :dialogConfirm="modal_confirm" 
            @handleConfirm="handleConfirm"
            @close="handleClose()"/>
    </div>
</template>
<script>
import { ApiService } from '@/core/services/api.service'
export default {
    props: {
        appointment: {}
    },
    data() {
        return {
            modal_confirm:false,
            // 
        }
    },
    methods:{
        handleSendPaymentLink(){
            this.modal_confirm = true;
        },
        handleClose(){
            this.modal_confirm = false;
        },

        handleConfirm(){
            this.loaderShow();
            ApiService.post("/vendor/appointment/payment/link",{
                "appointment_id": this.appointment.id
            })
            .then((resp) =>{
                this.loaderHide();
                this.messageSuccess(resp.message);
                this.handleClose();
                this.$emit('refresh')
            })
            .catch((error) =>{
                this.loaderHide();
                this.messageError(error.response.data.error);
                console.log({error});
                this.handleClose();
            })
        },
    },
    components:{
        ModalConfirm: () => import('@components/layout/DialogConfirm.vue'),
    }
}
</script>