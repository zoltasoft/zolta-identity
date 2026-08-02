<script setup lang="ts">
const props = defineProps<{
  title: string
  description?: string
  count?: number
}>()
</script>

<template>
  <section class="overflow-hidden rounded-2xl border border-default bg-default shadow-xs">
    <header class="flex flex-col gap-3 border-b border-default px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
      <div class="min-w-0">
        <div class="flex items-center gap-2">
          <h2 class="font-semibold text-highlighted">
            {{ props.title }}
          </h2>
          <UBadge
            v-if="props.count !== undefined"
            :label="String(props.count)"
            color="neutral"
            variant="soft"
          />
        </div>
        <p
          v-if="props.description"
          class="mt-1 text-sm text-muted"
        >
          {{ props.description }}
        </p>
      </div>
      <slot name="actions" />
    </header>

    <div class="overflow-x-auto">
      <slot />
    </div>

    <footer
      v-if="$slots.footer"
      class="flex flex-col gap-3 border-t border-default px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5"
    >
      <slot name="footer" />
    </footer>
  </section>
</template>
