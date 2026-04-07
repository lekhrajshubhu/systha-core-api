<template>
    <v-container fluid>
        <v-data-table-server
            :headers="headers"
            :items="inquiries"
            :items-length="total"
            :items-per-page="itemsPerPage"
            :page="page"
            item-value="id"
            class="mt-4"
            :show-select="false"
            :loading="loading"
            loading-text="Loading your email logs..."
            dense
            @update:options="onOptionsUpdate"
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

            <template v-slot:[`item.sn`]="{ index }">
                {{ serialNumber(index) }}
            </template>

       
            <template v-slot:[`item.subject`]="{ item }">
                <div style="min-width: max-content;">
                    <span>{{ item.subject }}</span>
                </div>
            </template>
            <template v-slot:[`item.created_at`]="{ item }">
                {{ formatDate(item.created_at) }}
            </template>

            <template v-slot:[`item.email_type`]="">
                <div><span>Sent</span></div>
            </template>

            <template v-slot:[`item.remarks`]="{ item }">
                {{ formatTimeAgo(item.created_at) }}
            </template>

            <template v-slot:[`item.sent_status`]="{ item }">
                <v-chip class="text-capitalize" :color="getInquiryStatusColor(item.sent_status)" size="small">
                    {{ item.sent_status }}
                </v-chip>
            </template>

            <template v-slot:[`item.actions`]="{}">
                <v-btn size="x-small" variant="tonal" color="primary" icon elevation="0" title="View Inquiry">
                    <v-icon color="primary">mdi-eye</v-icon>
                </v-btn>
            </template>
        </v-data-table-server>
    </v-container>
</template>

<script>
import { mapStores } from 'pinia'
import { useGlobalStore } from '@/global_clients/stores/account'
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
            selectedStatus: null,
            statusList: ['sent', 'failed', 'queued', 'pending'],
            loading: false,
            page: 1,
            itemsPerPage: 12,
            total: 0,
            sortBy: null,
            sortOrder: null,
            initialized: false,
            headers: [
                { title: 'SN', key: 'sn', sortable: false },
                { title: 'Date', key: 'created_at' },
                { title: 'Status', key: 'sent_status' },
                { title: 'Email Type', key: 'email_type' },
                { title: 'Subject', key: 'subject' },
                { title: 'Actions', key: 'actions', sortable: false }
            ]
        }
    },
    computed: {
        ...mapStores(useGlobalStore),
    },
    watch: {
        search() {
            this.page = 1
            this.fetchEmailLogs()
        },
        selectedStatus() {
            this.page = 1
            this.fetchEmailLogs()
        }
    },
    methods: {
        formatDate,
        formatTimeAgo,
        getInquiryStatusColor,
        serialNumber(index) {
            return (this.page - 1) * this.itemsPerPage + index + 1
        },
        async fetchEmailLogs() {
            try {
                this.loading = true
                const resp = await axios.get('/email-logs', {
                    params: {
                        page: this.page,
                        per_page: this.itemsPerPage,
                        search: this.search || null,
                        status: this.selectedStatus || null,
                        sort_by: this.sortBy,
                        sort_order: this.sortOrder,
                    },
                })
                const response = resp ?? {}
                const payload = response?.data ?? response ?? {}

                let rows = []
                if (Array.isArray(response?.data)) {
                    rows = response.data
                } else if (Array.isArray(payload?.data)) {
                    rows = payload.data
                } else if (Array.isArray(payload)) {
                    rows = payload
                }

                this.inquiries = rows
                this.total = response?.meta?.total ?? payload?.meta?.total ?? rows.length
            } catch (err) {
                console.error(err?.response?.data?.message || 'Failed to fetch inquiries')
            } finally {
                this.loading = false
            }
        },
        refreshInquiries() {
            this.page = 1
            this.fetchEmailLogs()
        },
        onOptionsUpdate(options) {
            const nextPage = options.page ?? this.page
            const nextItemsPerPage = options.itemsPerPage ?? this.itemsPerPage
            const nextSortBy = Array.isArray(options.sortBy) && options.sortBy.length
                ? options.sortBy[0]
                : null

            const hasChanged = !this.initialized
                || nextPage !== this.page
                || nextItemsPerPage !== this.itemsPerPage
                || (nextSortBy?.key ?? null) !== this.sortBy
                || (nextSortBy?.order ?? null) !== this.sortOrder

            this.page = nextPage
            this.itemsPerPage = nextItemsPerPage
            this.sortBy = nextSortBy ? (nextSortBy.key ?? null) : null
            this.sortOrder = nextSortBy ? (nextSortBy.order ?? null) : null
            this.initialized = true

            if (hasChanged) {
                this.fetchEmailLogs()
            }
        },
        createNewInquiry() {
            console.log('Redirect to New Inquiry form')
        }
    },
}
</script>
