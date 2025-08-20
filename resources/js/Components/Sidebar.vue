<script setup>
import { useRoute } from 'vue-router';
import { ref } from 'vue';
import { ChevronDownIcon, ChevronUpIcon, Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';
import { system_information } from '@/Lang/en';

const route = useRoute();
const menuLinks = [
    { href: '/dashboard', name: 'Home', icon: 'fi fi-rr-home', isActive: true },
    { href: '/residents', name: 'Residents', icon: 'fi fi-rr-people-group', isActive: false },
    {
        href: '/documents',
        name: 'Documents',
        icon: 'fi fi-rr-document-signed',
        isActive: false,
        children: [
            { href: '/documents/dashboard', name: 'Dashboard', icon: 'fi fi-rr-document-signed', isActive: false },
            { href: '/documents/list-documents', name: 'List', icon: 'fi fi-rr-document-signed', isActive: false },
        ],
    },
    { href: '/complaints', name: 'Complaints', icon: 'fi fi-rr-triangle-warning', isActive: false },
    { href: '/blotter', name: 'Blotter', icon: 'fi fi-rr-shield', isActive: false },
    { href: '/projects', name: 'Projects', icon: 'fi fi-rr-chart-pie-alt', isActive: false },
    { href: '/officials', name: 'Officials', icon: 'fi fi-rr-shirt', isActive: false },
    { href: '/health', name: 'Health Services', icon: 'fi fi-rr-heart', isActive: false },
    { href: '/announcement-events', name: 'Announcements & Events', icon: 'fi fi-rr-megaphone', isActive: false },
];

const subMenuActiveParentIndex = ref();
const collapsed = ref(true);
const mobileOpen = ref(false);

const toggleSubMenu = (index) => {
    subMenuActiveParentIndex.value = subMenuActiveParentIndex.value === index ? null : index;
};

const toggleMobileSidebar = () => {
    mobileOpen.value = !mobileOpen.value;
};
</script>

<template>
    <!-- Mobile Hamburger -->
    <button @click="toggleMobileSidebar" class="md:hidden fixed top-4 left-4 z-50 bg-green-700 text-white p-2 rounded">
        <template v-if="mobileOpen">
            <XMarkIcon class="h-6 w-6" />
        </template>
        <template v-else>
            <Bars3Icon class="h-6 w-6" />
        </template>
    </button>

    <!-- Sidebar -->
    <aside @mouseenter="collapsed = false" @mouseleave="collapsed = true" :class="[
        'bg-white text-gray-500 flex flex-col p-4 shadow-sm sticky top-0 duration-300 z-40',
        collapsed ? 'w-20 md:w-20' : 'w-64 md:w-64',
        mobileOpen ? 'fixed inset-0 w-64 md:relative md:translate-x-0' : '-translate-x-full md:translate-x-0'
    ]">
        <!-- Logo -->
        <div class="flex items-center space-x-3 p-2 mb-6">
            <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0v-4m0 4h5m0 0v-4m0 4h5m0 0v-4m0 4h5M5 21v-4a2 2 0 012-2h10a2 2 0 012 2v4">
                </path>
            </svg>
            <h1 v-if="!collapsed" class="text-2xl font-bold text-gray-700">{{ system_information.title }}</h1>
        </div>

        <!-- Menu Links -->
        <template v-for="(menuLink, index) in menuLinks" :key="index">
            <div class="flex flex-col">
                <div class="flex items-center p-2 text-base font-normal mt-5 duration-700 rounded-lg grow" :class="route.path.startsWith(menuLink.href)
                    ? 'text-white bg-green-700 scale-105'
                    : 'text-gray-700 hover:bg-green-700 hover:text-white hover:scale-105'">
                    <router-link :to="menuLink.href" class="flex items-center w-full">
                        <i :class="menuLink.icon"></i>
                        <span v-if="!collapsed" class="ml-3 uppercase">{{ menuLink.name }}</span>
                    </router-link>

                    <template v-if="menuLink.children && !collapsed">
                        <button @click="toggleSubMenu(index)" class="flex items-center p-2">
                            <ChevronDownIcon v-if="subMenuActiveParentIndex == index" class="h-5 w-5" />
                            <ChevronUpIcon v-else class="h-5 w-5" />
                        </button>
                    </template>
                </div>

                <!-- Submenu -->
                <template v-for="(subMenuLink, subIndex) in menuLink.children" :key="subIndex">
                    <router-link v-if="subMenuActiveParentIndex == index && !collapsed" :to="subMenuLink.href"
                        class="flex items-center p-2 text-base font-normal mt-2 rounded-lg duration-700" :class="route.path.startsWith(subMenuLink.href)
                            ? 'text-white bg-green-700 scale-105'
                            : 'text-gray-700 hover:bg-green-700 hover:text-white hover:scale-105'">
                        <i :class="subMenuLink.icon"></i>
                        <span class="ml-3 uppercase">{{ subMenuLink.name }}</span>
                    </router-link>
                </template>
            </div>
        </template>
    </aside>
</template>
