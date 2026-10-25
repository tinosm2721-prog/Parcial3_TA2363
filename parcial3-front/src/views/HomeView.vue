<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const accesos = computed(() =>
  [
    { titulo: 'Registrar constancia', texto: 'Crear una nueva solicitud', icono: 'mdi-file-plus', ruta: '/registrar', roles: ['administrador', 'registrador'] },
    { titulo: 'Constancias', texto: 'Ver activas e inactivas, editar y eliminar', icono: 'mdi-format-list-bulleted', ruta: '/constancias', roles: ['administrador'] },
    { titulo: 'Informes', texto: auth.esAdmin ? 'PDF, CSV y gráfico por mes' : 'PDF y CSV por mes', icono: 'mdi-chart-bar', ruta: '/informes', roles: ['administrador', 'registrador'] },
  ].filter((a) => a.roles.includes(auth.user?.rol)),
)
</script>

<template>
  <v-container>
    <h2>Bienvenido, {{ auth.user?.name }}</h2>
    <p class="mb-6 text-medium-emphasis">
      {{ auth.esAdmin ? 'Tienes acceso completo al sistema.' : 'Puedes registrar constancias y generar informes en PDF y CSV.' }}
    </p>

    <v-row>
      <v-col v-for="a in accesos" :key="a.ruta" cols="12" md="4">
        <v-card class="pa-4" hover @click="router.push(a.ruta)">
          <v-icon :icon="a.icono" size="40" color="primary" class="mb-2" />
          <v-card-title class="pa-0">{{ a.titulo }}</v-card-title>
          <v-card-text class="pa-0 mt-1">{{ a.texto }}</v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>