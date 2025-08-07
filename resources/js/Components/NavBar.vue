<script setup>
import { HomeIcon, BellIcon, UserIcon, CogIcon, ArrowRightIcon } from "@heroicons/vue/24/outline";
import { ref } from "vue";
import { useAuthenticationStore } from "@/Stores";
import { system_information } from "@/Lang/en";

const authStore = useAuthenticationStore();

const isOpen = ref(false);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};
</script>



<template>
    <div class="flex justify-between p-5 bg-white shadow-sm rounded-lg">
        <h1 class="text-xl font-bold">{{ system_information.title }}</h1>
        <div class="flex gap-4 items-center">
            <!-- <HomeIcon class="w-6 h-6 cursor-pointer" />
            <div class="relative cursor-pointer">
                <BellIcon class="w-6 h-6 cursor-pointer" />
                <span class="absolute top-0 -right-10 inline-flex items-center justify-center px-2.5
                rounded-full
                bg-red-600
                text-xs
                font-medium
                text-white
                leading-5
                transform
                -translate-x-1/2
                -translate-y-1/2
                ">
                    <span class="animate-pulse">99+</span>
                </span>
            </div> -->
            <div class="flex gap-2 items-center cursor-pointer relative">

                <template v-if="authStore.user">
                    <img class="w-10 h-10 rounded-full cursor-pointer"
                    src="https://placehold.co/100x100/e2e8f0/334155?text=AR" alt="">
                    <div @click="toggleDropdown">
                        <p class="font-bold">Ariel Recto</p>
                        <p class="text-xs text-gray-500">Ariel Recto</p>
                    </div>
                </template>
                <template v-else>
                    <router-link to="/login" @click="toggleDropdown" class="flex gap-2 items-center cursor-pointer bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg">
                        <UserIcon class="w-6 h-6 cursor-pointer" /> <span> Login</span>
                    </router-link>
                </template>
                <transition enter-active-class="transition ease-out duration-100 "
                    enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
                    leave-active-class="transition ease-in duration-75"
                    leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                    <div v-if="isOpen"
                        class="origin-bottom-right z-50 top-10 absolute right-0 mt-2 w-64 rounded-md shadow-lg py-1 bg-white ring-1 focus:outline-none">
                        <router-link to="/dashboard" class="flex gap-2 items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <UserIcon class="w-6 h-6 cursor-pointer" /> <span>Dashboard</span></router-link>
                        <router-link to="/login" @click="authStore.logout()"
                            class="flex gap-2 items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <ArrowRightIcon class="w-6 h-6 cursor-pointer" /> <span> Sign out</span></router-link>
                    </div>
                </transition>
            </div>

        </div>
    </div>
</template>
