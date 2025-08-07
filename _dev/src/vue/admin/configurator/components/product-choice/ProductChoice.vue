<script setup>
import { ref, computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import ProductSearch from '@/vue/admin/configurator/components/product-choice/ProductSearch.vue'

const props = defineProps({
  stepId: { type: [String, Number], required: true },
  productChoiceId: { type: [String, Number], required: true },
})

const $t = inject('$t')

const emit = defineEmits(['remove'])

const store = useConfiguratorStore()

const isCollapsed = ref(false)

const productChoice = computed(() => {
  return store.getProductChoice(props.stepId, props.productChoiceId)
})

const isVirtual = computed(() => {
  return productChoice.value && productChoice.value.is_virtual === true
})

const productChoiceIndex = computed(() => {
  const step = store.getStep(props.stepId)

  if (!step || !step.product_choices) {
    return -1
  }

  return step.product_choices.findIndex(
    (choice) => choice.id === props.productChoiceId,
  )
})

const updateLabel = (event) => {
  if (productChoice.value) {
    productChoice.value.label = event.target.value
  }
}

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

const updateAllowQuantity = (event) => {
  if (productChoice.value) {
    productChoice.value.allow_quantity = event.target.checked

    // Reset forced quantity if allow quantity is disabled
    if (!event.target.checked) {
      productChoice.value.forced_quantity = null
    }
  }
}

const updateForcedQuantity = (event) => {
  if (productChoice.value) {
    const value = event.target.value ? parseInt(event.target.value, 10) : null
    productChoice.value.forced_quantity = value
  }
}

const updateActive = (event) => {
  if (productChoice.value) {
    productChoice.value.active = event.target.checked
  }
}

const updateProductId = (productId) => {
  if (productChoice.value) {
    productChoice.value.product_id = productId
  }
}

const handleRemove = () => {
  store.removeProductChoice(props.stepId, props.productChoiceId)
  emit('remove', props.productChoiceId)
}

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}
</script>

<template>
  <div
    class="product-choice-item card mb-3"
    :class="{ 'border-primary': !isCollapsed }"
  >
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <strong>
          {{ $t('Product choice') }} #{{ productChoiceId }}
          <span v-if="productChoice">{{ productChoice.label }}</span>
        </strong>
        <span v-if="isVirtual" class="badge bg-info ml-2">{{ $t('New') }}</span>
      </div>

      <div class="d-flex align-items-center">
        <span class="badge bg-secondary me-2">{{ productChoiceIndex }}</span>
        <button class="btn btn-link" type="button" @click="toggleCollapse">
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
          :value="productChoice ? productChoice.label : ''"
          @input="updateLabel"
          :placeholder="$t('Choice wording')"
        />
      </div>

      <!-- Product Search -->
      <div class="form-group mb-3">
        <label class="form-label">{{ $t('Product') }}</label>
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
        <div class="col-md-6">
          <div class="form-check mb-3">
            <input
              type="checkbox"
              class="form-check-input"
              :id="`is-default-${productChoiceId}`"
              :checked="productChoice ? productChoice.is_default : false"
              @change="updateIsDefault"
            />
            <label
              class="form-check-label"
              :for="`is-default-${productChoiceId}`"
            >
              {{ $t('Default choice') }}
            </label>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-check mb-3">
            <input
              type="checkbox"
              class="form-check-input"
              :id="`active-${productChoiceId}`"
              :checked="productChoice ? productChoice.active : true"
              @change="updateActive"
            />
            <label class="form-check-label" :for="`active-${productChoiceId}`">
              {{ $t('Active') }}
            </label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-check mb-3">
            <input
              type="checkbox"
              class="form-check-input"
              :id="`allow-quantity-${productChoiceId}`"
              :checked="productChoice ? productChoice.allow_quantity : true"
              @change="updateAllowQuantity"
            />
            <label
              class="form-check-label"
              :for="`allow-quantity-${productChoiceId}`"
            >
              {{ $t('Allow selection of quantity') }}
            </label>
          </div>
        </div>

        <div class="col-md-6">
          <div class="form-group mb-3">
            <label class="form-label">{{ $t('Forced quantity') }}</label>
            <input
              type="number"
              class="form-control"
              :value="productChoice ? productChoice.forced_quantity : null"
              @input="updateForcedQuantity"
              :disabled="productChoice ? !productChoice.allow_quantity : false"
              min="1"
              :placeholder="$t('Quantity')"
            />
          </div>
        </div>
      </div>

      <!-- Remove Button -->
      <div class="text-end mt-3">
        <button type="button" class="btn btn-danger" @click="handleRemove">
          <i class="material-icons">delete</i>
          {{ $t('Delete') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
