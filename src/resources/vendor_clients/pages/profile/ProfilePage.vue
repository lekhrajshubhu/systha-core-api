<template>
	<v-container fluid class="py-4">
        <v-snackbar v-model="showSuccess" color="success" timeout="2500">
            {{ successMessage }}
        </v-snackbar>

		<v-card class="pa-4 mb-4" elevation="0">
			<div class="d-flex flex-column flex-sm-row align-sm-center">
				<v-avatar size="88" class="mr-sm-4 mb-3 mb-sm-0">
					<v-img :src="profile.avatar" alt="Profile Avatar" contain />
				</v-avatar>
				<div>
					<h2 class="mb-1">{{ profile.name }}</h2>
					<p class="mb-1 text-medium-emphasis">{{ profile.email }}</p>
					<p class="mb-0 text-medium-emphasis">{{ fullAddress }}</p>
				</div>
			</div>
		</v-card>

		<v-card elevation="0">
			<v-tabs v-model="activeTab" color="primary">
				<v-tab v-for="item in tabItems" :key="item.value" :value="item.value">
					{{ item.label }}
				</v-tab>
			</v-tabs>

			<v-divider />

			<div>
				<component
                    :is="activeComponent"
                    :profile="profile"
                    :errors="passwordErrors"
                    :loading="activeTabLoading"
                    :reset-key="passwordResetKey"
                    :documents="documents"
                    @update-basic-info="updateBasicInfo"
                    @update-address-info="updateAddressInfo"
                    @update-password-info="updatePasswordInfo"
                />
			</div>
		</v-card>
	</v-container>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref } from 'vue';
import ProfileTabBasicInfo from '@shared/components/profile/ProfileTabBasicInfo.vue';
import ProfileTabAddressInfo from '@shared/components/profile/ProfileTabAddressInfo.vue';
import ProfileTabPassword from '@shared/components/profile/ProfileTabPassword.vue';
import ProfileTabDocuments from '@shared/components/profile/ProfileTabDocuments.vue';

const activeTab = ref('basic');
const tabItems = [
	{ value: 'basic', label: 'Basic Info' },
	{ value: 'address', label: 'Address Info' },
	{ value: 'password', label: 'Password' },
	{ value: 'documents', label: 'Documents' },
];
const tabComponents = {
	basic: ProfileTabBasicInfo,
	address: ProfileTabAddressInfo,
	password: ProfileTabPassword,
	documents: ProfileTabDocuments,
};
const { proxy } = getCurrentInstance();
const showSuccess = ref(false);
const successMessage = ref('');
const passwordErrors = ref({});
const passwordUpdating = ref(false);
const passwordResetKey = ref(0);
const addressUpdating = ref(false);
const basicUpdating = ref(false);

const profile = ref({
	id: null,
	avatar: 'https://i.pravatar.cc/200?img=12',
	name: 'John Doe',
	fname: '',
	lname: '',
	email: 'john.doe@example.com',
	phone: '+1 (555) 123-4567',
	address: {
		line1: '123 Main Street',
		line2: 'Apt 4B',
		city: 'Dallas',
		state: 'TX',
		zip: '75001',
	},
});

const documents = ref([
	{ name: 'Government ID.pdf', date: 'Uploaded on Jan 14, 2026' },
	{ name: 'Utility Bill.pdf', date: 'Uploaded on Jan 03, 2026' },
]);

const fullAddress = computed(() => {
	const address = profile.value.address;
	return [address.line1, address.line2, address.city, address.state, address.zip]
		.filter(Boolean)
		.join(', ');
});

const activeComponent = computed(() => tabComponents[activeTab.value] || ProfileTabBasicInfo);
const activeTabLoading = computed(() => {
	if (activeTab.value === 'password') return passwordUpdating.value;
	if (activeTab.value === 'address') return addressUpdating.value;
	if (activeTab.value === 'basic') return basicUpdating.value;
	return false;
});

const mapProfileResponse = (raw) => {
	const data = raw || {};
	const contact = data.contact || {};
	const address = data.address || {};

	const resolvedName =
		data.fullName ||
		data.name ||
		[data.fname, data.lname].filter(Boolean).join(' ').trim() ||
		[contact.fname, contact.lname].filter(Boolean).join(' ').trim() ||
		profile.value.name;

	return {
		id: data.id || profile.value.id,
		avatar: data.avatar || data.profile_pic || profile.value.avatar,
		name: resolvedName,
		fname: data.fname || contact.fname || profile.value.fname,
		lname: data.lname || contact.lname || profile.value.lname,
		email: data.email || contact.email || profile.value.email,
		phone: data.phone_no || data.mobile_no || contact.mobile_no || contact.phone_no || profile.value.phone,
		address: {
			line1: address.add1 || address.line1 || profile.value.address.line1,
			line2: address.add2 || address.line2 || profile.value.address.line2,
			city: address.city || profile.value.address.city,
			state: address.state || profile.value.address.state,
			zip: address.zip || profile.value.address.zip,
		},
	};
};

const fetchProfile = async () => {
	try {
		const response = await proxy.$axios.get('/profile');
		const payload = response?.data?.data || response?.data || {};
		const mapped = mapProfileResponse(payload);
		profile.value = {
			...profile.value,
			...mapped,
			address: {
				...profile.value.address,
				...mapped.address,
			},
		};
	} catch (error) {
		console.error('Failed to fetch profile', error);
	}
};

const updateBasicInfo = async (form) => {
	const payload = form || profile.value;
	basicUpdating.value = true;

	try {
		await proxy.$axios.put('/profile', {
			id: payload.id,
			fname: payload.fname,
			lname: payload.lname,
			email: payload.email,
			phone: payload.phone,
		});
		successMessage.value = 'Profile updated successfully.';
		showSuccess.value = true;
		await fetchProfile();
	} catch (error) {
		console.error('Failed to update basic info', error);
	} finally {
		basicUpdating.value = false;
	}
};

const updateAddressInfo = async (form) => {
	const payload = form || {};
	addressUpdating.value = true;

	try {
		await proxy.$axios.put('/profile-address', {
			id: payload.id,
			add1: payload.add1,
			add2: payload.add2,
			city: payload.city,
			state: payload.state,
			zip: payload.zip,
		});
		successMessage.value = 'Address updated successfully.';
		showSuccess.value = true;
		await fetchProfile();
	} catch (error) {
		console.error('Failed to update address info', error);
	} finally {
		addressUpdating.value = false;
	}
};

const updatePasswordInfo = async (form) => {
	const payload = form || {};
	passwordErrors.value = {};
	passwordUpdating.value = true;

	try {
		await proxy.$axios.put('/profile-update-password', {
			current_password: payload.current_password,
			new_password: payload.new_password,
			new_password_confirmation: payload.new_password_confirmation,
		});
		successMessage.value = 'Password updated successfully.';
		showSuccess.value = true;
		passwordResetKey.value += 1;
	} catch (error) {
		passwordErrors.value = error?.response?.data?.errors || {};
		console.error('Failed to update password', error);
	} finally {
		passwordUpdating.value = false;
	}
};

onMounted(() => {
	fetchProfile();
});
</script>
