<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../api'

const route = useRoute()
const router = useRouter()
const id = route.params.id

const form = ref(null)
const c = ref(null)
const guardando = ref(false)
const enviando = ref(false)
const dialogo = ref(false)
const snackbar = ref({ mostrar: false, texto: '', color: 'success' })

const tipos = ['Laboral', 'Salarial', 'Antigüedad', 'Estudio', 'Residencia']

const reglas = {
  req: [(v) => !!v || 'Campo obligatorio'],
  correo: [(v) => !!v || 'El correo es obligatorio', (v) => /.+@.+\..+/.test(v) || 'Correo no válido'],
  movil: [(v) => /^\d{8}$/.test(v) || 'Debe tener 8 dígitos'],
  cantidad: [(v) => Number(v) >= 1 || 'Mínimo 1'],
}

function aviso(texto, color = 'success') {
  snackbar.value = { mostrar: true, texto, color }
}

onMounted(async () => {
  try {
    const { data } = await api.get('/constancias/' + id)
    c.value = data
  } catch {
    router.push('/constancias')
  }
})

async function actualizar() {
  const { valid } = await form.value.validate()
  if (!valid) return
  guardando.value = true
  try {
    await api.put('/constancias/' + id, {
      nombre_empleado: c.value.nombre_empleado,
      correo: c.value.correo,
      movil: String(c.value.movil),
      tipo_de_constancia: c.value.tipo_de_constancia,
      descripcion: c.value.descripcion,
      cantidad: Number(c.value.cantidad),
      estado_solicitud: !!c.value.estado_solicitud,
    })
    aviso('Constancia actualizada')
  } catch {
    aviso('No se pudo actualizar', 'error')
  } finally {
    guardando.value = false
  }
}

async function eliminar() {
  dialogo.value = false
  try {
    await api.delete('/constancias/' + id)
    router.push('/constancias')
  } catch {
    aviso('No se pudo eliminar', 'error')
  }
}

async function enviarCorreo() {
  enviando.value = true
  try {
    const { data } = await api.post('/constancias/' + id + '/enviar-correo')
    aviso(data.message)
  } catch {
    aviso('No se pudo enviar el correo', 'error')
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <v-container v-if="c">
    <v-card class="pa-6" max-width="750">
      <v-card-title>Constancia #{{ c.id_constancias }}</v-card-title>
      <v-card-subtitle>Solicitada el {{ c.fecha_de_solicitud }} a las {{ c.hora_de_solicitud }}</v-card-subtitle>

      <v-form ref="form" class="mt-4" @submit.prevent="actualizar">
        <v-text-field v-model="c.nombre_empleado" label="Nombre del empleado" :rules="reglas.req" />
        <v-text-field v-model="c.correo" label="Correo" :rules="reglas.correo" />
        <v-text-field v-model="c.movil" label="Móvil" maxlength="8" :rules="reglas.movil" />
        <v-select v-model="c.tipo_de_constancia" :items="tipos" label="Tipo de constancia" :rules="reglas.req" />
        <v-textarea v-model="c.descripcion" label="Descripción" rows="3" :rules="reglas.req" />
        <v-text-field v-model="c.cantidad" label="Cantidad" type="number" min="1" :rules="reglas.cantidad" />
        <v-switch v-model="c.estado_solicitud" :label="c.estado_solicitud ? 'Activa' : 'Inactiva'" color="success" />

        <div class="d-flex flex-wrap ga-2">
          <v-btn type="submit" color="primary" prepend-icon="mdi-content-save" :loading="guardando">Actualizar</v-btn>
          <v-btn color="info" prepend-icon="mdi-email-fast" :loading="enviando" @click="enviarCorreo">Enviar correo (lista)</v-btn>
          <v-btn color="error" prepend-icon="mdi-delete" @click="dialogo = true">Eliminar</v-btn>
          <v-btn variant="text" @click="router.push('/constancias')">Volver</v-btn>
        </div>
      </v-form>
    </v-card>

    <v-dialog v-model="dialogo" max-width="400">
      <v-card>
        <v-card-title>¿Eliminar constancia?</v-card-title>
        <v-card-text>Esta acción no se puede deshacer.</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn @click="dialogo = false">Cancelar</v-btn>
          <v-btn color="error" @click="eliminar">Eliminar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.mostrar" :color="snackbar.color" timeout="3000">{{ snackbar.texto }}</v-snackbar>
  </v-container>
</template>