<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const auth = useAuthStore()
const router = useRouter()

const menu = computed(() =>
  [
    { titulo: 'Inicio', icono: 'mdi-home', ruta: '/', roles: ['administrador', 'registrador'] },
    { titulo: 'Registrar constancia', icono: 'mdi-file-plus', ruta: '/registrar', roles: ['administrador', 'registrador'] },
    { titulo: 'Constancias', icono: 'mdi-format-list-bulleted', ruta: '/constancias', roles: ['administrador'] },
    { titulo: 'Informes', icono: 'mdi-chart-bar', ruta: '/informes', roles: ['administrador', 'registrador'] },
  ].filter((i) => i.roles.includes(auth.user?.rol)),
)

async function salir() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <v-app>
    <template v-if="auth.autenticado">
      <v-app-bar color="primary">
        <v-app-bar-title>Constancias ULS</v-app-bar-title>
        <span class="mr-2">{{ auth.user.name }} ({{ auth.user.rol }})</span>
        <v-btn icon="mdi-logout" @click="salir" />
      </v-app-bar>

      <v-navigation-drawer permanent>
        <v-list nav>
          <v-list-item
            v-for="item in menu"
            :key="item.ruta"
            :to="item.ruta"
            :prepend-icon="item.icono"
            :title="item.titulo"
          />
        </v-list>
      </v-navigation-drawer>
    </template>

    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>