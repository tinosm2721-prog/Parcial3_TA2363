import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('p3_user') || 'null'))
  const token = ref(localStorage.getItem('p3_token'))

  const autenticado = computed(() => !!token.value)
  const esAdmin = computed(() => user.value?.rol === 'administrador')

  // Paso 1: correo y contraseña
  async function login(email, password) {
    const { data } = await api.post('/login', { email, password })
    return data
  }

  // Paso 2: código de 6 dígitos
  async function verificar(mfa_token, code) {
    const { data } = await api.post('/login/verificar', { mfa_token, code })
    token.value = data.token
    user.value = data.user
    localStorage.setItem('p3_token', data.token)
    localStorage.setItem('p3_user', JSON.stringify(data.user))
  }

  async function logout() {
    try { await api.post('/logout') } catch (e) { /* sin acción */ }
    token.value = null
    user.value = null
    localStorage.removeItem('p3_token')
    localStorage.removeItem('p3_user')
  }

  return { user, token, autenticado, esAdmin, login, verificar, logout }
})