import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import LoginView from '../views/LoginView.vue'
import HomeView from '../views/HomeView.vue'
import RegistrarView from '../views/RegistrarView.vue'
import ConstanciasView from '../views/ConstanciasView.vue'
import DetalleView from '../views/DetalleView.vue'
import InformesView from '../views/InformesView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', component: LoginView, meta: { publica: true } },
    { path: '/', component: HomeView },
    { path: '/registrar', component: RegistrarView },
    { path: '/constancias', component: ConstanciasView, meta: { roles: ['administrador'] } },
    { path: '/constancias/:id', component: DetalleView, meta: { roles: ['administrador'] } },
    { path: '/informes', component: InformesView },
  ],
})

router.beforeEach((to) => {
  const auth = useAuthStore()

  if (to.meta.publica) return auth.autenticado ? '/' : true
  if (!auth.autenticado) return '/login'
  if (to.meta.roles && !to.meta.roles.includes(auth.user.rol)) return '/'
})

export default router