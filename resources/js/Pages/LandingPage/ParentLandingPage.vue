<script setup>

import { BaseLayout } from '@/Layouts';
import { NavBar } from '@/Components';

</script>




<template>
    <BaseLayout>
     <div class="w-screen flex flex-col">
        <NavBar />
        <div class="w-full mx-auto">
            <router-view />
        </div>
     </div>
    </BaseLayout>
</template>
