<script setup>
import { computed } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import StepItem from './StepItem.vue'

const $t = inject('$t')

const store = useConfiguratorStore()

const steps = computed(() => store.steps || [])
const hasSteps = computed(() => steps.value.length > 0)
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
      <!--      TODO rajouter la boucle sur Step -->
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
