<script setup>
import { computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
})

const $t = inject('$t')
const store = useConfiguratorStore()

const rule = computed({
  get() {
    const choice = store.getProductChoice(props.stepId, props.productChoiceId)

    if (!choice.quantity_rule) {
      const def = {
        mode: 'none',
        locked: false,
        sources: [],
        offset: 0,
        min: null,
        max: null,
        round: 'none',
      }

      // Initialize in store so v-model binds to reactive object from store
      store.updateQuantityRule(props.stepId, props.productChoiceId, def)

      return def
    }

    return choice.quantity_rule
  },
  set(v) {
    store.updateQuantityRule(props.stepId, props.productChoiceId, v)
  },
})
</script>

<template>
  <div class="mt-3">
    <h6 class="mb-2">{{ $t('Quantity rule') }}</h6>

    <div class="row">
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-locked-${productChoiceId}`">{{ $t('Locked') }}</label>
        <ToggleSwitch
          :inputId="`qr-locked-${productChoiceId}`"
          v-model="rule.locked"
        />
      </div>

      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-mode-${productChoiceId}`">{{ $t('Mode') }}</label>
        <Dropdown
          :inputId="`qr-mode-${productChoiceId}`"
          v-model="rule.mode"
          :options="[
            { label: $t('None'), value: 'none' },
            { label: $t('Fixed'), value: 'fixed' },
            { label: $t('Expression'), value: 'expression' },
          ]"
          optionLabel="label"
          optionValue="value"
        />
      </div>

      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-round-${productChoiceId}`">{{ $t('Rounded') }}</label>
        <Dropdown
          :inputId="`qr-round-${productChoiceId}`"
          v-model="rule.round"
          :options="[
            { label: $t('None'), value: 'none' },
            { label: $t('Floor'), value: 'floor' },
            { label: $t('Ceil'), value: 'ceil' },
            { label: $t('Round'), value: 'round' },
          ]"
          optionLabel="label"
          optionValue="value"
        />
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-offset-${productChoiceId}`">{{
            $t('Quantity or Offset')
          }}</label>
        <InputNumber
          :inputId="`qr-offset-${productChoiceId}`"
          v-model.number="rule.offset"
        />
      </div>

      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-min-${productChoiceId}`">{{ $t('Min') }}</label>
        <InputNumber
          :inputId="`qr-min-${productChoiceId}`"
          v-model.number="rule.min"
          :min="0"
        />
      </div>

      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`qr-max-${productChoiceId}`">{{ $t('Max') }}</label>
        <InputNumber
          :inputId="`qr-max-${productChoiceId}`"
          v-model.number="rule.max"
          :min="0"
        />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
