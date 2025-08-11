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
    <Menubar>
      <template #start>
        <h4 class="mb-0">{{ $t('Product selection') }}</h4>
      </template>
      <template #end>
        <Button type="button" @click="addProductChoice">
          <i class="material-icons">add</i>
          {{ $t('Add a product selection') }}
        </Button>
      </template>
    </Menubar>

    <Transition name="fade" mode="out-in">
      <Message v-if="!hasProductChoices" severity="info" class="mt-3">
        {{ $t('No product selection defined for this step.') }}
      </Message>

      <div v-else class="product-choices-list mt-3">
        <ProductChoice
          v-for="(productChoice, index) in productChoices"
          :key="productChoice.id"
          :step-id="stepId"
          :product-choice-id="productChoice.id"
          :class="0 < index ? 'mt-3' : ''"
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped lang="scss"></style>
