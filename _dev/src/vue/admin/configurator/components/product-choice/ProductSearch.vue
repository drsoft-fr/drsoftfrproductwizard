<script setup>
import { ref, watch } from 'vue'
import { useProductSearchStore } from '@/js/admin/configurator/form/stores/productSearch'

const props = defineProps({
  value: { type: [Number, String], default: null },
  placeholder: { type: String, default: 'Search for a product...' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  productChoiceId: { type: [Number, String], default: null },
})

const store = useProductSearchStore()

const searchQuery = ref('')
const searchTimeout = ref(null)

const search = async () => {
  if (searchTimeout.value) {
    clearTimeout(searchTimeout.value)
  }

  searchTimeout.value = setTimeout(async () => {
    await store.searchProducts(searchQuery.value)
  }, 300)
}

watch(searchQuery, search)
</script>

<template>
  <div class="product-search">
    <div class="input-group">
      <input
        ref="inputElement"
        type="text"
        class="form-control"
        :placeholder="placeholder"
        v-model="searchQuery"
        :disabled="disabled"
        :required="required"
        autocomplete="off"
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
  </div>
</template>

<style scoped lang="scss"></style>
