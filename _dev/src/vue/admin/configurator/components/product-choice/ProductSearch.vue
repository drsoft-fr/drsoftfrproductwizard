<script setup>
const props = defineProps({
  value: { type: [Number, String], default: null },
  placeholder: { type: String, default: 'Search for a product...' },
  required: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
  productChoiceId: { type: [Number, String], default: null },
})
</script>

<template>
  <div class="product-search">
    <div class="input-group">
      <input
        ref="inputElement"
        type="text"
        class="form-control"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        autocomplete="off"
      />
      <button
        class="btn btn-outline-secondary"
        type="button"
        :disabled="disabled"
      >
        <i class="material-icons">search</i>
      </button>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
