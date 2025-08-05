<script setup>
import { ref } from 'vue';
import { MapPinIcon, PhoneIcon, EnvelopeIcon, ClockIcon } from '@heroicons/vue/24/outline';

// Hardcoded data
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
  {
    title: 'Barangay Clearance',
    description: 'Official document issued for various business and personal transactions',
    icon: '📝'
  },
  {
    title: 'Barangay ID',
    description: 'Valid identification card for all registered residents',
    icon: '🆔'
  },
  {
    title: 'Business Permit',
    description: 'Required for operating businesses within the barangay',
    icon: '🏪'
  },
  {
    title: 'Certificate of Residency',
    description: 'Proof of residence for various requirements',
    icon: '🏠'
  },
  {
    title: 'Certificate of Indigency',
    description: 'For availing government assistance and benefits',
    icon: '🤝'
  }
];

const announcements = [
  {
    title: 'Scheduled Power Interruption',
    date: 'August 10, 2025',
    content: 'There will be a scheduled power interruption on August 10, 2025 from 8:00 AM to 5:00 PM for maintenance works.'
  },
  {
    title: 'Clean-Up Drive',
    date: 'August 15, 2025',
    content: 'Join us for our monthly clean-up drive. Meet at the Barangay Hall at 6:00 AM.'
  },
  {
    title: 'Free Medical Check-Up',
    date: 'August 20, 2025',
    content: 'Free medical check-up for senior citizens and PWDs at the Barangay Health Center.'
  }
];

const contactInfo = {
  address: '123 Rizal Street, Barangay San Isidro, San Pablo City, Laguna',
  phone: '(049) 123-4567',
  email: 'sanisidro.brgy@sanpablocity.gov.ph',
  officeHours: 'Monday to Friday, 8:00 AM - 5:00 PM',
  emergencyHotline: '911'
};

const activeTab = ref('announcements');
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-green-800 text-white">
      <div class="absolute inset-0">
        <img
          src="https://images.unsplash.com/photo-1567013127542-490d757e51fc?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80"
          alt="Barangay Hall"
          class="w-full h-full object-cover opacity-20"
        >
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
          <a href="#contact" class="bg-green-600 text-white px-6 py-3 rounded-md font-medium hover:bg-green-700">
            Contact Us
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
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Our Barangay Services
          </p>
          <p class="mt-4 max-w-2xl text-xl text-gray-500 lg:mx-auto">
            We provide various services to cater to the needs of our community members.
          </p>
        </div>

        <div class="mt-10">
          <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="(service, index) in services" :key="index" class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
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
    <div class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
          <h2 class="text-base text-green-600 font-semibold tracking-wide uppercase">Updates</h2>
          <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
            Announcements & Events
          </p>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6">
              <button
                @click="activeTab = 'announcements'"
                :class="[
                  activeTab === 'announcements'
                    ? 'border-green-500 text-green-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Announcements
              </button>
              <button
                @click="activeTab = 'events'"
                :class="[
                  activeTab === 'events'
                    ? 'border-green-500 text-green-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Upcoming Events
              </button>
            </nav>
          </div>

          <div class="px-6 py-5">
            <div v-if="activeTab === 'announcements'" class="space-y-6">
              <div v-for="(announcement, index) in announcements" :key="index" class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                <h3 class="text-lg font-medium text-gray-900">{{ announcement.title }}</h3>
                <p class="mt-1 text-sm text-green-600">{{ announcement.date }}</p>
                <p class="mt-2 text-gray-600">{{ announcement.content }}</p>
              </div>
            </div>
            <div v-else class="text-center py-12">
              <p class="text-gray-500">No upcoming events at the moment. Please check back later.</p>
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
            <div class="bg-white p-6 rounded-lg shadow-md">
              <h3 class="text-lg font-medium text-gray-900">Send us a message</h3>
              <form class="mt-6 space-y-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                  <input type="text" id="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input type="email" id="email" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                  <input type="text" id="subject" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                  <textarea id="message" rows="4" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-green-500 focus:border-green-500"></textarea>
                </div>
                <div>
                  <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Send Message
                  </button>
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
                  <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white">
                <span class="sr-only">Twitter</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                </svg>
              </a>
              <a href="#" class="text-gray-400 hover:text-white">
                <span class="sr-only">Instagram</span>
                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
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
  </div>
</template>

<style scoped>
/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}
</style>
