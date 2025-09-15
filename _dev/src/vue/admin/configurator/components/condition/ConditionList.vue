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

const {
  addConditionGroup,
  addCondition,
  conditionGroups,
  hasConditions,
  isVirtual,
  removeConditionGroup,
} = useConditions(props.stepId, props.productChoiceId)

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

      <div class="flex items-center gap-3 mb-2">
        <Button
          severity="info"
          text
          @click="addConditionGroup"
          class="align-bottom"
        >
          <i class="material-icons align-middle">add</i>
          {{ $t('Add a group') }} (OR)
        </Button>
      </div>

      <Divider />

      <Transition name="fade" mode="out-in">
        <Message severity="info" v-if="false === hasConditions">{{
          $t('No conditions defined. This choice will always be displayed.')
        }}</Message>

        <div v-else class="mt-3">
          <div
            v-for="(group, gIndex) in conditionGroups"
            :key="`group-${gIndex}`"
            class="mb-4 p-2 border rounded"
          >
            <div class="flex items-center justify-between mb-2">
              <strong>{{ $t('Group') }} {{ gIndex + 1 }}</strong>
              <div class="flex items-center gap-2">
                <Button severity="info" text @click="addCondition(gIndex)">
                  <i class="material-icons align-middle">add</i>
                  {{ $t('Add a condition') }} (AND)
                </Button>
                <Button
                  severity="danger"
                  text
                  @click="removeConditionGroup(gIndex)"
                >
                  <i class="material-icons align-middle">delete</i>
                  {{ $t('Remove group') }}
                </Button>
              </div>
            </div>

            <TransitionGroup name="fade" tag="div" class="conditions-list">
              <Condition
                v-for="(condition, cIndex) in group"
                :key="`${condition.step}-${condition.choice}-${gIndex}-${cIndex}`"
                :condition
                :product-choice-id="productChoiceId"
                :step-id="stepId"
                :group-index="gIndex"
                :condition-index="cIndex"
                class="mt-2"
                @on-change="handleChange"
                @on-delete="handleDelete"
              />
            </TransitionGroup>
          </div>
        </div>
      </Transition>
    </template>
  </Panel>
</template>

<style scoped lang="scss"></style>
