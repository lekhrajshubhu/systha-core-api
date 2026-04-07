<template>
    <v-container>
        <v-row>
            <v-col cols="12" md="6" offset-md="3">
                <div>
                    <div v-if="inquiry">
                        <v-row>
                            <v-col cols="12" md="12">
                                <!-- Inquiry Information -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Inquiry Information</h4>
                                    <v-divider class="my-2" />
                                    <div class="d-flex align-center justify-space-between">
                                        <h4 class="primary--text">{{ inquiry.inquiry_no }}</h4>
                                        <p class="mb-0">{{ formatDateTime(inquiry.created_at) }}</p>
                                    </div>
                                    <v-chip v-if="inquiry.status" class="text-capitalize mt-2"
                                        :color="getInquiryStatusColor(inquiry.status)">
                                        {{ inquiry.status }}
                                    </v-chip>
                                </v-card>
                            </v-col>
                            <v-col cols="12" md="6">
                                <!-- Preferred Date & Time -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Preferred Date & Time</h4>
                                    <v-divider class="my-2" />
                                    <div class="d-flex pt-4">
                                        <v-icon>mdi-calendar</v-icon>
                                        <div class="pl-2">
                                            <p class="mb-0">{{ formatDate(inquiry.preferred_date) }}</p>
                                            <p class="mb-0">{{ formatTime(inquiry.preferred_time) }}</p>
                                        </div>
                                    </div>
                                </v-card>
                            </v-col>
                            <v-col cols="12" md="6">
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
                            <v-col cols="12" md="12" v-if="inquiry.description">
                                <!-- Description -->
                                <v-card elevation="0"  class="pa-4">
                                    <h4>Description</h4>
                                    <v-divider class="mt-2" />
                                    <p class="mt-2">{{ inquiry.description }}</p>
                                </v-card>
                            </v-col>

                            <v-col cols="12" md="12">

                                <!-- Required Services -->
                                <v-card elevation="0" v-if="inquiry.services?.length" class="pa-4">
                                    <h4>Required Services</h4>
                                    <v-divider />
                                    <div v-for="(serv, idx) in inquiry.services" :key="idx"
                                        class="d-flex justify-space-between mt-3">
                                        <p class="mb-0 font-weight-bold text-capitalize">
                                            {{ idx + 1 }}. {{ serv.name }}
                                        </p>
                                        <!-- <p class="mb-0 font-weight-bold">
                                            {{ formatAmount(serv.price) }}
                                        </p> -->
                                    </div>
                                </v-card>
                            </v-col>
                            <v-col cols="12" md="12" v-if="inquiry.quotations?.length">

                                <!-- Quotations -->
                                <v-card elevation="0" class="pa-4">
                                    <h4>Quotations</h4>
                                    <v-divider />
                                    <div v-for="(quote, idx) in inquiry.quotations" :key="idx" class="mt-3">
                                        <div class="d-flex justify-space-between align-center">
                                            <div>
                                                <h4 class="primary--text text-capitalize">
                                                    {{ quote.quote_number }}
                                                    <v-chip
                                                        :color="quote.status === 'confirmed' ? 'success' : 'warning'"
                                                        class="ml-2">
                                                        {{ quote.status }}
                                                    </v-chip>
                                                </h4>
                                                <p class="mb-0">{{ formatDateTime(quote.created_at) }}</p>
                                            </div>
                                            <v-btn variant="tonal" color="primary" :to="{ name: 'vendorClientQuotationDetailPage', params: { id: quote.id } }">
                                                view quotation <v-icon>mdi-chevron-right</v-icon>
                                            </v-btn>
                                        </div>
                                    </div>
                                </v-card>
                            </v-col>

                        </v-row>





                    </div>
                </div>
            </v-col>
        </v-row>
    </v-container>
</template>
<script>
import { formatDate, formatTime, formatAmount, formatDateTime, formatTimeAgo, getInquiryStatusColor } from '@utils/helpers';
export default {
    data() {
        return {
            inquiry: null,
            inquiry_id: null,
        };
    },
    computed: {
    },
    mounted() {
        this.inquiry_id = this.$route.params.id;
        this.fetchInquiry();
    },
    computed: {
        add1_city() {
            return [this.inquiry.service_address.add1, this.inquiry.service_address.city].filter(Boolean).join(", ");
        },
        state_zip() {
            return [this.inquiry.service_address.state, this.inquiry.service_address.zip].filter(Boolean).join(" ");
        },
    },
    methods: {
        formatDate,
        formatTime,
        formatDateTime,
        formatTimeAgo,
        formatAmount,
        getInquiryStatusColor,


        async fetchInquiry() {
            try {
                this.loading = true;
                const resp = await this.$axios.get('/inquiries/' + this.inquiry_id);
                this.inquiry = resp.data;
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
