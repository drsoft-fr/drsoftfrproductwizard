<script setup>
import { inject } from 'vue'
import ProductChoice from '@/vue/front/configurator/components/product-choice/ProductChoice.vue'

const props = defineProps({
  step: { type: Object, required: true },
  choices: { type: Array, required: true },
})

const selectedChoice = inject('selectedChoice')

function isChoiceSelected(choiceId) {
  return selectedChoice.value && selectedChoice.value.id === choiceId
}
</script>

<template>
  <div>
    <ProductChoice
      v-for="choice in choices"
      :key="choice.id"
      :step
      :choice
      :selected="isChoiceSelected(choice.id)"
      class="col-12 col-sm-6 col-lg-3"
    />
  </div>
</template>

<style scoped lang="scss"></style>
