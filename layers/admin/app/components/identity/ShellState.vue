<script setup lang="ts">
type AdminShellState = 'loading' | 'empty' | 'error'

const props = withDefaults(defineProps<{
  state: AdminShellState
  title: string
  description?: string
  icon?: string
  loadingRows?: number
  loadingRowClass?: string
  retryLabel?: string
  retrying?: boolean
}>(), {
  description: undefined,
  icon: undefined,
  loadingRows: 3,
  loadingRowClass: 'h-16 w-full',
  retryLabel: 'Retry',
  retrying: false
})

const emit = defineEmits<{
  retry: []
}>()
</script>

<template>
  <UPageCard
    v-if="props.state === 'loading'"
    :title="props.title"
    :description="props.description"
    variant="subtle"
    role="status"
    aria-live="polite"
    aria-busy="true"
  >
    <div class="space-y-3">
      <p class="sr-only">
        {{ props.description ?? props.title }}
      </p>

      <USkeleton
        v-for="index in props.loadingRows"
        :key="index"
        :class="props.loadingRowClass"
      />
    </div>
  </UPageCard>

  <UPageCard
    v-else-if="props.state === 'empty'"
    :title="props.title"
    :description="props.description"
    variant="subtle"
    :ui="{ body: 'space-y-4' }"
    role="status"
    aria-live="polite"
  >
    <div class="flex items-center gap-2 text-sm text-muted">
      <UIcon
        :name="props.icon ?? 'i-lucide-inbox'"
        class="size-4"
      />
      <span>{{ props.title }}</span>
    </div>

    <slot name="empty-actions" />
  </UPageCard>

  <UAlert
    v-else
    color="error"
    variant="subtle"
    :icon="props.icon ?? 'i-lucide-circle-alert'"
    :title="props.title"
    :description="props.description"
    role="alert"
    aria-live="assertive"
  >
    <template #actions>
      <slot name="error-actions">
        <UButton
          :label="props.retryLabel"
          :aria-label="props.retryLabel"
          color="error"
          variant="outline"
          :loading="props.retrying"
          @click="emit('retry')"
        />
      </slot>
    </template>
  </UAlert>
</template>
