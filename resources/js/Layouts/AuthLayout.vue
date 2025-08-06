<script setup>
    import { Sidebar, NavBar, Header } from "@/Components";
    import { defineEmits } from "vue";


    const emit = defineEmits([
        'search'
    ])

</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <Sidebar />
        <div class="flex flex-col gap-2 grow p-5">
            <!-- <NavBar /> -->
            <Header @search="emit('search', $event)" />
            <slot />
        </div>
    </div>

</template>
