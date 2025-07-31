<script setup>
import { inject } from 'vue'
import Action from '@/vue/front/configurator/components/cart/Action.vue'
import Summary from '@/vue/front/configurator/components/cart/Summary.vue'

const $t = inject('$t')
</script>

<template>
  <div class="cart-summary">
    <h3 class="text-right">{{ $t('Your Selection') }}</h3>
    <div class="card">
      <div class="card-body">
        <Summary />
        <Action class="mt-4" />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
