<script setup>
import { computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ProductChoiceList from '@/vue/admin/configurator/components/product-choice/ProductChoiceList.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const emit = defineEmits(['remove'])

const store = useConfiguratorStore()

const step = computed(() => store.getStep(props.stepId))
const stepUIState = computed(() => store.getStepUIState(props.stepId))
const isCollapsed = computed(() => stepUIState.value.isCollapsed)
const isVirtual = computed(() => step.value && step.value.is_virtual === true)
const stepIndex = computed(() => {
  if (!store.steps || !step.value) {
    return -1
  }

  return store.steps.findIndex((s) => s.id === props.stepId)
})

const updateLabel = (event) => {
  if (step.value) {
    step.value.label = event.target.value
  }
}

const updatePosition = (event) => {
  if (step.value) {
    const position = parseInt(event.target.value, 10)
    step.value.position = isNaN(position) ? 0 : position
  }
}

const updateActive = (event) => {
  if (step.value) {
    step.value.active = event.target.checked
  }
}

const handleRemove = () => {
  store.removeStep(props.stepId)
  emit('remove', props.stepId)
}

const toggleCollapse = () => {
  store.toggleStepCollapse(props.stepId)
}
</script>

<template>
  <div
    class="step-item card mb-3 shadow-sm position-relative"
    :data-step-id="stepId"
    :data-position="step ? step.position : 0"
  >
    <div class="card-header d-flex justify-content-between">
      <div>
        <span class="step-drag-handle align-bottom" style="cursor: grab">
          <i class="material-icons">drag_indicator</i>
        </span>
        <strong>
          {{ $t('Step') }} #{{ stepId }}
          <span v-if="step">{{ step.label }}</span>
        </strong>
        <span v-if="isVirtual" class="badge bg-info ml-2">{{ $t('New') }}</span>
      </div>

      <div class="d-flex align-items-center">
        <span class="badge bg-primary js-badge-position">{{ stepIndex }}</span>
        <button
          class="btn btn-link"
          type="button"
          @click="toggleCollapse"
          :disabled="store.isDragging"
        >
          <i class="material-icons">{{
            isCollapsed ? 'expand_more' : 'expand_less'
          }}</i>
        </button>
      </div>
    </div>

    <div class="card-body" v-if="!isCollapsed">
      <!-- Label -->
      <div class="form-group mb-3">
        <label class="form-label">{{ $t('Wording') }}</label>
        <input
          type="text"
          class="form-control"
          :value="step ? step.label : ''"
          @input="updateLabel"
          :placeholder="$t('Step description')"
        />
      </div>

      <!-- Position -->
      <div class="form-group mb-3">
        <label class="form-label">{{ $t('Position') }}</label>
        <input
          type="number"
          class="form-control"
          :value="step ? step.position : 0"
          @input="updatePosition"
          min="0"
        />
        <small class="form-text text-muted">
          {{
            $t(
              'The position is automatically updated when dragging and dropping.',
            )
          }}
        </small>
      </div>

      <!-- Active -->
      <div class="form-check mb-3">
        <input
          type="checkbox"
          class="form-check-input"
          id="active-{{ stepId }}"
          :checked="step ? step.active : true"
          @change="updateActive"
        />
        <label class="form-check-label" for="active-{{ stepId }}">
          {{ $t('Active') }}
        </label>
      </div>

      <!-- Remove Button -->
      <div class="text-end mb-3">
        <button type="button" class="btn btn-danger" @click="handleRemove">
          <i class="material-icons align-middle me-1">delete</i>
          {{ $t('Delete') }}
        </button>
      </div>

      <hr />

      <!-- Product Choices -->
      <ProductChoiceList :step-id="stepId" />
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
