<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  official: {
    type: Object,
    required: true
  },
  size: {
    type: String,
    default: 'md', // xs, sm, md, lg, xl
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  colorScheme: {
    type: String,
    default: 'blue'
  },
  showActions: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['view', 'edit', 'delete']);

// Helper function to format name properly
const formatName = (name) => {
  if (!name) return 'Unknown Official';

  if (name.includes('null')) {
    return name
      .replace(/\s+null\s+/gi, ' ')
      .replace(/^null\s+/gi, '')
      .replace(/\s+null$/gi, '')
      .trim();
  }

  return name;
};

// Size configurations
const sizeConfig = {
  xs: {
    container: 'px-2 py-2',
    avatar: 'w-10 h-10',
    name: 'text-xs',
    position: 'text-xs',
    description: 'text-xs line-clamp-1',
    terms: 'text-xs'
  },
  sm: {
    container: 'px-3 py-2',
    avatar: 'w-12 h-12',
    name: 'text-sm',
    position: 'text-xs',
    description: 'text-xs line-clamp-2',
    terms: 'text-xs'
  },
  md: {
    container: 'px-4 py-3',
    avatar: 'w-16 h-16',
    name: 'text-base',
    position: 'text-sm',
    description: 'text-xs line-clamp-3',
    terms: 'text-xs'
  },
  lg: {
    container: 'px-6 py-4',
    avatar: 'w-20 h-20 lg:w-24 lg:h-24',
    name: 'text-lg sm:text-xl',
    position: 'text-sm sm:text-base',
    description: 'text-xs sm:text-sm line-clamp-3',
    terms: 'text-xs sm:text-sm'
  },
  xl: {
    container: 'px-6 py-4',
    avatar: 'w-24 h-24',
    name: 'text-xl',
    position: 'text-base',
    description: 'text-sm line-clamp-3',
    terms: 'text-sm'
  }
};

const config = sizeConfig[props.size];

// Color scheme configurations
const colorSchemes = {
  blue: 'from-blue-100 to-blue-200 text-blue-600',
  green: 'from-green-100 to-green-200 text-green-600',
  purple: 'from-purple-100 to-purple-200 text-purple-600',
  orange: 'from-orange-100 to-orange-200 text-orange-600',
  red: 'from-red-100 to-red-200 text-red-600',
  teal: 'from-teal-100 to-teal-200 text-teal-600',
  indigo: 'from-indigo-100 to-indigo-200 text-indigo-600',
  pink: 'from-pink-100 to-pink-200 text-pink-600',
  yellow: 'from-yellow-100 to-yellow-200 text-yellow-600',
  lime: 'from-lime-100 to-lime-200 text-lime-600',
  cyan: 'from-cyan-100 to-cyan-200 text-cyan-600',
  sky: 'from-sky-100 to-sky-200 text-sky-600',
  emerald: 'from-emerald-100 to-emerald-200 text-emerald-600',
  violet: 'from-violet-100 to-violet-200 text-violet-600',
  rose: 'from-rose-100 to-rose-200 text-rose-600',
  amber: 'from-amber-100 to-amber-200 text-amber-600'
};

const colorClass = colorSchemes[props.colorScheme] || colorSchemes.blue;

// Get official image or fallback to resident's avatar or placeholder
const getOfficialImage = (official) => {
  // Check if official has image property and it's not null/empty
  if (official.image && official.image.trim() !== '') {
    // If it's a full URL, use it directly
    if (official.image.startsWith('http')) {
      return official.image;
    }
    // If it's a relative path, construct the full URL
    return `/storage/${official.image}`;
  }

  // If official doesn't have image but has resident data with avatar
  if (official.resident && official.resident.avatar) {
    // Handle both full URLs and relative paths for resident avatars
    if (official.resident.avatar.startsWith('http')) {
      return official.resident.avatar;
    }
    // If it's a storage path, ensure proper URL construction
    return official.resident.avatar.startsWith('/') ? official.resident.avatar : `/${official.resident.avatar}`;
  }

  return null; // No image available
};

const officialImage = getOfficialImage(props.official);
</script>

<template>
  <div class="bg-white rounded-xl shadow-lg text-center relative group" :class="config.container">
    <!-- Avatar/Image -->
    <div v-if="officialImage" class="mx-auto mb-3 overflow-hidden rounded-full" :class="config.avatar">
      <img :src="officialImage" :alt="formatName(official.name)" class="w-full h-full object-cover"
        @error="$event.target.style.display = 'none'; $event.target.nextElementSibling.style.display = 'block'" />
      <!-- Fallback div shown when image fails to load -->
      <div :class="[config.avatar, `bg-gradient-to-br ${colorClass.split(' ')[0]} ${colorClass.split(' ')[1]}`]"
        class="mx-auto rounded-full hidden"></div>
    </div>
    <!-- Placeholder when no image -->
    <div v-else :class="[config.avatar, `bg-gradient-to-br ${colorClass.split(' ')[0]} ${colorClass.split(' ')[1]}`]"
      class="mx-auto mb-3 rounded-full"></div>

    <!-- Name -->
    <h2 :class="config.name" class="font-bold text-gray-800 break-words">
      {{ formatName(official.name) }}
    </h2>

    <!-- Position -->
    <p :class="[config.position, colorClass.split(' ')[2]]" class="font-semibold break-words">
      {{ official.position }}
    </p>

    <!-- Description (only show for md and larger) -->
    <p v-if="['md', 'lg', 'xl'].includes(size)" :class="config.description" class="text-gray-500 mt-2 break-words">
      {{ official.description }}
    </p>

    <!-- Terms -->
    <p class="text-gray-600 break-words" :class="[config.terms, { 'mt-2': ['md', 'lg', 'xl'].includes(size) }]">
      {{ size === 'xs' ? official.terms : `Term: ${official.terms}` }}
    </p>


    <!-- Action Buttons -->
    <div v-if="showActions"
      class="absolute top-2 right-2 flex gap-1 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity duration-200"
      :class="{ 'top-1 right-1': ['xs', 'sm'].includes(size) }">
      <!-- View Button -->
      <button @click="$emit('view', official)"
        class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs" title="View">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
      </button>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>