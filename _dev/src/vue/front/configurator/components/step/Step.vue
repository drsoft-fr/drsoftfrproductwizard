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
    const dc = choice.displayConditions

    // Expect nested groups (OR-of-ANDs)
    const groups = Array.isArray(dc) ? dc : []

    if (groups.length === 0) {
      return true
    }

    const predicate = (condition) => {
      const selection = selections.value.find(
        (s) => Number(s.stepId) === Number(condition.step),
      )

      if (!selection) {
        return false
      }

      return Number(selection.id) === Number(condition.choice)
    }

    // OR of ANDs
    return groups.some(
      (group) =>
        Array.isArray(group) && group.length > 0 && group.every(predicate),
    )
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
    class="step-container drpw:collapse drpw:collapse-arrow drpw:bg-base-200 drpw:border drpw:border-base-300"
    :class="`${true === disabled ? ' drpw:opacity-25' : ''}`"
  >
    <input
      type="radio"
      name="step-accordion"
      :checked="active"
      @click="handleToggleStep"
      :disabled
    />

    <div class="drpw:flex drpw:collapse-title drpw:items-center">
      <Transition name="slide-fade" mode="out-in">
        <div v-if="completed" class="drpw:badge drpw:badge-success">
          <i aria-hidden="true">✓</i>
        </div>

        <div v-else-if="active" class="drpw:badge drpw:badge-primary">
          <span>{{ stepIndex + 1 }}</span>
        </div>

        <div v-else class="drpw:badge drpw:badge-neutral">
          <span>{{ stepIndex + 1 }}</span>
        </div>
      </Transition>

      <h3 class="drpw:ml-3 drpw:mb-0">{{ step.label }}</h3>
    </div>

    <div class="drpw:collapse-content step-content" v-show="active">
      <div
        v-if="step.description"
        v-html="step.description"
        class="drpw:my-3"
      ></div>

      <ProductChoices
        v-if="0 < filteredChoices.length"
        :step
        :choices="filteredChoices"
      />

      <div v-else class="drpw:text-center drpw:alert drpw:alert-info">
        <p>{{ $t('No options available for this step.') }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
