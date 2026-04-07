<template>
	<v-row justify="center">
		<v-dialog v-model="dialogPayDue" persistent fullscreen>
			<v-card class="">
				<v-card-title class="w-100">
                    <div class="d-flex align-center justify-space-between w-100">
                        <span>Payment Due</span>
                        <v-btn fab small text @click="handleClose()"><v-icon>mdi-close</v-icon></v-btn>
                    </div>
                </v-card-title>
				<v-card-text class="pa-4">
                    <div class="pa-4 text-center">
                        <h1 class="primary--text">{{ formatAmount(appointment.total_amount) }}</h1>
                    </div>
                    <div class="custom-bs pa-4 mt-4">
                        <!-- <CardStripe :publishablekey="stripeKey" @proceed="handleProceed" :loading="loading" /> -->
                        <CardStripe :publishablekey="stripeKey"  :loading="loading" />
                    </div>
                    <div>
                        <v-btn block color="primary" rounded x-large  @click="handleProceed()">Proceed</v-btn>
                    </div>
                </v-card-text>
			</v-card>
		</v-dialog>
	</v-row>
</template>
<script>
import { mapGetters } from 'vuex'
export default {
	props: {
        loading:{},
		dialogPayDue: {},
        appointment:{}
	},
	data() {
		return {
			// loading: false,
			type: 'deliver',
		}
	},
    mounted() {
    },
	// watch: {
	// 	dialogPayDue: function () {
	// 		this.loading = false;
	// 	},
	// },
	methods: {
		handleClose() {
			this.$emit('close');
		},
		handleConfirm() {
			this.$emit('handleConfirm',{
				"status":"OK"
			});
		},
        // async handleProceed(param) {
        async handleProceed() {
            console.log("test");
            // this.$emit('onProceed',param);
            // this.payment.stripeToken = param.stripeToken;
            // this.payment.stripe_card_id = param.stripe.card.id,
            // this.payment.last4 = param.stripe.card.last4,
            // this.payment.name = param.stripe.card.name ? param.stripe.card.name:'',
            // this.payment.brand = param.stripe.card.brand,
            // this.payment.cr_exp_month = param.stripe.card.exp_month,
            // this.payment.cr_exp_year = param.stripe.card.exp_year,
            // this.payment.country = param.stripe.card.country,
            // this.payment.payment_type = param.stripe.card.brand,
           
            // this.payment.payment_due = this.appointment.total_amount;
            // this.payment.appointment_id = this.appointment.id;

            // console.log(this.payment);
            // try {
            //     this.loading = true;
            //     let resp = await this.$axios.post("/service/provider/duepayment", this.payment);
            //     this.loading = false;
            //     this.messageSuccess(resp.message);
            //     this.handleClose();
            // } catch (error) {
            //     this.loading = false;
            //     console.log({error});
            // }

            // .then((resp) => {
            //     this.loaderHide();
            //     this.messageSuccess(resp.message);
            //     this.handleClose();
            // })
            // .catch((error) => {
            //     this.loaderHide();
            //     console.log({error});
            // })

        },
	},
    components:{
        CardStripe: () => import('@components/payment/CardStripe.vue'),
    },
    computed: {
        ...mapGetters({
            currentUser: 'auth/currentUser',
            payment_credential: 'myapp/getPaymentKey'
        }),
        stripeKey(){
            return this.payment_credential?.val1;
        }
    }
}
</script>