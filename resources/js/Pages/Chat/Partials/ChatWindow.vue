<script setup lang="ts">
    import { router } from '@inertiajs/vue3';
    import { computed, nextTick, onMounted, ref, watch } from 'vue';
    import { IChat, IMessage, IUser } from '@/types';

    const props = defineProps<{
        users: IUser[];
        chat: IChat;
        messages: IMessage[];
        currentUserId: number;
    }>();

    const body = ref('');

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

    /* Scroll To Message (Start) */

    const scrollToMessage = (messageId: number) => {
        nextTick(() => {
            const messagesContainer = document.getElementById('messages-container');
            const messageHolder = document.getElementById(`message-${messageId}`);
            if (messageHolder) {
                messagesContainer?.scrollTo(0, messageHolder.offsetTop);
            }
        });
    };

    const lastMessageId = computed(() => props.messages.at(-1)?.id || null);

    onMounted(() => {
        if (lastMessageId.value) {
            scrollToMessage(lastMessageId.value);
        }
    });

    watch(lastMessageId, (newId) => {
        if (newId) {
            scrollToMessage(newId);
        }
    });

    /* Scroll To Message (End) */
</script>

<template>
    <div class="w-3/4 rounded-xl bg-white p-3 shadow msm:w-full">
        <h3 class="mb-4 text-center text-lg font-semibold text-gray-900">
            {{ props.chat.title }}
        </h3>
        <div class="flex flex-col gap-4" v-if="props.chat">
            <!--Messages Window-->
            <div
                class="transparent-scrollbar max-h-122 min-h-122 flex-1 space-y-3 overflow-y-auto rounded-xl bg-slate-200 p-3"
                id="messages-container"
            >
                <div class="mb-2" v-for="message in props.messages" :key="message.id">
                    <!--Message-->
                    <div
                        class="flex justify-end text-right"
                        v-if="message.is_owner"
                        :id="`message-${message.id}`"
                    >
                        <div class="w-2/4 rounded-xl bg-blue-100 p-2 text-right shadow-sm">
                            <p class="font-semibold text-gray-700">You</p>
                            <p class="text-gray-700">{{ message.body }}</p>
                            <span class="text-xs text-gray-400">{{ message.time }}</span>
                        </div>
                    </div>
                    <div class="flex justify-start text-left" v-else :id="`message-${message.id}`">
                        <div class="w-2/4 rounded-xl bg-gray-100 p-2 text-left shadow-sm">
                            <p class="font-semibold text-gray-700">{{ message.user_name }}</p>
                            <p class="text-gray-700">{{ message.body }}</p>
                            <span class="text-xs text-gray-400">{{ message.time }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Message Input -->
            <div class="flex items-center">
                <input
                    class="mr-2 flex-auto rounded-xl border p-2 transition-shadow duration-200 focus:border-blue-300 focus:outline-none focus:ring"
                    v-model="body"
                    placeholder="Type your message..."
                    type="text"
                    @keydown="handleKeydown"
                />
                <button
                    class="rounded-xl bg-blue-500 p-4 px-4 py-2 text-white transition-colors duration-200 hover:bg-blue-600"
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
