const viteAllowedHosts = [
  'localhost',
  '127.0.0.1',
  ...(process.env.NUXT_VITE_ALLOWED_HOSTS ?? '')
    .split(',')
    .map(host => host.trim())
    .filter(Boolean)
]

export default defineNuxtConfig({
  extends: [
    './layers/auth',
    './layers/admin',
    './packages/i18n',
    './packages/ui'
  ],
  modules: ['@nuxt/eslint', '@vueuse/nuxt'],
  i18n: {
    locales: [
      {
        code: 'en',
        name: 'English',
        file: 'en.json'
      },
      {
        code: 'fr',
        name: 'Français',
        file: 'fr.json'
      }
    ],
    defaultLocale: 'en',
    langDir: 'locales'
  },
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  nitro: {
    prerender: {
      routes: [],
      crawlLinks: false,
      failOnError: true
    }
  },
  routeRules: {
    '/': { redirect: { to: '/admin/sign-in', statusCode: 302 } }
  },
  eslint: {
    config: {
      stylistic: {
        indent: 2,
        quotes: 'single',
        semi: false,
        commaDangle: 'never',
        braceStyle: '1tbs'
      }
    }
  },
  vite: {
    plugins: [],
    server: {
      allowedHosts: viteAllowedHosts,
      watch: {
        ignored: ['**/docs/**', '**/storage/**', '**/.cache/**', '**/tmp/**']
      }
    }
  }
})
