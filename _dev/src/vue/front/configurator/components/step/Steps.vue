<script setup>
import { inject } from 'vue'
import Step from '@/vue/front/configurator/components/step/Step.vue'

const activeStepIndex = inject('activeStepIndex')
const steps = inject('steps')
</script>

<template>
  <div>
    <Step
      v-for="(step, index) in steps"
      :step
      :step-index="index"
      :active="index === activeStepIndex"
      :completed="index < activeStepIndex && activeStepIndex >= 0"
      :disabled="index > activeStepIndex && activeStepIndex >= 0"
      :class="0 < index ? 'mt-8' : ''"
    />
  </div>
</template>

<style scoped lang="scss"></style>
