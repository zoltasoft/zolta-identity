export function formatIdentityDate(value: string | null | undefined) {
  if (!value) return 'Never'

  return new Intl.DateTimeFormat('en', {
    dateStyle: 'medium',
    timeStyle: 'short'
  }).format(new Date(value))
}
