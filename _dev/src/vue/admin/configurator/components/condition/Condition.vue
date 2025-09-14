<script setup>
import { computed, inject, onMounted, ref, watch } from 'vue'
import { useConditions } from '@/js/admin/configurator/form/composables/useConditions'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
  condition: { type: Object, required: true },
})

const $t = inject('$t')

const emit = defineEmits(['onChange', 'onDelete'])

const {
  availableSteps,
  getAvailableChoices,
  isValidChoice,
  isValidStep,
  removeCondition,
  updateConditionChoice,
  updateConditionStep,
} = useConditions(props.stepId, props.productChoiceId)

const selectedStepId = ref(props.condition.step)
const selectedChoiceId = ref(props.condition.choice)
const availableChoices = ref([])

const isInvalidChoice = computed(() => {
  return (
    selectedChoiceId.value &&
    !isValidChoice(selectedStepId.value, selectedChoiceId.value)
  )
})

const isInvalidStep = computed(() => {
  return selectedStepId.value && !isValidStep(selectedStepId.value)
})

const hasError = computed(() => {
  return isInvalidStep.value || isInvalidChoice.value
})

const emitOnChange = () =>
  emit('onChange', {
    item: `${props.stepId}-${props.productChoiceId}`,
    choice: selectedChoiceId.value,
    step: selectedStepId.value,
    isValid: !hasError.value,
  })

const emitOnDelete = () =>
  emit('onDelete', {
    item: `${props.stepId}-${props.productChoiceId}`,
  })

const handleStepChange = (event) => {
  const newStepId = event.value

  selectedStepId.value = newStepId
  updateConditionStep(props.condition, newStepId)

  // Reset choice when step changes
  selectedChoiceId.value = null

  // Update available choices
  if (newStepId) {
    availableChoices.value = getAvailableChoices(newStepId)
  } else {
    availableChoices.value = []
  }
}

const handleChoiceChange = (event) => {
  const newChoiceId = event.value

  selectedChoiceId.value = newChoiceId
  updateConditionChoice(props.condition, newChoiceId)
}

const handleRemove = () => {
  removeCondition(selectedStepId.value, selectedChoiceId.value)

  emitOnDelete()
}

watch(hasError, emitOnChange)

onMounted(() => {
  if (!props.condition.step) {
    return
  }

  availableChoices.value = getAvailableChoices(props.condition.step)
})
</script>

<template>
  <div class="row align-items-center condition-block">
    <div class="col-5">
      <Select
        @change="handleStepChange"
        fluid
        :options="availableSteps"
        optionLabel="label"
        optionValue="id"
        :required="required"
        showClear
        v-model="selectedStepId"
        :invalid="isInvalidStep"
      />
    </div>
    <div class="col-5">
      <Select
        @change="handleChoiceChange"
        fluid
        :options="availableChoices"
        optionLabel="label"
        optionValue="id"
        :required="required"
        showClear
        v-model="selectedChoiceId"
        :invalid="isInvalidChoice"
      />
    </div>
    <div class="col-2 text-right">
      <Button
        type="button"
        severity="danger"
        @click="handleRemove"
        text
        rounded
      >
        <i class="material-icons">delete</i>
      </Button>
    </div>

    <div v-if="hasError" class="col-12">
      <Message severity="error" class="mt-2">
        {{
          $t(
            'This condition no longer points to a valid step or choice. Modify or delete it.',
          )
        }}
      </Message>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
