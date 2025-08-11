<script setup>
import { computed, inject } from 'vue'

const props = defineProps({
  fullscreen: { type: Boolean, default: false },
  size: { type: String, default: 'md' },
  text: { type: String, default: null },
  showText: { type: Boolean, default: true },
})

const $t = inject('$t')

const spinnerClass = computed(() => {
  const classes = ['spinner-border']

  switch (props.size) {
    case 'sm':
      classes.push('spinner-border-sm')

      break
    case 'lg':
      classes.push('spinner-border-lg')

      break
    default:
      break
  }

  return classes.join(' ')
})

const loaderClass = computed(() => {
  return props.fullscreen ? 'loader-fullscreen' : 'loader-container'
})

const computedText = computed(() => {
  return props.text || $t('Loading...')
})
</script>

<template>
  <div :class="loaderClass">
    <div class="loader-content">
      <ProgressSpinner />
      <p v-if="showText" class="mt-3 mb-0">{{ computedText }}</p>
    </div>
  </div>
</template>

<style scoped>
.loader-container {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.75);
  z-index: 1000;
}

.loader-fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: rgba(255, 255, 255, 0.75);
  z-index: 9999;
}

.loader-content {
  text-align: center;
}
</style>
