<script setup lang="ts">
    import { router } from '@inertiajs/vue3';
    import axios from 'axios';
    import { computed, nextTick, onMounted, ref, watch } from 'vue';
    import { IChat, IMessage, IUser } from '@/types';

    const props = defineProps<{
        users: IUser[];
        chat: IChat;
        messages: IMessage[];
        currentUserId: number;
        isLastPage: boolean;
    }>();

    const body = ref('');
    const page = ref(1);
    const localMessages = ref<IMessage[]>(props.messages);
    const isLastPage = ref(props.isLastPage);

    const userIds = computed(() => {
        return props.users
            ?.filter((user) => user.id !== props.currentUserId)
            .map((user) => user.id);
    });

    /* Message Send (Start) */

    const sendMessage = (message: string) => {
        if (props.chat) {
            router.post(
                route('messages.store'),
                {
                    chat_id: props.chat.id,
                    body: message,
                    user_ids: userIds.value,
                },
                {
                    onFinish: () => {
                        scrollToBottom();
                        body.value = '';
                    },
                }
            );
        }
    };

    const handleKeydown = (event: KeyboardEvent) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage(body.value);
        }
    };

    /* Message Send (End) */

    /* Time Update Interval (Start)*/

    /*
         let timeUpdateInterval: number;

         onMounted(() => {
             const timeUpdateInterval = setInterval(() => {
                 router.reload({ only: ['messages'] });
             }, 60000);
         });

         onUnmounted(() => {
             clearInterval(timeUpdateInterval);
         });
    */

    /* Time Update Interval (End)*/

    /* Scroll To Last Message (Start) */

    const scrollToBottom = () => {
        nextTick(() => {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    };

    onMounted(() => {
        scrollToBottom();
    });

    watch(
        () => props.messages,
        () => {
            if (page.value === 1) {
                scrollToBottom();
            }
        },
        { deep: true }
    );

    /* Scroll To Last Message (End) */

    const getMoreMessages = () => {
        axios.get(`/chats/${props.chat.id}?page=${++page.value}`).then(({ data }) => {
            localMessages.value.push(...data.messages);
            isLastPage.value = data.isLastPage;
        });
    };
</script>

<template>
    <div class="w-full rounded-lg bg-white p-6 shadow-md lg:w-3/4 mlg:order-1">
        <h3 class="mb-6 text-center text-xl font-semibold text-gray-800">
            {{ props.chat.title }}
        </h3>

        <div class="flex flex-col space-y-6" v-if="props.chat">
            <!-- Messages Window -->
            <div
                class="transparent-scrollbar max-h-124 min-h-124 relative flex-1 overflow-y-auto rounded-lg bg-gray-100 p-4 pt-16"
                id="messages-container"
            >
                <!-- Load More Button -->
                <div
                    class="absolute inset-y-3 left-1/2 -translate-x-1/2 transform"
                    v-show="!isLastPage"
                >
                    <button
                        class="rounded-full bg-blue-500 px-4 py-2 text-white shadow-lg transition hover:bg-blue-600"
                        @click="getMoreMessages"
                    >
                        Load more...
                    </button>
                </div>
                <div
                    class="flex"
                    v-for="message in localMessages.slice().reverse()"
                    :key="message.id"
                    :class="{ 'justify-end': message.is_owner, 'justify-start': !message.is_owner }"
                >
                    <div
                        class="mb-4 w-3/4 rounded-lg p-4 shadow"
                        :class="[
                            { 'text-right': message.is_owner, 'text-left': !message.is_owner },
                            { 'bg-blue-100': message.is_owner, 'bg-white': !message.is_owner },
                        ]"
                    >
                        <p class="font-medium text-gray-900">
                            {{ message.is_owner ? 'You' : message.user_name }}
                        </p>
                        <p class="text-gray-800">{{ message.body }}</p>
                        <span class="text-xs text-gray-500">{{ message.time }}</span>
                    </div>
                </div>
            </div>
            <!-- Message Input -->
            <div class="flex items-center space-x-2">
                <input
                    class="min-w-0 flex-grow rounded border p-2 focus:border-blue-300 focus:outline-none focus:ring"
                    v-model="body"
                    @keydown="handleKeydown"
                    placeholder="Type your message..."
                    type="text"
                />
                <button
                    class="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                    @click="sendMessage(body)"
                >
                    Send
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .transparent-scrollbar {
        scrollbar-width: none;
    }
</style>
