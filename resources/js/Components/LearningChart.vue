<script setup>
import { computed } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler
} from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Filler
);

const props = defineProps({
  pretest: Number,
  posttest: Number,
  courseTitle: String
});

const chartData = computed(() => ({
  labels: ['Pre-test', 'Post-test'],
  datasets: [
    {
      label: 'Your Performance',
      backgroundColor: ['rgba(99, 102, 241, 0.2)', 'rgba(99, 102, 241, 0.8)'],
      borderColor: ['rgba(99, 102, 241, 0.4)', 'rgba(99, 102, 241, 1)'],
      borderWidth: 2,
      borderRadius: 12,
      data: [props.pretest || 0, props.posttest || 0],
      barThickness: 60,
    }
  ]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: '#1e293b',
      padding: 12,
      titleFont: { size: 14, weight: 'bold' },
      bodyFont: { size: 13 },
      displayColors: false,
      callbacks: {
        label: (context) => ` Score: ${context.raw}%`
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      max: 100,
      grid: {
        display: true,
        color: 'rgba(0,0,0,0.03)',
        drawBorder: false
      },
      ticks: {
        stepSize: 20,
        font: { size: 11, family: 'Inter' },
        color: '#94a3b8'
      }
    },
    x: {
      grid: {
        display: false
      },
      ticks: {
        font: { size: 12, weight: '600', family: 'Outfit' },
        color: '#475569'
      }
    }
  }
};
</script>

<template>
  <div class="h-[300px] w-full">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>
