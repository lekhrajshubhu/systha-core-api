<template>
    <v-container fluid>
			<v-data-table-server
				:headers="headers"
				:items="inquiries"
				:items-length="total"
				:search="search"
				:items-per-page="itemsPerPage"
				:page="page"
				item-value="id"
				class="mt-4"
				:show-select="false"
				:loading="loading"
				loading-text="Loading your requests..."
				dense
				@update:options="onOptionsUpdate"
			>
			<!-- Top bar -->
			<template #top>
				<v-toolbar flat class="px-4">
					<v-text-field v-model="search" placeholder="Search your requests..." density="compact"
						variant="outlined" prepend-inner-icon="mdi-magnify" style="max-width: 300px" hide-details />
					<v-select class="ml-4 text-capitalize elevation-0" v-model="selected_status" clearable
						label="Select Status" :items="status_list" style="max-width: 300px" density="compact"
						variant="outlined" hide-details />

					<v-spacer></v-spacer>
					<!-- <v-btn color="primary" @click="createNewInquiry">New Inquiry</v-btn> -->
					<v-btn color="secondary" class="ml-2" @click="refreshInquiries">Refresh</v-btn>
				</v-toolbar>
			</template>

			<!-- Serial Number -->
			<template v-slot:[`item.sn`]="{ index }">
				{{ index + 1 }}
			</template>
			<template v-slot:[`item.inquiry_no`]="{ item }">
				<strong class="">{{ item.inquiry_no }}</strong>
			</template>
			<template v-slot:[`item.created_at`]="{ item }">
				{{ formatDate(item.created_at) }}
			</template>
			<template v-slot:[`item.vendor.name`]="{ item }">
				<div class="d-flex align-center">
					<v-avatar size="24" class="me-2" tile>
						<v-img v-if="item.vendor?.logo" :src="item.vendor.logo" alt="Vendor logo" cover></v-img>
						<v-icon v-else color="grey">mdi-storefront-outline</v-icon>
					</v-avatar>
					<span class="text-capitalize">{{ item.vendor?.name }}</span>
				</div>
			</template>
			<template v-slot:[`item.remarks`]="{ item }">
				{{ formatTimeAgo(item.created_at) }}
			</template>

			<!-- Status Chip -->
			<template v-slot:[`item.status`]="{ item }">
				<v-chip class="text-capitalize" :color="getInquiryStatusColor(item.status)" size="small">
					{{ item.status }}
				</v-chip>
			</template>
			<template v-slot:[`item.service_count`]="{ item }">
				<span>
					{{ item.service_count }} Services
				</span>
			</template>
			<template v-slot:[`item.quotes_count`]="{ item }">
				<span class="text-primary">
					{{ item.quotes_count }} Quotations
				</span>
			</template>
			<template v-slot:[`item.preferred_date`]="{ item }">
				<span>
					{{ formatDate(item.preferred_date) }}
				</span>

			</template>
			<template v-slot:[`item.preferred_time`]="{ item }">

				<span class="ml-3">
					{{ formatTime(item.preferred_time) }}
				</span>
			</template>

			<!-- Actions -->
			<template v-slot:[`item.actions`]="{ item }">
				<v-btn icon size="x-small" variant="tonal" color="primary" :to="{ name: 'globalInquiryDetailPage', params: { id: item.id } }" elevation="0" title="View Inquiry">
					<v-icon color="primary">mdi-eye</v-icon>
				</v-btn>
			</template>
			</v-data-table-server>
		</v-container>
</template>
<script>
import { formatDate, formatTime, formatDateTime, formatTimeAgo, getInquiryStatusColor } from '@utils/helpers';
export default {
	data() {
		return {
			status_list: ['New', 'Quoted','Confirmed'],
			selected_status: '',
			date_filter: '',
			search: '',
			loading: false,
			itemsPerPage: 10,
			page: 1,
			total: 0,
			sortBy: null,
			sortOrder: null,
			currentCustomerId: null,
			headers: [
				// { title: 'S.N.', key: 'sn' },
				{ title: 'Request Date', key: 'created_at' },
				{ title: 'Request#', key: 'inquiry_no' },
				{ title: 'Vendor', key: 'vendor.name' },
				{ title: 'Quotations', key: 'quotes_count' },
				{ title: 'Services', key: 'service_count' },
				{ title: 'Status', key: 'status' },
				{ title: 'Preferred Date', key: 'preferred_date' },
				{ title: 'Preferred Time', key: 'preferred_time' },
				{ title: 'Remarks', key: 'remarks' },
				{ title: 'Actions', key: 'actions', sortable: false },
			],
				inquiries: [],

			};
		},
	computed: {},
	methods: {
		formatDate,
		formatTime,
		formatDateTime,
		formatTimeAgo,
		getInquiryStatusColor,


			async fetchInquiries() {
				try {
					this.loading = true;
					const resp = await this.$axios.get('/inquiries', {
						params: {
							page: this.page,
							per_page: this.itemsPerPage,
							search: this.search,
							status: this.selected_status || null,
							sort_by: this.sortBy,
							sort_order: this.sortOrder,
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

					this.inquiries = rows;
					this.total = response?.meta?.total ?? payload?.meta?.total ?? rows.length;
					// Set logged-in client ID if needed
					if (this.inquiries.length && this.inquiries[0].client_id) {
						this.currentCustomerId = this.inquiries[0].client_id;
					}
				} catch (err) {
					this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
				} finally {
					this.loading = false;
				}
			},
			refreshInquiries() {
				this.page = 1;
				this.fetchInquiries();
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

				this.fetchInquiries();
			},
			createNewInquiry() {
			alert('Redirect to New Inquiry form');
			// or this.$router.push('/inquiries/create');
		},
		viewInquiry(inquiry) {
			alert(`Viewing inquiry ${inquiry.inquiry_no}`);
			// or this.$router.push(`/inquiries/${inquiry.id}`);
		},
		getStatusColor(status) {
			switch (status.toLowerCase()) {
				case 'open':
					return 'green';
				case 'closed':
					return 'grey';
				case 'pending':
					return 'orange';
				default:
					return 'blue';
			}
		},
	},
};
</script>
