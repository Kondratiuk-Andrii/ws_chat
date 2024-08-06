<script setup lang="ts">
    import { Head, Link, router, usePage } from '@inertiajs/vue3';
    import { ref } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { IChat, IUser } from '@/types';

    const isGroup = ref(false);
    const userIds = ref<number[]>([]);
    const title = ref('');

    const props = defineProps<{
        users: IUser[];
        chats: IChat[];
    }>();

    const createChat = (userId: number) => {
        router.post(route('chats.store'), { title: null, user_ids: [userId] });
    };

    const createGroupChat = () => {
        router.post(route('chats.store'), { title: title.value, user_ids: userIds.value });
    };

    const toggleUsers = (userId: number) => {
        let index = userIds.value.indexOf(userId);
        if (index === -1) {
            userIds.value.push(userId);
        } else {
            userIds.value.splice(index, 1);
        }
    };

    window.Echo.channel(`users.${usePage().props.auth.user.id}`).listen(
        '.store-message-status',
        (res: { chat_id: number; count: number }) => {
            props.chats.map((item) => {
                if (item.id === res.chat_id) {
                    item.unreadable_count = res.count;
                }
            });
        }
    );
</script>

<template>
    <Head title="Chats" />
    <authenticated-layout>
        <template #header>Chats</template>
        <div class="flex items-start gap-4">
            <div class="w-1/2 rounded-lg bg-white p-3 shadow">
                <div class="mx-4 mb-4 flex min-h-12 items-center justify-center gap-4 mlg:min-h-26">
                    <h3 class="text-lg font-semibold text-gray-700">My Chats</h3>
                </div>
                <div class="flex flex-col gap-4" v-if="props.chats">
                    <div v-for="chat in props.chats" :key="chat.id">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-slate-100 p-4 hover:bg-gray-50"
                        >
                            <div>
                                <p class="text-lg text-gray-900">
                                    {{ chat.title }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-4">
                                <p
                                    class="rounded-full bg-sky-500 px-3 py-1.5 font-semibold text-white"
                                    v-show="chat.unreadable_count > 0"
                                >
                                    {{ chat.unreadable_count }}
                                </p>
                                <Link
                                    class="rounded-xl bg-blue-500 px-4 py-2 text-center text-white transition-colors duration-200 hover:bg-blue-600"
                                    :href="route('chats.show', chat.id)"
                                >
                                    Open chat
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-1/2 rounded-xl bg-white p-3 shadow">
                <div
                    class="relative mx-4 mb-4 flex min-h-12 items-center justify-between gap-4 mmd:flex-col mlg:min-h-26"
                >
                    <h3
                        class="absolute text-center text-lg font-semibold text-gray-700"
                        v-if="!isGroup"
                    >
                        Users
                    </h3>
                    <transition name="slide-left">
                        <button
                            class="absolute right-0 rounded-xl bg-indigo-500 px-4 py-2 text-white transition-colors duration-200 hover:bg-indigo-600 mmd:inset-x-0 mmd:bottom-0"
                            v-if="!isGroup"
                            @click="isGroup = true"
                        >
                            Make group
                        </button>
                        <div
                            class="absolute flex flex-wrap gap-4 md:right-0 mmd:bottom-0 mlg:justify-between"
                            v-else
                        >
                            <input
                                class="flex-auto rounded-xl border p-2 transition-shadow duration-200 focus:border-blue-200 focus:outline-none focus:ring"
                                v-model="title"
                                type="text"
                                placeholder="Name of chat..."
                            />
                            <button
                                class="rounded-xl bg-green-500 px-3 py-2 text-white hover:bg-green-400"
                                @click="createGroupChat"
                            >
                                Go Chat
                            </button>
                            <button
                                class="rounded-xl bg-red-500 px-4 py-2 text-white transition-colors duration-200 hover:bg-red-400"
                                @click="
                                    () => {
                                        isGroup = false;
                                        title = '';
                                    }
                                "
                            >
                                X
                            </button>
                        </div>
                    </transition>
                </div>
                <div class="flex flex-col gap-4" v-if="props.users">
                    <div v-for="user in props.users" :key="user.id">
                        <div
                            class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-100 p-4 hover:bg-gray-50 mmd:h-35 mmd:flex-col"
                        >
                            <div>
                                <p class="text-lg font-semibold text-gray-800">{{ user.name }}</p>
                                <p class="text-sm text-gray-600">{{ user.email }}</p>
                            </div>
                            <div
                                class="relative mt-2 flex items-center justify-center gap-2 text-nowrap"
                            >
                                <transition name="slide-up">
                                    <button
                                        class="absolute rounded-xl bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 md:right-0 mmd:bottom-0"
                                        v-if="!isGroup"
                                        @click="createChat(user.id)"
                                    >
                                        Send Message
                                    </button>
                                    <label
                                        class="absolute flex cursor-pointer items-center gap-4 md:right-0 mmd:bottom-0"
                                        v-else
                                    >
                                        <div class="flex items-center">
                                            <input
                                                class="size-5 cursor-pointer rounded-xl border-gray-300"
                                                type="checkbox"
                                                @click="toggleUsers(user.id)"
                                            />
                                        </div>
                                        <div>
                                            <strong class="font-medium text-gray-900">
                                                Add to Group
                                            </strong>
                                        </div>
                                    </label>
                                </transition>
                            </div>
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
