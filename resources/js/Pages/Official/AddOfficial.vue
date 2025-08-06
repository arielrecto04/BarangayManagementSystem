<script setup>
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { ref, computed, onMounted } from 'vue'
import { useOfficialStore } from '@/Stores'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast'
import { storeToRefs } from 'pinia'


const router = useRouter()
const { showToast } = useToast()
const officialStore = useOfficialStore()
const { officials } = storeToRefs(officialStore)
const isLoading = ref(false);
const formErrors = ref({});
const selectedResident = ref(null);
watch(selectedResident, (val) => {
  officialDataForm.value.resident_id = val?.id ?? '';
});

const handleSubmit = async () => {
  isLoading.value = true;
  formErrors.value = {};
  try {
    await officialStore.createOfficial(officialDataForm.value);
    showToast('Official added successfully!');
    router.push('/officials/list-officials');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      formErrors.value = error.response.data.errors || {};
    }
  } finally {
    isLoading.value = false;
  }
};

const residents = ref([])
const residentSearch = ref('')
const filteredResidents = ref([]);

onMounted(async () => {
  await residentStore.fetchResidents();
  filteredResidents.value = residentStore.residents.filter(resident => resident.status === 'Alive');
});

const officialDataForm = ref({
  position: '',
  termFrom: '',
  termTo: '',
  no_of_per_term: null,
  elected_date: '',
  start_date: '',
  end_date: '',
  description: '',
  resident_id: null
})

// Position options with their limits
const positionOptions = [
  // Barangay Officials
  { value: 'Barangay Captain', label: 'Barangay Captain', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Secretary', label: 'Barangay Secretary', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Treasurer', label: 'Barangay Treasurer', limit: 1, section: 'Barangay Officials' },
  { value: 'Barangay Councilor', label: 'Barangay Councilor', limit: 7, section: 'Barangay Officials' },
  { value: 'SK Chairman', label: 'SK Chairman', limit: 1, section: 'Barangay Officials' },
  { value: 'SK Secretary', label: 'SK Secretary', limit: 1, section: 'Barangay Officials' },
  { value: 'SK Treasurer', label: 'SK Treasurer', limit: 1, section: 'Barangay Officials' },
  { value: 'Youth Councilor', label: 'Youth Councilor', limit: 7, section: 'Barangay Officials' },

  // Volunteer and Functional Personnel
  { value: 'Barangay Tanod', label: 'Barangay Tanod', limit: 20, section: 'Volunteer and Functional Personnel' },
  { value: 'Barangay Health Workers', label: 'Barangay Health Workers', limit: 10, section: 'Volunteer and Functional Personnel' },
  { value: 'Barangay Nutrition Scholars', label: 'Barangay Nutrition Scholars', limit: 3, section: 'Volunteer and Functional Personnel' },
  { value: 'Lupon Tagapamayapa', label: 'Lupon Tagapamayapa', limit: 10, section: 'Volunteer and Functional Personnel' },
  { value: 'BADAC Members', label: 'BADAC Members (Barangay Anti-Drug Council)', limit: 999, section: 'Volunteer and Functional Personnel' },
  { value: 'BCPC Members', label: 'BCPC Members (Barangay Council for the Protection of Children)', limit: 999, section: 'Volunteer and Functional Personnel' },

  // Program Based Committees
  { value: 'BDRRMC', label: 'BDRRMC (Barangay Disaster Risk Reduction and Management Committee)', limit: 15, section: 'Barangay Program Based-Committees' },
  { value: 'BPOC', label: 'BPOC (Barangay Peace and Order Council)', limit: 15, section: 'Barangay Program Based-Committees' },
  { value: 'Barangay Environment Committee', label: 'Barangay Environment Committee', limit: 10, section: 'Barangay Program Based-Committees' },
  { value: 'GAD Committee', label: 'GAD Committee (Gender and Development)', limit: 8, section: 'Barangay Program Based-Committees' },
  { value: 'VAWC Desk', label: 'VAWC Desk (Anti-Violence Against Women and Children)', limit: 3, section: 'Barangay Program Based-Committees' },
  { value: 'BPLO Section', label: 'BPLO Section (Business Permit and Licensing)', limit: 3, section: 'Barangay Program Based-Committees' }
]

// Group positions by section
const groupedPositions = computed(() => {
  const groups = {}
  positionOptions.forEach(option => {
    if (!groups[option.section]) {
      groups[option.section] = []
    }
    groups[option.section].push(option)
  })
  return groups
})

// Check if position has reached its limit
const checkPositionLimit = (position) => {
  const positionOption = positionOptions.find(opt => opt.value === position)
  if (!positionOption) return { canAdd: true, message: '' }

  const currentCount = officials.value.filter(official =>
    official.position && official.position.toLowerCase().includes(position.toLowerCase())
  ).length

  if (positionOption.limit === 999) {
    return { canAdd: true, message: `Current count: ${currentCount} (varies)` }
  }

  const canAdd = currentCount < positionOption.limit
  const message = canAdd
    ? `Current count: ${currentCount}/${positionOption.limit}`
    : `Limit reached: ${currentCount}/${positionOption.limit}`

  return { canAdd, message }
}

// Computed property for position validation
const positionValidation = computed(() => {
  if (!officialDataForm.value.position) return { canAdd: true, message: '' }
  return checkPositionLimit(officialDataForm.value.position)
})

// Validate all fields filled
const validateForm = () => {
  const requiredFields = [
    officialDataForm.value.firstName,
    officialDataForm.value.lastName,
    officialDataForm.value.position,
  ]

  if (!requiredFields.every(field => field !== '' && field !== null)) {
    showToast({ icon: 'error', title: 'Please fill all required fields before submitting.' })
    return false
  }

  if (!positionValidation.value.canAdd) {
    showToast({ icon: 'error', title: 'Position limit reached', text: positionValidation.value.message })
    return false
  }

  return true
}

const createOfficial = async () => {
  if (!validateForm()) return

  // Compose term as YY-YY string
  const term = officialDataForm.value.termFrom && officialDataForm.value.termTo
    ? `${officialDataForm.value.termFrom}-${officialDataForm.value.termTo}`
    : ''

  // Compose full name
  const name = `${officialDataForm.value.firstName} ${officialDataForm.value.middleName} ${officialDataForm.value.lastName}`.trim()

  try {
    await officialStore.addOfficial({
      name,
      position: officialDataForm.value.position,
      terms: term,
      no_of_per_term: officialDataForm.value.no_of_per_term,
      elected_date: officialDataForm.value.elected_date,
      start_date: officialDataForm.value.start_date,
      end_date: officialDataForm.value.end_date,
      description: officialDataForm.value.description,
      resident_id: officialDataForm.value.resident_id
    })
    showToast({ icon: 'success', title: 'Official created successfully' })
    router.push('/officials')
  } catch (error) {
    const errorMsg =
      error.response?.data?.message ||
      error.response?.data?.errors?.join(', ') ||
      error.message
    showToast({ icon: 'error', title: 'Failed to create official', text: errorMsg })
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex justify-center items-center p-10">
    <form @submit.prevent="createOfficial">
      <div class="bg-white rounded-2xl shadow-xl p-10 w-full max-w-5xl">
        <h1 class="text-3xl font-bold mb-2 text-center">Add New Official</h1>
        <h2 class="text-lg font-semibold mb-6 text-center text-gray-600">Official Profile Information</h2>

        <div class="grid grid-cols-12 gap-6">
          <!-- Image Placeholder -->
          <div class="col-span-12 md:col-span-4 lg:col-span-3 flex justify-center items-center">
            <div
              class="w-40 h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center">
              <div class="text-center">
                <div class="text-4xl text-gray-400 mb-2">📸</div>
                <p class="text-sm text-gray-500">Upload Photo</p>
              </div>
            </div>
          </div>

          <!-- Personal Information -->
          <div class="col-span-12 md:col-span-8 lg:col-span-9 grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Resident Full Name via Resident ID -->
            <div class="flex flex-col gap-2">
              <Mult v-model="officialDataForm.resident_id" required
                class="border border-gray-300 rounded-lg px-4 py-3 mt-2">
                <option disabled value="">Select Resident</option>
                <option v-for="resident in filteredResidents" :key="resident.id" :value="resident.id">
                  {{ resident.first_name }} {{ resident.middle_name }} {{ resident.last_name }}
                </option>
              </Mult>
            </div>
          </div>

          <!-- Position Selection -->
          <div class="col-span-12 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-700">Position *</label>
            <select v-model="officialDataForm.position" required
              class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              :class="{ 'border-red-500': !positionValidation.canAdd }">
              <option value="">Select Position</option>
              <optgroup v-for="(positions, section) in groupedPositions" :key="section" :label="section">
                <option v-for="position in positions" :key="position.value" :value="position.value">
                  {{ position.label }} (Max: {{ position.limit === 999 ? 'Varies' : position.limit }})
                </option>
              </optgroup>
            </select>
            <div v-if="positionValidation.message" :class="positionValidation.canAdd ? 'text-blue-600' : 'text-red-600'"
              class="text-sm mt-1">
              {{ positionValidation.message }}
            </div>
          </div>

          <!-- Terms Information -->
          <div class="col-span-12 grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Term From -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">Term From (Year)</label>
              <input type="number" v-model="officialDataForm.termFrom" min="2000" max="2099" placeholder="2022"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Term To -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">Term To (Year)</label>
              <input type="number" v-model="officialDataForm.termTo" min="2000" max="2099" placeholder="2025"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Number of Terms -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">Number of Terms</label>
              <input type="number" v-model="officialDataForm.no_of_per_term" min="1" placeholder="1"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Elected Date -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">Elected Date</label>
              <input type="date" v-model="officialDataForm.elected_date"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Service Dates -->
          <div class="col-span-12 grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Start Date -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">Start Date</label>
              <input type="date" v-model="officialDataForm.start_date"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- End Date -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-semibold text-gray-700">End Date</label>
              <input type="date" v-model="officialDataForm.end_date"
                class="border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Description -->
          <div class="col-span-12 flex flex-col gap-2">
            <label class="text-sm font-semibold text-gray-700">Description</label>
            <textarea v-model="officialDataForm.description"
              placeholder="Enter a detailed description about the official's role, responsibilities, and achievements..."
              rows="4"
              class="resize-y border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-center mt-10 gap-4">
          <button type="submit" :disabled="!positionValidation.canAdd"
            :class="positionValidation.canAdd ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
            class="text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105">
            <span class="flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Official
            </span>
          </button>
          <router-link to="/officials"
            class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancel
          </router-link>
        </div>

        <!-- Position Limits Legend -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Position Limits Guide:</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 text-xs text-gray-600">
            <div v-for="position in positionOptions.slice(0, 6)" :key="position.value">
              <span class="font-medium">{{ position.value }}:</span>
              <span>{{ position.limit === 999 ? 'Varies' : position.limit }} max</span>
            </div>
          </div>
          <p class="text-xs text-gray-500 mt-2">* Some positions have flexible limits based on barangay needs</p>
        </div>
      </div>
    </form>
  </div>
</template>