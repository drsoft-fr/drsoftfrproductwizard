<script setup>
import { computed, inject } from 'vue'
import { useConditions } from '@/js/admin/configurator/form/composables/useConditions'
import Condition from '@/vue/admin/configurator/components/condition/Condition.vue'

const props = defineProps({
  productChoiceId: { type: [String, Number], required: true },
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const { addCondition, currentProductChoice, conditions } = useConditions(
  props.stepId,
  props.productChoiceId,
)

const hasConditions = computed(() => conditions.value.length > 0)
const isVirtual = computed(() => {
  return (
    currentProductChoice.value && currentProductChoice.value.is_virtual === true
  )
})
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

    <template #icons>
      <Button severity="info" text @click="addCondition" class="align-bottom">
        <i class="material-icons align-middle">add</i>
        {{ $t('Add a condition') }}
      </Button>
    </template>

    <Message severity="info" v-if="isVirtual">{{
      $t(
        'This product selection is new, so you cannot set conditions yet. You must register before you can configure the conditions.',
      )
    }}</Message>

    <template v-else>
      <Transition name="fade" mode="out-in">
        <Message severity="info" v-if="false === hasConditions">{{
          $t('No conditions defined. This choice will always be displayed.')
        }}</Message>

        <div v-else class="conditions-list mt-3">
          <Condition
            v-for="(condition, index) in conditions"
            :key="index"
            :index
            :condition
            :product-choice-id="productChoiceId"
            :step-id="stepId"
          />
        </div>
      </Transition>
    </template>
  </Panel>
</template>

<style scoped lang="scss"></style>
