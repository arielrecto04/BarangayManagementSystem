<script setup>
import { defineEmits } from "vue";
import Sidebar from "@/Components/Sidebar.vue";
import Header from "@/Components/Header.vue";
// import NavBar from "@/Components/NavBar.vue"; // optional

const emit = defineEmits(["search"]);
</script>

<template>
    <div class="flex min-h-screen w-full bg-gray-50">
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 gap-2 p-5 overflow-x-auto bg-gray-50">
            <!-- Optional Navbar -->
            <!-- <NavBar /> -->

            <!-- Header -->
            <Header @search="emit('search', $event)" />

            <!-- Page Content -->
            <div class="flex-1 w-full">
                <slot />
            </div>
        </div>
    </div>
</template>
