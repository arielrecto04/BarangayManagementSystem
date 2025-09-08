<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const emit = defineEmits(['image-uploaded'])

// Refs
const video = ref(null)
const canvas = ref(null)
const isWebcamActive = ref(false)
const isCaptured = ref(false)
const capturedImageSrc = ref('')
const stream = ref(null)
const error = ref('')

// Start webcam
const startWebcam = async () => {
    try {
        error.value = ''

        // Stop any existing stream first
        stopWebcam()

        const mediaStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        })

        stream.value = mediaStream

        if (video.value) {
            video.value.srcObject = mediaStream
            isWebcamActive.value = true
        }
    } catch (err) {
        console.error('Webcam error:', err)
        error.value = 'Unable to access camera. Please check permissions.'
        isWebcamActive.value = false
    }
}

// Stop webcam
const stopWebcam = () => {
    if (stream.value) {
        const tracks = stream.value.getTracks()
        tracks.forEach(track => {
            track.stop()
            console.log('Stopped track:', track.kind)
        })
        stream.value = null
    }

    if (video.value && video.value.srcObject) {
        video.value.srcObject = null
    }

    isWebcamActive.value = false
}

// Capture photo and convert to base64
const capturePhoto = () => {
    if (!video.value || !isWebcamActive.value) return

    const videoElement = video.value
    const canvasElement = canvas.value

    if (!canvasElement) return

    // Set canvas dimensions to match video
    canvasElement.width = videoElement.videoWidth
    canvasElement.height = videoElement.videoHeight

    // Draw video frame to canvas
    const context = canvasElement.getContext('2d')
    context.drawImage(videoElement, 0, 0, canvasElement.width, canvasElement.height)

    // Convert to base64 data URL
    const dataURL = canvasElement.toDataURL('image/jpeg', 0.8)

    capturedImageSrc.value = dataURL
    isCaptured.value = true

    // Stop webcam after capture
    stopWebcam()
}

// Use the captured photo
const usePhoto = () => {
    if (capturedImageSrc.value) {
        emit('image-uploaded', capturedImageSrc.value)
        // Keep the captured image for preview
    }
}

// Retake photo
const retakePhoto = () => {
    isCaptured.value = false
    capturedImageSrc.value = ''
    startWebcam()
}

// Reset component
const reset = () => {
    stopWebcam()
    isCaptured.value = false
    capturedImageSrc.value = ''
    error.value = ''
}

// Cleanup on unmount
onBeforeUnmount(() => {
    console.log('WebcamCapture unmounting, cleaning up...')
    stopWebcam()
})

// Auto-start webcam on mount
onMounted(() => {
    console.log('WebcamCapture mounted')
    // Don't auto-start, let user click to open camera
})

// Expose methods for parent components
defineExpose({
    startWebcam,
    stopWebcam,
    reset
})
</script>

<template>
    <div class="webcam-capture w-full">
        <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">Profile Photo</label>

        <!-- Camera preview or captured image display -->
        <div class="relative w-full max-w-sm mx-auto mb-4">
            <!-- Default state - no camera, no image -->
            <div v-if="!isWebcamActive && !isCaptured && !error"
                class="w-full h-64 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path
                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Click "Open Camera" to take a photo</p>
                </div>
            </div>

            <!-- Video preview -->
            <video v-show="isWebcamActive && !isCaptured" ref="video"
                class="w-full h-64 bg-black rounded-lg object-cover transform scale-x-[-1]" autoplay playsinline
                muted></video>

            <!-- Captured image preview -->
            <img v-if="isCaptured && capturedImageSrc" :src="capturedImageSrc"
                class="w-full h-64 object-cover rounded-lg border transform scale-x-[-1]" alt="Captured photo" />

            <!-- Error state -->
            <div v-if="error"
                class="w-full h-64 bg-red-50 rounded-lg border border-red-200 flex items-center justify-center">
                <div class="text-center p-4">
                    <svg class="w-12 h-12 text-red-400 mx-auto mb-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-red-600 mb-2">{{ error }}</p>
                    <button @click="startWebcam"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        Try Again
                    </button>
                </div>
            </div>

            <!-- Hidden canvas for capturing -->
            <canvas ref="canvas" class="hidden"></canvas>
        </div>

        <!-- Camera controls -->
        <div class="flex flex-col gap-2">
            <!-- Open camera button -->
            <button v-if="!isWebcamActive && !isCaptured" @click="startWebcam" type="button"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                Open Camera
            </button>

            <!-- Camera active controls -->
            <div v-if="isWebcamActive && !isCaptured" class="flex gap-2">
                <button @click="capturePhoto" type="button"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium flex-1 transition-colors">
                    Capture
                </button>
                <button @click="stopWebcam" type="button"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Cancel
                </button>
            </div>

            <!-- Captured image controls -->
            <div v-if="isCaptured" class="flex gap-2">
                <button @click="usePhoto" type="button"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium flex-1 transition-colors">
                    Use This Photo
                </button>
                <button @click="retakePhoto" type="button"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Retake
                </button>
            </div>
        </div>

        <!-- Error message -->
        <p v-if="error" class="text-red-500 text-xs mt-2">{{ error }}</p>
    </div>
</template>

<style scoped>
.webcam-capture {
    width: 100%;
}

/* Mirror the video for better UX (like a mirror) */
video {
    transform: scaleX(-1);
}

/* Also mirror the captured image to match the video preview */
img {
    transform: scaleX(-1);
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>