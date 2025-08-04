<script setup>
import { inject, ref } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
})

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
    <template v-if="true === choice.allowQuantity">
      <label for="quantity-input" class="form-label">{{
        $t('Quantity:')
      }}</label>
      <div class="quantity-input-group">
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
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
          class="btn btn-outline-secondary btn-sm"
          @click="increment"
        >
          <i class="fa fa-plus" aria-hidden="true"></i>
        </button>
      </div>
    </template>
    <template v-else>
      <div class="form-label">{{ $t('Quantity:') }}</div>
      <div class="form-control text-center">x {{ quantity }}</div>
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
