export const routes = [
  { path: '/', redirect: '/domains' },
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: 'domains',
        component: () => import('@/pages/domains/list.vue'),
      }, 
      {
        path: 'domains/store',
        component: () => import('@/pages/domains/store.vue'),
      },            
      {
        path: 'domains/:id',
        component: () => import('@/pages/domains/edit.vue'),
      },   
      {
        path: 'logs',
        component: () => import('@/pages/logs.vue'),
      }, 
      {
        path: 'settings',
        component: () => import('@/pages/settings.vue'),
      },                      
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  },
]
