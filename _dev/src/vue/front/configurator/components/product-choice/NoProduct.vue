<script setup>
import { inject } from 'vue'
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'
import Quantity from '@/vue/front/configurator/components/product-choice/Quantity.vue'

const props = defineProps({
  choice: { type: Object, required: true },
})

const emit = defineEmits(['onSelect'])

const selected = inject('selected')
</script>

<template>
  <div
    class="drpw:card drpw:bg-base-100 drpw:shadow-sm drpw:h-full drpw:transition drpw:border"
    :class="true === selected ? 'drpw:border-success' : 'drpw:border-base-100'"
  >
    <div class="drpw:card-body">
      <h4 class="drpw:card-title">{{ choice.label }}</h4>

      <div
        v-if="choice.description"
        v-html="choice.description"
        class="drpw:mt-3 drpw:grow"
      ></div>

      <Quantity :choice />

      <Action @click="$emit('onSelect', choice)" />
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
