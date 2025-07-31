<script setup>
import { inject, provide, ref } from 'vue'
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
const $t = inject('$t')

const selectedChoice = ref(null)

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
        <span v-if="completed" class="fa fa-check" aria-hidden="true"></span>
        <span v-else>{{ stepIndex + 1 }}</span>
      </div>
      <h3 class="step-title">{{ step.label }}</h3>
      <div class="step-toggle">
        <Transition>
          <span v-if="active" class="fa fa-arrow-up" aria-hidden="true"></span>
          <span v-else class="fa fa-arrow-down" aria-hidden="true"></span>
        </Transition>
      </div>
    </div>
    <div class="step-content" v-if="active">
      <ProductChoices
        v-if="0 < step.choices.length"
        :step
        :choices="step.choices"
        class="product-choices row g-3"
      />
      <div v-else class="text-center alert alert-info">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        <p>{{ $t('No options available for this step.') }}</p>
      </div>
    </div>
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
