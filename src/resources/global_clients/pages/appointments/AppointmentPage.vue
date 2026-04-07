<template>
    <v-container fluid>
        <div>
            <v-data-table-server :headers="headers" :items="appointments" :items-length="total" :search="search"
                :items-per-page="itemsPerPage" :page="page" item-value="id" :loading="loading"
                loading-text="Loading your appointments..." @update:options="onOptionsUpdate">
                <template #top>
                    <v-toolbar flat class="px-4 bg-white py-4">
                        <v-container fluid class="d-flex pa-0 align-center">
                            <v-text-field v-model="search" placeholder="Search..." density="compact" variant="outlined"
                                prepend-inner-icon="mdi-magnify" class="mx-2" style="max-width: 300px"
                                hide-details></v-text-field>

                            <v-select v-model="selectedPaymentStatus" variant="outlined" density="compact"
                                :items="paymentStatuses" label="Payment Status" dense outlined class="mx-2" hide-details
                                style="max-width: 220px"></v-select>

                            <v-select v-model="selectedStatus" variant="outlined" density="compact"
                                :items="statusOptions" label="Status" dense outlined class="mx-2" hide-details
                                style="max-width: 200px"></v-select>

                            <v-date-input prepend-icon="" v-model="selectedDate" label="Select a date" density="compact"
                                variant="outlined" class="mx-2" hide-details clearable
                                style="max-width: 220px"></v-date-input>
                            <v-btn color="primary" variant="outlined" class="mx-2" @click="refreshAppointments">
                                Refresh</v-btn>
                        </v-container>
                    </v-toolbar>
                    <v-divider></v-divider>
                </template>

                <template v-slot:[`item.appointment_date`]="{ item }">
                    <div class="d-flex align-center">
                        <span>{{ formatDate(item.appointment_date) }}</span>
                    </div>
                </template>
                <template v-slot:[`item.vendor_name`]="{ item }">
                    <div class="d-flex align-center">
                        <v-avatar size="28" class="me-2" tile>
                            <v-img v-if="item.vendor?.logo" :src="item.vendor.logo" alt="Vendor logo" cover></v-img>
                            <v-icon v-else color="grey">mdi-storefront-outline</v-icon>
                        </v-avatar>
                        <span class="text-capitalize">{{ item.vendor?.name }}</span>
                    </div>
                </template>
                <template v-slot:[`item.appointment_no`]="{ item }">
                    <div class="d-flex align-center" style="min-width: 100px;">
                        <span>{{ item.appointment_no }}</span>
                    </div>
                </template>
                <template v-slot:[`item.appointment_time`]="{ item }">
                    <div class="d-flex align-center" style="min-width: 100px;">
                        <span>{{ formatTime(item.appointment_time) }}</span>
                    </div>
                </template>
                <template v-slot:[`item.provider_name`]="{ item }">
                    <div class="d-flex align-center" style="min-width: 150px;">
                        <v-icon class="me-2" color="primary">mdi-account</v-icon>
                        <span>{{ item.provider_name ? item.provider_name : 'Not Assigned' }}</span>
                    </div>
                </template>

                <template v-slot:[`item.status`]="{ item }">
                    <v-chip class="text-capitalize" :color="item.status === 'booked' ? 'green' : 'orange'" size="small">
                        {{ item.status }}
                    </v-chip>
                </template>

                <template v-slot:[`item.is_paid`]="{ item }">
                    <v-chip :color="item.is_paid === 1 ? 'green' : 'red'" size="small" dark>
                        <v-icon left class="mr-1" v-if="item.is_paid === 1">
                            {{ item.is_paid === 1 ? 'mdi-check' : '' }}
                        </v-icon>
                        {{ item.is_paid === 1 ? 'Paid' : 'Unpaid' }}
                    </v-chip>
                </template>

                <template v-slot:[`item.total_info.total_amount`]="{ item }">
                    <span>{{ formatAmount(item.total_info.total_amount) }}</span>
                </template>
                <template v-slot:[`item.actions`]="{ item }">
                    <v-btn icon :to="{ name: 'globalAppointmentDetailPage', params: { id: item.id } }" elevation="0"
                        title="View Appointment">
                        <v-icon color="primary">mdi-eye</v-icon>
                    </v-btn>
                </template>

            </v-data-table-server>
        </div>
    </v-container>
</template>

<script setup>
import { computed, ref } from 'vue';
import { formatDate, formatTime, formatAmount } from '@utils/helpers';
import $axios from '@shared/axios.config';

const itemsPerPage = ref(12);
const page = ref(1);
const total = ref(0);
const loading = ref(false);

const headers = [
    { title: 'Appointment#', key: 'appointment_no', sortable: true },
    { title: 'Vendor', key: 'vendor_name', sortable: false },
    { title: 'Date', key: 'appointment_date', sortable: true },
    { title: 'Time', key: 'appointment_time', sortable: false },
    { title: 'Assigned', key: 'provider_name', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Amount', key: 'total_info.total_amount', sortable: true },
    { title: 'Payment', key: 'is_paid', sortable: false },
    { title: 'Actions', key: 'actions', sortable: false },
];

const appointments = ref([]);
const search = ref("");
const selectedPaymentStatus = ref(null);
const selectedStatus = ref(null);
const selectedDate = ref(null);
const sortBy = ref(null);
const sortOrder = ref(null);
const error = ref("");

const paymentStatuses = computed(() => [
    { title: 'All', value: null },
    { title: 'Paid', value: 1 },
    { title: 'Unpaid', value: 0 },
]);

const statusOptions = computed(() => [
    { title: 'All', value: null },
    { title: 'Booked', value: 'booked' },
    { title: 'Pending', value: 'pending' },
    { title: 'Completed', value: 'completed' },
    { title: 'Cancelled', value: 'cancelled' },
]);

const fetchAppointments = async () => {
    try {
        loading.value = true;
        const selectedDateValue = selectedDate.value
            ? new Date(selectedDate.value).toLocaleDateString('en-CA')
            : null;
        const resp = await $axios.get('/appointments', {
            params: {
                page: page.value,
                per_page: itemsPerPage.value,
                is_paid: selectedPaymentStatus.value,
                status: selectedStatus.value,
                search: search.value,
                date: selectedDateValue,
                sort_by: sortBy.value,
                sort_order: sortOrder.value,
            },
        });
        appointments.value = resp?.data || [];
        total.value = resp?.meta?.total ?? appointments.value.length;
    } catch (err) {
        error.value = err.response?.data?.message || 'Something went wrong. Please try again.';
    } finally {
        loading.value = false;
    }
};

const refreshAppointments = () => {
    page.value = 1;
    fetchAppointments();
};

const onOptionsUpdate = (options) => {
    const nextPage = options.page ?? page.value;
    const nextItemsPerPage = options.itemsPerPage ?? itemsPerPage.value;
    const nextSortBy = Array.isArray(options.sortBy) && options.sortBy.length
        ? options.sortBy[0]
        : null;
    if (nextPage !== page.value || nextItemsPerPage !== itemsPerPage.value) {
        page.value = nextPage;
        itemsPerPage.value = nextItemsPerPage;
    }
    if (nextSortBy) {
        sortBy.value = nextSortBy.key ?? null;
        sortOrder.value = nextSortBy.order ?? null;
    } else {
        sortBy.value = null;
        sortOrder.value = null;
    }
    fetchAppointments();
};

</script>
