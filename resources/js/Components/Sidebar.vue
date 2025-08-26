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
    <div class="flex">
        <!-- Mobile Hamburger -->
        <button @click="toggleMobileSidebar"
            class="md:hidden fixed top-4 left-4 z-50 bg-green-700 text-white p-2 rounded">
            <template v-if="mobileOpen">
                <XMarkIcon class="h-6 w-6" />
            </template>
            <template v-else>
                <Bars3Icon class="h-6 w-6" />
            </template>
        </button>

        <!-- Sidebar -->
        <aside @mouseenter="collapsed = false" @mouseleave="collapsed = true" :class="[
            'bg-white text-gray-500 flex flex-col p-4 shadow-lg duration-300 h-screen overflow-y-auto',
            collapsed ? 'w-20' : 'w-64',
            // Desktop: sticky positioning to follow scroll
            'md:sticky md:top-0',
            // Mobile styles - higher z-index to overlap content, remove overlay
            mobileOpen ? 'fixed left-0 top-0 z-40 translate-x-0' : 'md:block hidden'
        ]">
            <!-- Logo -->
            <div class="flex items-center space-x-3 p-2 mb-6">
                <svg class="w-8 h-8 text-gray-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0v-4m0 4h5m0 0v-4m0 4h5m0 0v-4m0 4h5M5 21v-4a2 2 0 012-2h10a2 2 0 012 2v4">
                    </path>
                </svg>
                <h1 v-if="!collapsed" class="text-2xl font-bold text-gray-700 truncate">{{ system_information.title }}
                </h1>
            </div>

            <!-- Menu Links -->
            <nav class="flex-1">
                <template v-for="(menuLink, index) in menuLinks" :key="index">
                    <div class="flex flex-col">
                        <div class="flex items-center p-2 text-base font-normal mt-5 duration-700 rounded-lg grow"
                            :class="route.path.startsWith(menuLink.href)
                                ? 'text-white bg-green-700 scale-105'
                                : 'text-gray-700 hover:bg-green-700 hover:text-white hover:scale-105'">
                            <router-link :to="menuLink.href" class="flex items-center flex-1 min-w-0"
                                @click="mobileOpen = false">
                                <i :class="[menuLink.icon, 'flex-shrink-0']"></i>
                                <span v-if="!collapsed" class="ml-3 uppercase truncate">{{ menuLink.name }}</span>
                            </router-link>

                            <template v-if="menuLink.children && !collapsed">
                                <button @click="toggleSubMenu(index)" class="flex items-center p-2 flex-shrink-0">
                                    <ChevronDownIcon v-if="subMenuActiveParentIndex == index" class="h-5 w-5" />
                                    <ChevronUpIcon v-else class="h-5 w-5" />
                                </button>
                            </template>
                        </div>

                        <!-- Submenu -->
                        <template v-for="(subMenuLink, subIndex) in menuLink.children" :key="subIndex">
                            <router-link v-if="subMenuActiveParentIndex == index && !collapsed" :to="subMenuLink.href"
                                class="flex items-center p-2 text-base font-normal mt-2 rounded-lg duration-700 ml-4"
                                :class="route.path.startsWith(subMenuLink.href)
                                    ? 'text-white bg-green-700 scale-105'
                                    : 'text-gray-700 hover:bg-green-700 hover:text-white hover:scale-105'"
                                @click="mobileOpen = false">
                                <i :class="[subMenuLink.icon, 'flex-shrink-0']"></i>
                                <span class="ml-3 uppercase truncate">{{ subMenuLink.name }}</span>
                            </router-link>
                        </template>
                    </div>
                </template>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            <!-- Your main content goes here -->
            <slot />
        </main>
    </div>
</template>