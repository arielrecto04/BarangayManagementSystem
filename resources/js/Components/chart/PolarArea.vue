<script setup>
import { computed , defineProps} from 'vue';
import { PolarArea } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, CategoryScale , RadialLinearScale } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, CategoryScale, RadialLinearScale);

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const isDataAvailable = computed(() => {
  return props.data &&
         props.data.labels &&
         props.data.labels.length > 0 &&
         props.data.datasets &&
         props.data.datasets.some(dataset => dataset.data && dataset.data.length > 0);
});

const chartData = computed(() => {
  if (isDataAvailable.value) {
    return props.data;
  }
  return {
    labels: ['No Data'],
    datasets: [{
      label: 'No data available',
      data: [0],
      backgroundColor: '#e5e7eb',
      borderRadius: 4,
    }]
  };
});

const chartOptions = computed(() => {
  if (isDataAvailable.value) {
    return props.options;
  }
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { enabled: false },
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          display: false,
        },
        grid: {
          drawTicks: false,
        }
      },
      x: {
        grid: {
          display: false,
        }
      }
    }
  };
});
</script>

<template>
  <div class="relative w-full h-full">
    <PolarArea :data="chartData" :options="chartOptions" />


    <div v-if="!isDataAvailable" class="absolute inset-0 flex items-center justify-center bg-white/70 backdrop-blur-sm">
      <div class="text-center text-gray-500">
        <i class="fa fa-rr-chart-pie-alt text-4xl mb-2"></i>
        <p class="font-medium">No Data Available</p>
        <p class="text-sm">There is no data to display in this chart.</p>
      </div>
    </div>
  </div>
</template>
