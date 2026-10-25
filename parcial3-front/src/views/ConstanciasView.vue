<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

const constancias = ref([])
const pagina = ref(1)
const totalPaginas = ref(1)
const total = ref(0)
const estado = ref('')
const cargando = ref(false)

const filtros = [
  { title: 'Todas', value: '' },
  { title: 'Activas', value: 1 },
  { title: 'Inactivas', value: 0 },
]

async function cargar() {
  cargando.value = true
  try {
    const params = { page: pagina.value }
    if (estado.value !== '') params.estado = estado.value
    const { data } = await api.get('/constancias', { params })
    constancias.value = data.data
    totalPaginas.value = data.last_page
    total.value = data.total
  } finally {
    cargando.value = false
  }
}

watch(pagina, cargar)
watch(estado, () => {
  if (pagina.value === 1) cargar()
  else pagina.value = 1
})
onMounted(cargar)
</script>

<template>
  <v-container>
    <v-row align="center">
      <v-col cols="12" md="8"><h2>Constancias ({{ total }})</h2></v-col>
      <v-col cols="12" md="4">
        <v-select v-model="estado" :items="filtros" label="Filtrar por estado" density="compact" hide-details />
      </v-col>
    </v-row>

    <v-table class="mt-4">
      <thead>
        <tr>
          <th>ID</th><th>Empleado</th><th>Correo</th><th>Tipo</th>
          <th>Cant.</th><th>Fecha</th><th>Estado</th><th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="c in constancias" :key="c.id_constancias">
          <td>{{ c.id_constancias }}</td>
          <td>{{ c.nombre_empleado }}</td>
          <td>{{ c.correo }}</td>
          <td>{{ c.tipo_de_constancia }}</td>
          <td>{{ c.cantidad }}</td>
          <td>{{ c.fecha_de_solicitud }}</td>
          <td>
            <v-chip :color="c.estado_solicitud ? 'success' : 'grey'" size="small">
              {{ c.estado_solicitud ? 'Activa' : 'Inactiva' }}
            </v-chip>
          </td>
          <td>
            <v-btn size="small" color="primary" prepend-icon="mdi-eye" @click="router.push('/constancias/' + c.id_constancias)">
              Detalle
            </v-btn>
          </td>
        </tr>
        <tr v-if="!constancias.length && !cargando">
          <td colspan="8" class="text-center">No hay constancias</td>
        </tr>
      </tbody>
    </v-table>

    <v-pagination v-model="pagina" :length="totalPaginas" :total-visible="5" class="mt-4" />
  </v-container>
</template>