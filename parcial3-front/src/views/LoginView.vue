<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import QRCode from 'qrcode'
import { useAuthStore } from '../stores/auth'
import logo from '../assets/logo-constancias.svg'

const auth = useAuthStore()
const router = useRouter()

const paso = ref('credenciales') // credenciales | qr | codigo
const email = ref('')
const password = ref('')
const verPassword = ref(false)
const codigo = ref('')
const mfaToken = ref('')
const qr = ref('')
const error = ref('')
const cargando = ref(false)

const reglas = {
  email: [(v) => !!v || 'El correo es obligatorio', (v) => /.+@.+\..+/.test(v) || 'Correo no válido'],
  password: [(v) => !!v || 'La contraseña es obligatoria'],
}

async function entrar() {
  error.value = ''
  cargando.value = true
  try {
    const data = await auth.login(email.value, password.value)
    mfaToken.value = data.mfa_token
    if (data.needs_setup) {
      qr.value = await QRCode.toDataURL(data.otpauth_url, { width: 200, margin: 1 })
      paso.value = 'qr'
    } else {
      paso.value = 'codigo'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'No se pudo iniciar sesión'
  } finally {
    cargando.value = false
  }
}

async function verificar() {
  error.value = ''
  if (codigo.value.length !== 6) {
    error.value = 'Ingresa los 6 dígitos'
    return
  }
  cargando.value = true
  try {
    await auth.verificar(mfaToken.value, codigo.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Código incorrecto'
    codigo.value = ''
  } finally {
    cargando.value = false
  }
}

function volver() {
  paso.value = 'credenciales'
  codigo.value = ''
  error.value = ''
  password.value = ''
}
</script>

<template>
  <div class="fondo">
    <v-card class="tarjeta pa-8" elevation="12" rounded="xl">
      <div class="text-center">
        <img :src="logo" alt="Logo" width="96" />
        <h2 v-if="paso === 'credenciales'" class="titulo mt-4">Sistema de Gestión de<br />Constancias ULS</h2>
      </div>

      <v-alert v-if="error" type="error" variant="tonal" density="compact" class="my-4">{{ error }}</v-alert>

      <!-- Pantalla 1: credenciales -->
      <v-form v-if="paso === 'credenciales'" class="mt-4" @submit.prevent="entrar">
        <p class="text-center texto-chico mb-4">Ingrese su correo electrónico y contraseña para acceder a su cuenta</p>
        <v-text-field v-model="email" label="Correo electrónico" prepend-inner-icon="mdi-account" variant="outlined" density="comfortable" :rules="reglas.email" />
        <v-text-field
          v-model="password"
          label="Contraseña"
          prepend-inner-icon="mdi-lock"
          :type="verPassword ? 'text' : 'password'"
          :append-inner-icon="verPassword ? 'mdi-eye-off' : 'mdi-eye'"
          variant="outlined"
          density="comfortable"
          :rules="reglas.password"
          @click:append-inner="verPassword = !verPassword"
        />
        <v-btn type="submit" block size="large" class="boton" :loading="cargando">Ingresar</v-btn>
      </v-form>

      <!-- Pantalla 2: QR (primera vez) -->
      <div v-else-if="paso === 'qr'" class="panel mt-4 pa-4 text-center">
        <img :src="qr" alt="Código QR" width="190" class="qr" />
        <p class="titulo-chico my-3">Escanea el código QR en tu APP. Una vez configurado ingresa el código generado para iniciar sesión</p>
        <v-otp-input v-model="codigo" length="6" variant="outlined" />
        <v-btn block class="boton mt-2" :loading="cargando" @click="verificar">Ingresar</v-btn>
        <v-btn variant="text" size="small" class="mt-2" @click="volver">Volver</v-btn>
      </div>

      <!-- Pantalla 3: solo código -->
      <div v-else class="panel mt-4 pa-4 text-center">
        <p class="titulo-chico my-4">Ingresa el código generado en tu app para tu inicio de sesión</p>
        <v-otp-input v-model="codigo" length="6" variant="outlined" />
        <v-btn block class="boton mt-2" :loading="cargando" @click="verificar">Ingresar</v-btn>
        <v-btn variant="text" size="small" class="mt-2" @click="volver">Volver</v-btn>
      </div>
    </v-card>
  </div>
</template>

<style scoped>
.fondo {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: radial-gradient(circle at 20% 20%, #0e7490 0%, #0b1f33 45%, #020617 100%);
}
.tarjeta { width: 100%; max-width: 420px; background: #ffffff !important; color: #1e1b6b !important; }
.titulo { color: #2e2a9c; font-weight: 700; line-height: 1.25; }
.titulo-chico { color: #2e2a9c; font-weight: 600; }
.texto-chico { color: #2e2a9c; font-size: 0.8rem; }
.boton { background: #8b7fd6 !important; color: #ffffff !important; letter-spacing: 1px; }
.panel { background: #e3e6f3; border-radius: 12px; }
.qr { background: #ffffff; border-radius: 8px; padding: 6px; }
</style>