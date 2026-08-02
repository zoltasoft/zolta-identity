import { defineNuxtConfig } from 'nuxt/config'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    [
      '@nuxtjs/i18n',
      {
        strategy: 'prefix_and_default',
        skipSettingLocaleOnNavigate: false,
        detectBrowserLanguage: {
          useCookie: true,
          cookieKey: 'i18n_redirected',
          alwaysRedirect: true,
          redirectOn: 'root'
        }
      }
    ]
  ],
  compatibilityDate: '2025-07-15'
})
