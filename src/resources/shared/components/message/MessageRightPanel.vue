<template>
    <div v-if="selectedUser">
        <v-card class="pa-4 d-flex flex-column message-panel" elevation="0" style="height: calc(100vh - 100px);">
            <div class="mb-2">
                <h4 class="mb-1">{{ selectedUser.title }}</h4>
            </div>
            <v-divider></v-divider>
            <div ref="chatBox" class="flex-grow-1 overflow-y-auto pr-2 messages-scroll" style="scroll-behavior: smooth;">
                <div v-if="loading" class="py-2">
                    <div v-for="n in 6" :key="`msg-skeleton-${n}`" class="d-flex mb-2" :class="n % 2 ? 'justify-start' : 'justify-end'">
                        <v-skeleton-loader type="article" class="message-skeleton" />
                    </div>
                </div>
                <div v-else-if="!conversation.length" class="no-message-state">
                    <v-icon size="28" color="grey">mdi-message-outline</v-icon>
                    <p class="mt-2 mb-0 text-medium-emphasis">No messages yet</p>
                </div>
                <template v-else>
                    <div
                        v-for="(msg, index) in conversation"
                        :key="index"
                        class="d-flex mb-2"
                        :class="{ 'justify-end': msg.from === 'me', 'justify-start': msg.from !== 'me' }"
                    >
                        <v-card
                            :color="msg.from === 'me' ? 'blue-darken-2' : 'grey-lighten-3'"
                            :text-color="msg.from === 'me' ? 'white' : 'black'"
                            class="pa-3"
                            style="max-width: 70%; border-radius: 18px;"
                            elevation="0"
                        >
                            <div v-html="msg.message"></div>
                        </v-card>
                    </div>
                </template>
            </div>

            <div class="pt-2 composer-bar">
                <v-textarea
                    v-model="newMessage"
                    variant="outlined"
                    placeholder="Type a message..."
                    density="compact"
                    auto-grow

                    rows="1"
                    max-rows="3"
                    no-resize
                    :maxlength="messageMaxChars"
                    :counter="messageMaxChars"
                    hide-details
                    :disabled="loading"
                    @keydown.enter.exact.prevent="handleSend"
                />
                <div class="composer-action">
                    <v-btn color="primary" :disabled="loading" prepend-icon="mdi-send" @click="handleSend">
                        Send
                    </v-btn>
                </div>
            </div>
        </v-card>
    </div>
    <div v-else>
        <v-card class="elevation-0 pa-4 no-selection-state" style="height: calc(100vh - 100px);">
            <v-icon size="36" color="grey">mdi-forum-outline</v-icon>
            <p class="mt-2 mb-1 font-weight-medium">No conversation selected</p>
            <p class="mb-0 text-medium-emphasis">Choose a chat from the left panel to start messaging.</p>
        </v-card>
    </div>
</template>

<script>
export default {
    name: 'MessageRightPanel',
    props: {
        selectedUser: {
            type: Object,
            default: null,
        },
        conversation: {
            type: Array,
            default: () => [],
        },
        loading: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['send-message'],
    data() {
        return {
            newMessage: '',
            messageMaxChars: 300,
        };
    },
    watch: {
        conversation: {
            handler() {
                this.scrollToBottom();
            },
            deep: true,
        },
        selectedUser() {
            this.newMessage = '';
            this.scrollToBottom();
        },
    },
    methods: {
        handleSend() {
            const message = (this.newMessage || '').trim();
            if (!message) return;
            this.$emit('send-message', message);
            this.newMessage = '';
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const chatBox = this.$refs.chatBox;
                if (chatBox) {
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            });
        },
    },
};
</script>

<style scoped>
.message-panel {
    overflow: hidden;
    font-size: 0.82rem;
}

.messages-scroll {
    min-height: 0;
    padding-bottom: 8px;
    padding-top: 10px;
}

.message-skeleton {
    width: min(70%, 360px);
}

.no-message-state {
    height: 100%;
    min-height: 220px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.no-selection-state {
    min-height: 320px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
}

.composer-bar {
    background: #fff;
    margin-top: auto;
    position: sticky;
    bottom: 0;
    z-index: 2;
}

.composer-action {
    display: flex;
    justify-content: flex-end;
    margin-top: 8px;
}
</style>
