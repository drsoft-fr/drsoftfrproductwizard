<script setup>
import { inject, computed } from 'vue'
import { useQuantityRule } from '@/js/front/configurator/composables/useQuantityRule.js'

const props = defineProps({
  choice: { type: Object, required: true },
})

const selected = inject('selected')
const selections = inject('selections')
const steps = inject('steps')
const $t = inject('$t')

const { onSelectedQuantityChanged } = useQuantityRule()

const rule = computed(() => props.choice.quantityRule)
const locked = computed(() => !!rule.value?.locked)
const min = computed(() => rule.value?.min ?? 0)
const max = computed(() => rule.value?.max ?? null)

function updateQty(newVal) {
  let v = Number(newVal || 0)
  if (min.value != null) v = Math.max(min.value, v)
  if (max.value != null) v = Math.min(max.value, v)
  props.choice.quantity = v

  if (true === selected.value) {
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
  <template v-if="'none' !== rule.mode">
    <div class="drpw:mt-3">
      <template v-if="false === locked">
        <label :for="`pc-${choice.id}__quantity-input`" class="drpw:hidden">
          {{ $t('Quantity:') }}
        </label>

        <div class="drpw:input">
          <button
            type="button"
            class="drpw:btn drpw:btn-neutral drpw:btn-sm"
            @click="decrement"
            :disabled="choice.quantity <= min"
          >
            -
          </button>

          <input
            type="number"
            :id="`pc-${choice.id}__quantity-input`"
            class="drpw:text-center"
            :value="choice.quantity"
            :min="min"
            :max="max"
            @input="updateQty($event.target.value)"
          />

          <button
            type="button"
            class="drpw:btn drpw:btn-neutral drpw:btn-sm"
            @click="increment"
          >
            +
          </button>
        </div>
      </template>

      <template v-else>
        <div class="drpw:input">
          <div class="drpw:mx-auto">x {{ choice.quantity }}</div>
        </div>
      </template>
    </div>
  </template>
</template>

<style scoped lang="scss"></style>
