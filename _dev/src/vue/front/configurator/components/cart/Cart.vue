<script setup>
import { inject } from 'vue'
import Action from '@/vue/front/configurator/components/cart/Action.vue'
import Products from '@/vue/front/configurator/components/cart/Products.vue'

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
        <Products :selections />
        <Action class="mt-4" :selections />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
