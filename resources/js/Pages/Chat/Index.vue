<script setup lang="ts">
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { IChat, IMessage, IUser } from '@/types';

    const isGroup = ref(false);
    const userIds = ref<number[]>([]);
    const title = ref('');

    const props = defineProps<{
        users: IUser[];
        chats: IChat[];
        errors?: {
            title: string;
        };
    }>();

    const createChat = async (userId: number) => {
        router.post(route('chats.store'), { title: null, user_ids: [userId], isGroup: false });
    };

    const createGroupChat = () => {
        router.post(route('chats.store'), {
            title: title.value,
            user_ids: userIds.value,
            isGroup: true,
        });
    };

    const toggleUsers = (userId: number) => {
        let index = userIds.value.indexOf(userId);
        if (index === -1) {
            userIds.value.push(userId);
        } else {
            userIds.value.splice(index, 1);
        }
    };

    window.Echo.private(`users.${usePage().props.auth.user.id}`).listen(
        '.store-message-status',
        (res: { chat_id: number; count: number; message: IMessage }) => {
            props.chats.forEach((chat) => {
                if (chat.id === res.chat_id) {
                    chat.unreadable_count = res.count;
                    chat.last_message = res.message;
                }
            });
        }
    );
</script>

<template>
    <Head title="Chats" />
    <authenticated-layout>
        <template #header>Chats</template>
        <div class="flex flex-col gap-6 lg:flex-row lg:gap-12">
            <div class="w-full rounded-lg bg-white p-6 shadow-md lg:w-1/2">
                <div class="mb-6 flex min-h-25 items-center justify-center text-center">
                    <h3 class="text-xl font-semibold text-gray-800">My Chats</h3>
                </div>
                <div class="space-y-4" v-if="props.chats.length">
                    <div
                        class="transform rounded-lg bg-gray-50 p-4 transition-transform hover:scale-105 hover:bg-gray-100"
                        v-for="chat in props.chats"
                        :key="chat.id"
                    >
                        <div
                            class="mb-2 flex items-center justify-between gap-4 msm:flex-col msm:items-start"
                        >
                            <p class="font-medium">{{ chat.title }}</p>
                            <Link
                                class="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                :href="route('chats.show', chat.id)"
                            >
                                Open chat
                            </Link>
                        </div>
                        <div class="flex justify-between gap-4 truncate">
                            <div class="flex items-center gap-2 truncate">
                                <p class="text-sm text-gray-700">
                                    {{ chat.last_message?.user_name }}:
                                </p>
                                <p class="truncate text-sm text-gray-600">
                                    {{ chat.last_message?.body }}
                                </p>
                            </div>
                            <div
                                class="flex rounded-full bg-blue-500 px-3 py-1.5 text-sm text-white"
                                v-show="chat.unreadable_count"
                            >
                                {{ chat.unreadable_count }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full rounded-lg bg-white p-6 shadow-md lg:w-1/2">
                <div class="relative mb-6 flex min-h-25 items-center justify-between px-4">
                    <h3 class="absolute left-4 text-xl font-semibold text-gray-800" v-if="!isGroup">
                        Users
                    </h3>
                    <transition name="slide-left">
                        <button
                            class="absolute right-4 rounded bg-indigo-500 px-4 py-2 text-white transition hover:bg-indigo-600"
                            v-if="!isGroup"
                            @click="isGroup = true"
                        >
                            Make group
                        </button>
                        <div class="absolute inset-x-4 space-y-4" v-else>
                            <input
                                class="w-full rounded border px-4 py-2 focus:border-blue-300 focus:ring"
                                v-model="title"
                                type="text"
                                placeholder="Name of chat..."
                            />

                            <div class="flex items-center justify-between space-x-2">
                                <button
                                    class="rounded bg-green-500 px-4 py-2 text-white transition hover:bg-green-600"
                                    @click="createGroupChat"
                                >
                                    Go Chat
                                </button>
                                <p class="font-semibold text-red-500" v-if="props.errors">
                                    {{ props.errors?.title }}
                                </p>
                                <button
                                    class="rounded bg-red-500 px-4 py-2 text-white transition hover:bg-red-600"
                                    @click="
                                        isGroup = false;
                                        title = '';
                                    "
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </transition>
                </div>
                <div class="space-y-4" v-if="props.users.length">
                    <div
                        class="transform rounded-lg bg-gray-50 p-4 transition-transform hover:scale-105 hover:bg-gray-100"
                        v-for="user in props.users"
                        :key="user.id"
                    >
                        <div class="relative flex items-center justify-between">
                            <div>
                                <p class="text-lg font-medium text-gray-900">{{ user.name }}</p>
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                            </div>
                            <transition name="slide-up">
                                <div class="absolute right-0" v-if="!isGroup">
                                    <button
                                        class="rounded bg-blue-500 px-4 py-2 text-white transition hover:bg-blue-600"
                                        @click="createChat(user.id)"
                                    >
                                        Send Message
                                    </button>
                                </div>
                                <div class="absolute right-0 flex items-center space-x-2" v-else>
                                    <input
                                        class="h-4 w-4 cursor-pointer rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @click="toggleUsers(user.id)"
                                        type="checkbox"
                                    />
                                    <label class="text-gray-900">Add to Group</label>
                                </div>
                            </transition>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </authenticated-layout>
</template>

<style scoped>
    .slide-left-enter-active,
    .slide-left-leave-active {
        transition: all 0.25s ease-out;
    }

    .slide-left-enter-from {
        opacity: 0;
        transform: translateX(30px);
    }

    .slide-left-leave-to {
        opacity: 0;
        transform: translateX(-30px);
    }

    .slide-up-enter-active,
    .slide-up-leave-active {
        transition: all 0.25s ease-out;
    }

    .slide-up-enter-from {
        opacity: 0;
        transform: translateY(30px);
    }

    .slide-up-leave-to {
        opacity: 0;
        transform: translateY(-30px);
    }
</style>
