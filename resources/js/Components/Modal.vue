<script setup>
import { onMounted, onBeforeUnmount } from 'vue';

const emit = defineEmits(['close']);
const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    maxWidth: {
        type: String,
        default: '2xl' // sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl
    },
    closeable: {
        type: Boolean,
        default: true
    }
});

// Close on escape key
const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

// Close modal
const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

// Handle backdrop click - prevent event bubbling
const handleBackdropClick = (e) => {
    // Only close if the click target is the backdrop itself, not a child element
    if (e.target === e.currentTarget) {
        close();
    }
};

// Add event listeners when component is mounted
onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
});

// Clean up event listeners when component is unmounted
onBeforeUnmount(() => {
    document.removeEventListener('keydown', closeOnEscape);
});
</script>

<template>
    <teleport to="body">
        <transition enter-active-class="ease-out duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-show="show" class="fixed inset-0 overflow-y-auto z-50" scroll-region>
                <!-- Overlay with proper background and click handling -->
                <transition enter-active-class="ease-out duration-200" enter-from-class="opacity-0"
                    enter-to-class="opacity-100" leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100" leave-to-class="opacity-0">
                    <div v-show="show" class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                        @click="handleBackdropClick">
                    </div>
                </transition>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <transition enter-active-class="ease-out duration-300"
                        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-active-class="ease-in duration-200"
                        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <div v-show="show"
                            class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 w-full"
                            :class="{
                                'sm:max-w-sm': maxWidth === 'sm',
                                'sm:max-w-md': maxWidth === 'md',
                                'sm:max-w-lg': maxWidth === 'lg',
                                'sm:max-w-xl': maxWidth === 'xl',
                                'sm:max-w-2xl': maxWidth === '2xl',
                                'sm:max-w-3xl': maxWidth === '3xl',
                                'sm:max-w-4xl': maxWidth === '4xl',
                                'sm:max-w-5xl': maxWidth === '5xl',
                                'sm:max-w-6xl': maxWidth === '6xl',
                                'sm:max-w-7xl': maxWidth === '7xl',
                                'sm:w-full': !maxWidth
                            }" @click.stop>
                            <!-- Header -->
                            <div v-if="title || closeable"
                                class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ title }}
                                </h3>

                                <button v-if="closeable" @click="close"
                                    class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Content -->
                            <div class="px-6 py-4">
                                <slot></slot>
                            </div>

                            <!-- Footer -->
                            <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 text-right border-t border-gray-200">
                                <slot name="footer"></slot>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </transition>
    </teleport>
</template>