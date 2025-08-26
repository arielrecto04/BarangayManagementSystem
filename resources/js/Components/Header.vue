<script setup>
import { ref, defineEmits } from 'vue';
import { useAuthenticationStore } from '@/Stores';

const authStore = useAuthenticationStore();
const emit = defineEmits(['search']);

const isProfileOpen = ref(false);
const isMobileMenuOpen = ref(false);

const toggleProfileDropdown = () => {
    isProfileOpen.value = !isProfileOpen.value;
};

const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
};
</script>

<template>
    <header class="bg-white border-b border-gray-200">
        <div class="flex items-center justify-between h-20 px-4 md:px-6">

            <!-- 1. Logo / Search -->
            <div class="flex items-center w-full md:w-auto gap-2">
                <!-- Search Bar -->
                <div class="relative w-full max-w-xs md:flex-1">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa fa-rr-search text-gray-400"></i>
                        </div>
                        <input id="search" name="search" type="search" placeholder="Search..."
                            class="block w-full bg-gray-100 border border-transparent rounded-lg py-2 pl-10 pr-3 text-sm placeholder-gray-500 focus:outline-none focus:bg-white focus:border-blue-500 focus:ring-blue-500"
                            @input="emit('search', $event.target.value)" />
                    </div>
                </div>
            </div>

            <!-- 2. Desktop Actions -->
            <div class="hidden md:flex items-center space-x-4">
                <!-- Profile Dropdown -->
                <div class="relative">
                    <button @click="toggleProfileDropdown"
                        class="flex items-center text-left rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <img class="h-10 w-10 rounded-full object-cover"
                            src="https://placehold.co/100x100/e2e8f0/334155?text=AR" alt="User avatar">
                    </button>

                    <transition enter-active-class="transition ease-out duration-100"
                        enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                        leave-active-class="transition ease-in duration-75"
                        leave-from-class="transform opacity-100 scale-100"
                        leave-to-class="transform opacity-0 scale-95">
                        <div v-if="isProfileOpen"
                            class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 focus:outline-none z-50">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <a @click="authStore.logout"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</a>
                        </div>
                    </transition>
                </div>
            </div>

            <!-- 3. Mobile Hamburger -->
            <div class="flex md:hidden items-center">
                <button @click="toggleMobileMenu"
                    class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span v-if="!isMobileMenuOpen">☰</span>
                    <span v-else>✕</span>
                </button>
            </div>
        </div>

        <!-- 4. Mobile Menu -->
        <transition name="mobile-menu">
            <div v-if="isMobileMenuOpen" class="md:hidden px-4 pb-4 space-y-2 bg-white">
                <div class="pt-2">
                    <a href="#" class="block w-full p-2 rounded-lg hover:bg-gray-100">Your Profile</a>
                    <a href="#" class="block w-full p-2 rounded-lg hover:bg-gray-100">Settings</a>
                    <button @click="authStore.logout" class="w-full text-left p-2 rounded-lg hover:bg-gray-100">Sign
                        out</button>
                </div>
            </div>
        </transition>
    </header>
</template>
