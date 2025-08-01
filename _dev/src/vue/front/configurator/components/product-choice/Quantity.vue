<script setup>
import { inject, ref } from 'vue'

const $t = inject('$t')

const quantity = ref(1)

function increment() {
  quantity.value++
}

function decrement() {
  if (1 >= quantity.value) {
    return
  }

  quantity.value--
}
</script>

<template>
  <div class="product-quantity mt-3">
    <label for="quantity-input" class="form-label">{{ $t('Quantity:') }}</label>
    <div class="quantity-input-group">
      <button
        type="button"
        class="btn btn-outline-secondary"
        @click="decrement"
        :disabled="quantity <= 1"
      >
        <i class="fa fa-minus" aria-hidden="true"></i>
      </button>
      <input
        type="number"
        id="quantity-input"
        class="form-control"
        v-model.number="quantity"
        min="1"
      />
      <button
        type="button"
        class="btn btn-outline-secondary"
        @click="increment"
      >
        <i class="fa fa-plus" aria-hidden="true"></i>
      </button>
    </div>
  </div>
</template>

<style scoped lang="scss">
.quantity-input-group {
  align-items: center;
  display: flex;
}

.quantity-input-group input {
  margin: 0 0.5rem;
  text-align: center;
  width: 60px;
}
</style>
