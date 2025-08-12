<script setup>
import { computed, inject, onMounted, ref } from 'vue'
import { useProductSearchStore } from '@/js/admin/configurator/form/stores/productSearch'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
  productId: { type: Number, default: null },
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

const toast = useToast()

const searchResults = computed(() => {
  if (null === selectedProduct.value) {
    return store.searchResults
  }

  return [...new Set([...[selectedProduct.value], ...store.searchResults])]
})

const search = async (event) => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    await store.searchProducts(event.value.trim())
  }, 300)
}

const findProductById = (productId) => {
  return null === productId
    ? null
    : searchResults.value.find((product) => product.id === productId)
}

const selectProduct = (event) => {
  selectedProduct.value = findProductById(event.value)
  props.productId = event.value

  emit('update:value', event.value)
}

onMounted(async () => {
  if (!props.productId) {
    return
  }

  const product = await store.getProduct(props.productId)

  if (product) {
    selectedProduct.value = product
  } else {
    selectedProduct.value = null
  }

  if (store.error) {
    toast.add({ severity: 'error', detail: store.error, summary: $t('Error') })
  }
})
</script>

<template>
  <div class="product-search d-flex flex-column gap-2">
    <label :for="`pc-product-${productChoiceId}`">{{ $t('Product') }}</label>
    <Select
      :autoFilterFocus="true"
      @change="selectProduct"
      :disabled="disabled"
      :emptyFilterMessage="$t('No product found with this name.')"
      :emptyMessage="$t('Search for a product to pair with this selection.')"
      filter
      @filter="search"
      fluid
      :id="`pc-product-${productChoiceId}`"
      :loading="store.loading"
      :options="searchResults"
      optionLabel="name"
      optionValue="id"
      :required="required"
      showClear
      v-model="props.productId"
    />
  </div>
</template>

<style scoped lang="scss"></style>
