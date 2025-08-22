<script setup>
import { ref, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import ConditionList from '@/vue/admin/configurator/components/condition/ConditionList.vue'
import ProductSearch from '@/vue/admin/configurator/components/product-choice/ProductSearch.vue'
import QuantityRuleField from '@/vue/admin/configurator/components/product-choice/QuantityRuleField.vue'
import CoreEditor from '@/vue/admin/configurator/components/core/CoreEditor.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
})

const $t = inject('$t')
const { lifetime } = inject('toast')

const emit = defineEmits(['remove'])

const store = useConfiguratorStore()

const isValid = ref(true)
const menu = ref(null)

const confirm = useConfirm()
const toast = useToast()

const menuItems = [
  {
    label: $t('Delete'),
    command: () => {
      confirm.require({
        message: $t('Do you want to delete this product choice?'),
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
          store.removeProductChoice(props.stepId, props.productChoiceId)
          emit('remove', props.productChoiceId)
          toast.add({
            severity: 'success',
            summary: 'Confirmed',
            detail: $t('Product choice deleted successfully'),
            life: lifetime.value,
          })
        },
      })
    },
  },
]

const productChoice = computed(() => {
  return store.getProductChoice(props.stepId, props.productChoiceId)
})

const isVirtual = computed(() => {
  return productChoice.value && productChoice.value.is_virtual === true
})

const updateIsDefault = (event) => {
  if (productChoice.value) {
    const isDefault = event.target.checked

    if (isDefault) {
      // Uncheck other product choices in the same step
      const step = store.getStep(props.stepId)
      if (step && step.product_choices) {
        step.product_choices.forEach((choice) => {
          if (choice.id !== props.productChoiceId) {
            choice.is_default = false
          }
        })
      }
    }

    productChoice.value.is_default = isDefault
  }
}

const updateProductId = (productId) => {
  if (productChoice.value) {
    productChoice.value.product_id = productId
  }
}

const toggleMenu = (event) => {
  menu.value.toggle(event)
}

const handleConditionChange = (event) => {
  isValid.value = event.isValid
}
</script>

<template>
  <Panel
    toggleable
    class="product-choice-item"
    :data-product-choice-id="productChoiceId"
    :data-step-id="stepId"
  >
    <template #header>
      <div class="d-flex align-items-center">
        <i v-if="false === isValid" class="material-icons text-danger mr-3">
          error
        </i>
        <h5 class="my-0">
          {{ $t('Product choice') }} #{{ productChoiceId }}
          <span v-if="productChoice">{{ productChoice.label }}</span>
        </h5>
        <Tag
          v-if="isVirtual"
          severity="info"
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
      <label :for="`pc-label-${productChoiceId}`">{{ $t('Wording') }}</label>
      <InputText
        v-model="productChoice.label"
        required
        :id="`pc-label-${productChoiceId}`"
      />
    </div>

    <!-- Description -->
    <CoreEditor
      :id="`pc-description-${productChoiceId}`"
      v-model="productChoice.description"
    />

    <!-- Product Search -->
    <div class="mt-3">
      <ProductSearch
        :product-id="productChoice ? productChoice.product_id : null"
        :product-choice-id="productChoiceId"
        @update:value="updateProductId"
        :placeholder="$t('Search for a product...')"
        :required="false"
        :disabled="false"
      />
    </div>

    <!-- Conditions -->
    <ConditionList
      :step-id="stepId"
      :product-choice-id="productChoiceId"
      class="mt-3"
      @on-change="handleConditionChange"
    />

    <!-- Options -->
    <div class="row mt-3">
      <div class="col-md-6 mb-3 mb-md-0 d-flex align-items-center">
        <ToggleSwitch
          :inputId="`pc-is-default-${productChoiceId}`"
          v-model="productChoice.is_default"
          class="mr-3"
          @change="updateIsDefault"
        />
        <label :for="`pc-is-default-${productChoiceId}`" class="m-0">{{
          $t('Default choice')
        }}</label>
      </div>

      <div class="col-md-6 d-flex align-items-center">
        <ToggleSwitch
          :inputId="`pc-active-${productChoiceId}`"
          v-model="productChoice.active"
          class="mr-3"
        />
        <label :for="`pc-active-${productChoiceId}`" class="m-0">{{
          $t('Active')
        }}</label>
      </div>
    </div>

    <Divider />

    <QuantityRuleField :step-id="stepId" :product-choice-id="productChoiceId" />

    <Divider />

    <div class="row mt-3">
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`pc-reduction-${productChoiceId}`">{{
          $t('Reduction')
        }}</label>
        <InputNumber
          :inputId="`pc-reduction-${productChoiceId}`"
          v-model.number="productChoice.reduction"
          min="0"
          :max="productChoice.reduction_type === 'percentage' ? 100 : null"
        />
      </div>
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`pc-reduction-type-${productChoiceId}`">{{
          $t('Reduction type')
        }}</label>
        <Dropdown
          :inputId="`pc-reduction-type-${productChoiceId}`"
          :options="[
            { label: '%', value: 'percentage' },
            { label: '€', value: 'amount' },
          ]"
          optionLabel="label"
          optionValue="value"
          v-model="productChoice.reduction_type"
        />
      </div>
      <div class="col-md-4 d-flex flex-column gap-2">
        <label :for="`pc-reduction-tax-${productChoiceId}`">{{
          $t('Tax included?')
        }}</label>
        <ToggleSwitch
          :inputId="`pc-reduction-tax-${productChoiceId}`"
          v-model="productChoice.reduction_tax"
        />
      </div>
    </div>
  </Panel>
</template>

<style scoped lang="scss"></style>
