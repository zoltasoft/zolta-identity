declare module '@nuxt/schema' {
  interface PublicRuntimeConfig {
    identityAuth: {
      productName: string
      loginRedirect: string
      logoutRedirect: string
      registerRedirect: string
    }
  }

  interface RuntimeConfig {
    identity: {
      apiUrl: string
      project: string
      clientId: string
      clientSecret: string
      sandbox: {
        apiUrl: string
        project: string
        clientId: string
        clientSecret: string
      }
    }
    identityHostedApplicationsToken: string
  }
}

export {}
