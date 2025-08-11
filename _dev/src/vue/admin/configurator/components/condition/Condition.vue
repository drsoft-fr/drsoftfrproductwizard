<script setup>
import { ref } from 'vue'
import { useConditions } from '@/js/admin/configurator/form/composables/useConditions'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
  condition: { type: Object, required: true },
  index: { type: Number, required: true },
})

const { removeCondition } = useConditions(props.stepId, props.productChoiceId)

const selectedStepId = ref(props.condition.step)
const selectedChoiceId = ref(props.condition.choice)

const handleRemove = () => {
  removeCondition(selectedStepId.value, selectedChoiceId.value)
}
</script>

<template>
  <div class="row align-items-center condition-block">
    <div class="col-md-5">Step placeholder</div>
    <div class="col-md-5">Step placeholder</div>
    <div class="col-md-2">
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
  </div>
</template>

<style scoped lang="scss"></style>
