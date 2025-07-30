<script setup>
import { inject } from 'vue'

const props = defineProps({
  selections: { type: Array, required: true, default: () => [] },
})

const $t = inject('$t')

function formatPrice(price) {
  return new Intl.NumberFormat(document.documentElement.lang, {
    style: 'currency',
    currency: prestashop.currency.iso_code,
  }).format(price)
}
</script>

<template>
  <div class="cart-summary">
    <div class="card">
      <div
        class="card-header d-flex justify-content-between align-items-center"
      >
        <h3 class="mb-0">{{ $t('Your Selection') }}</h3>
        <span class="badge badge-secondary" v-if="selections.length > 0">{{
          selections.length
        }}</span>
      </div>
      <div class="card-body">
        <div v-if="selections.length === 0" class="text-center p-4">
          <div class="empty-cart">
            <i class="cart-icon">&#128722;</i>
            <h4 class="mt-3">{{ $t('No products selected yet.') }}</h4>
            <p class="text-muted">
              {{
                $t(
                  'Select options from the configurator to build your product.',
                )
              }}
            </p>
          </div>
        </div>
        <div v-else class="selected-products">
          <div
            v-for="(selection, index) in selections"
            :key="index"
            class="selected-product"
          >
            <div class="product-details">
              <div class="product-name">{{ selection.name }}</div>
              <div v-if="selection.combinationName" class="product-variant">
                <span class="badge badge-light">{{
                  selection.combinationName
                }}</span>
              </div>
              <div class="product-quantity">
                <span class="quantity-label">{{ $t('Quantity') }}:</span>
                <span class="quantity-value">{{ selection.quantity }}</span>
              </div>
            </div>
            <div class="product-price">
              {{ formatPrice(selection.price * selection.quantity) }}
            </div>
          </div>
          <div class="cart-total">
            <div class="total-label">{{ $t('Total') }}</div>
            <div class="total-price">{{ formatPrice(totalPrice) }}</div>
          </div>
        </div>
        <div class="cart-actions mt-4">
          <button
            type="button"
            class="btn btn-primary btn-lg btn-block"
            :disabled="!canAddToCart || selections.length === 0"
            @click="emitAddToCart"
          >
            <i class="cart-btn-icon">&#128722;</i> {{ $t('Add to Cart') }}
          </button>
          <div
            v-if="!canAddToCart && selections.length > 0"
            class="alert alert-info mt-3 text-center"
          >
            <small>{{
              $t('Please complete all steps before adding to cart.')
            }}</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped></style>
