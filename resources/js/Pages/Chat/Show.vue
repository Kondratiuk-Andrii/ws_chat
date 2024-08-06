<script setup lang="ts">
    import { Head, router, usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import ChatWindow from '@/Pages/Chat/Partials/ChatWindow.vue';
    import { IChat, IMessage, IUser } from '@/types';
    // import { route } from '../../../../vendor/tightenco/ziggy/src/js/index';

    const props = defineProps<{
        users: IUser[];
        chat: IChat;
        messages: IMessage[];
    }>();

    const localMessages = ref([...props.messages]);

    const { auth } = usePage().props;
    const currentUserId = ref(auth.user.id);

    window.Echo.channel(`store-message.${props.chat.id}`).listen(
        '.store-message',
        (res: { message: IMessage }) => {
            res.message.is_owner = currentUserId.value === res.message.user_id;
            localMessages.value.push(res.message);
            router.patch(route('message_statuses.update'), {
                message_id: res.message.id,
                user_id: currentUserId.value,
            });
        }
    );
</script>

<template>
    <Head title="Chat" />
    <authenticated-layout>
        <template #header>Chats</template>
        <div class="flex items-start gap-4 msm:flex-col msm:gap-12">
            <div class="w-1/4 rounded-xl bg-white p-3 shadow msm:w-full">
                <h3 class="mb-4 text-center text-lg font-semibold text-gray-700">Users in Chat</h3>
                <div class="flex justify-center gap-4 sm:flex-col" v-if="props.users">
                    <div v-for="user in props.users" :key="user.id">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-100 p-4"
                        >
                            <div>
                                <p class="text-lg font-semibold text-gray-800">{{ user.name }}</p>
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <ChatWindow
                :users="props.users"
                :chat="props.chat"
                :messages="localMessages"
                :current-user-id="currentUserId"
            />
        </div>
    </authenticated-layout>
</template>
