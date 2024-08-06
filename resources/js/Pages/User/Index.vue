<script setup lang="ts">
    import { Head, router } from '@inertiajs/vue3';
    import { Link } from '@inertiajs/vue3';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { IUser } from '@/types';

    const props = defineProps<{
        users: IUser[];
    }>();

    const sendMessage = (userId: number) => {
        router.post('/chats', { title: null, users: [userId] });
    };
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800">Users</h2>
        </template>

        <div class="container mx-auto p-4">
            <h2 class="mb-4 text-2xl font-semibold text-gray-800">User List</h2>

            <div class="grid grid-cols-2 gap-4">
                <div
                    class="cursor-pointer rounded-xl bg-white p-4 shadow"
                    v-for="user in props.users"
                    :key="user.id"
                >
                    <h3 class="text-lg font-semibold text-gray-800">{{ user.name }}</h3>
                    <p class="text-sm text-gray-600">{{ user.email }}</p>
                    <button
                        class="mt-2 rounded-xl bg-blue-500 px-4 py-2 text-white transition-colors duration-200 hover:bg-blue-600"
                        @click="sendMessage(user.id)"
                    >
                        Send Message
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
