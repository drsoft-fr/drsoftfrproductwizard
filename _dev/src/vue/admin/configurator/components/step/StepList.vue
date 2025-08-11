<script setup>
import { ref, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import { useSortable } from '@/js/admin/configurator/form/composables/useSortable'
import Step from '@/vue/admin/configurator/components/step/Step.vue'

const $t = inject('$t')

const store = useConfiguratorStore()

const stepsContainer = ref(null)

const steps = computed(() => store.sortedSteps)
const hasSteps = computed(() => steps.value.length > 0)

const addStep = () => {
  store.addStep()
}

useSortable(() => stepsContainer.value)
</script>

<template>
  <div class="steps-container">
    <Menubar>
      <template #start>
        <h2 class="m-0">{{ $t('Steps in the scenario') }}</h2>
      </template>
      <template #end>
        <Button type="button" @click="addStep">
          <i class="material-icons">add</i>
          {{ $t('Add a step') }}
        </Button>
      </template>
    </Menubar>
    <Transition name="fade" mode="out-in">
      <div v-if="!hasSteps" class="alert alert-info mt-3">
        {{ $t('No steps defined for this scenario.') }}
      </div>
    </Transition>
    <div ref="stepsContainer" class="steps-list sortable-list mt-3">
      <Step
        v-for="(step, index) in steps"
        :key="step.id"
        :step-id="step.id"
        :class="0 < index ? 'mt-3' : ''"
      />
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
