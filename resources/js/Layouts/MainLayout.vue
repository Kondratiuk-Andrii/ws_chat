<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';
    import ApplicationLogo from '@/Components/ApplicationLogo.vue';
    import NavLink from '@/Components/NavLink.vue';
</script>

<template>
    <!--    Wrapper-->
    <div class="flex min-h-screen flex-col overflow-clip bg-gray-100">
        <!--    Header-->
        <header class="bg-white shadow">
            <div class="container flex h-16 items-center justify-between">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <Link :href="route('dashboard')">
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    </Link>
                </div>
                <!-- Navigation Links -->
                <nav class="flex gap-5">
                    <NavLink :active="route().current('dashboard')" :href="route('dashboard')">
                        Dashboard
                    </NavLink>
                    <NavLink :active="route().current('chats.index')" :href="route('chats.index')">
                        Index
                    </NavLink>
                </nav>
                <div></div>
            </div>
        </header>
        <main class="flex-auto">
            <slot class="container" />
        </main>
    </div>
</template>

<style scoped></style>
