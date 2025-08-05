<script setup>
import { computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ProductChoice from '@/vue/admin/configurator/components/product-choice/ProductChoice.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const store = useConfiguratorStore()

const step = computed(() => store.getStep(props.stepId))
const productChoices = computed(() => {
  if (!step.value || !step.value.product_choices) {
    return []
  }
  return step.value.product_choices
})
const hasProductChoices = computed(() => productChoices.value.length > 0)

const addProductChoice = () => {
  store.addProductChoice(props.stepId)
}
</script>

<template>
  <div class="product-choices-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0">{{ $t('Product selection') }}</h5>
    </div>

    <div v-if="!hasProductChoices" class="alert alert-info">
      <i class="material-icons">info</i>
      {{ $t('No product selection defined for this step.') }}
    </div>

    <div v-else class="product-choices-list mb-3">
      <ProductChoice
        v-for="productChoice in productChoices"
        :key="productChoice.id"
        :step-id="stepId"
        :product-choice-id="productChoice.id"
      />
    </div>

    <button
      type="button"
      class="btn btn-outline-primary"
      @click="addProductChoice"
    >
      <i class="material-icons">add</i>
      {{ $t('Add a product selection') }}
    </button>
  </div>
</template>

<style scoped lang="scss"></style>
