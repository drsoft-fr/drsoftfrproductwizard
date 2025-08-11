<script setup>
import { computed, inject, ref, watch } from 'vue'
import { useProductSearchStore } from '@/js/admin/configurator/form/stores/productSearch'

const props = defineProps({
  value: { type: Number, default: null },
  placeholder: { type: String, default: 'Search for a product...' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  productChoiceId: { type: [Number, String], default: null },
})

const emit = defineEmits(['update:value'])

const $t = inject('$t')

const store = useProductSearchStore()

const searchTimeout = ref(null)
const selectedProduct = ref(null)

const search = async (event) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    await store.searchProducts(event.value.trim())
  }, 300)
}

const selectProduct = (event) => {
  selectedProduct.value = event.value
  emit('update:value', event.value.id)
}
</script>

<template>
  <div class="product-search d-flex flex-column gap-2">
    <label :for="`pc-product-${productChoiceId}`">{{ $t('Product') }}</label>
    <Select
      showClear
      filter
      v-model="selectedProduct"
      optionLabel="text"
      :options="store.searchResults"
      @filter="search"
      :disabled="disabled"
      :required="required"
      :id="`pc-product-${productChoiceId}`"
      @change="selectProduct"
    />
  </div>
</template>

<style scoped lang="scss"></style>
