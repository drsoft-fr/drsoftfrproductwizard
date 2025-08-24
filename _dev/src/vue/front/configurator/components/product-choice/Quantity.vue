<script setup>
import { inject, computed } from 'vue'
import { useQuantityRule } from '@/js/front/configurator/composables/useQuantityRule.js'

const props = defineProps({
  choice: { type: Object, required: true },
  isSelected: { type: Boolean, required: true },
})

const selections = inject('selections')
const steps = inject('steps')
const $t = inject('$t')

const { onSelectedQuantityChanged } = useQuantityRule()

const rule = computed(() => props.choice.quantityRule)
const locked = computed(() => !!rule.value?.locked)
const min = computed(() => rule.value?.min ?? 1)
const max = computed(() => rule.value?.max ?? null)

function updateQty(newVal) {
  let v = Number(newVal || 0)
  if (min.value != null) v = Math.max(min.value, v)
  if (max.value != null) v = Math.min(max.value, v)
  props.choice.quantity = v

  if (props.isSelected) {
    onSelectedQuantityChanged(steps.value, props.choice, selections.value)
  }
}

const increment = () => {
  if (locked.value) {
    return
  }

  const nxt =
    max.value !== null
      ? Math.min(max.value, (props.choice.quantity || 0) + 1)
      : (props.choice.quantity || 0) + 1

  updateQty(nxt)
}

const decrement = () => {
  if (locked.value) {
    return
  }

  const nxt = Math.max(min.value ?? 1, (props.choice.quantity || 0) - 1)

  updateQty(nxt)
}
</script>

<template>
  <div class="product-quantity mt-3">
    <template v-if="false === locked">
      <label :for="`pc-${choice.id}__quantity-input`" class="form-label">{{
        $t('Quantity:')
      }}</label>
      <div class="quantity-input-group">
        <button
          type="button"
          class="btn btn-outline-secondary btn-sm"
          @click="decrement"
          :disabled="choice.quantity <= min"
        >
          <i class="fa fa-minus" aria-hidden="true"></i>
        </button>

        <input
          type="number"
          :id="`pc-${choice.id}__quantity-input`"
          class="form-control"
          :value="choice.quantity"
          :min="min"
          :max="max"
          @input="updateQty($event.target.value)"
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
