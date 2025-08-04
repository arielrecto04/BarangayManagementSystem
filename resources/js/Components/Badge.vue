<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) => 
            ['new request', 'processing', 'approved', 'rejected', 'cancelled'].includes(value)
    },
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['default', 'outline', 'solid'].includes(value)
    }
});

const type = computed(() => {
    const baseClasses = 'px-2 py-1 text-xs font-medium rounded-full text-center';
    
    // Define status colors
    const statusColors = {
        'new request': 'bg-blue-100 text-blue-800 border-blue-200',
        'processing': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'approved': 'bg-green-100 text-green-800 border-green-200',
        'rejected': 'bg-red-100 text-red-800 border-red-200',
        'cancelled': 'bg-gray-100 text-gray-800 border-gray-200'
    };

    // Get base color classes
    const baseColor = statusColors[props.status] || 'bg-gray-100 text-gray-800 border-gray-200';
    
    // Handle different variants
    if (props.variant === 'outline') {
        return `${baseClasses} border ${baseColor.replace(/bg-(\w+)-100/g, 'border-$1-300')} bg-transparent`;
    }
    
    if (props.variant === 'solid') {
        return `${baseClasses} ${baseColor
            .replace(/bg-(\w+)-100/g, 'bg-$1-600')
            .replace(/text-(\w+)-800/g, 'text-white')
            .replace(/border-(\w+)-200/g, 'border-transparent')}`;
    }
    
    // Default variant
    return `${baseClasses} ${baseColor}`;
});

const displayText = computed(() => {
    return props.status
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
});
</script>

<template>
    <span :class="type">
        {{ displayText }}
    </span>
</template>