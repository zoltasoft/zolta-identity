<script setup lang="ts">
const { t } = useI18n()
const props = withDefaults(defineProps<{
  panelId: string
  title: string
  icon?: string
  description?: string
  backTo?: string
  backLabel?: string
  bodyClass?: string
}>(), {
  description: undefined,
  icon: undefined,
  backTo: undefined,
  backLabel: undefined,
  bodyClass: 'mx-auto flex w-full max-w-7xl flex-col gap-6'
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
          <PageTitle
            :title="props.title"
            :icon="props.icon"
          />
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
      <UContainer class="h-full w-full overflow-y-auto py-6 sm:py-8">
        <div :class="props.bodyClass">
          <p
            v-if="props.description"
            class="max-w-3xl text-sm leading-6 text-muted"
          >
            {{ props.description }}
          </p>

          <slot />
        </div>
      </UContainer>
    </template>
  </UDashboardPanel>
</template>
