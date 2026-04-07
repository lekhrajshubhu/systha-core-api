<template>
    <div>
        <v-data-table
            :headers="headers"
            :items="filteredInquiries"
            :search="search"
            item-value="id"
            class="mt-4"
            :show-select="false"
            :loading="loading"
            loading-text="Loading your email logs..."
            dense
        >
            <template #top>
                <v-toolbar flat class="px-4">
                    <v-text-field
                        v-model="search"
                        placeholder="Search ..."
                        density="compact"
                        variant="outlined"
                        prepend-inner-icon="mdi-magnify"
                        style="max-width: 300px"
                        hide-details
                    />

                    <v-select
                        class="ml-4 text-capitalize elevation-0"
                        v-model="selectedStatus"
                        clearable
                        label="Select Status"
                        :items="statusList"
                        style="max-width: 300px"
                        density="compact"
                        variant="outlined"
                        hide-details
                    />

                    <v-spacer></v-spacer>
                    <v-btn color="primary" @click="createNewInquiry">New Inquiry</v-btn>
                    <v-btn color="secondary" class="ml-2" @click="refreshInquiries">Refresh</v-btn>
                </v-toolbar>
            </template>

            <template #item.sn="{ index }">
                {{ index + 1 }}
            </template>

            <template #item.inquiry_no="{ item }">
                <strong class="text-info">{{ item.inquiry_no }}</strong>
            </template>

            <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
            </template>

            <template #item.email_type="{ item }">
                <div><span>Sent</span></div>
            </template>

            <template #item.remarks="{ item }">
                {{ formatTimeAgo(item.created_at) }}
            </template>

            <template #item.sent_status="{ item }">
                <v-chip class="text-capitalize" :color="getInquiryStatusColor(item.sent_status)" size="small">
                    {{ item.sent_status }}
                </v-chip>
            </template>

            <template #item.actions="{ item }">
                <v-btn icon :to="{ name: 'vendorClientInquiryDetailPage', params: { id: item.id } }" elevation="0" title="View Inquiry">
                    <v-icon color="primary">mdi-eye</v-icon>
                </v-btn>
            </template>
        </v-data-table>
    </div>
</template>

<script>
import { mapStores } from 'pinia'
import { useVendorClientStore } from '@/vendor_clients/stores/account'
import axios from '@shared/axios.config'

import {
    formatDate,
    formatTimeAgo,
    getInquiryStatusColor
} from '@utils/helpers'

export default {
    name: 'EmailLogsTable',
    data() {
        return {
            inquiries: [],
            search: '',
            selectedStatus: 'New',
            statusList: ['New', 'Quoted', 'Confirmed', 'Cancelled'],
            loading: false,
            currentCustomerId: null,
            headers: [
                { title: 'Date', key: 'created_at' },
                { title: 'Status', key: 'sent_status' },
                { title: 'Email Type', key: 'email_type' },
                { title: 'Subject', key: 'subject' },
                { title: 'Actions', key: 'actions', sortable: false }
            ]
        }
    },
    computed: {
        ...mapStores(useVendorClientStore),
        filteredInquiries() {
            return this.currentCustomerId
                ? this.inquiries.filter(inq => inq.client_id === this.currentCustomerId)
                : this.inquiries
        }
    },
    methods: {
        formatDate,
        formatTimeAgo,
        getInquiryStatusColor,
        async fetchEmailLogs() {
            try {
                this.loading = true
                const resp = await axios.get('/email-logs')
                this.inquiries = resp.data
            } catch (err) {
                console.error(err?.response?.data?.message || 'Failed to fetch inquiries')
            } finally {
                this.loading = false
            }
        },
        refreshInquiries() {
            this.fetchEmailLogs()
        },
        createNewInquiry() {
            alert('Redirect to New Inquiry form')
            // this.$router.push('/inquiries/create')
        }
    },
    mounted() {
        this.fetchEmailLogs()
        this.userStore.fetchProfile()
        console.log(this.userStore.email)
    }
}
</script>
