<template>
    <v-container fluid class="py-4">
        <v-row>
            <v-col cols="12" md="4">
                <message-left-panel
                    :users="users"
                    :selected-user="selectedUser"
                    :loading="loadingUsers"
                    @select-user="selectUser"
                />
            </v-col>

            <v-col cols="12" md="8">
                <message-right-panel
                    :selected-user="selectedUser"
                    :conversation="conversation"
                    :loading="loadingMessages"
                    @send-message="sendMessage"
                />
            </v-col>
        </v-row>
    </v-container>
</template>

<script>
import MessageLeftPanel from '@shared/components/message/MessageLeftPanel.vue';
import MessageRightPanel from '@shared/components/message/MessageRightPanel.vue';

export default {
    name: 'MessagePage',
    components: {
        MessageLeftPanel,
        MessageRightPanel,
    },
    data() {
        return {
            users: [],
            selectedUser: null,
            conversation: [],
            loadingUsers: false,
            loadingMessages: false,
            loading_button: false,
            error: null,
        };
    },
    methods: {
        async selectUser(user) {
            this.selectedUser = user;
            this.loadingMessages = true;
            try {
                const resp = await this.$axios.get('/conversations/' + user.id);
                this.conversation = resp?.data?.messages || [];
            } catch (err) {
                this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
                this.conversation = [];
            } finally {
                this.loadingMessages = false;
            }
        },
        sendMessage(message) {
            if (!this.selectedUser || !message) return;

            this.loading_button = true;
            this.$axios
                .post(`/conversations/${this.selectedUser.id}/send-message`, {
                    message,
                })
                .then((resp) => {
                    this.conversation.push(resp.data);
                    this.loading_button = false;
                })
                .catch(() => {
                    this.loading_button = false;
                });
        },
        async fetchConversation() {
            try {
                this.loadingUsers = true;
                const resp = await this.$axios.get('/conversations');
                this.users = resp.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Something went wrong. Please try again.';
            } finally {
                this.loadingUsers = false;
            }
        },
    },
    mounted() {
        this.fetchConversation();
    },
};
</script>
