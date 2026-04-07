<template>
    <v-app>
        <!-- Navigation Drawer -->
        <v-navigation-drawer v-if="isMobile" :model-value="drawer" @update:model-value="onDrawerUpdate" app temporary
            class="customer-drawer" elevation="0">
            <div class="pt-4">
                <div>
                    <v-img :src="logo" height="40"></v-img>
                </div>
                <div class="text-center">
                    <span>
                        {{ profileName || profileEmail || 'Customer' }}
                    </span>
                </div>
            </div>
            <v-list dense nav>
                <div v-for="group in menuGroups" :key="group.groupTitle" class="menu-group">
                    <h5 style="color: #838383; font-size: 0.8rem;" class="text-uppercase">
                        {{ group.groupTitle }}
                    </h5>
                    <template v-for="item in group.items" :key="item.title">
                        <router-link v-if="item.name" :to="{ name: item.name }"
                            class="d-flex align-center customer-list-item"
                            :class="{ 'customer-list-item--active': isRouteActive(item.name) }" @click="closeDrawer()">
                            <v-icon size="24" class="mr-2">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </router-link>
                        <div v-else class="d-flex align-center customer-list-item" @click="handleMenuAction(item)">
                            <v-icon size="24" class="mr-2">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </div>
                    </template>

                </div>
            </v-list>

            <!-- Fixed Bottom User Info Section -->
            <div class="pa-4 d-flex justify-space-between align-center" style="">
                Logout
                <v-btn icon @click="logout" title="Logout">
                    <v-icon color="red">mdi-logout</v-icon>
                </v-btn>
            </div>
        </v-navigation-drawer>

        <v-navigation-drawer v-else :model-value="drawer" @update:model-value="onDrawerUpdate" app
            class="customer-drawer" elevation="0">
            <div class="py-6">
                <div>
                    <v-img :src="logo" height="40"></v-img>
                </div>
                <div class="text-center">
                    <h5>
                        Hello, {{ profileName || profileEmail || 'Customer' }}
                    </h5>
                </div>
            </div>
            <v-list dense nav>
                <div v-for="group in menuGroups" :key="group.groupTitle" class="menu-group">
                    <h5 style="color: #838383; font-size: 0.8rem;" class="text-uppercase">
                        {{ group.groupTitle }}
                    </h5>
                    <template v-for="item in group.items" :key="item.title">
                        <router-link v-if="item.name" :to="{ name: item.name }"
                            class="d-flex align-center customer-list-item"
                            :class="{ 'customer-list-item--active': isRouteActive(item.name) }"
                            @click="isMobile && closeDrawer()">
                            <v-icon size="24" class="mr-2">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </router-link>
                        <div v-else class="d-flex align-center customer-list-item" @click="handleMenuAction(item)">
                            <v-icon size="24" class="mr-2">{{ item.icon }}</v-icon>
                            <span>{{ item.title }}</span>
                        </div>
                    </template>

                </div>
            </v-list>

            <!-- Fixed Bottom User Info Section -->
            <!-- <div class="pa-4 d-flex justify-space-between align-center" style="">
                Logout
                <v-btn icon @click="logout" title="Logout">
                    <v-icon color="red">mdi-logout</v-icon>
                </v-btn>
            </div> -->
        </v-navigation-drawer>

        <!-- Global Modal -->
        <v-dialog v-model="globalModal.isOpen" :max-width="globalModal.width" :persistent="globalModal.persistent"
            :scrollable="globalModal.scrollable" :transition="globalModal.disableTransition ? false : undefined"
            content-class="global-modal-content">
            <v-card class="elevation-0">
                <div class="global-modal-header">
                    <p class="global-modal-title">{{ globalModal.title }}</p>
                    <v-btn icon size="small" variant="text" @click="globalModal.close()">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>

                <component :is="globalModal.component" v-if="globalModal.component" v-bind="globalModal.props" />

            </v-card>
        </v-dialog>

        <!-- Main content area -->
        <v-main class="bg-grey-lighten-4">
            <v-container fluid class="pa-0">
                <v-app-bar app elevation="0" style="position: fixed">
                    <!-- Left: Hamburger icon -->
                    <v-app-bar-nav-icon @click="toggleDrawer" />
                    <v-app-bar-title class="toolbar-title">
                        <h5 class="mb-0 pb-0 toolbar-title-text">{{ pageTitle }}</h5>
                    </v-app-bar-title>

                    <v-spacer />

                    <div class="pr-4 topbar-user">

                        <div>
                            <p style="font-size: 0.8rem; font-weight: 500;">

                                <span style="color: #4e4e4e;" class="ml-2">System Date:</span> {{ systemDate }}
                            </p>
                        </div>
                    </div>
                </v-app-bar>

                <div class="router-view-wrap">
                    <router-view />
                </div>
            </v-container>
        </v-main>
    </v-app>
</template>
<script>
import logo from './logo.png'
import { useGlobalStore } from '@/global_clients/stores/account';
import { useGlobalModalStore } from '@shared/stores/globalModal';
import { mapActions, mapState } from 'pinia';

export default {
    data() {
        return {
            logo,
            drawer: false,
            isMobile: false,
            resizeHandler: null,
            globalModal: useGlobalModalStore(),
            menuGroups: [
                {
                    groupTitle: 'Welcome',
                    items: [
                        { title: 'Dashboard', icon: 'mdi-view-dashboard-outline', name: 'globalDashboardPage' },
                        { title: 'Appointments', icon: 'mdi-calendar-check-outline', name: 'globalAppointmentPage' },
                        { title: 'Estimates', icon: 'mdi-comment-question-outline', name: 'globalInquiryPage' },
                        { title: 'Subscriptions', icon: 'mdi-certificate-outline', name: 'globalSubscriptionPage' },
                    ],
                },
                {
                    groupTitle: 'Communications',
                    items: [
                        { title: 'Message', icon: 'mdi-chat-outline', name: 'globalMessagePage' },
                        { title: 'Email Notifications', icon: 'mdi-email-outline', name: 'globalEmailNotificationPage' },
                        // { title: 'Reviews', icon: 'mdi-star-outline', name: 'reviewPage' },
                    ],
                },
                // {
                //     groupTitle: 'Invoices',
                //     items: [
                //         { title: 'Invoice', icon: 'mdi-file-document-outline', name: 'invoicePage' },
                //         { title: 'Payment', icon: 'mdi-credit-card-outline', name: 'paymentPage' },
                //     ],
                // },
                // {
                //     groupTitle: 'Notifications',
                //     items: [
                //         { title: 'Notification', icon: 'mdi-bell-outline', name: 'notificationPage' },
                //     ],
                // },
                {
                    groupTitle: 'Settings',
                    items: [
                        { title: 'Profile', icon: 'mdi-account-outline', name: 'globalProfilePage' },
                        { title: 'Cards', icon: 'mdi-cog-outline', name: 'globalCarsPage' },
                        { title: 'Logout', icon: 'mdi-logout-variant', name: '' },
                    ],
                },
            ],

            notifications: [
                { title: 'New booking request', time: '2 min ago' },
                { title: 'Service completed', time: '30 min ago' },
                { title: 'Payment received', time: '1 hour ago' },
            ],
            systemDate: '',
            intervalId: null,
        };
    },

    computed: {
        ...mapState(useGlobalStore, ['profileName', 'profileEmail', 'hasProfile']),

        notificationCount() {
            return this.notifications.length;
        },
        pageTitle() {
            return this.$route.meta.pageTitle || 'Dashboard';
        },

    },
    methods: {
        ...mapActions(useGlobalStore, {
            fetchProfile: 'fetchProfile',
            userLogout: 'logout',
        }),
        updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true,
            };
            this.systemDate = now.toLocaleString('en-US', options);
        },
        isRouteActive(name) {
            return this.$route.name?.startsWith(name.replace('Page', ''));
        },
        closeDrawer() {
            this.drawer = false;
        },
        handleMenuAction(item) {
            if (item.title && item.title.toLowerCase() === 'logout') {
                this.logout();
            }
        },
        onDrawerUpdate(nextValue) {
            if (this.drawer !== nextValue) {
                this.drawer = nextValue;
            }
        },
        toggleDrawer() {
            this.drawer = !this.drawer;
        },
        async logout() {
            await this.userLogout();
            this.$router.replace({ name: "globalLoginPage" });
        },
    },
    mounted() {
        // if (localStorage.getItem('token') && !this.hasProfile) {
        //     console.log("fasd");
        //     this.fetchProfile();
        // }
        // Detect if mobile
        this.isMobile = window.innerWidth < 600;
        this.drawer = !this.isMobile;
        this.resizeHandler = () => {
            const nextIsMobile = window.innerWidth < 600;
            if (nextIsMobile !== this.isMobile) {
                this.isMobile = nextIsMobile;
                this.drawer = !this.isMobile;
            }
        };
        window.addEventListener('resize', this.resizeHandler);

        this.updateDateTime()
        this.intervalId = setInterval(this.updateDateTime, 1000)
    },
    beforeUnmount() {
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }
    },
};
</script>
<style lang="scss">
a {
    text-decoration: none;
    color: #232323;
    font-weight: 500;
}

.customer-drawer {
    padding: 0 10px;
    border-right: 0 !important;
    position: fixed;
    top: 0;
    height: 100vh;
    max-height: 100vh;
    overflow-y: auto;
    background: white;
}

.global-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 4px 16px;
    border-bottom: 1px solid #e5e7eb;
}


.global-modal-body {
    padding: 16px;
}

@media (max-width: 960px) {
    .global-modal-content {
        max-width: 100vw !important;
        width: 100vw !important;
        margin: 0 !important;
    }
}

.router-view-wrap {
    max-height: calc(100vh - 64px);
    overflow: auto;
}

.topbar-user {
    display: block;
    width: max-content;
    max-width: 250px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.toolbar-title {
    flex: 1 1 auto;
    min-width: 0;
}

.toolbar-title-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

@media (max-width: 800px) {
    .topbar-user {
        display: none;
    }
}

.bx-shadow {
    box-shadow: 1px -2px 6px rgba(0, 0, 0, 0.05) !important;
}

.menu-group {
    padding-top: 8px;
    padding-bottom: 8px;
}

.customer-list-item {
    padding: 5px 8px;
    margin: 4px 0;
    font-size: 0.84rem;
    transition: background-color 0.2s ease, color 0.2s ease;
    cursor: pointer;
}

.customer-list-item:hover {
    background-color: #e0f2f1;
    border-radius: 4px;
    /* subtle teal hover */
}

.customer-list-item--active {
    background-color: #26a69a !important;
    /* teal active background */
    color: white !important;
    border-radius: 4px;
}


.customer-list-item .v-icon {
    color: #00796b;
    /* darker teal icon */
}

.customer-list-item--active .v-icon {
    color: #ffffff;
    /* darker teal icon */
}

.v-subheader {
    font-size: 0.85rem;
    letter-spacing: 0.1em;
    margin-left: 12px;
    margin-top: 16px;
    margin-bottom: 4px;
}
</style>
