<script setup lang="ts">
withDefaults(defineProps<{
  variant?: 'starter' | 'saas' | 'identity' | 'tasks'
  size?: 'small' | 'medium' | 'header' | 'large'
}>(), {
  variant: 'starter',
  size: 'medium'
})
</script>

<template>
  <span
    aria-hidden="true"
    class="talred-brand-mark"
    :class="[`talred-brand-mark--${variant}`, `talred-brand-mark--${size}`]"
  />
</template>

<style scoped>
.talred-brand-mark {
  background-color: var(--talred-brand-mark-color);
  display: block;
  flex: none;
  mask: url('/branding/talred-mark.svg') center / contain no-repeat;
  -webkit-mask: url('/branding/talred-mark.svg') center / contain no-repeat;
}

.talred-brand-mark--small {
  height: 1.75rem;
  width: 1.75rem;
}

.talred-brand-mark--medium {
  height: 2rem;
  width: 2rem;
}

.talred-brand-mark--header {
  height: 2.5rem;
  width: 2.5rem;
}

.talred-brand-mark--large {
  height: 5rem;
  width: 5rem;
}

.talred-brand-mark--starter {
  --talred-brand-mark-color: #16937d;
}

.talred-brand-mark--saas {
  --talred-brand-mark-color: #8b5cf6;
}

.talred-brand-mark--identity {
  --talred-brand-mark-color: var(--ui-color-primary-500);
}

.talred-brand-mark--tasks {
  --talred-brand-mark-color: #6366f1;
}
</style>
