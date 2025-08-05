<script setup>
import { computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import StepItem from './StepItem.vue'
import Step from '@/vue/admin/configurator/components/step/Step.vue'

const $t = inject('$t')

const store = useConfiguratorStore()

const steps = computed(() => store.steps || [])
const hasSteps = computed(() => steps.value.length > 0)

const addStep = () => {
  store.addStep()
}
</script>

<template>
  <div class="steps-container">
    <h2 class="mb-3">{{ $t('Steps in the scenario') }}</h2>
    <div class="mb-3">
      <div class="alert alert-info">
      <div v-if="!hasSteps" class="alert alert-info">
        <i class="material-icons">info</i>
        {{ $t('No steps defined for this scenario.') }}
      </div>
      <Step v-for="step in steps" :key="step.id" :step-id="step.id" />
    </div>
    <button type="button" class="btn btn-outline-primary" @click="addStep">
      <i class="material-icons">add</i>
      {{ $t('Add a step') }}
    </button>
  </div>
</template>

<style scoped lang="scss"></style>
