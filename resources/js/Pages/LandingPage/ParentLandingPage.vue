<script setup>

import { BaseLayout } from '@/Layouts';
import { NavBar } from '@/Components';

</script>




<template>
    <BaseLayout>
     <div class="w-full flex flex-col">
        <NavBar />
        <div class="w-5/6 mx-auto">
            <router-view />
        </div>
     </div>
    </BaseLayout>
</template>
