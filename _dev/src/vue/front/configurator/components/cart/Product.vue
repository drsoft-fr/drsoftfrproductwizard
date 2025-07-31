<script setup>
import { inject } from 'vue'

const props = defineProps({
  selection: { type: Object, required: true },
})

const $t = inject('$t')

function formatPrice(price) {
  return new Intl.NumberFormat(document.documentElement.lang, {
    style: 'currency',
    currency: prestashop.currency.iso_code,
  }).format(price)
}
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
