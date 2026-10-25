<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale,
} from 'chart.js'
import api from '../api'
import { useAuthStore } from '../stores/auth'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)
ChartJS.defaults.color = '#cccccc'

const auth = useAuthStore()
const tab = ref('pdf')

// ---------- PDF ----------
const pdfUrl = ref('')
const cargandoPdf = ref(false)
const errorPdf = ref('')

async function verPdf() {
  cargandoPdf.value = true
  errorPdf.value = ''
  try {
    const { data } = await api.get('/reportes/pdf', { responseType: 'blob' })
    if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
    pdfUrl.value = URL.createObjectURL(new Blob([data], { type: 'application/pdf' }))
  } catch {
    errorPdf.value = 'No se pudo generar el informe PDF'
  } finally {
    cargandoPdf.value = false
  }
}

function descargarPdf() {
  const a = document.createElement('a')
  a.href = pdfUrl.value
  a.download = 'informe_constancias.pdf'
  a.click()
}

// ---------- CSV ----------
const descargandoCsv = ref(false)

async function descargarCsv() {
  descargandoCsv.value = true
  try {
    const { data } = await api.get('/reportes/csv', { responseType: 'blob' })
    const url = URL.createObjectURL(new Blob([data], { type: 'text/csv' }))
    const a = document.createElement('a')
    a.href = url
    a.download = 'constancias_por_mes.csv'
    a.click()
    URL.revokeObjectURL(url)
  } finally {
    descargandoCsv.value = false
  }
}

// ---------- Gráfico ----------
const nombresMes = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio',
  'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const meses = nombresMes.map((m, i) => ({ title: m, value: i + 1 }))

const mes = ref(new Date().getMonth() + 1)
const anio = ref(new Date().getFullYear())
const datos = ref({ activas: 0, inactivas: 0 })

async function cargarGrafico() {
  if (!anio.value) return
  const { data } = await api.get('/reportes/grafico', { params: { mes: mes.value, anio: anio.value } })
  datos.value = data
}

const chartData = computed(() => ({
  labels: ['Activas', 'Inactivas'],
  datasets: [{
    label: `Constancias de ${nombresMes[mes.value - 1]} ${anio.value}`,
    data: [datos.value.activas, datos.value.inactivas],
    backgroundColor: ['#4caf50', '#9e9e9e'],
  }],
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
}

watch([mes, anio], cargarGrafico)

onMounted(() => {
  verPdf()
  if (auth.esAdmin) cargarGrafico()
})

onBeforeUnmount(() => {
  if (pdfUrl.value) URL.revokeObjectURL(pdfUrl.value)
})
</script>

<template>
  <v-container>
    <h2 class="mb-4">Informes</h2>

    <v-tabs v-model="tab" color="primary">
      <v-tab value="pdf" prepend-icon="mdi-file-pdf-box">Informe PDF</v-tab>
      <v-tab value="csv" prepend-icon="mdi-file-delimited">Informe CSV</v-tab>
      <v-tab v-if="auth.esAdmin" value="grafico" prepend-icon="mdi-chart-bar">Gráfico</v-tab>
    </v-tabs>

    <v-window v-model="tab" class="mt-4">
      <!-- PDF -->
      <v-window-item value="pdf">
        <div class="d-flex ga-2 mb-3">
          <v-btn color="primary" prepend-icon="mdi-refresh" :loading="cargandoPdf" @click="verPdf">Generar informe</v-btn>
          <v-btn prepend-icon="mdi-download" :disabled="!pdfUrl" @click="descargarPdf">Descargar PDF</v-btn>
        </div>
        <v-alert v-if="errorPdf" type="error" variant="tonal" class="mb-3">{{ errorPdf }}</v-alert>
        <iframe v-if="pdfUrl" :src="pdfUrl" title="Informe de constancias" style="width: 100%; height: 75vh; border: 0" />
      </v-window-item>

      <!-- CSV -->
      <v-window-item value="csv">
        <v-card class="pa-6" max-width="500">
          <p class="mb-4">Descarga la cantidad de constancias registradas por mes.</p>
          <v-btn color="success" prepend-icon="mdi-download" :loading="descargandoCsv" @click="descargarCsv">
            Descargar CSV
          </v-btn>
        </v-card>
      </v-window-item>

      <!-- Gráfico -->
      <v-window-item v-if="auth.esAdmin" value="grafico">
        <v-row>
          <v-col cols="12" md="4">
            <v-select v-model="mes" :items="meses" label="Mes" />
          </v-col>
          <v-col cols="12" md="4">
            <v-text-field v-model.number="anio" label="Año" type="number" />
          </v-col>
        </v-row>
        <div style="height: 350px">
          <Bar :data="chartData" :options="chartOptions" />
        </div>
      </v-window-item>
    </v-window>
  </v-container>
</template>