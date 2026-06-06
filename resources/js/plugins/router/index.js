import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

let _authChecked = false
let _isAuthenticated = false

async function checkAuth() {
  if (_authChecked) return _isAuthenticated
  const token = localStorage.getItem('auth_token')
  if (!token) {
    _authChecked = true
    _isAuthenticated = false
    return false
  }
  try {
    const res = await fetch('/api/user', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
    })
    _isAuthenticated = res.ok
    if (!res.ok) { 
      localStorage.removeItem('auth_token')
    }else {
      localStorage.setItem('user_name', (await res.json()).name)
    }
  } catch {
    _isAuthenticated = false
  }
  _authChecked = true
  return _isAuthenticated
}

function resetAuth() {
  _authChecked = false
  _isAuthenticated = false
}

router.beforeEach(async to => {
  if (!to.matched.some(r => r.meta?.requiresAuth)) return true
  let isAuth = await checkAuth()
  if (!isAuth){
      return { path: '/login' }
  } 
})

export default function (app) {
  app.use(router)
}
export { router, checkAuth, resetAuth }
