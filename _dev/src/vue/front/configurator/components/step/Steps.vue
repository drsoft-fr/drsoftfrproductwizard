<script setup>
import Step from '@/vue/front/configurator/components/step/Step.vue'

const props = defineProps({
  configurator: { type: Object, required: true },
  steps: { type: Array, required: true },
})
</script>

<template>
  <Step
    v-for="(step, index) in steps"
    :step
    :configurator
    :class="0 < index ? 'mt-8' : ''"
  />
</template>

<style scoped lang="scss"></style>
