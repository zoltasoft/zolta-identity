<script setup lang="ts">
withDefaults(defineProps<{
  logoUrl?: string | null
  size?: 'header' | 'compact' | 'form'
}>(), {
  logoUrl: null,
  size: 'form'
})
</script>

<template>
  <span
    class="identity-auth-brand-mark"
    :class="`identity-auth-brand-mark--${size}`"
    aria-hidden="true"
  >
    <img
      v-if="logoUrl"
      :src="logoUrl"
      alt=""
    >
    <UIcon
      v-else
      name="i-lucide-app-window"
      class="identity-auth-brand-mark-icon"
    />
  </span>
</template>

<style scoped>
.identity-auth-brand-mark {
  align-items: center;
  background: var(--ui-color-primary-500);
  border-radius: 9999px;
  box-sizing: border-box;
  color: #fff;
  display: inline-flex;
  flex: none;
  justify-content: center;
  overflow: hidden;
}

.identity-auth-brand-mark--header {
  height: 2.5rem;
  width: 2.5rem;
}

.identity-auth-brand-mark--compact {
  height: 1.75rem;
  width: 1.75rem;
}

.identity-auth-brand-mark--form {
  height: 2.75rem;
  width: 2.75rem;
}

.identity-auth-brand-mark img {
  border-radius: inherit;
  display: block;
  height: 100%;
  object-fit: contain;
  width: 100%;
}

.identity-auth-brand-mark-icon {
  height: 1.25rem;
  width: 1.25rem;
}
</style>
