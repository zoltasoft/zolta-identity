import { fileURLToPath } from 'node:url'
import { defineNuxtConfig } from 'nuxt/config'

const uiAssetsDir = fileURLToPath(new URL('./assets', import.meta.url))

export default defineNuxtConfig({
  alias: {
    '#ui-assets': uiAssetsDir
  },
  modules: ['@nuxt/ui'],
  ui: {
    fonts: false
  },
  css: ['#ui-assets/css/main.css'],
  compatibilityDate: '2025-07-15'
})
