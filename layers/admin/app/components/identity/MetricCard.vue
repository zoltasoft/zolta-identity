<script setup lang="ts">
const props = withDefaults(defineProps<{
  label: string
  value: string | number
  icon: string
  description?: string
  color?: 'primary' | 'success' | 'warning' | 'error' | 'info' | 'neutral'
}>(), {
  description: undefined,
  color: 'primary'
})

const iconColorClass = computed(() => ({
  primary: 'text-primary',
  success: 'text-success',
  warning: 'text-warning',
  error: 'text-error',
  info: 'text-info',
  neutral: 'text-muted'
})[props.color])
</script>

<template>
  <UPageCard
    variant="subtle"
    class="min-w-0"
    :ui="{ container: 'gap-3 p-4 sm:p-5' }"
  >
    <div class="flex items-start justify-between gap-4">
      <div class="min-w-0">
        <p class="text-sm font-medium text-muted">
          {{ props.label }}
        </p>
        <p class="mt-1 text-2xl font-semibold tracking-tight text-highlighted">
          {{ props.value }}
        </p>
        <p
          v-if="props.description"
          class="mt-1 truncate text-xs text-muted"
        >
          {{ props.description }}
        </p>
      </div>
      <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-elevated ring ring-default">
        <UIcon
          :name="props.icon"
          class="size-5"
          :class="iconColorClass"
        />
      </div>
    </div>
  </UPageCard>
</template>
