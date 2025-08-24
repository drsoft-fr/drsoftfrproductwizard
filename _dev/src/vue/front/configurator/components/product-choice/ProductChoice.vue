<script setup>
import { inject, provide, ref, watch } from 'vue'
import NoProduct from '@/vue/front/configurator/components/product-choice/NoProduct.vue'
import Product from '@/vue/front/configurator/components/product-choice/Product.vue'
import { useQuantityRule } from '@/js/front/configurator/composables/useQuantityRule.js'

const props = defineProps({
  step: { type: Object, required: true },
  choice: { type: Object, required: true },
})

const activeStepIndex = inject('activeStepIndex')
const configurator = inject('configurator')
const selections = inject('selections')
const selectedChoice = inject('selectedChoice')
const steps = inject('steps')

const { applyRulesFromStep } = useQuantityRule()

const selected = ref(false)

const { drsoftfrproductwizard } = window?.prestashop?.modules || {
  noPictureImage: {},
}
const noPictureImage = drsoftfrproductwizard.noPictureImage || {}

watch(selectedChoice, () => {
  selected.value = props.choice.id === selectedChoice.value.id
})

function handleSelect(choice) {
  selectedChoice.value = choice

  const stepIndex = steps.value.findIndex((step) => step.id === props.step.id)

  if (stepIndex === -1) {
    return
  }

  let arr = [
    ...selections.value.filter((s) => s.stepId !== props.step.id),
    choice,
  ]

  arr.sort((a, b) => a.stepPosition - b.stepPosition)

  selections.value = arr

  applyRulesFromStep(steps.value, selections.value)

  // @TODO recalculer le prix total

  if (
    stepIndex === activeStepIndex.value &&
    stepIndex < steps.value.length - 1
  ) {
    activeStepIndex.value++
  }
}

provide('selected', selected)
</script>

<template>
  <div
    :id="
      'configurator-' +
      configurator.id +
      '__step-' +
      step.id +
      '__choice-' +
      choice.id
    "
    class="product-choice"
    :class="{ selected: selected }"
  >
    <Product
      v-if="choice.product"
      :choice
      :noPictureImage
      :product="choice.product"
      :is-selected="selected"
      @select="handleSelect"
    />
    <NoProduct
      v-else
      :choice
      :noPictureImage
      :is-selected="selected"
      @select="handleSelect"
    />
  </div>
</template>

<style scoped lang="scss">
.product-choice .card {
  transition: all 0.25s ease-in-out;
}

.product-choice:hover .card {
  box-shadow: var(--bs-box-shadow);
}

.product-choice.selected .card {
  border-color: var(--bs-success);
}
</style>
