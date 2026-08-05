<script setup lang="ts">
const props = withDefaults(defineProps<{
  title: string
  icon?: string
}>(), {
  icon: undefined
})
</script>

<template>
  <div class="flex items-center gap-2">
    <UIcon
      v-if="props.icon"
      :name="props.icon"
      class="size-4 shrink-0 text-muted"
    />
    <span class="truncate font-semibold text-highlighted">
      {{ props.title }}
    </span>
  </div>
</template>
