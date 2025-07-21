<script setup>
import { BaseLayout } from '@/Layouts'
</script>

<template>
    <BaseLayout>
        <h1>Hello World</h1>
        <router-link to="/login" class="w-full h-full cursor-pointer"> login</router-link>
    </BaseLayout>
</template>
