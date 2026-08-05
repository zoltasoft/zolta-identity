<script setup lang="ts">
withDefaults(defineProps<{
  label: string
  value: string | number
  icon?: string
  description?: string
  color?: 'neutral' | 'primary' | 'success' | 'warning' | 'error'
}>(), {
  icon: 'i-lucide-info',
  description: '',
  color: 'neutral'
})

const toneClasses = {
  neutral: 'text-muted',
  primary: 'text-primary',
  success: 'text-success',
  warning: 'text-warning',
  error: 'text-error'
} as const
</script>

<template>
  <div class="rounded-xl border border-default bg-elevated/40 px-3 py-2">
    <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-muted">
      <UIcon
        v-if="icon"
        :name="icon"
        :class="['size-4', toneClasses[color]]"
      />
      <span>{{ label }}</span>
    </div>

    <div class="mt-1 flex items-baseline gap-2">
      <span class="text-lg font-semibold text-highlighted tabular-nums">
        {{ value }}
      </span>
      <span
        v-if="description"
        class="text-xs text-muted"
      >
        {{ description }}
      </span>
    </div>
  </div>
</template>
