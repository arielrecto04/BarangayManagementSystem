<script setup>
import { computed, h } from 'vue';

const props = defineProps({
    /**
     * File name or extension to determine the icon
     */
    file: {
        type: String,
        required: false
    },
    /**
     * Size of the icon
     * @values 'sm', 'md', 'lg', 'xl'
     */
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
    },
    /**
     * Custom color for the icon
     */
    color: {
        type: String,
        default: ''
    },
    type: {
        type: String,
        default: 'docx'
    }
});

const sizeClasses = computed(() => ({
    'h-4 w-4': props.size === 'sm',
    'h-6 w-6': props.size === 'md',
    'h-8 w-8': props.size === 'lg',
    'h-12 w-12': props.size === 'xl',
    [props.color]: !!props.color
}));

// Get file extension from filename
const fileExtension = computed(() => {
    if (!props.file) return '';
    const parts = props.file.split('.');
    return parts.length > 1 ? parts.pop().toLowerCase() : '';
});

// Determine file type based on extension
const fileType = computed(() => {
    const extension = fileExtension.value || props.type;

    // Document types
    if (['doc', 'docx', 'odt', 'rtf', 'txt', 'md'].includes(extension)) {
        return 'document';
    }

    // Spreadsheet types
    if (['xls', 'xlsx', 'ods', 'csv'].includes(extension)) {
        return 'spreadsheet';
    }

    // Presentation types
    if (['ppt', 'pptx', 'odp'].includes(extension)) {
        return 'presentation';
    }

    // Image types
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'tiff'].includes(extension)) {
        return 'image';
    }

    // Archive types
    if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
        return 'archive';
    }

    // Video types
    if (['mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv'].includes(extension)) {
        return 'video';
    }

    // Audio types
    if (['mp3', 'wav', 'ogg', 'm4a', 'flac'].includes(extension)) {
        return 'audio';
    }

    // Code types
    if (['js', 'jsx', 'ts', 'tsx', 'py', 'java', 'c', 'cpp', 'h', 'hpp', 'cs', 'php', 'rb', 'go', 'rs', 'swift', 'kt', 'dart'].includes(extension)) {
        return 'code';
    }

    if(['rar','zip','7z','tar','gz'].includes(extension)) return 'archive';

    // Specific file types
    if (extension === 'pdf') return 'pdf';
    if (extension === 'html') return 'html';
    if (extension === 'css') return 'css';
    if (extension === 'json') return 'json';
    if (extension === 'xml') return 'xml';

    return 'unknown';
});

// SVG Icons
const icons = {
    document: {
        viewBox: '0 0 24 24',
        path: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm8 16h-2v-5h2v5zm0-7h-2v-2h2v2z',
        color: 'text-blue-500'
    },
    spreadsheet: {
        viewBox: '0 0 24 24',
        path: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 4h7v5h5v11H6V4zm4 14v-4h4v4h-4zm0-6V8h4v4h-4z',
        color: 'text-green-500'
    },
    presentation: {
        viewBox: '0 0 24 24',
        path: 'M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-9 12H9v2H7v-2H5v-2h2V9h2v2h2v2zm2-2h6v2h-6v-2zm6-4h-6V7h6v2z',
        color: 'text-orange-500'
    },
    pdf: {
        viewBox: '0 0 24 24',
        path: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM6 20V4h7v5h5v11H6zm4-8h2v4h-2v-4zm0-6h2v2h-2V6zm4 6h2v4h-2v-4zm0-6h2v2h-2V6z',
        color: 'text-red-500'
    },
    image: {
        viewBox: '0 0 24 24',
        path: 'M19 5v14H5V5h14m0-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-4.86 8.86l-3 3.87L9 13.14 6 17h12l-3.86-5.14z',
        color: 'text-purple-500'
    },
    video: {
        viewBox: '0 0 24 24',
        path: 'M21 3H3a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-9 13V6l7 5-7 5z',
        color: 'text-red-400'
    },
    audio: {
        viewBox: '0 0 24 24',
        path: 'M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6zm-2 16c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z',
        color: 'text-blue-400'
    },
    archive: {
        viewBox: '0 0 24 24',
        path: 'M20.54 5.23l-1.39-1.68C18.88 3.21 18.47 3 18 3H6c-.47 0-.88.21-1.16.55L3.46 5.23C3.17 5.57 3 6.02 3 6.5V19c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6.5c0-.48-.17-.93-.46-1.27zM12 17.5L6.5 12H10v-2h4v2h3.5L12 17.5zM5.12 5l.81-1h12l.94 1H5.12z',
        color: 'text-yellow-500'
    },
    code: {
        viewBox: '0 0 24 24',
        path: 'M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z',
        color: 'text-gray-700'
    },
    html: {
        viewBox: '0 0 24 24',
        path: 'M12 17.56l4.07-1.13.55-6.1H9.38L9.2 8.3h7.6l.2-1.99H7l.56 6.01h6.89l-.23 2.58-2.22.6-2.22-.6-.14-1.66h-2l.29 3.19L12 17.56zM4.07 3h15.86L18.5 19.2 12 21l-6.5-1.8L4.07 3z',
        color: 'text-orange-500'
    },
    css: {
        viewBox: '0 0 24 24',
        path: 'M5.5 3h13l-2.2 24.3-9.6 2.7-9.6-2.7L.5 3h13zm1.5 3.5h-6.8l.4 4.4h6.4l-.2 2.4H4.2l.4 4.4h6.8l-.2 2.4-4.8 1.3-.3 3.3 6.1 1.7 6.1-1.7.3-3.7-3.7-1 .1-1.1h2.3l.2 2.6 4.1 1.1.3-4.5-11.1.1.1-1.1h11.1l.1-1.1H5.9l-.4-4.4z',
        color: 'text-blue-500'
    },
    json: {
        viewBox: '0 0 24 24',
        path: 'M5 3h2v18H5V3zm14 0h2v18h-2V3zm-7 3h2v12h-2V6zm-4 3h2v6H8V9zm8 0h2v6h-2V9z',
        color: 'text-yellow-600'
    },
    xml: {
        viewBox: '0 0 24 24',
        path: 'M20.5 11H19V7c0-1.1-.9-2-2-2h-4V3.5a2.5 2.5 0 0 0-5 0V5H4c-1.1 0-1.99.9-1.99 2v3.8H3.5c1.49 0 2.7 1.21 2.7 2.7s-1.21 2.7-2.7 2.7H2V20c0 1.1.9 2 2 2h3.8v-1.5c0-1.49 1.21-2.7 2.7-2.7s2.7 1.21 2.7 2.7V22H17c1.1 0 2-.9 2-2v-4h1.5a2.5 2.5 0 0 0 0-5z',
        color: 'text-blue-600'
    },
    unknown: {
        viewBox: '0 0 24 24',
        path: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z',
        color: 'text-gray-500'
    }
};

// Get the appropriate icon component based on file type
const getFileIcon = computed(() => {
    const icon = icons[fileType.value] || icons.unknown;

    return h('svg', {
        xmlns: 'http://www.w3.org/2000/svg',
        viewBox: icon.viewBox,
        fill: 'currentColor',
        class: [icon.color, props.color || ''] // Use an array for dynamic classes
    },
        h('path', { d: icon.path })
    );
});
</script>


<template>
    <div class="file-icon" :class="sizeClasses">
        <component :is="getFileIcon" />
    </div>
</template>



<style scoped>
.file-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
