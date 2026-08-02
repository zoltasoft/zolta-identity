<script setup lang="ts">
const { t } = useI18n()
const props = withDefaults(defineProps<{
  panelId: string
  title: string
  description?: string
  backTo?: string
  backLabel?: string
  bodyClass?: string
}>(), {
  description: undefined,
  backTo: undefined,
  backLabel: undefined,
  bodyClass: 'mx-auto flex w-full max-w-6xl flex-col gap-6'
})

const resolvedBackLabel = computed(() => {
  return props.backLabel ?? t('admin.common.back')
})
</script>

<template>
  <UDashboardPanel :id="props.panelId">
    <template #header>
      <UDashboardNavbar>
        <template #title>
          <PageTitle :title="props.title" />
        </template>

        <template #right>
          <slot name="leading" />

          <div
            v-if="props.backTo || $slots.actions"
            class="flex items-center gap-2"
          >
            <UButton
              v-if="props.backTo"
              :label="resolvedBackLabel"
              color="neutral"
              variant="ghost"
              icon="i-lucide-arrow-left"
              :to="props.backTo"
            />

            <slot name="actions" />
          </div>
        </template>
      </UDashboardNavbar>
    </template>

    <template #body>
      <div :class="props.bodyClass">
        <UPageCard
          v-if="props.description"
          :title="props.title"
          :description="props.description"
          variant="subtle"
        />

        <slot />
      </div>
    </template>
  </UDashboardPanel>
</template>
