<script setup>
import { ref, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import ProductSearch from '@/vue/admin/configurator/components/product-choice/ProductSearch.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
})

const $t = inject('$t')
const { lifetime } = inject('toast')

const emit = defineEmits(['remove'])

const store = useConfiguratorStore()

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

    <!-- Product Search -->
    <div class="mt-3">
      <ProductSearch
        :value="productChoice ? productChoice.product_id : null"
        :product-choice-id="productChoiceId"
        @update:value="updateProductId"
        :placeholder="$t('Search for a product...')"
        :required="false"
        :disabled="false"
      />
    </div>

    <!-- Conditions -->
      <!--      TODO Condition -->

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

    <div class="row mt-3">
      <div class="col-md-6 mb-3 mb-md-0 d-flex align-items-center">
        <ToggleSwitch
          :inputId="`pc-allow-quantity-${productChoiceId}`"
          v-model="productChoice.allow_quantity"
          class="mr-3"
        />
        <label :for="`pc-allow-quantity-${productChoiceId}`" class="m-0">{{
          $t('Allow selection of quantity')
        }}</label>
      </div>

      <div class="col-md-6 d-flex flex-column gap-2">
        <label :for="`pc-label-${productChoiceId}`">{{
          $t('Forced quantity')
        }}</label>
        <InputNumber
          v-model.number="productChoice.forced_quantity"
          :min="1"
          required
          fluid
          :inputId="`pc-label-${productChoiceId}`"
        />
      </div>
    </div>
  </Panel>
</template>

<style scoped lang="scss"></style>
