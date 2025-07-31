<script setup>
import { inject, provide } from 'vue'
import NoProduct from '@/vue/front/configurator/components/product-choice/NoProduct.vue'
import Product from '@/vue/front/configurator/components/product-choice/Product.vue'

const props = defineProps({
  step: { type: Object, required: true },
  choice: { type: Object, required: true },
  selected: { type: Boolean, default: false },
})

const activeStepIndex = inject('activeStepIndex')
const configurator = inject('configurator')
const selections = inject('selections')
const selectedChoice = inject('selectedChoice')
const steps = inject('steps')

const { drsoftfrproductwizard } = window?.prestashop?.modules || {
  noPictureImage: {},
}
const noPictureImage = drsoftfrproductwizard.noPictureImage || {}

function handleSelect(choice) {
  selectedChoice.value = choice

  const stepIndex = steps.value.findIndex((step) => step.id === props.step.id)

  if (stepIndex === -1) {
    return
  }

  selections.value = [
    ...selections.value.filter((s) => s.stepId !== props.step.id),
    choice,
  ]

  // @TODO recalculer le prix total

  if (
    stepIndex === activeStepIndex.value &&
    stepIndex < steps.value.length - 1
  ) {
    activeStepIndex.value++
  }
}

provide('selected', props.selected)
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
      @select="handleSelect"
    />
    <NoProduct v-else :choice :noPictureImage @select="handleSelect" />
  </div>
</template>

<style scoped lang="scss"></style>
