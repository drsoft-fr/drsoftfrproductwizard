<script setup>
import { inject, provide } from 'vue'
import Action from '@/vue/front/configurator/components/cart/Action.vue'
import Summary from '@/vue/front/configurator/components/cart/Summary.vue'

const configurator = inject('configurator')
const loading = inject('loading')
const $r = inject('$r')
const selections = inject('selections')
const showAlert = inject('showAlert')
const $t = inject('$t')

async function addToCart() {
  try {
    loading.value = true

    // Build a clean payload: only selected products with a valid quantity
    const selectedItems = (selections.value || [])
      .filter(
        (s) => s && null !== s.productId && typeof s.productId !== 'undefined',
      )
      .filter((s) => (s.quantity || 0) > 0)
      .map((s) => ({
        productId: s.productId,
        combinationId: s.combinationId || 0,
        quantity: s.quantity || 1,
        productChoiceId: s.id,
        stepId: s.stepId,
        productName: s.product.name || '',
      }))

    if (selectedItems.length === 0) {
      showAlert('warning', $t('No products selected yet.'))

      return
    }

    const response = await fetch($r('addToCart'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'add-to-cart',
        data: JSON.stringify({
          items: selectedItems,
          configuratorId: String(configurator?.value?.id || ''),
        }),
      }),
    })

    const data = await response.json()

    if (false === data.success) {
      const errorMessage = $t('Add to cart failed', {}, 'Error')

      showAlert('danger', data.message || errorMessage)

      return
    }

    if (data.cartUrl) {
      window.location.href = data.cartUrl
    } else {
      showAlert('success', $t('Products added to cart successfully'))
    }
  } catch (error) {
    showAlert(
      'danger',
      $t('An error occurred while loading the configurator', {}, 'Error'),
    )
  } finally {
    loading.value = false
  }
}

provide('addToCart', addToCart)
</script>

<template>
  <div>
    <h3 class="drpw:text-right">{{ $t('Your Selection') }}</h3>

    <div class="drpw:card drpw:bg-base-100 drpw:shadow-sm">
      <div class="drpw:card-body">
        <Summary />

        <Action class="drpw:mt-3" />
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
