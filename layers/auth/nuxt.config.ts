import {
  createAuthCsurfConfig,
  createAuthRuntimeConfig
} from './index'

export default defineNuxtConfig({
  modules: ['nuxt-auth-utils', 'nuxt-csurf'],
  runtimeConfig: createAuthRuntimeConfig(),
  csurf: createAuthCsurfConfig(),
  routeRules: {
    '/api/auth/**': {
      headers: {
        'cache-control': 'no-store'
      }
    }
  }
})
