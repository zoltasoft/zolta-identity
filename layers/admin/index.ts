import { fileURLToPath } from 'node:url'

export const adminDir = fileURLToPath(new URL('./', import.meta.url))

export const adminAliases = {
  '#admin': adminDir
} as const
