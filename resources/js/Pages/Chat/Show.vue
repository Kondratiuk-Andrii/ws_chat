<script setup lang="ts">
    import { Head, router, usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import ChatWindow from '@/Pages/Chat/Partials/ChatWindow.vue';
    import { IChat, IMessage, IUser } from '@/types';

    const props = defineProps<{
        users: IUser[];
        chat: IChat;
        messages: IMessage[];
        isLastPage: boolean;
    }>();

    const localMessages = ref([...props.messages]);

    const { auth } = usePage().props;
    const currentUserId = ref(auth.user.id);

    window.Echo.channel(`store-message.${props.chat.id}`).listen(
        '.store-message',
        (res: { message: IMessage }) => {
            res.message.is_owner = currentUserId.value === res.message.user_id;
            localMessages.value.unshift(res.message);

            if (usePage().url === `/chats/${props.chat.id}`) {
                router.patch(route('message_statuses.update'), {
                    message_id: res.message.id,
                    user_id: currentUserId.value,
                });
            }
        }
    );
</script>

<template>
    <Head title="Chat" />
    <authenticated-layout>
        <template #header>Chats</template>
        <div class="flex flex-col gap-6 lg:flex-row lg:gap-12">
            <div class="w-full rounded-lg bg-white p-6 shadow-md lg:w-1/4 mlg:order-2">
                <h3 class="mb-6 text-center text-xl font-semibold text-gray-800">Users in Chat</h3>
                <div class="space-y-4" v-if="props.users">
                    <div
                        class="rounded-lg bg-gray-50 p-4 transition hover:bg-gray-100"
                        v-for="user in props.users"
                        :key="user.id"
                    >
                        <div class="flex flex-col items-center">
                            <p class="text-lg font-medium text-gray-900">{{ user.name }}</p>
                            <p class="text-sm text-gray-600">{{ user.email }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <ChatWindow
                :users="props.users"
                :chat="props.chat"
                :messages="localMessages"
                :current-user-id="currentUserId"
                :is-last-page="props.isLastPage"
            />
        </div>
    </authenticated-layout>
</template>
