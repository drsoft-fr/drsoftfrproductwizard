<script setup>
import { computed, inject, ref } from 'vue'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ProductChoiceList from '@/vue/admin/configurator/components/product-choice/ProductChoiceList.vue'
import CoreEditor from '@/vue/admin/configurator/components/core/CoreEditor.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
})

const $t = inject('$t')
const { lifetime } = inject('toast')

const emit = defineEmits(['remove'])

const store = useConfiguratorStore()

const menu = ref(null)

const confirm = useConfirm()
const toast = useToast()

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

const menuItems = [
  {
    label: $t('Delete'),
    command: () => {
      confirm.require({
        message: $t('Do you want to delete this step?'),
        header: 'Danger Zone',
        rejectLabel: 'Cancel',
        rejectProps: {
          label: 'Cancel',
          severity: 'secondary',
          outlined: true,
        },
        acceptProps: {
          label: 'Delete',
          severity: 'danger',
        },
        accept() {
          store.removeStep(props.stepId)
          emit('remove', props.stepId)
          toast.add({
            severity: 'success',
            summary: 'Confirmed',
            detail: $t('Step deleted successfully'),
            life: lifetime.value,
          })
        },
      })
    },
  },
]

const toggleCollapse = () => {
  store.toggleStepCollapse(props.stepId)
}

const toggleMenu = (event) => {
  menu.value.toggle(event)
}
</script>

<template>
  <Panel
    :collapsed="isCollapsed"
    :toggleable="!store.isDragging"
    @toggle="toggleCollapse"
    class="step-item"
    :data-step-id="stepId"
    :data-position="step ? step.position : 0"
  >
    <template #header>
      <div class="d-flex align-items-center">
        <Tag
          severity="info"
          class="js-badge-position mr-3"
          :value="stepIndex"
        ></Tag>
        <span class="step-drag-handle align-bottom mx-3" style="cursor: grab">
          <i class="material-icons">drag_indicator</i>
        </span>
        <h3 class="mx-3 my-0">
          {{ $t('Step') }} #{{ stepId }}
          <span v-if="step">{{ step.label }}</span>
        </h3>
        <Tag
          severity="info"
          v-if="isVirtual"
          class="ml-3"
          :value="$t('New')"
        ></Tag>
      </div>
    </template>

    <template #icons>
      <Button
        severity="secondary"
        rounded
        text
        @click="toggleMenu"
        class="align-text-bottom p-0"
      >
        <i class="material-icons">settings</i>
      </Button>
      <Menu ref="menu" :model="menuItems" popup />
    </template>

    <!-- Label -->
    <div class="mt-3 d-flex flex-column gap-2">
      <label :for="`label-${stepId}`">{{ $t('Wording') }}</label>
      <InputText v-model="step.label" required :id="`label-${stepId}`" />
    </div>

    <!-- Description -->
    <CoreEditor :id="`description-${stepId}`" v-model="step.description" />

    <!-- Position -->
    <div class="mt-3 d-flex flex-column gap-2">
      <label :for="`position-${stepId}`">{{ $t('Position') }}</label>
      <InputNumber
        v-model.number="step.position"
        :min="0"
        required
        fluid
        :inputId="`position-${stepId}`"
      />
      <Message size="small" severity="secondary" variant="simple">{{
        $t('The position is automatically updated when dragging and dropping.')
      }}</Message>
    </div>

    <!-- Active -->
    <div class="d-flex align-items-center mt-3">
      <ToggleSwitch
        :inputId="`active-${stepId}`"
        v-model="step.active"
        class="mr-3"
      />
      <label :for="`active-${stepId}`" class="m-0">{{ $t('Active') }}</label>
    </div>

    <Divider />

    <!-- Reduction settings -->
    <div class="row mt-3">
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`step-reduction-${stepId}`">{{ $t('Reduction') }}</label>
        <InputNumber
          :inputId="`step-reduction-${stepId}`"
          v-model.number="step.reduction"
          min="0"
          :max="step.reduction_type === 'percentage' ? 100 : null"
        />
      </div>
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`step-reduction-type-${stepId}`">{{
          $t('Reduction type')
        }}</label>
        <Dropdown
          :inputId="`step-reduction-type-${stepId}`"
          :options="[
            { label: '%', value: 'percentage' },
            { label: '€', value: 'amount' },
          ]"
          optionLabel="label"
          optionValue="value"
          v-model="step.reduction_type"
        />
      </div>
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`step-reduction-tax-${stepId}`">{{
          $t('Tax included?')
        }}</label>
        <ToggleSwitch
          :inputId="`step-reduction-tax-${stepId}`"
          v-model="step.reduction_tax"
        />
      </div>
    </div>

    <Divider />

    <!-- Product Choices -->
    <ProductChoiceList :step-id="stepId" />
  </Panel>
</template>

<style scoped lang="scss"></style>
