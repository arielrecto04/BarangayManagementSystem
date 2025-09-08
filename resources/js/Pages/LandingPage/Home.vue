<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { MapPinIcon, PhoneIcon, EnvelopeIcon, ClockIcon, EyeIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import { useAnnouncementEventStore } from '@/Stores';
import { storeToRefs } from 'pinia';
import Modal from '@/Components/Modal.vue';

// Hardcoded data for About + Services + Contact
const barangayInfo = {
  name: 'Barangay San Isidro',
  city: 'San Pablo City',
  province: 'Laguna',
  motto: 'Serbisyong Mabilis, Maayos, at May Malasakit',
  about: 'Barangay San Isidro is a progressive community committed to providing excellent public service to its residents. We strive to maintain peace and order, promote sustainable development, and enhance the quality of life of our constituents.',
  vision: 'A progressive, peaceful, and resilient community with empowered and God-loving citizens enjoying a healthy and sustainable environment under a dynamic and transparent governance.',
  mission: 'To deliver quality public service through effective governance, active community participation, and sustainable development programs that will uplift the living condition of every Isidrenian.'
};

const services = [
  { title: 'Barangay Clearance', description: 'Official document issued for various business and personal transactions', icon: '📝' },
  { title: 'Barangay ID', description: 'Valid identification card for all registered residents', icon: '🆔' },
  { title: 'Business Permit', description: 'Required for operating businesses within the barangay', icon: '🏪' },
  { title: 'Certificate of Residency', description: 'Proof of residence for various requirements', icon: '🏠' },
  { title: 'Certificate of Indigency', description: 'For availing government assistance and benefits', icon: '🤝' }
];

const contactInfo = {
  address: '123 Rizal Street, Barangay San Isidro, San Pablo City, Laguna',
  phone: '(049) 123-4567',
  email: 'sanisidro.brgy@sanpablocity.gov.ph',
  officeHours: 'Monday to Friday, 8:00 AM - 5:00 PM',
  emergencyHotline: '911'
};

// Store for announcements and events
const announcementEventStore = useAnnouncementEventStore();
const { events, isLoading } = storeToRefs(announcementEventStore);
const activeTab = ref('announcements');
const showModal = ref(false);
const selectedEvent = ref(null);

// Carousel state
const carouselPosition = ref(0);
const itemsPerView = ref(3); // Default number of items to show

// Auto-refresh setup
let refreshInterval;

// Update items per view based on screen size
const updateItemsPerView = () => {
  if (window.innerWidth < 640) {
    itemsPerView.value = 1;
  } else if (window.innerWidth < 1024) {
    itemsPerView.value = 2;
  } else {
    itemsPerView.value = 3;
  }
};

// ✅ Unified onMounted (fetch + auto-refresh + resize listener)
onMounted(async () => {
  await announcementEventStore.getEvents(1, 12); // Fetch more items for carousel

  // Set up window resize listener
  updateItemsPerView();
  window.addEventListener('resize', updateItemsPerView);

  refreshInterval = setInterval(async () => {
    await announcementEventStore.getEvents(1, 12);
  }, 30000);
});

onUnmounted(() => {
  clearInterval(refreshInterval);
  window.removeEventListener('resize', updateItemsPerView);
});

// Date formatting
const formatDate = (dateString) => {
  if (!dateString) return 'N/A';

  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return 'Invalid Date';

    const options = {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      hour12: true,
    };

    return date.toLocaleDateString('en-US', options);
  } catch (error) {
    console.error('Date formatting error:', error);
    return 'Invalid Date';
  }
};

// Status color helper
const getStatusColor = (status) => {
  switch (status) {
    case 'Ongoing':
      return 'bg-emerald-500/90 text-white';
    case 'Upcoming':
      return 'bg-amber-500/90 text-white';
    case 'Past':
      return 'bg-slate-500/90 text-white';
    default:
      return 'bg-slate-500/90 text-white';
  }
};

// Modal functions
const openModal = (eventData) => {
  selectedEvent.value = eventData;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  selectedEvent.value = null;
};

// Carousel navigation functions
const nextSlide = () => {
  const items = displayedItems.value;
  const maxPosition = Math.max(0, items.length - itemsPerView.value);

  if (carouselPosition.value < maxPosition) {
    carouselPosition.value += 1;
  } else {
    carouselPosition.value = 0; // Loop back to start
  }
};

const prevSlide = () => {
  const items = displayedItems.value;
  const maxPosition = Math.max(0, items.length - itemsPerView.value);

  if (carouselPosition.value > 0) {
    carouselPosition.value -= 1;
  } else {
    carouselPosition.value = maxPosition; // Loop to end
  }
};

// Reset carousel position when tab changes
watch(activeTab, () => {
  carouselPosition.value = 0;
});

// Computed: all announcements
const announcements = computed(() =>
  events.value
    .filter(item => item.type === 'announcement')
    .sort((a, b) => new Date(b.start_date) - new Date(a.start_date))
);

// Computed: upcoming events
const upcomingEvents = computed(() =>
  events.value
    .filter(item => item.type === 'event' && new Date(item.start_date) > new Date())
    .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
);

// Computed: ongoing events
const ongoingEvents = computed(() =>
  events.value
    .filter(item => item.status === 'Ongoing')
    .sort((a, b) => new Date(a.start_date) - new Date(b.start_date))
);

// Computed: past events
const pastEvents = computed(() =>
  events.value
    .filter(item => item.status === 'Past')
    .sort((a, b) => new Date(b.start_date) - new Date(a.start_date))
);

// Currently displayed items based on active tab
const displayedItems = computed(() => {
  switch (activeTab.value) {
    case 'announcements':
      return announcements.value;
    case 'upcoming':
      return upcomingEvents.value;
    case 'ongoing':
      return ongoingEvents.value;
    case 'past':
      return pastEvents.value;
    default:
      return announcements.value;
  }
});

// Visible items for carousel
const visibleItems = computed(() => {
  const start = carouselPosition.value;
  const end = start + itemsPerView.value;
  return displayedItems.value.slice(start, end);
});

// Carousel indicators
const carouselIndicators = computed(() => {
  const totalItems = displayedItems.value.length;
  if (totalItems <= itemsPerView.value) return [];

  const indicatorCount = Math.ceil(totalItems / itemsPerView.value);
  return Array.from({ length: indicatorCount }, (_, i) => i);
});

// Go to specific slide
const goToSlide = (index) => {
  carouselPosition.value = index * itemsPerView.value;
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 w-full">
    <!-- Hero Section -->
    <div class="relative bg-green-800 text-white">
      <div class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1567013127542-490d757e51fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80"
          alt="Barangay Hall" class="w-full h-full object-cover opacity-20">
      </div>
      <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
          Welcome to {{ barangayInfo.name }}
        </h1>
        <p class="mt-6 text-xl max-w-3xl">
          {{ barangayInfo.motto }}
        </p>
        <div class="mt-10 flex space-x-4">
          <a href="#services" class="bg-white text-green-700 px-6 py-3 rounded-md font-medium hover:bg-green-50">
            Our Services
          </a>
          <a href="#announcements" class="bg-green-600 text-white px-6 py-3 rounded-md font-medium hover:bg-green-700">
            Announcements & Events
          </a>
        </div>
      </div>
    </div>

    <!-- About Section -->
    <div class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
          <h2 class="text-base text-green-600 font-semibold tracking-wide uppercase">About Us</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            {{ barangayInfo.name }}, {{ barangayInfo.city }}
          </p>
          <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            {{ barangayInfo.about }}
          </p>
        </div>

        <div class="mt-20">
          <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Our Vision</h3>
              <p class="mt-2 text-base text-gray-500">
                {{ barangayInfo.vision }}
              </p>
            </div>
            <div>
              <h3 class="text-lg font-medium text-gray-900">Our Mission</h3>
              <p class="mt-2 text-base text-gray-500">
                {{ barangayInfo.mission }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Section -->
    <div id="services" class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
          <h2 class="text-base text-green-600 font-semibold tracking-wide uppercase">Services</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-3xl">
            Our Barangay Services
          </p>
          <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            We provide various services to cater to the needs of our community members.
          </p>
        </div>

        <div class="mt-10">
          <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="(service, index) in services" :key="index"
              class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
              <div class="text-4xl mb-4">{{ service.icon }}</div>
              <h3 class="text-lg font-medium text-gray-900">{{ service.title }}</h3>
              <p class="mt-2 text-base text-gray-500">
                {{ service.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Announcements & Events Section -->
    <div id="announcements" class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
          <h2 class="text-base text-green-600 font-semibold tracking-wide uppercase">Updates</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Announcements & Events
          </p>
        </div>

        <!-- Tabs  -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
          <div class="border-b border-gray-200">
            <nav class="flex space-x-4 px-4 sm:px-6 overflow-x-auto scrollbar-hide">
              <button @click="activeTab = 'announcements'" :class="[
                activeTab === 'announcements'
                  ? 'border-green-500 text-green-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm flex-shrink-0'
              ]">
                Announcements
              </button>
              <button @click="activeTab = 'upcoming'" :class="[
                activeTab === 'upcoming'
                  ? 'border-green-500 text-green-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm flex-shrink-0'
              ]">
                Upcoming Events
              </button>
              <button @click="activeTab = 'ongoing'" :class="[
                activeTab === 'ongoing'
                  ? 'border-green-500 text-green-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm flex-shrink-0'
              ]">
                Ongoing Events
              </button>
              <button @click="activeTab = 'past'" :class="[
                activeTab === 'past'
                  ? 'border-green-500 text-green-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm flex-shrink-0'
              ]">
                Past Events
              </button>
            </nav>
          </div>
        </div>


        <!-- Content -->
        <div class="px-6">
          <!-- Loading -->
          <div v-if="isLoading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
          </div>

          <!-- Empty State -->
          <div v-else-if="displayedItems.length === 0" class="bg-white rounded-2xl p-8 text-center shadow-sm">
            <div class="mx-auto h-24 w-24 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 20H5a2 2 0 01-2-23V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 3 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
              </svg>
            </div>
            <h3 class="mt-4 text-xl font-medium text-gray-900">No {{ activeTab }} at the moment</h3>
            <p class="mt-2 text-gray-500">Please check back later for updates</p>
          </div>

          <!-- Carousel -->
          <div v-else class="relative">
            <!-- Navigation Arrows -->
            <button v-if="displayedItems.length > itemsPerView" @click="prevSlide"
              class="absolute left-0 top-1/2 transform -translate-y-1/2 -translate-x-6 z-10 bg-white rounded-full p-2 shadow-md hover:bg-green-50 transition-colors">
              <ChevronLeftIcon class="w-6 h-6 text-green-600" />
            </button>

            <button v-if="displayedItems.length > itemsPerView" @click="nextSlide"
              class="absolute right-0 top-1/2 transform -translate-y-1/2 translate-x-6 z-10 bg-white rounded-full p-2 shadow-md hover:bg-green-50 transition-colors">
              <ChevronRightIcon class="w-6 h-6 text-green-600" />
            </button>

            <!-- Card Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
              <div v-for="event in visibleItems" :key="event.id"
                class="group relative bg-white/80 backdrop-blur-sm border border-white/50 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col min-h-[400px]">

                <!-- Image Section -->
                <div class="relative h-40 sm:h-44 md:h-48 overflow-hidden">
                  <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent z-10"></div>
                  <img v-if="event.image" :src="`/${event.image}`" :alt="event.title"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                  <div v-else
                    class="w-full h-full bg-gradient-to-br from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
                    <div class="text-white text-6xl opacity-30">
                      <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                          d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                          clip-rule="evenodd" />
                      </svg>
                    </div>
                  </div>

                  <!-- Center View Button (appears on hover) -->
                  <div
                    class="absolute inset-0 z-30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <button @click="openModal(event)"
                      class="w-14 h-14 bg-white/95 hover:bg-white backdrop-blur-sm border border-white/70 shadow-lg rounded-full flex items-center justify-center transition-all duration-300 hover:scale-105">
                      <EyeIcon class="w-6 h-6 text-green-600" />
                    </button>
                  </div>
                </div>

                <!-- Badges Section -->
                <div class="px-4 pt-3 pb-2 flex gap-2">
                  <span
                    :class="[getStatusColor(event.status || 'Upcoming'), 'backdrop-blur-sm px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow']">
                    {{ event.status || 'Upcoming' }}
                  </span>
                  <span :class="[
                    event.type === 'announcement' ? 'bg-blue-500/90 text-white' : 'bg-purple-500/90 text-white',
                    'backdrop-blur-sm px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider shadow'
                  ]">
                    {{ event.type }}
                  </span>
                </div>

                <!-- Content Section -->
                <div class="p-4 sm:p-5 space-y-3 flex-grow flex flex-col">
                  <!-- Title -->
                  <h3
                    class="font-bold text-lg md:text-xl text-slate-900 group-hover:text-green-700 transition-colors duration-300 line-clamp-2">
                    {{ event.title }}
                  </h3>

                  <!-- Author & Location -->
                  <div class="flex flex-wrap items-center gap-3 text-sm">
                    <!-- Author -->
                    <div v-if="event.author" class="flex items-center gap-2 min-w-0">
                      <div
                        class="shrink-0 w-7 h-7 md:w-8 md:h-8 rounded-full bg-gradient-to-r from-green-500 to-blue-600 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5 text-white" fill="none"
                          viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M5.121 17.804A9.969 9.969 0 0112 15c2.21 0 4.21.714 5.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                      </div>
                      <span class="font-semibold text-slate-700 truncate min-w-0">{{ event.author }}</span>
                    </div>

                    <!-- Location -->
                    <div v-if="event.location" class="flex items-center gap-1 text-slate-500 min-w-0">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 8c0 7.5-7.5 13.5-7.5 13.5S4.5 15.5 4.5 8a7.5 7.5 0 1115 0z" />
                      </svg>
                      <span class="truncate min-w-0">{{ event.location }}</span>
                    </div>
                  </div>

                  <!-- Dates -->
                  <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                      <div class="shrink-0 w-3 h-3 rounded-full bg-emerald-400"></div>
                      <span class="font-medium text-slate-600 shrink-0">Start:</span>
                      <span class="text-slate-900 truncate">{{ formatDate(event.start_date) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <div class="shrink-0 w-3 h-3 rounded-full bg-rose-400"></div>
                      <span class="font-medium text-slate-600 shrink-0">End:</span>
                      <span class="text-slate-900 truncate">{{ formatDate(event.end_date) }}</span>
                    </div>
                  </div>

                  <!-- Description -->
                  <p class="text-slate-600 text-sm leading-relaxed line-clamp-3 mt-auto pt-2">
                    {{ event.description && event.description.length > 120
                      ? event.description.slice(0, 120) + '…'
                      : event.description || 'No description available' }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Carousel Indicators -->
            <div v-if="carouselIndicators.length > 1" class="flex justify-center mt-6 space-x-2">
              <button v-for="(indicator, index) in carouselIndicators" :key="index" @click="goToSlide(index)" :class="[
                'w-3 h-3 rounded-full transition-all duration-300',
                index === Math.floor(carouselPosition / itemsPerView)
                  ? 'bg-green-600 w-6'
                  : 'bg-gray-300 hover:bg-gray-400'
              ]" :aria-label="`Go to slide ${index + 1}`">
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Contact Section -->
    <div id="contact" class="bg-gray-50 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
          <h2 class="text-base text-green-600 font-semibold tracking-wide uppercase">Contact Us</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Get in Touch
          </p>
          <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            We're here to help and answer any questions you might have.
          </p>
        </div>

        <div class="mt-10">
          <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
            <div>
              <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
                <div class="mt-6 space-y-4">
                  <div class="flex">
                    <MapPinIcon class="h-6 w-6 text-green-600" />
                    <div class="ml-3">
                      <p class="text-base text-gray-500">{{ contactInfo.address }}</p>
                    </div>
                  </div>
                  <div class="flex">
                    <PhoneIcon class="h-6 w-6 text-green-600" />
                    <div class="ml-3">
                      <p class="text-base text-gray-500">{{ contactInfo.phone }}</p>
                      <p class="text-sm text-gray-400">Emergency: {{ contactInfo.emergencyHotline }}</p>
                    </div>
                  </div>
                  <div class="flex">
                    <EnvelopeIcon class="h-6 w-6 text-green-600" />
                    <div class="ml-3">
                      <p class="text-base text-gray-500">{{ contactInfo.email }}</p>
                    </div>
                  </div>
                  <div class="flex">
                    <ClockIcon class="h-6 w-6 text-green-600" />
                    <div class="ml-3">
                      <p class="text-base text-gray-500">{{ contactInfo.officeHours }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <form class="mt-6 space-y-6">
              <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-medium text-gray-900">Send us a message</h3>
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                  <input type="text" id="name"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input type="email" id="email"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                  <input type="text" id="subject"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                  <textarea id="message" rows="4"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div>
                  <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm-3 text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white">
    <div class="max-w-7xl mx-auto py-12 px-4 overflow-hidden sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-lg font-medium">{{ barangayInfo.name }}</h3>
          <p class="mt-4 text-sm text-gray-300">
            {{ barangayInfo.motto }}
          </p>
        </div>
        <div>
          <h4 class="text-sm font-semibold text-green-400 tracking-wider uppercase">Quick Links</h4>
          <ul class="mt-4 space-y-2">
            <li><a href="#" class="text-base text-gray-300 hover:text-white">Home</a></li>
            <li><a href="#services" class="text-base text-gray-300 hover:text-white">Services</a></li>
            <li><a href="#announcements" class="text-base text-gray-300 hover:text-white">Announcements</a></li>
            <li><a href="#contact" class="text-base text-gray-300 hover:text-white">Contact</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-sm font-semibold text-green-400 tracking-wider uppercase">Connect With Us</h4>
          <div class="mt-4 flex space-x-6">
            <a href="#" class="text-gray-400 hover:text-white">
              <span class="sr-only">Facebook</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                  clip-rule="evenodd" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white">
              <span class="sr-only">Twitter</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path
                  d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
              </svg>
            </a>
            <a href="#" class="text-gray-400 hover:text-white">
              <span class="sr-only">Instagram</span>
              <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"
                  clip-rule="evenodd" />
              </svg>
            </a>
          </div>
        </div>
      </div>
      <p class="mt-8 text-center text-base text-gray-400">
        &copy; {{ new Date().getFullYear() }} {{ barangayInfo.name }}. All rights reserved.
      </p>
    </div>
  </footer>

  <!-- Detailed Modal -->
  <Modal :show="showModal" title="Event Details" max-width="4xl" @close="closeModal">
    <div v-if="selectedEvent" class="max-h-[80vh] overflow-y-auto">
      <!-- Image Section -->
      <div class="relative mb-4 rounded-xl overflow-hidden">
        <img v-if="selectedEvent.image" :src="`/${selectedEvent.image}`" :alt="selectedEvent.title"
          class="w-full h-auto max-h-[70vh] object-contain bg-slate-50" />
        <div v-else
          class="w-full h-64 sm:h-80 bg-gradient-to-br from-green-500 via-blue-500 to-purple-500 flex items-center justify-center">
          <div class="text-white text-8xl opacity-30">
            <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Badges Section -->
      <div class="flex gap-2 mb-6">
        <span :class="{
          'bg-emerald-500 text-white': selectedEvent.status === 'Ongoing',
          'bg-amber-500 text-white': selectedEvent.status === 'Upcoming',
          'bg-slate-500 text-white': selectedEvent.status === 'Past'
        }" class="px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wider shadow-lg">
          {{ selectedEvent.status }}
        </span>
        <span :class="[
          selectedEvent.type === 'announcement' ? 'bg-blue-500 text-white' : 'bg-purple-500 text-white',
          'px-3 py-1 rounded-full text-sm font-bold uppercase tracking-wider shadow-lg'
        ]">
          {{ selectedEvent.type }}
        </span>
      </div>

      <!-- Content Section -->
      <div class="space-y-6">
        <!-- Title -->
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mb-2">
            {{ selectedEvent.title }}
          </h2>
        </div>

        <!-- Author & Location -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Author -->
          <div v-if="selectedEvent.author" class="flex items-center gap-3">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-r from-green-500 to-blue-600 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M5.121 17.804A9.969 9.969 0 0112 15c2.21 0 4.21.714 5.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-slate-500 font-medium">Author</p>
              <p class="text-slate-900 font-semibold">{{ selectedEvent.author }}</p>
            </div>
          </div>

          <!-- Location -->
          <div v-if="selectedEvent.location" class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5s-3 1.343-3 3 1.343 3 3 3z" />
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 8c0 7.5-7.5 13.5-7.5 13.5S4.5 15.5 4.5 8a7.5 7.5 0 1115 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-slate-500 font-medium">Location</p>
              <p class="text-slate-900 font-semibold">{{ selectedEvent.location }}</p>
            </div>
          </div>
        </div>

        <!-- Dates -->
        <div class="bg-gradient-to-r from-slate-50 to-blue-50 rounded-xl p-6">
          <h3 class="text-lg font-semibold text-slate-900 mb-4">Schedule</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
              <div>
                <p class="text-sm text-slate-500 font-medium">Start Date & Time</p>
                <p class="text-slate-900 font-semibold">{{ formatDate(selectedEvent.start_date) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-3 h-3 rounded-full bg-rose-500"></div>
              <div>
                <p class="text-sm text-slate-500 font-medium">End Date & Time</p>
                <p class="text-slate-900 font-semibold">{{ formatDate(selectedEvent.end_date) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h3 class="text-lg font-semibold text-slate-900 mb-3">Description</h3>
          <div class="prose prose-slate max-w-none">
            <p class="text-slate-700 leading-relaxed whitespace-pre-wrap">
              {{ selectedEvent.description || 'No description available for this event.' }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-200">
          <button @click="closeModal"
            class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 font-medium">
            Close
          </button>
        </div>
      </div>
    </div>
  </Modal>
</template>

<style scoped>
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

.min-w-0 {
  min-width: 0;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.prose {
  color: inherit;
}

.prose p {
  margin: 0;
}

/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}

/* Hide scrollbar for carousel */
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>