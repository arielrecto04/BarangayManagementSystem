<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useOfficialStore, useResidentStore } from '@/Stores'
import { useRouter } from 'vue-router'
import useToast from '@/Utils/useToast'
import { storeToRefs } from 'pinia'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

// Stores & router
const router = useRouter()
const { showToast } = useToast()
const officialStore = useOfficialStore()
const { officials } = storeToRefs(officialStore)
const residentStore = useResidentStore()

// Data
const residents = ref([])
const selectedResident = ref(null)

const officialDataForm = ref({
  position: '',
  termFrom: '',
  termTo: '',
  no_of_per_term: null,
  elected_date: '',
  start_date: '',
  end_date: '',
  description: '',
})

// Load residents
onMounted(async () => {
  await residentStore.getResidents()
  residents.value = residentStore.residents
})

// Position options
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

// Group positions
const groupedPositions = computed(() => {
  const groups = {}
  positionOptions.forEach(option => {
    if (!groups[option.section]) groups[option.section] = []
    groups[option.section].push(option)
  })
  return groups
})

// Check position limit
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

// Computed validation
const positionValidation = computed(() => {
  if (!officialDataForm.value.position) return { canAdd: true, message: '' }
  return checkPositionLimit(officialDataForm.value.position)
})

// Validate form
const validateForm = () => {
  if (!selectedResident.value) {
    showToast({ icon: 'error', title: 'Please select a resident.' })
    return false
  }
  if (!officialDataForm.value.position) {
    showToast({ icon: 'error', title: 'Please select a position.' })
    return false
  }
  if (!positionValidation.value.canAdd) {
    showToast({ icon: 'error', title: 'Position limit reached', text: positionValidation.value.message })
    return false
  }
  return true
}

// Create official
const createOfficial = async () => {
  if (!validateForm()) return

  const term = officialDataForm.value.termFrom && officialDataForm.value.termTo
    ? `${officialDataForm.value.termFrom}-${officialDataForm.value.termTo}`
    : ''

  const name = selectedResident.value
    ? `${selectedResident.value.first_name} ${selectedResident.value.middle_name} ${selectedResident.value.last_name}`.trim()
    : ''

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
    <form @submit.prevent="createOfficial" class="w-full max-w-6xl">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col md:flex-row">

        <!-- Left Column -->
        <div
          class="bg-gradient-to-b from-blue-50 to-white p-8 md:w-1/3 flex flex-col items-center justify-center border-b md:border-b-0 md:border-r border-gray-200">
          <h1 class="text-2xl font-bold mb-4 text-center">Add New Official</h1>
          <h2 class="text-base font-medium mb-6 text-center text-gray-600">Official Profile Information</h2>

          <!-- Image Placeholder -->
          <div
            class="w-40 h-40 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center cursor-pointer hover:bg-gray-50 transition">
            <div class="text-center">
              <div class="text-4xl text-gray-400 mb-2">📸</div>
              <p class="text-sm text-gray-500">Upload Photo</p>
            </div>
          </div>
        </div>

        <!-- Right Column (Form) -->
        <div class="p-8 md:w-2/3">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Full Name Searchable Dropdown -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Select Resident *</label>
              <Multiselect v-model="selectedResident" :options="residents"
                :custom-label="resident => `${resident.first_name} ${resident.middle_name || ''} ${resident.last_name}`.trim()"
                placeholder="Search and select resident" track-by="id" :searchable="true" :show-labels="false"
                :allow-empty="true" />
            </div>

            <!-- Position -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Position *</label>
              <select v-model="officialDataForm.position" required
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                :class="{ 'border-red-500': !positionValidation.canAdd }">
                <option value="">Select Position</option>
                <optgroup v-for="(positions, section) in groupedPositions" :key="section" :label="section">
                  <option v-for="position in positions" :key="position.value" :value="position.value">
                    {{ position.label }} (Max: {{ position.limit === 999 ? 'Varies' : position.limit }})
                  </option>
                </optgroup>
              </select>
              <div v-if="positionValidation.message"
                :class="positionValidation.canAdd ? 'text-blue-600' : 'text-red-600'" class="text-sm mt-1">
                {{ positionValidation.message }}
              </div>
            </div>
          </div>

          <!-- Terms -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <!-- Term From (Year) -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Term From (Year)</label>
              <input type="number" v-model="officialDataForm.termFrom" min="2000" max="2099" placeholder="2022"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Term To (Year) -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Term To (Year)</label>
              <input type="number" v-model="officialDataForm.termTo" min="2000" max="2099" placeholder="2025"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Number of Terms -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Number of Terms</label>
              <input type="number" v-model="officialDataForm.no_of_per_term" min="1" placeholder="1"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>

            <!-- Elected Date -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Elected Date</label>
              <input type="date" v-model="officialDataForm.elected_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Dates -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Start Date -->
            <div>
              <label class="text-sm font-semibold text-gray-700">Start Date</label>
              <input type="date" v-model="officialDataForm.start_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
            <!-- End Date -->
            <div>
              <label class="text-sm font-semibold text-gray-700">End Date</label>
              <input type="date" v-model="officialDataForm.end_date"
                class="border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all" />
            </div>
          </div>

          <!-- Description -->
          <div class="mt-4">
            <label class="text-sm font-semibold text-gray-700">Description</label>
            <textarea v-model="officialDataForm.description" placeholder="Enter details about the official's role..."
              rows="4"
              class="resize-y border border-gray-300 rounded-lg px-4 py-3 w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
          </div>

          <!-- Buttons -->
          <div class="flex justify-center mt-8 gap-4">
            <button type="submit" :disabled="!positionValidation.canAdd"
              :class="positionValidation.canAdd ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-400 cursor-not-allowed'"
              class="text-white px-8 py-3 rounded-xl shadow-lg font-semibold transition-all transform hover:scale-105">
              <span class="flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Official
              </span>
            </button>
            <router-link to="/officials"
              class="bg-white border-2 border-gray-300 hover:border-gray-400 px-8 py-3 rounded-xl shadow-lg font-semibold text-gray-700 hover:bg-gray-50 transition-all transform hover:scale-105 flex items-center gap-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel
            </router-link>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>
