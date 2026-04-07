<template>
    <v-card class="pa-2" elevation="0" style="height: calc(100vh - 100px);">
        <div class="pa-2">
            <v-text-field
                v-model="searchQuery"
                prepend-inner-icon="mdi-magnify"
                placeholder="Search messages"
                variant="outlined"
                density="compact"
                hide-details
                class="mb-3"
            />
        </div>
        <v-list v-if="loading" nav class="overflow-y-auto pa-2" style="height: 100%;">
            <v-list-item v-for="n in 6" :key="`left-skeleton-${n}`" class="mb-3 px-3 py-2">
                <template #prepend>
                    <v-skeleton-loader type="avatar" />
                </template>
                <v-list-item-title>
                    <v-skeleton-loader type="text" />
                </v-list-item-title>
                <v-list-item-subtitle>
                    <v-skeleton-loader type="text" />
                </v-list-item-subtitle>
            </v-list-item>
        </v-list>

        <v-list v-else nav class="overflow-y-auto pa-2" style="height: 100%; padding-bottom: 100px !important;">
            <v-list-item
                v-for="(user, index) in filteredUsers"
                :key="index"
                :value="user"
                @click="$emit('select-user', user)"
                :active="selectedUser && selectedUser.id === user.id"
                class="mb-3 px-3 py-2 bg-grey-lighten-5 elevation-0 hover:bg-grey-lighten-4"
            >
                <template #prepend>
                    <v-avatar size="44" contain>
                        <v-img :src="getAvatar(user)" alt="avatar" />
                    </v-avatar>
                </template>

                <v-list-item-title class="d-flex justify-space-between align-center mb-1">
                    <p class="font-weight-semibold text-body-1 text-capitalize">
                        {{ user?.message_to?.name }}
                        <span class="user-title">({{ user?.title }})</span>
                    </p>
                    <span class="text-grey text-caption text-time">{{
                        formatTime(user?.last_message?.created_at)
                    }}</span>
                </v-list-item-title>

                <v-list-item-subtitle class="d-flex justify-space-between align-center">
                    <span class="text-truncate text-caption text-grey-darken-2" style="max-width: 200px;">
                        {{ getLastMessageText(user?.last_message?.message) }}
                    </span>

                    <v-badge
                        v-if="user.unread_client_count > 0"
                        :content="user.unread_client_count"
                        color="deep-purple-accent-4"
                        inline
                        size="small"
                        class="ml-2"
                    />
                </v-list-item-subtitle>
            </v-list-item>
        </v-list>
    </v-card>
</template>

<script>
export default {
    name: 'MessageLeftPanel',
    props: {
        users: {
            type: Array,
            default: () => [],
        },
        selectedUser: {
            type: Object,
            default: null,
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['select-user'],
    data() {
        return {
            searchQuery: '',
        };
    },
    computed: {
        filteredUsers() {
            const query = (this.searchQuery || '').trim().toLowerCase();
            if (!query) return this.users;

            return this.users.filter((user) => {
                const name = (user?.message_to?.name || '').toLowerCase();
                const title = (user?.title || '').toLowerCase();
                const lastMessage = this.getLastMessageText(user?.last_message?.message || '').toLowerCase();
                return name.includes(query) || title.includes(query) || lastMessage.includes(query);
            });
        },
    },
    methods: {
        getLastMessageText(htmlMessage) {
            if (!htmlMessage) return '';
            const plain = htmlMessage.replace(/<\/?[^>]+(>|$)/g, '');
            return plain.length > 50 ? `${plain.substring(0, 50)}...` : plain;
        },
        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            return new Intl.DateTimeFormat('en-US', {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true,
            }).format(date);
        },
        getAvatar(user) {
            return user.message_to?.icon;
        },
    },
};
</script>

<style scoped>
.text-time {
    font-family: 'poppins', sans-serif !important;
}

.text-body-1 {
    font-family: 'poppins', sans-serif;
    font-weight: 600;
    font-size: 0.9rem !important;
}

.text-truncate {
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    font-family: 'poppins', sans-serif;
}

.user-title {
    color: rgb(144 144 144);
    font-weight: 400;
    font-size: 0.8rem;
}
</style>
