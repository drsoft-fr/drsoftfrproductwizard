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

const previousSteps = computed(() => {
  const currentStep = store.getStep(props.stepId)
  const pos = Number(currentStep?.position ?? 0)

  // Use existing getter to filter previous steps if available; fallback manual filter
  return Array.isArray(store.steps)
    ? store.steps.filter((s) => Number(s.position) < pos)
    : []
})

const stepOptions = computed(() =>
  previousSteps.value.map((s) => ({
    label: `${s.label} (#${s.id})`,
    value: s.id,
  })),
)

const getChoicesForStep = (stepId) => {
  const step = store.steps.find((s) => String(s.id) === String(stepId))
  const choices = Array.isArray(step?.product_choices)
    ? step.product_choices
    : []

  return choices
    .filter((c) => c.active)
    .map((c) => ({ label: `${c.label} (#${c.id})`, value: c.id }))
}

const addSource = () => {
  const r = { ...rule.value }

  r.sources = r.sources ? [...r.sources] : []

  r.sources.push({ step: null, choice: null, coeff: 1 })

  rule.value = r
}

const removeSource = (idx) => {
  const r = { ...rule.value }

  r.sources = (r.sources || []).filter((_, i) => i !== idx)
  rule.value = r
}
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

    <div v-if="rule.mode === 'expression'" class="mt-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">{{ $t('Sources') }}</h6>
        <Button severity="info" text @click="addSource" class="align-bottom">
          <i class="material-icons align-middle">add</i>
          {{ $t('Add a source') }}
        </Button>
      </div>

      <div v-if="!rule.sources || rule.sources.length === 0" class="text-muted">
        {{ $t('No specific source.') }}
      </div>

      <div
        v-for="(src, idx) in rule.sources"
        :key="idx"
        class="row g-2 align-items-end mb-2"
      >
        <div class="col-md-4 d-flex flex-column gap-2">
          <label :for="`qr-src-step-${productChoiceId}-${idx}`">{{
            $t('Step')
          }}</label>
          <Dropdown
            :inputId="`qr-src-step-${productChoiceId}-${idx}`"
            v-model="src.step"
            :options="stepOptions"
            optionLabel="label"
            optionValue="value"
          />
        </div>
        <div class="col-md-4 d-flex flex-column gap-2">
          <label :for="`qr-src-choice-${productChoiceId}-${idx}`">{{
            $t('Choice')
          }}</label>
          <Dropdown
            :inputId="`qr-src-choice-${productChoiceId}-${idx}`"
            v-model="src.choice"
            :options="getChoicesForStep(src.step)"
            optionLabel="label"
            optionValue="value"
          />
        </div>
        <div class="col-md-3 d-flex flex-column gap-2">
          <label :for="`qr-src-coeff-${productChoiceId}-${idx}`">{{
            $t('Coeff.')
          }}</label>
          <InputNumber
            :inputId="`qr-src-coeff-${productChoiceId}-${idx}`"
            v-model.number="src.coeff"
            :min="-999999"
          />
        </div>

        <div class="col-md-1 d-flex align-items-center">
          <Button
            type="button"
            severity="danger"
            @click="removeSource(idx)"
            text
            rounded
          >
            <i class="material-icons">delete</i>
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
