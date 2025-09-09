<script setup>
import { inject } from 'vue'

const addToCart = inject('addToCart')
const selections = inject('selections')
const $t = inject('$t')

function handleClick() {
  addToCart()
}

const steps = inject('steps')
</script>

<template>
  <div>
    <button
      type="button"
      class="btn btn-primary btn-lg btn-block"
      :disabled="selections.length < steps.length"
      @click="handleClick"
    >
      <i class="cart-btn-icon">&#128722;</i> {{ $t('Add to Cart') }}
    </button>
    <div
      v-if="selections.length < steps.length"
      class="alert alert-info mt-3 text-center"
    >
      <small>{{
        $t('Please complete all steps before adding to cart.')
      }}</small>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
