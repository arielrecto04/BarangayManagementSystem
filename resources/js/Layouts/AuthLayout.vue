<script setup>
    import { Sidebar, NavBar, Header } from "@/Components";
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <Sidebar />
        <div class="flex flex-col gap-2 grow p-5">
            <NavBar />
            <Header />
            <slot />
        </div>
    </div>

</template>
