<script setup>
import { computed, inject, ref } from 'vue'
import { useConditions } from '@/js/admin/configurator/form/composables/useConditions'
import Condition from '@/vue/admin/configurator/components/condition/Condition.vue'

const props = defineProps({
  productChoiceId: { type: [String, Number], required: true },
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const emit = defineEmits(['onChange'])

const { addCondition, conditions, hasConditions, isVirtual } = useConditions(
  props.stepId,
  props.productChoiceId,
)

const isValid = ref(true)
const validity = computed((conditions) => {
  const validity = {}

  if (!conditions) {
    return validity
  }

  for (const condition of conditions) {
    validity[`${condition.step}-${condition.choice}`] = true
  }

  return validity
})

const checkValidity = () =>
  (isValid.value = !(
    true === Object.values(validity.value).some((value) => value === false)
  ))

const emitOnChange = () => {
  checkValidity()
  emit('onChange', {
    isValid: isValid.value,
  })
}

const handleChange = (event) => {
  validity.value[event.item] = event.isValid

  emitOnChange()
}

const handleDelete = (event) => {
  if (false === validity.value.hasOwnProperty(event.item)) {
    return
  }

  delete validity.value[event.item]

  emitOnChange()
}
</script>

<template>
  <Panel
    class="conditions-container"
    toggleable
    :collapsed="false === hasConditions"
    :data-product-choice-id="productChoiceId"
    :data-step-id="stepId"
  >
    <template #header>
      <h6 class="my-0">{{ $t('Display conditions') }}</h6>
    </template>

    <Message severity="info" v-if="isVirtual">{{
      $t(
        'This product selection is new, so you cannot set conditions yet. You must register before you can configure the conditions.',
      )
    }}</Message>

    <template v-else>
      <Message severity="info" class="my-2">
        {{
          $t('Remember to save so that you can select the newly added items.')
        }}
      </Message>

      <Button severity="info" text @click="addCondition" class="align-bottom">
        <i class="material-icons align-middle">add</i>
        {{ $t('Add a condition') }}
      </Button>

      <Divider />

      <Transition name="fade" mode="out-in">
        <Message severity="info" v-if="false === hasConditions">{{
          $t('No conditions defined. This choice will always be displayed.')
        }}</Message>

        <TransitionGroup
          name="fade"
          tag="div"
          v-else
          class="conditions-list mt-3"
        >
          <Condition
            v-for="(condition, index) in conditions"
            :key="`${condition.step}-${condition.choice}`"
            :condition
            :product-choice-id="productChoiceId"
            :step-id="stepId"
            :class="0 < index ? 'mt-3' : ''"
            @on-change="handleChange"
            @on-delete="handleDelete"
          />
        </TransitionGroup>
      </Transition>
    </template>
  </Panel>
</template>

<style scoped lang="scss"></style>
