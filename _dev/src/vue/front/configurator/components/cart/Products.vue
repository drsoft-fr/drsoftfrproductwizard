<script setup>
import { inject } from 'vue'

const props = defineProps({
  selections: { type: Array, required: true, default: () => [] },
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
  <template v-if="selections.length === 0">
    <div class="text-center">
      <i>&#128722;</i>
      <h4 class="mt-3">{{ $t('No products selected yet.') }}</h4>
      <p class="text-muted">
        {{ $t('Select options from the configurator to build your product.') }}
      </p>
    </div>
  </template>
  <template v-else>
    <template v-for="(selection, index) in selections" :key="index">
      <div>
        <div>{{ selection.name }}</div>
        <div v-if="selection.combinationName">
          <span class="badge badge-success">{{
            selection.combinationName
          }}</span>
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
    <div>
      <div>{{ $t('Total') }}</div>
      <div>{{ formatPrice(0) }}</div>
    </div>
  </template>
</template>

<style scoped lang="scss"></style>
