export default defineAppConfig({
  ui: {
    colors: {
      primary: 'green',
      neutral: 'slate'
    },
    avatar: {
      slots: {
        root: 'rounded-md',
        fallback: 'text-inherit font-bold'
      }
    }
  }
})
