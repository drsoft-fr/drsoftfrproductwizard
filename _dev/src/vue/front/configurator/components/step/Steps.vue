<script setup>
import { inject } from 'vue'
import Step from '@/vue/front/configurator/components/step/Step.vue'

const steps = inject('steps')
</script>

<template>
  <div>
    <Step
      v-for="(step, index) in steps"
      :step
      :class="0 < index ? 'mt-8' : ''"
    />
  </div>
</template>

<style scoped lang="scss"></style>
