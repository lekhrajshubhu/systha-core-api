<template>
    <v-container fluid class="subscription-detail-page">
        <div class="mb-4">
            <v-btn variant="text" size="small" class="px-0" @click="$router.back()">
                <v-icon start size="18">mdi-arrow-left</v-icon>
                Back to Subscriptions
            </v-btn>
        </div>

        <v-alert
            v-if="error"
            type="error"
            variant="tonal"
            class="mb-4"
            density="comfortable"
        >
            {{ error }}
        </v-alert>

        <v-row v-if="loading">
            <v-col cols="12" lg="8">
                <v-skeleton-loader type="article, article" class="mb-4" />
                <v-skeleton-loader type="article" />
            </v-col>
            <v-col cols="12" lg="4">
                <v-skeleton-loader type="article, list-item-three-line" />
            </v-col>
        </v-row>

        <v-row v-else-if="subscription">
            <v-col cols="12" lg="8">
                <v-card elevation="0"  class="pa-4  mb-4">
                    <div class="d-flex flex-wrap align-center justify-space-between ga-2">
                        <div>
                            <p class="text-caption text-medium-emphasis mb-1">Subscription</p>
                            <h3 class="text-h6 mb-0">{{ subscription.subs_no || 'N/A' }}</h3>
                        </div>
                        <v-chip
                            v-if="subscription.status"
                            :color="getStatusColor(subscription.status)"
                            class="text-capitalize"
                            size="small"
                        >
                            {{ subscription.status }}
                        </v-chip>
                    </div>

                    <v-divider class="my-4" />

                    <v-row dense>
                        <v-col cols="12" sm="6" md="3">
                            <p class="meta-label">Started On</p>
                            <p class="meta-value">{{ formatDateTime(subscription.created_at) }}</p>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <p class="meta-label">Plan Amount</p>
                            <p class="meta-value">{{ formatAmount(subscription.amount) }}</p>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <p class="meta-label">Billing Cycle</p>
                            <p class="meta-value text-capitalize">{{ billingCycleText }}</p>
                        </v-col>
                        <v-col cols="12" sm="6" md="3">
                            <p class="meta-label">Renewal Date</p>
                            <p class="meta-value">{{ renewalDateText }}</p>
                        </v-col>
                    </v-row>
                </v-card>

                <v-card elevation="0"  class="pa-4  mb-4">
                    <h4 class="text-subtitle-1 font-weight-bold">Package Details</h4>
                    <v-divider class="my-3" />

                    <v-row dense>
                        <v-col cols="12" sm="6">
                            <p class="meta-label">Package</p>
                            <p class="meta-value">{{ subscription.package?.name || 'N/A' }}</p>
                        </v-col>
                        <v-col cols="12" sm="6">
                            <p class="meta-label">Subscription Plan</p>
                            <p class="meta-value text-capitalize">{{ packageTypeLabel }}</p>
                        </v-col>
                    </v-row>
                </v-card>

                <v-card elevation="0"  class="pa-4  mb-4">
                    <h4 class="text-subtitle-1 font-weight-bold">Services</h4>
                    <v-divider class="my-3" />

                    <div v-if="serviceList.length">
                        <div
                            v-for="(service, index) in serviceList"
                            :key="service.id || index"
                            class="service-row"
                        >
                            <span class="service-index">{{ index + 1 }}</span>
                            <span class="service-name">{{ service.name || 'Unnamed service' }}</span>
                        </div>
                    </div>
                    <p v-else class="text-medium-emphasis mb-0">No services linked to this subscription.</p>
                </v-card>

                <v-card elevation="0"  class="pa-4 ">
                    <h4 class="text-subtitle-1 font-weight-bold">Service Address</h4>
                    <v-divider class="my-3" />

                    <div class="d-flex align-start ga-3">
                        <v-icon color="primary" size="20">mdi-map-marker-outline</v-icon>
                        <div>
                            <p class="mb-1">{{ addressLine1 }}</p>
                            <p class="mb-0 text-medium-emphasis">{{ addressLine2 }}</p>
                        </div>
                    </div>
                </v-card>
            </v-col>

            <v-col cols="12" lg="4">
                <v-card elevation="0"  class="pa-4  mb-4">
                    <h4 class="text-subtitle-1 font-weight-bold">Actions</h4>
                    <v-divider class="my-3" />

                    <v-alert
                        v-if="Number(subscription.is_cancelled) === 1"
                        type="warning"
                        variant="tonal"
                        density="comfortable"
                        class="mb-3"
                    >
                        This subscription is already cancelled.
                    </v-alert>

                    <v-btn
                        v-else
                        block
                        color="error"
                        variant="outlined"
                        prepend-icon="mdi-cancel"
                        @click="cancelSubscription"
                    >
                        Cancel Subscription
                    </v-btn>
                </v-card>

                <v-card elevation="0"  class="pa-4 ">
                    <div class="d-flex align-center justify-space-between mb-2">
                        <h4 class="text-subtitle-1 font-weight-bold mb-0">Transactions</h4>
                        <span class="text-caption text-medium-emphasis">{{ payments.length }}</span>
                    </div>
                    <v-divider class="my-3" />

                    <div v-if="payments.length">
                        <v-card
                            v-for="(transaction, index) in payments"
                            :key="transaction.id || index"
                            elevation="1"
                            
                            class="mb-3"
                        >
                            <v-card-text class="py-3">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <span class="text-caption text-medium-emphasis">Amount</span>
                                    <span class="font-weight-bold text-success">{{ formatAmount(transaction.amount) }}</span>
                                </div>
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <span class="text-caption text-medium-emphasis">Payment</span>
                                    <span class="text-capitalize">
                                        {{ transaction.payment_type || 'N/A' }}
                                        <span v-if="transaction.cr_last4">• **** {{ transaction.cr_last4 }}</span>
                                    </span>
                                </div>
                                <div class="d-flex justify-space-between align-center">
                                    <span class="text-caption text-medium-emphasis">Date</span>
                                    <span>{{ formatDateTime(transaction.created_at) }}</span>
                                </div>
                            </v-card-text>
                        </v-card>
                    </div>
                    <p v-else class="text-medium-emphasis mb-0">No transactions found.</p>
                </v-card>
            </v-col>
        </v-row>

        <v-card v-else elevation="0"  class="pa-6  text-center">
            <v-icon size="36" class="mb-2" color="grey">mdi-file-search-outline</v-icon>
            <p class="mb-0 text-medium-emphasis">Subscription details are not available.</p>
        </v-card>

        <modal-template ref="globalModal" @close="handleClose" />
    </v-container>
</template>

<script>
import { formatAmount, formatDateTime, getStatusColor } from '@utils/helpers';

export default {
    data() {
        return {
            subscription: null,
            subscriptionId: null,
            loading: false,
            error: null,
        };
    },
    computed: {
        serviceList() {
            return this.subscription?.services || [];
        },
        payments() {
            return this.subscription?.payments || [];
        },
        billingCycleText() {
            const type = this.subscription?.package_type;
            if (!type) return 'N/A';
            const duration = type.duration || '1';
            const unit = type.type_name || 'cycle';
            return `Every ${duration} ${unit}`;
        },
        renewalDateText() {
            const s = this.subscription || {};
            const renewalDate = s.renewal_date || s.next_billing_date || s.next_renewal_date || s.expires_at || s.end_date;
            return renewalDate ? this.formatDateTime(renewalDate) : 'N/A';
        },
        packageTypeLabel() {
            const type = this.subscription?.package_type;
            if (!type) return 'N/A';
            return `${type.name || 'Plan'} (${type.duration || '1'} ${type.type_name || 'cycle'})`;
        },
        addressLine1() {
            const address = this.subscription?.address || {};
            return [address.add1, address.city].filter(Boolean).join(', ') || 'N/A';
        },
        addressLine2() {
            const address = this.subscription?.address || {};
            return [address.state, address.zip].filter(Boolean).join(' ') || 'N/A';
        },
    },
    mounted() {
        this.subscriptionId = this.$route.params.id;
        this.fetchSubscription();
    },
    methods: {
        formatAmount,
        formatDateTime,
        getStatusColor,
        handleClose() {
            this.fetchSubscription();
        },
        async cancelSubscription() {
            if (!this.subscription?.id) return;
            const comp = await import('./modal/ModalCancelSubscription.vue');
            this.$refs.globalModal.open({
                title: 'Cancel Subscription',
                component: comp.default,
                size: 'sm',
                props: {
                    id: this.subscription.id,
                },
                close: this.handleClose,
            });
        },
        async fetchSubscription() {
            try {
                this.loading = true;
                this.error = null;
                const resp = await this.$axios.get('/subscriptions/' + this.subscriptionId);
                this.subscription = resp?.data?.data ?? resp?.data ?? null;
            } catch (err) {
                this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
                this.subscription = null;
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.subscription-detail-page {
    padding-bottom: 24px;
}
.meta-label {
    font-size: 12px;
    color: #6b7280;
    margin-bottom: 2px;
}

.meta-value {
    margin-bottom: 0;
    font-weight: 500;
}

.service-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f2f5;
}

.service-row:last-child {
    border-bottom: 0;
}

.service-index {
    width: 20px;
    font-size: 12px;
    color: #6b7280;
}

.service-name {
    flex: 1;
}
</style>
