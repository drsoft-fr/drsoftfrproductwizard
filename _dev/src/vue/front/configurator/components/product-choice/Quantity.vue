<script setup>
import { inject, ref } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
})

const $t = inject('$t')

function increment() {
  const max = props.choice.quantityRule.max
    ? props.choice.quantityRule.max
    : null

  if (null !== max && max <= props.choice.quantity) {
    return
  }

  props.choice.quantity++
}

function decrement() {
  const min = props.choice.quantityRule.min ? props.choice.quantityRule.min : 1

  if (min >= props.choice.quantity) {
    return
  }

  props.choice.quantity--
}
</script>

<template>
  <div class="product-quantity mt-3">
    <template v-if="false === choice.quantityRule.locked">
      <label for="quantity-input" class="form-label">{{
        $t('Quantity:')
      }}</label>
      <div class="quantity-input-group">
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
          @click="decrement"
          :disabled="choice.quantity <= 1"
        >
          <i class="fa fa-minus" aria-hidden="true"></i>
        </button>
        <input
          type="number"
          id="quantity-input"
          class="form-control"
          v-model.number="choice.quantity"
          :min="choice.quantityRule.min ? choice.quantityRule.min : 1"
          :max="choice.quantityRule.max ? choice.quantityRule.max : null"
        />
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
          @click="increment"
        >
          <i class="fa fa-plus" aria-hidden="true"></i>
        </button>
      </div>
    </template>
    <template v-else>
      <div class="form-label">{{ $t('Quantity:') }}</div>
      <div class="form-control text-center">x {{ choice.quantity }}</div>
    </template>
  </div>
</template>

<style scoped lang="scss">
.quantity-input-group {
  display: flex;
}

.quantity-input-group input {
  border-width: 1px 0;
  flex: auto;
  text-align: center;
}
</style>
