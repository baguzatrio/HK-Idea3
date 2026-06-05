<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { authState, fetchAuthUser } from '@/store/auth';
import Dropdown from '@/Components/Dropdown.vue';
import AppFooter from '@/Components/AppFooter.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { User } from '@lucide/vue';

const route = useRoute();
const router = useRouter();

const showingNavigationDropdown = ref(false);

const isMasterDataActive = computed(() => {
    return route.path.startsWith('/users') ||
        route.path.startsWith('/divisi') ||
        route.path.startsWith('/roles') ||
        route.path.startsWith('/permissions');
});

const handleLogout = async () => {
    await axios.post('/logout');
    authState.user = null;
    router.push('/login');
};

onMounted(() => {
    fetchAuthUser();
});
</script>

<template>
    <div class="min-h-screen flex flex-col" v-if="authState.user">
        <div
            class="absolute top-[20px] -left-[100px] w-[300px] h-[300px] bg-red-200 opacity-20 blur-3xl rounded-full pointer-events-none z-0">
        </div>
        <div
            class="absolute -bottom-[200px] right-[10px] w-[300px] h-[400px] bg-blue-300 opacity-20 blur-3xl rounded-full pointer-events-none z-0">
        </div>
        <div
            class="absolute -bottom-[550px] -left-[100px] w-[400px] h-[300px] bg-yellow-200 opacity-20 blur-3xl rounded-full pointer-events-none z-0">
        </div>
        <div class="flex flex-col flex-1 relative z-10">
            <div class="sticky top-0 z-50 shadow-md backdrop-blur-md bg-white/70">
                <div class="bg-blue-900 backdrop-blur-md py-4"></div>
                <div class="bg-red-500 h-0.5"></div>
                <nav class=" border-b border-gray-100 ">
                    <!-- Primary Navigation Menu -->
                    <div class="mx-auto  max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="flex h-16 justify-between">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="flex shrink-0 items-center">
                                    <router-link to="/dashboard">
                                        <img src="\images\logo\logoHK.png" alt="" class="block h-9 w-auto fill-current">
                                    </router-link>
                                </div>

                                <!-- Navigation Links -->
                                <div class="items-center hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                    <NavLink to="/dashboard" :active="route.path === '/dashboard'">
                                        Dashboard
                                    </NavLink>
                                    <div class="relative flex items-center h-full pt-1" v-if="authState.is_super_admin">
                                        <Dropdown hoverable>
                                            <template #trigger>
                                                <span class="inline-flex rounded-md h-full">
                                                    <button type="button" :class="[
                                                        isMasterDataActive
                                                            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out h-full'
                                                            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out h-full'
                                                    ]">
                                                        Master Data

                                                        <svg class="-me-0.5 ms-2 h-4 w-4"
                                                            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </button>
                                                </span>
                                            </template>

                                            <template #content>
                                                <DropdownLink to="/users">User</DropdownLink>
                                                <DropdownLink to="/divisis">Divisi</DropdownLink>
                                                <DropdownLink to="/roles">Role</DropdownLink>
                                                <DropdownLink to="/permissions">Permission</DropdownLink>
                                            </template>
                                        </Dropdown>
                                    </div>
                                </div>
                            </div>

                            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                                <!-- Settings Dropdown -->
                                <div class="relative ms-3">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                    <User class="h-4 w-4 mr-2" />
                                                    {{ authState.user?.name }}

                                                    <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink as="button" @click="handleLogout">
                                                <div class="text-red-500 hover:text-red-700">Log Out</div>
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="-me-2 flex items-center sm:hidden">
                                <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path
                                            :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16" />
                                        <path
                                            :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu -->
                    <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                        class="sm:hidden">
                        <div class="space-y-1 pb-3 pt-2">
                            <ResponsiveNavLink to="/dashboard" :active="route.path === '/dashboard'">Dashboard
                            </ResponsiveNavLink>
                        </div>

                        <!-- Responsive Master Data -->
                        <div class="space-y-1 pb-3 pt-2 border-t border-gray-200" v-if="authState.is_super_admin">
                            <div class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 mt-2">
                                Master Data</div>
                            <ResponsiveNavLink to="/users" :active="route.path.startsWith('/users')">User
                            </ResponsiveNavLink>
                            <ResponsiveNavLink to="/divisis" :active="route.path.startsWith('/divisis')">Divisi
                            </ResponsiveNavLink>
                            <ResponsiveNavLink to="/roles" :active="route.path.startsWith('/roles')">Role
                            </ResponsiveNavLink>
                            <ResponsiveNavLink to="/permissions" :active="route.path.startsWith('/permissions')">
                                Permission
                            </ResponsiveNavLink>
                        </div>

                        <!-- Responsive Settings Options -->
                        <div class="border-t border-gray-200 pb-1 pt-4">
                            <div class="px-4">
                                <div class="text-base font-medium text-gray-800">{{ authState.user?.name }}</div>
                                <div class="text-sm font-medium text-gray-500">{{ authState.user?.email }}</div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink as="button" @click="handleLogout">
                                    <div class="text-red-500 hover:text-red-700 w-full text-start">Log Out</div>
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
            <!-- Page Heading -->
            <header v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>
            <!-- Page Content -->
            <main class="flex-grow min-h-screen">
                <slot />
            </main>
            <div>
                <AppFooter />
            </div>
        </div>
    </div>
    <div v-else
        class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-white via-white to-blue-200/50">
        <!-- Optional Loading indicator wait state before user info is fetched -->
        <img src="\images\logo\logoHK.png" alt="" class="w-26 h-16">
        <span class="text-gray-500">Memuat data pengguna...</span>
    </div>
</template>
