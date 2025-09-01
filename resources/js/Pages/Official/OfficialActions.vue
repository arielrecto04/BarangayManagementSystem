<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    official: Object
});

const emit = defineEmits(['view', 'edit', 'delete']);

const handleView = () => emit('view', props.official);
</script>

<template>
    <div class="absolute top-2 right-2 flex gap-1">
        <!-- Only View button visible here -->
        <button @click="handleView" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs"
            title="View">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
    </div>
</template>
