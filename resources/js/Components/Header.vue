<script setup>
import { ref } from 'vue';
import { useAuthenticationStore } from '@/Stores';

const authStore = useAuthenticationStore();

const isProfileOpen = ref(false);

const toggleProfileDropdown = () => {
    isProfileOpen.value = !isProfileOpen.value;
};
</script>

<template>
    <!--
    Main header container.
    It's designed to be a flexible row with a bottom border, padding, and alignment properties.
  -->
    <header class="bg-white border-b border-gray-200 re">
        <div class="flex items-center justify-between h-20 px-6">
            <!-- 1. Search Bar Section (Left) -->
            <div class="relative w-full max-w-xs">
                <label for="search" class="sr-only">Search</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <!-- Search Icon -->
                        <i class="fa fa-rr-search text-gray-400"></i>
                    </div>
                    <input id="search" name="search"
                        class="block w-full bg-gray-100 border border-transparent rounded-lg py-2 pl-10 pr-3 text-sm placeholder-gray-500 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Search for projects or tasks..." type="search">
                </div>
            </div>

            <!-- 2. Actions & User Profile Section (Right) -->
            <div class="flex items-center space-x-5">


                <!-- Notifications Button -->
                <button
                    class="p-2 rounded-full text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="sr-only">View notifications</span>
                    <i class="fa fa-rr-bell text-xl"></i>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button @click="toggleProfileDropdown"
                        class="flex items-center text-left rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="sr-only">Open user menu</span>
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="https://placehold.co/100x100/e2e8f0/334155?text=AR" alt="User avatar">
                    </button>

                    <!-- Dropdown Panel -->
                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="isProfileOpen"
                            class="origin-top-right z-50 absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 focus:outline-none">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a @click="authStore.logout"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </header>
</template>
