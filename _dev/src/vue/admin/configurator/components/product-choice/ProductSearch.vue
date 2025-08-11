<script setup>
import { computed, inject, ref, watch } from 'vue'
import { useProductSearchStore } from '@/js/admin/configurator/form/stores/productSearch'
import Loader from '@/vue/admin/configurator/components/core/Loader.vue'

const props = defineProps({
  value: { type: [Number, String], default: null },
  placeholder: { type: String, default: 'Search for a product...' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  productChoiceId: { type: [Number, String], default: null },
})

const emit = defineEmits(['update:value'])

const $t = inject('$t')

const store = useProductSearchStore()

const searchQuery = ref('')
const searchTimeout = ref(null)
const selectedProduct = ref(null)
const showDropdown = ref(false)

const searchResults = computed(() => store.searchResults)
const hasResults = computed(() => searchResults.value.length > 0)

const search = async () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    await store.searchProducts(searchQuery.value)
    showDropdown.value = true
  }, 300)
}

const selectProduct = (product) => {
  selectedProduct.value = product
  searchQuery.value = product.text
  showDropdown.value = false
  emit('update:value', product.id)
}

watch(searchQuery, search)
</script>

<template>
  <div class="product-search">
    <div class="input-group">
      <label :for="`pc-product-${productChoiceId}`" class="form-label">{{
        $t('Product')
      }}</label>
      <input
        ref="inputElement"
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="searchQuery"
        :disabled="disabled"
        :required="required"
        autocomplete="off"
        :id="`pc-product-${productChoiceId}`"
      />
      <button
        class="btn btn-outline-secondary"
        type="button"
        :disabled="disabled || store.loading"
      >
        <i class="material-icons" v-if="store.loading">refresh</i>
        <i class="material-icons" v-else>search</i>
      </button>
    </div>
    <div v-if="showDropdown" class="dropdown">
      <TransitionGroup name="fade" tag="ul" class="dropdown-menu show">
        <li v-if="store.loading" class="dropdown-item">
          <Loader :fullscreen="true" />
        </li>
        <li v-else-if="!hasResults" class="dropdown-item">
          {{ $t('No results found') }}
        </li>
        <template v-else>
          <li
            v-for="product in searchResults"
            :key="product.id"
            class="dropdown-item"
          >
            <a
              href="#"
              @click.prevent="selectProduct(product)"
              class="dropdown-item"
            >
              #{{ product.id }} - {{ product.text }}
            </a>
          </li>
        </template>
      </TransitionGroup>
    </div>
  </div>
</template>

<style scoped lang="scss">
.dropdown-menu {
  width: 100%;
  max-height: 300px;
  overflow-y: auto;
}
</style>
