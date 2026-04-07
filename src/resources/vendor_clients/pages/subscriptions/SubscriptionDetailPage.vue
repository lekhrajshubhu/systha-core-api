<template>
    <v-container>
        <v-row>
            <v-col cols="12" md="7">
                <div>
                    <div v-if="subscription">
                        <v-row>
                            <v-col cols="12" md="12">
                                <!-- Inquiry Information -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Subscription Information</h4>
                                    <v-divider class="my-2" />
                                    <div class="d-flex align-center justify-space-between">
                                        <h4 class="primary--text">{{ subscription.subs_no }}</h4>
                                        <p class="mb-0">{{ formatDateTime(subscription.created_at) }}</p>
                                    </div>
                                    <v-chip v-if="subscription.status" class="text-capitalize mt-2"
                                        :color="getStatusColor(subscription.status)">
                                        {{ subscription.status }}
                                    </v-chip>
                                </v-card>
                            </v-col>
                            <v-col cols="12">
                                <!-- Preferred Date & Time -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Package Information</h4>
                                    <v-divider class="my-2" />
                                    <div class="pt-4">

                                        <div class="mb-4 mt-4">
                                            <h5 class="mb-0 text-uppercase">Package</h5>
                                            <p>{{ subscription.package ? subscription.package.name :
                                                '' }}</p>
                                        </div>
                                        <v-divider></v-divider>
                                        <div class="mb-4 mt-4">
                                            <h5 class="mb-0 text-uppercase">Package Type</h5>
                                            <p class="text-capitalize">{{ subscription.package_type ?
                                                subscription.package_type.name : '' }} / {{
                                                    subscription.package_type.duration }} {{
                                                    subscription.package_type.type_name }}</p>
                                        </div>
                                    </div>
                                </v-card>
                            </v-col>
                            <v-col cols="12">
                                <!-- Service Address -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Service Address</h4>
                                    <v-divider class="my-2" />
                                    <div class="d-flex pt-4">
                                        <v-icon>mdi-map-marker</v-icon>
                                        <div class="pl-2">
                                            <p class="mb-0">{{ add1_city }}</p>
                                            <p class="mb-0">{{ state_zip }}</p>
                                        </div>
                                    </div>
                                </v-card>


                            </v-col>
                            <v-col cols="12">
                                <v-card elevation="0" class="pa-4">
                                    <h4>Services</h4>
                                    <v-divider class="my-2" />
                                    <div>
                                        <div v-for="(service, index) in subscription.services" :key="index">
                                            <div class="d-flex">
                                                <div style="width:20px;">
                                                    {{ index + 1 }}.
                                                </div>
                                                <div>
                                                    {{ service.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </v-card>


                            </v-col>


                        </v-row>
                        <div class="text-center mt-4" v-if="subscription && subscription.is_cancelled == 0">
                            <v-btn size="large" color="error" @click="cancelSubscription()" variant="outlined"
                                prepend-icon="mdi-cancel">
                                Cancel Subscription
                            </v-btn>
                        </div>


                    </div>
                </div>
            </v-col>
            <v-col cols="12" md="5">
                <div>
                    <v-card class="elevation-0">
                        <v-card-title>
                            <h4>Transactions</h4>
                        </v-card-title>
                        <v-card-text>
                            <div v-if="subscription && subscription.payments">
                                <v-card v-for="(transaction, index) in subscription.payments" :key="index" class="mb-3"
                                    elevation="1" rounded="lg">
                                    <v-card-text>
                                       

                                        <div class="d-flex justify-space-between align-center mb-2">
                                            <div class="text-grey text-caption">Amount</div>
                                            <div class="font-weight-medium text-success">
                                                {{ formatAmount(transaction.amount) }}
                                            </div>
                                        </div>
                                        <div class="d-flex justify-space-between align-center mb-2">
                                            <div class="text-grey text-caption">payment Type</div>
                                            <div class="font-weight-medium text-success text-capitalize">
                                                {{ transaction.payment_type }} <span v-if="transaction.cr_last4">*****{{
                                                    transaction.cr_last4 }}</span>
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
                    </v-card>
                </div>
            </v-col>
        </v-row>
        <modal-template ref="globalModal" @close="handleClose"></modal-template>
    </v-container>
</template>
<script>
import { formatDate, formatTime, formatAmount, formatDateTime, formatTimeAgo, getStatusColor } from '@utils/helpers';
export default {
    data() {
        return {
            subscription: null,
            subscription_id: null,
        };
    },
    computed: {
    },
    mounted() {
        this.subscription_id = this.$route.params.id;
        this.fetchInquiry();
    },
    computed: {
        add1_city() {
            return [this.subscription.address.add1, this.subscription.address.city].filter(Boolean).join(", ");
        },
        state_zip() {
            return [this.subscription.address.state, this.subscription.address.zip].filter(Boolean).join(" ");
        },
    },
    methods: {
        formatDate,
        formatTime,
        formatDateTime,
        formatTimeAgo,
        formatAmount,
        getStatusColor,


        handleClose() {
            console.log("here");
            // this.$refs.globalModal.close();
        },
        async cancelSubscription() {
            const comp = await import('./modal/ModalCancelSubscription.vue')
            this.$refs.globalModal.open({
                title: 'Cancel Subscription',
                component: comp.default,
                size: 'sm',
                props: {
                    id: this.subscription.id
                },
                close: this.handleClose,
            })
        },

        async fetchInquiry() {
            try {
                this.loading = true;
                const resp = await this.$axios.get('/subscriptions/' + this.subscription_id);
                this.subscription = resp.data;
                console.log({ resp });
                this.loading = false;
            } catch (err) {
                this.loading = false;
                this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
            }
        },

    },
};
</script>
