<script setup>
import { computed, inject, provide, ref } from 'vue'
import ProductChoices from '@/vue/front/configurator/components/product-choice/ProductChoices.vue'

const props = defineProps({
  step: { type: Object, required: true },
  stepIndex: { type: Number, required: true },
  active: { type: Boolean, default: false },
  completed: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const activeStepIndex = inject('activeStepIndex')
const configurator = inject('configurator')
const selections = inject('selections')
const $t = inject('$t')

const selectedChoice = ref(null)

const filteredChoices = computed(() => {
  return props.step.choices.filter((choice) => {
    if (!choice.displayConditions || choice.displayConditions.length === 0) {
      return true
    }

    return choice.displayConditions.some((condition) => {
      const selection = selections.value.find(
        (s) => s.stepId === condition.step,
      )

      if (!selection) {
        return false
      }

      return selection.id === condition.choice
    })
  })
})

function handleToggleStep() {
  if (true === props.disabled) {
    return
  }

  if (props.stepIndex === activeStepIndex.value) {
    return
  }

  activeStepIndex.value = props.stepIndex
}

provide('selectedChoice', selectedChoice)
</script>

<template>
  <div
    :id="'configurator-' + configurator.id + '__step-' + step.id"
    class="step-container"
    :class="{ active: active, completed: completed, disabled: disabled }"
  >
    <div @click="handleToggleStep" class="step-header">
      <div class="step-number">
        <Transition name="slide-fade" mode="out-in">
          <span v-if="completed" class="fa fa-check" aria-hidden="true"></span>
          <span v-else>{{ stepIndex + 1 }}</span>
        </Transition>
      </div>
      <h3 class="step-title">{{ step.label }}</h3>
      <div class="step-toggle">
        <Transition name="fade" mode="out-in">
          <span v-if="active" class="fa fa-arrow-up" aria-hidden="true"></span>
          <span v-else class="fa fa-arrow-down" aria-hidden="true"></span>
        </Transition>
      </div>
    </div>
    <Transition name="height-fade" mode="out-in">
      <div class="step-content" v-show="active">
        <ProductChoices
          v-if="0 < filteredChoices.length"
          :step
          :choices="filteredChoices"
          class="product-choices row g-3"
        />
        <div v-else class="text-center alert alert-info">
          <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
          <p>{{ $t('No options available for this step.') }}</p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped lang="scss">
.step-container {
  transition: opacity 0.3s ease-in-out 0.1s;

  .step-header {
    display: flex;
    cursor: pointer;

    .step-title {
      flex-grow: 1;
    }
  }

  .step-number {
    align-items: center;
    background-color: var(--bs-gray);
    border-radius: 50%;
    color: var(--bs-light);
    display: flex;
    height: 30px;
    justify-content: center;
    margin-right: 1rem;
    transition: background-color 0.3s ease-in-out 0.1s;
    width: 30px;
  }

  .step-toggle {
    align-self: center;
  }

  &.active {
    .step-number {
      background-color: var(--bs-primary);
      color: var(--bs-light);
    }
  }

  &.completed {
    .step-number {
      background-color: var(--bs-success);
    }
  }

  &.disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }
}
</style>
