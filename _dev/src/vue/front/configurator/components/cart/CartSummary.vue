<script setup>
import { inject } from 'vue'
import CardAction from '@/vue/front/configurator/components/cart/CardAction.vue'
import CardProducts from '@/vue/front/configurator/components/cart/CardProducts.vue'

const props = defineProps({
  selections: { type: Array, required: true, default: () => [] },
})

const $t = inject('$t')
</script>

<template>
  <div class="cart-summary">
    <h3 class="text-right">{{ $t('Your Selection') }}</h3>
    <div class="card">
      <div class="card-body">
        <CardProducts :selections />
        <CardAction class="mt-4" :selections />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
