<template>
    <v-container>
        <div v-if="quotation">
            <div class="row">
                <v-col cols="12" md="6" offset-md="3">
                    <v-card elevation="0" class="pa-4">
                        <div>
                            <div class="d-flex mt-3">
                                <div class="pr-2">
                                    <v-avatar size="40" tile>
                                        <img contain height="30"
                                            :src="quotation && quotation.vendor && quotation.vendor.logo"
                                            alt="Profile Pic">
                                    </v-avatar>
                                </div>
                                <div>
                                    <p class="mb-0">{{ quotation.vendor ? quotation.vendor.name : '' }}</p>
                                </div>
                            </div>
                            <v-divider class="mt-2 mb-2"></v-divider>
                            <div class="d-flex align-center justify-space-between">
                                <p class="mb-2">Quotation Number</p>
                                <p class="mb-2">{{ quotation.quote_number }}</p>
                            </div>
                            <div class="d-flex align-center justify-space-between">
                                <p class="mb-2">Status</p>
                                <v-chip :color="quotation.status == 'confirmed' ? 'success' : 'warning'"
                                    class="text-capitalize">{{
                                        quotation.status }}</v-chip>
                            </div>
                            <div v-if="quotation.description">
                                <p class="mb-2">Description</p>
                                <div :v-html="quotation.description"></div>
                            </div>
                            <h4 class="primary--text">Services</h4>
                            <v-divider class="mb-2 mt-2"></v-divider>
                            <div class="mb-4">
                                <div v-for="(service, index) in quotation.services" :key="index" class="service_list">
                                    <div class="d-flex">
                                        <div style="width: 20px;">
                                            <p>{{ index + 1 }}.</p>
                                        </div>
                                        <div class="">
                                            <p class="mb-0 primary--text">
                                                {{ service.name }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <v-divider></v-divider>
                            <div>
                                <div class="d-flex py-4">
                                    <p class="mb-0">Total</p>
                                    <h4 class="ms-auto mb-0">{{ formatAmount(quotation.total) }}</h4>
                                </div>
                            </div>



                        </div>
                    </v-card>
                    <div class="text-center pt-4" v-if="quotation && quotation.status == 'new'">
                        <v-btn size="large" color="primary" @click="openConfirm(quotation)" rounded>Confirm</v-btn>

                    </div>
                </v-col>
            </div>
        </div>
        <div v-else>
            <p>no quotation</p>
        </div>
        <modal-template ref="globalModal" @close="handleClose"></modal-template>
    </v-container>
</template>
<script>

import { formatAmount } from '@utils/helpers'
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
        this.fetchQuotation();
    },
    methods: {
        formatAmount,
        fetchQuotation() {
            this.$axios.get("/customers/quotations/" + this.quotation_id)
                .then((resp) => {
                    this.quotation = resp.data;
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
        // ModalQuotationConfirm: () => import('./modal/ModalQuotationConfirm.vue')
    },
}
</script>
<style lang="scss" scoped>
.service_list_wrapper {
    .service_list {
        padding-top: 10px;
        padding-bottom: 10px;
        text-transform: capitalize;
    }

    .service_list:not(:last-child) {
        border-bottom: 1px dashed #dadada;
    }
}
</style>