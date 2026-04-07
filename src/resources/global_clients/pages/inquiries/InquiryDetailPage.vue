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
                <inquiry-hero
                    :title="heroTitle"
                    :subtitle="heroSubtitle"
                    :vendor-name="vendorName"
                    :vendor-address="vendorAddress"
                    :vendor-logo="vendorLogo"
                />

                <inquiry-service-list class="mt-4" :services="services" />
                <v-row class="mt-4">
                    <v-col cols="12" md="7">
                        <inquiry-address-card :lines="addressLines" />
                    </v-col>
                    <v-col cols="12" md="5">
                        <inquiry-date-time-card :dateTime="preferredDateTime" />
                    </v-col>
                </v-row>
                <inquiry-quote-list class="mt-4" :quotes="quotes" />
            </v-col>

            <v-col cols="12" lg="5">
                <section-notification />
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import InquiryHero from '../../components/InquiryHero.vue';
import InquiryServiceList from '../../components/InquiryServiceList.vue';
import InquiryAddressCard from '../../components/InquiryAddressCard.vue';
import InquiryDateTimeCard from '../../components/InquiryDateTimeCard.vue';
import InquiryQuoteList from '../../components/InquiryQuoteList.vue';
import SectionNotification from '@shared/components/notification/NotificationList.vue';

export default {
    data() {
        return {
            inquiry: null,
            inquiry_id: null,
            loading: false,
            error: null,
        };
    },
    computed: {
        heroTitle() {
            const id = this.inquiry?.inquiry_no || '—';
            return `Inquiry ${id}`;
        },
        heroSubtitle() {
            const created = this.formatDateLong(this.inquiry?.created_at);
            const status = this.inquiry?.status ? this.capitalize(this.inquiry.status) : '—';
            return `Created ${created} • ${status}`;
        },
        vendorName() {
            return this.inquiry?.vendor?.name || '—';
        },
        vendorAddress() {
            const address = this.inquiry?.service_address || {};
            const parts = [address.add1, address.add2, address.city, address.state, address.zip, address.country]
                .filter((part) => part && String(part).trim().length);
            return parts.join(', ') || '—';
        },
        vendorLogo() {
            return this.inquiry?.vendor?.logo || '';
        },
        services() {
            const services = this.inquiry?.services || [];
            return services.map((service) => ({
                id: service.id,
                title: service.name,
                meta: `1 Visit • Est. ${this.formatCurrency(service.price)}`,
                tag: 'Service',
            }));
        },
        addressLines() {
            const address = this.inquiry?.service_address || {};
            return [
                address.add1,
                [address.city, address.state, address.zip].filter(Boolean).join(', '),
                address.country,
            ].filter((line) => line && String(line).trim().length);
        },
        quotes() {
            const quotes = this.inquiry?.quotations || [];
            return quotes.map((quote) => ({
                id: quote.id,
                code: quote.quote_number,
                meta: `${this.formatDateShort(quote.created_at)} • ${this.capitalize(quote.status)}`,
                status: quote.status || 'pending',
            }));
        },
        preferredDateTime() {
            return [this.inquiry?.preferred_date || '', this.inquiry?.preferred_time || ''];
        },
    },
    mounted() {
        this.inquiry_id = this.$route.params.id;
        this.fetchInquiry();
    },
    methods: {
        async fetchInquiry() {
            try {
                this.loading = true;
                const resp = await this.$axios.get('/inquiries/' + this.inquiry_id);
                this.inquiry = resp?.data?.data ?? resp?.data ?? null;
            } catch (err) {
                this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        formatDateLong(value) {
            if (!value) return '—';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
            }).format(date);
        },
        formatDateShort(value) {
            if (!value) return '—';
            const date = new Date(value);
            if (Number.isNaN(date.getTime())) return value;
            return new Intl.DateTimeFormat('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric',
            }).format(date);
        },
        formatCurrency(value) {
            const amount = Number(value);
            if (Number.isNaN(amount)) return '$0.00';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
            }).format(amount);
        },
        capitalize(value) {
            return String(value || '').replace(/_/g, ' ').replace(/\b\w/g, (m) => m.toUpperCase());
        },
    },
    components: {
        InquiryHero,
        InquiryServiceList,
        InquiryAddressCard,
        InquiryDateTimeCard,
        InquiryQuoteList,
        SectionNotification,
    },
};
</script>
