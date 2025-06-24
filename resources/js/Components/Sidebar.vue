<script setup>
const menuLinks = [
    {
        href: '/',
        name: 'Home',
        icon: 'fi fi-rr-home',
        isActive: true,
    },
    {
        href: '/',
        name: 'Residents',
        icon: 'fi fi-rr-people-group',
        isActive: false,
    },
    {
        href: '/',
        name: 'Documents',
        icon: 'fi fi-rr-document-signed',
        isActive: false,
    },
    {
        href: '/',
        name: 'Complaints',
        icon: 'fi fi-rr-triangle-warning',
        isActive: false,
    },
    {
        href: '/',
        name: 'Blotter',
        icon: 'fi fi-rr-shield',
        isActive: false,
    },
    {
        href: '/',
        name: 'Projects',
        icon: 'fi fi-rr-chart-pie-alt',
        isActive: false,
    },
    {
        href: '/',
        name: 'Officials',
        icon: 'fi fi-rr-shirt',
        isActive: false,
    }
]

</script>

<template>
    <aside class="w-64 bg-white text-gray-500 flex flex-col p-4 flex-shrink-0 shadow-sm">
        <!-- Logo -->
        <div class="flex items-center space-x-3 p-2 mb-6">
            <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0v-4m0 4h5m0 0v-4m0 4h5m0 0v-4m0 4h5M5 21v-4a2 2 0 012-2h10a2 2 0 012 2v4">
                </path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-700
            ">BIMS</h1>
        </div>
        <template v-for="(menuLink, index) in menuLinks" :key="index">

            <a href="#"
                :class="`flex items-center p-2 text-base font-normal mt-5  ${menuLink.isActive ? 'text-white bg-green-700 scale-105 ' : 'text-gray-700 hover:bg-green-700 hover:text-white hover:scale-105'} duration-700 rounded-lg`">
                <i :class="menuLink.icon"></i>
                <span class="ml-3 uppercase">
                    {{ menuLink.name }}
                </span>
            </a>

        </template>
    </aside>
</template>
