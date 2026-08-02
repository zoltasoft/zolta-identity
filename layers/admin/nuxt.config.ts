import { adminAliases } from './index'

// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  alias: {
    ...adminAliases
  }
})
