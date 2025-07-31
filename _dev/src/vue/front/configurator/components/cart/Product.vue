<script setup>
import { inject } from 'vue'

const props = defineProps({
  selection: { type: Object, required: true },
})

const formatPrice = inject('formatPrice')
const $t = inject('$t')
</script>

<template>
  <div>
    <div>{{ selection.name }}</div>
    <div v-if="selection.combinationName">
      <span class="badge badge-success">{{ selection.combinationName }}</span>
    </div>
    <div>
      <span>{{ $t('Quantity') }}:</span>
      <span>{{ selection.quantity }}</span>
    </div>
  </div>
  <div>
    {{ formatPrice(selection.price * selection.quantity) }}
  </div>
</template>

<style scoped lang="scss"></style>
