<script setup>
import { ref } from 'vue'
import api from '../api'

const form = ref(null)
const cargando = ref(false)
const snackbar = ref({ mostrar: false, texto: '', color: 'success' })
const errores = ref({})

const tipos = ['Laboral', 'Salarial', 'Antigüedad', 'Estudio', 'Residencia']

const datos = ref({
  nombre_empleado: '',
  correo: '',
  movil: '',
  tipo_de_constancia: null,
  descripcion: '',
  cantidad: 1,
})

const reglas = {
  nombre: [(v) => !!v || 'El nombre es obligatorio', (v) => v.length <= 100 || 'Máximo 100 caracteres'],
  correo: [(v) => !!v || 'El correo es obligatorio', (v) => /.+@.+\..+/.test(v) || 'Correo no válido'],
  movil: [(v) => !!v || 'El móvil es obligatorio', (v) => /^\d{8}$/.test(v) || 'Debe tener 8 dígitos'],
  tipo: [(v) => !!v || 'Seleccione un tipo'],
  descripcion: [(v) => !!v || 'La descripción es obligatoria', (v) => v.length <= 500 || 'Máximo 500 caracteres'],
  cantidad: [(v) => !!v || 'La cantidad es obligatoria', (v) => Number(v) >= 1 || 'Mínimo 1'],
}

async function guardar() {
  const { valid } = await form.value.validate()
  if (!valid) return

  cargando.value = true
  errores.value = {}
  try {
    await api.post('/constancias', { ...datos.value, cantidad: Number(datos.value.cantidad) })
    snackbar.value = { mostrar: true, texto: 'Constancia registrada correctamente', color: 'success' }
    form.value.reset()
    datos.value.cantidad = 1
  } catch (e) {
    errores.value = e.response?.data?.errors || {}
    snackbar.value = { mostrar: true, texto: 'No se pudo registrar la constancia', color: 'error' }
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <v-container>
    <v-card class="pa-6" max-width="700">
      <v-card-title>Registrar constancia</v-card-title>

      <v-form ref="form" @submit.prevent="guardar">
        <v-text-field v-model="datos.nombre_empleado" label="Nombre del empleado" :rules="reglas.nombre" :error-messages="errores.nombre_empleado" />
        <v-text-field v-model="datos.correo" label="Correo" :rules="reglas.correo" :error-messages="errores.correo" />
        <v-text-field v-model="datos.movil" label="Móvil" maxlength="8" :rules="reglas.movil" :error-messages="errores.movil" />
        <v-select v-model="datos.tipo_de_constancia" :items="tipos" label="Tipo de constancia" :rules="reglas.tipo" :error-messages="errores.tipo_de_constancia" />
        <v-textarea v-model="datos.descripcion" label="Descripción" rows="3" :rules="reglas.descripcion" :error-messages="errores.descripcion" />
        <v-text-field v-model="datos.cantidad" label="Cantidad" type="number" min="1" :rules="reglas.cantidad" :error-messages="errores.cantidad" />

        <v-btn type="submit" color="primary" :loading="cargando">Guardar</v-btn>
      </v-form>
    </v-card>

    <v-snackbar v-model="snackbar.mostrar" :color="snackbar.color" timeout="3000">{{ snackbar.texto }}</v-snackbar>
  </v-container>
</template>