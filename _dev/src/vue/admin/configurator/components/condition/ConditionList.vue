<script setup>
import { inject } from 'vue'
import { useConditions } from '@/js/admin/configurator/form/composables/useConditions'
import Condition from '@/vue/admin/configurator/components/condition/Condition.vue'

const props = defineProps({
  productChoiceId: { type: [String, Number], required: true },
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const { addCondition, conditions, hasConditions, isVirtual } = useConditions(
  props.stepId,
  props.productChoiceId,
)
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
            :key="index"
            :index
            :condition
            :product-choice-id="productChoiceId"
            :step-id="stepId"
            :class="0 < index ? 'mt-3' : ''"
          />
        </TransitionGroup>
      </Transition>
    </template>
  </Panel>
</template>

<style scoped lang="scss"></style>
