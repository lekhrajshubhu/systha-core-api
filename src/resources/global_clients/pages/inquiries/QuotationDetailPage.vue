<template>
    <v-container fluid>
        <div class="mb-4">
            <v-btn variant="text" size="small" class="px-0" @click="$router.back()">
                <v-icon start size="18">mdi-arrow-left</v-icon>
                Back
            </v-btn>
        </div>
        <v-row>
            <v-col cols="12" lg="7">
                <div>
                    <div v-if="quotation">
                        <v-card class="elevation-0 pa-4">
                            <QuotationSectionsSection
                                :totals="quotation && quotation.total_info ? quotation.total_info : { sub_total: null, tax: null, total: null }"
                                :sections="quotation && quotation.sections && quotation.sections.length ? quotation.sections : []"
                                :quotation-number="quotation ? quotation.quote_number : ''"
                                :quotation-date="quotation ? quotation.created_at : ''"
                                :quotation-time="quotation ? quotation.created_at : ''"
                                :status="quotation ? quotation.status : ''"
                                @confirm="openConfirm(quotation)"
                            />
                        </v-card>
                    </div>
                  
                </div>
            </v-col>
            <v-col cols="12" lg="5">
                <section-notification />
            </v-col>
        </v-row>

        <modal-template ref="globalModal" @close="handleClose"></modal-template>
    </v-container>
</template>
<script>
import QuotationSectionsSection from '../../../shared/QuotationSectionsSection.vue';
import QuotationServicesSection from '../../../shared/QuotationServicesSection.vue';
import QuotationSummarySection from '../../../shared/QuotationSummarySection.vue';
import SectionNotification from '@shared/components/notification/NotificationList.vue';

export default {
    name: 'quotaionDetailPage',
    data() {
        return {
            dialog: false,
            quotation_id: null,
            quotation: null,
        }
    },
    mounted() {
        this.quotation_id = this.$route.params.id;
        console.log("Quotation ID:", this.quotation_id);
        this.fetchQuotation();
    },
    computed: {
        vendorAddress() {
            const vendor = this.quotation && this.quotation.vendor;
            const address = vendor && vendor.address;
            if (!address) return '';
            const parts = [
                address.add1,
                address.add2,
                address.city,
                address.state,
                address.zip,
                address.country,
            ].filter((part) => part && String(part).trim().length);
            return parts.join(', ');
        },
    },
    methods: {
        fetchQuotation() {
            this.$axios.get("/quotations/" + this.quotation_id)
                .then((resp) => {
                    this.quotation = resp.data;
                    console.log("Quotation:", this.quotation);
                })
                .catch((error) => {
                    console.log(error);
                })
        },
        handleClose() {
            this.fetchQuotation();
        },
        async openConfirm(quotation) {
            const comp = await import('./modal/ModalQuotationConfirm.vue')
            this.$refs.globalModal.open({
                title: 'Inquiry Detail',
                component: comp.default,
                size: 'sm',
                props: {
                    id: quotation.id,
                },
                close: this.handleClose,
            })
        }
        // async openConfirm(quotation) {
        //     const comp = await import('./modal/ModalQuotationConfirm.vue')
        //     GlobalModal.open({
        //         title: 'Confirm Quotation',
        //         component: comp.default,
        //         props: { id: quotation.id },
        //     })
        // }


    },
    components: {
        QuotationSummarySection,
        QuotationServicesSection,
        QuotationSectionsSection,
        SectionNotification,
    },
}
</script>
