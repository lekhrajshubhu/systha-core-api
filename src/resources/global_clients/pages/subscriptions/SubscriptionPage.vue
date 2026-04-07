<template>
    <v-container fluid>
        <v-data-table-server
            :headers="headers"
            :items="subscriptions"
            :items-length="total"
            :search="search"
            :items-per-page="itemsPerPage"
            :page="page"
            item-value="id"
            class="mt-4"
            :loading="loading"
            loading-text="Loading your subscriptions..."
            dense
            @update:options="onOptionsUpdate"
        >
            <!-- Top bar -->
            <template #top>
                <v-toolbar flat class="px-4">
                    <v-container fluid class="d-flex pa-0 align-center">

                        <v-text-field v-model="search" placeholder="Search..." density="compact" variant="outlined"
                            prepend-inner-icon="mdi-magnify" class="mx-2" style="max-width: 300px"
                            hide-details></v-text-field>

                        <v-select v-model="selectedYear" variant="outlined" density="compact" :items="years"
                            label="Select Year" dense outlined class="mx-2" hide-details
                            style="max-width: 220px"></v-select>
                        <v-spacer></v-spacer>
                        <v-btn color="primary" variant="tonal" class="mx-2" @click="refreshSubscriptions"> Refresh</v-btn>
                    </v-container>
                </v-toolbar>

            </template>

            <!-- Serial Number -->
            <template v-slot:[`item.sn`]="{ index }">
                <div class="d-flex align-center">
                    <span>{{ serialNumber(index) }}</span>
                </div>
            </template>

            <!-- Subscription Number -->
            <template v-slot:[`item.subs_no`]="{ item }">
                <div class="d-flex align-center" style="min-width: 100px;">
                    <strong>{{ item.subs_no }}</strong>
                </div>
            </template>

            <!-- Start Date -->
            <template v-slot:[`item.package`]="{ item }">
                <div class="d-flex align-center" style="min-width: 200px;">
                    <span>{{ item.package?.name || 'N/A' }}</span>
                </div>
            </template>

            <!-- End Date -->
            <template v-slot:[`item.package_type`]="{ item }">
                <div class="d-flex align-center" style="min-width: 450px;">

                    <p>{{ item.package_type?.name || 'N/A' }} ( per {{ item.package_type?.duration || '' }} {{
                        item.package_type?.type_name || '' }})</p>
                </div>
            </template>


            <!-- Status Chip -->
            <template v-slot:[`item.status`]="{ item }">
                <v-chip class="text-capitalize" :color="getStatusColor(item.status)" size="small">
                    {{ item.status }}
                </v-chip>
            </template>
            <template v-slot:[`item.amount`]="{ item }">
                <div style="min-width: 30px;" class="text-right">
                    <span>{{ formatAmount(item.amount) }}</span>
                </div>
            </template>

            <!-- Actions -->
            <template v-slot:[`item.actions`]="{ item }">
                 <div style="min-width: 150px;">
                     <v-btn size="x-small" variant="tonal" color="primary" icon :to="{ name: 'globalSubscriptionDetailPage', params: { id: item.id } }" elevation="0"
                         title="View Subscription">
                         <v-icon color="primary">mdi-eye</v-icon>
                     </v-btn>
                 </div>
            </template>
        </v-data-table-server>
    </v-container>
</template>

<script>
import { formatDate, getStatusColor, formatAmount } from '@utils/helpers';

export default {
    data() {
        return {
            loading: false,
            search: '',
            itemsPerPage: 10,
            page: 1,
            total: 0,
            sortBy: null,
            sortOrder: null,
            error: '',
            headers: [
                { title: 'SN', key: 'sn' },
                { title: 'Subscription#', key: 'subs_no' },
                { title: 'Status', key: 'status' },
                { title: 'Package', key: 'package' },
                { title: 'Subscription Plan', key: 'package_type' },
                { title: 'Amount', key: 'amount', align: 'end' },
                // { title: 'Status', key: 'status' },
                { title: 'Actions', key: 'actions', sortable: false, align: 'center' },
            ],
            subscriptions: [],
            selectedYear:'',
        };
    },
    computed: {
        years() {
            const startYear = 2024;
            const currentYear = new Date().getFullYear();
            const endYear = currentYear + 5;
            let list = [];
            for (let y = startYear; y <= endYear; y++) {
                list.push(y.toString()); // Use strings or numbers, both work
            }
            return list;
        },
    },
    methods: {
        formatAmount,
        formatDate,
        getStatusColor,
        serialNumber(index) {
            return (this.page - 1) * this.itemsPerPage + index + 1;
        },
        async fetchSubscriptions() {
            try {
                this.loading = true;
                const resp = await this.$axios.get('/subscriptions', {
                    params: {
                        page: this.page,
                        per_page: this.itemsPerPage,
                        search: this.search,
                        sort_by: this.sortBy,
                        sort_order: this.sortOrder,
                        year: this.selectedYear || null,
                    },
                });
                const response = resp ?? {};
                const payload = response?.data ?? response ?? {};

                let rows = [];
                if (Array.isArray(response?.data)) {
                    rows = response.data;
                } else if (Array.isArray(payload?.data)) {
                    rows = payload.data;
                } else if (Array.isArray(payload)) {
                    rows = payload;
                }

                const total = response?.meta?.total ?? payload?.meta?.total ?? rows.length;

                this.subscriptions = rows;
                this.total = total;
            } catch (err) {
                this.error =
                    err.response?.data?.message || 'Something went wrong. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        refreshSubscriptions() {
            this.page = 1;
            this.fetchSubscriptions();
        },
        onOptionsUpdate(options) {
            const nextPage = options.page ?? this.page;
            const nextItemsPerPage = options.itemsPerPage ?? this.itemsPerPage;
            const nextSortBy = Array.isArray(options.sortBy) && options.sortBy.length
                ? options.sortBy[0]
                : null;

            this.page = nextPage;
            this.itemsPerPage = nextItemsPerPage;
            this.sortBy = nextSortBy ? (nextSortBy.key ?? null) : null;
            this.sortOrder = nextSortBy ? (nextSortBy.order ?? null) : null;

            this.fetchSubscriptions();
        },
        viewSubscription(subscription) {
            console.log(`Viewing subscription ${subscription.subscription_no}`);
            // Replace with actual navigation or modal
        },
    },
};
</script>
