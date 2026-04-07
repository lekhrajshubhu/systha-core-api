<template>
    <v-container fluid class="mt-4">
        <v-data-table :headers="headers" :items="invoices" :search="search" item-value="id" class="elevation-0"
            :loading="loading" loading-text="Loading invoices..." dense>
            <!-- Top Bar -->
            <template #top>
                <v-toolbar flat class="px-4">
                    <v-text-field v-model="search" placeholder="Search invoices..." density="compact" variant="outlined"
                        prepend-inner-icon="mdi-magnify" style="max-width: 300px" hide-details />
                    <v-spacer />
                    <v-btn color="primary" @click="createNewInvoice">New Invoice</v-btn>
                    <v-btn class="ml-2" color="secondary" @click="fetchInvoices">Refresh</v-btn>
                </v-toolbar>
            </template>

            <!-- Serial Number -->
            <!-- <template #item.sn="{ index }">
                {{ index + 1 }}
            </template> -->
            <template #item.amount="{ item }">
                {{ formatAmount(item.amount) }}
            </template>
            <template #item.time_ago="{ item }">
                {{ formatTimeAgo(item.created_at) }}
            </template>
            <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
            </template>
            <template #item.appointment="{ item }">
                <span v-if="item.appointment">
                    {{ item?.appointment?.appointment_no }}
                </span>
                <span v-else>
                    n/a
                </span>
            </template>

            <!-- Status Chip -->
            <template #item.payment_id="{ item }">
                <v-chip :color="item.payment_id ? 'green' : 'red'" size="small">
                    {{ item.payment_id ? 'Paid' : 'Unpaid' }}
                </v-chip>
            </template>

            <!-- Actions -->
            <template #item.actions="{ item }">
                <v-btn icon @click="viewInvoice(item)" elevation="0" title="View Invoice">
                    <v-icon color="primary">mdi-eye</v-icon>
                </v-btn>
            </template>
        </v-data-table>
    </v-container>
</template>

<script>
import { formatAmount, formatDate, formatTimeAgo } from '@utils/helpers'
export default {
    data() {
        return {
            search: '',
            loading: false,
            headers: [
                // { title: 'S.N.', key: 'sn' },
                { title: 'Invoice#', key: 'invoice_no' },
                { title: 'Invoice Date', key: 'created_at' },
                { title: 'Appointment#', key: 'appointment' },
                // { title: 'Client Name', key: 'client_name' },
                { title: 'Amount', key: 'amount' },
                { title: 'Status', key: 'payment_id' },
                // { title: 'Status', key: 'status' },
                { title: 'Time', key: 'time_ago' },
                { title: 'Actions', key: 'actions', sortable: false },
            ],
            invoices: [],
        };
    },
    computed: {
        // async filteredInvoices() {
        //     // return this.invoices;

        // },
    },
    mounted() {
        this.fetchInvoices();
    },

    methods: {
        formatAmount,
        formatDate,
        formatTimeAgo,
        createNewInvoice() {
            alert('Redirect to New Invoice form');
        },
        viewInvoice(invoice) {
            alert(`Viewing invoice ${invoice.invoice_no}`);
        },
        async fetchInvoices() {
            this.loading = true;
            const resp = await this.$axios.get('/invoices');
            this.loading = false;
            this.invoices = resp.data;
            console.log(this.invoices);
        },
    },
};
</script>
