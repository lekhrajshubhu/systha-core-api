<template>
  <v-container fluid>
    <v-data-table :headers="headers" :items="payments" :search="search" item-value="id" class="elevation-0"
      :loading="loading" loading-text="Loading payments..." dense>
      <!-- Top bar -->
      <template #top>
        <v-toolbar flat class="px-4">
          <v-text-field v-model="search" placeholder="Search payments..." density="compact" variant="outlined"
            prepend-inner-icon="mdi-magnify" style="max-width: 300px" hide-details />
          <v-spacer />
          <v-btn color="primary" @click="createNewPayment">New Payment</v-btn>
          <v-btn class="ml-2" color="secondary" @click="refreshPayments">Refresh</v-btn>
        </v-toolbar>
      </template>

      <!-- S.N. -->
      <!-- <template #item.sn="{ index }">
        {{ index + 1 }}
      </template> -->

      <!-- Status chip -->
      <template #item.status="{ item }">
        <v-chip :color="getStatusColor(item.status)" size="small">
          {{ item.status }}
        </v-chip>
      </template>
      <template #item.reference="{ item }">

        <span v-if="item?.paymentable?.appointment_no">
          {{ item.paymentable.appointment_no }}
        </span>
        <span v-else-if="item?.paymentable?.subs_no">
          {{ item.paymentable.subs_no }}
        </span>
        <span v-else>
          N/A
        </span>

      </template>

      <!-- Date format -->
      <template #item.created_at="{ item }">
        {{ formatDate(item.created_at) }}
      </template>
      <template #item.payment_type="{ item }">
        {{ item && item.payment_type ? item.payment_type:'n/a' }}
      </template>

      <template #item.amount="{ item }">
        {{ formatAmount(item.amount) }}
      </template>

      <!-- Actions -->
      <template #item.actions="{ item }">
        <v-btn icon size="small" @click="viewPayment(item)" elevation="0" title="View Payment">
          <v-icon color="primary">mdi-eye</v-icon>
        </v-btn>
      </template>
    </v-data-table>
  </v-container>
</template>

<script>
import { formatAmount, formatDate } from '@utils/helpers'
export default {
  data() {
    return {
      search: '',
      loading: false,
      headers: [
        // { title: 'S.N.', key: 'sn' },
        { title: 'Payment No.', key: 'payment_code' },
        { title: 'Reference#', key: 'reference' },
        { title: 'Amount', key: 'amount' },
        { title: 'Paid By', key: 'payment_type' },
        { title: 'Date', key: 'created_at' },
        { title: 'Actions', key: 'actions', sortable: false },
      ],
      payments: [],
    };
  },
  computed: {
    filteredPayments() {
      return this.payments;
    },
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    formatAmount,
    formatDate,
    async fetchData() {
      this.loading = true;
      const resp = await this.$axios.get('/payments');
      this.loading = false;
      // console.log(resp.data);
      this.payments = resp.data;
    },
    // formatDate(dateStr) {
    //   const date = new Date(dateStr);
    //   const month = String(date.getMonth() + 1).padStart(2, '0');
    //   const day = String(date.getDate()).padStart(2, '0');
    //   const year = date.getFullYear();
    //   return `${month}/${day}/${year}`;
    // },
    getStatusColor(status) {
      switch (status?.toLowerCase()) {
        case 'success':
          return 'green';
        case 'failed':
          return 'red';
        case 'pending':
          return 'orange';
        default:
          return 'grey';
      }
    },
    createNewPayment() {
      alert('Redirect to New Payment form');
    },
    viewPayment(payment) {
      alert(`Viewing payment ${payment.payment_no}`);
    },
    refreshPayments() {
      this.loading = true;
      setTimeout(() => {
        this.loading = false;
      }, 1000);
    },
  },
};
</script>
