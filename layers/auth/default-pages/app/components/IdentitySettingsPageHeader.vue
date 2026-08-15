<script setup lang="ts">
const props = defineProps<{
  title: string
  description?: string
  icon: string
}>()
</script>

<template>
  <header class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex min-w-0 items-start gap-3">
      <slot name="leading">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
          <UIcon
            :name="props.icon"
            class="size-5"
          />
        </span>
      </slot>

      <div class="min-w-0">
        <h1 class="text-xl font-semibold tracking-tight text-highlighted">
          {{ props.title }}
        </h1>
        <p
          v-if="props.description"
          class="mt-1 max-w-2xl text-sm leading-6 text-muted"
        >
          {{ props.description }}
        </p>
      </div>
    </div>

    <div
      v-if="$slots.actions"
      class="shrink-0 sm:pt-1"
    >
      <slot name="actions" />
    </div>
  </header>
</template>
