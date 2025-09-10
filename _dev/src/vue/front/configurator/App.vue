<script setup>
import { inject, onMounted, provide, reactive, ref, watch } from 'vue'
import Alert from '@/vue/front/configurator/components/core/Alert.vue'
import Configurator from '@/vue/front/configurator/components/configurator/Configurator.vue'
import Loader from '@/vue/front/configurator/components/core/Loader.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const $r = inject('$r')
const $t = inject('$t')

const activeStepIndex = ref(0)
const configurator = ref(null)
const loading = ref(true)
const selections = ref([])
const steps = ref([])
const regularTotalPrice = ref(0)
const totalPrice = ref(0)

const alert = reactive({ show: false, type: 'info', message: '' })

onMounted(fetchConfigurator)

watch(selections, calculateTotalPrice, { deep: true })

function calculateTotalPrice() {
  let total = 0
  let regularTotal = 0

  for (const selection of selections.value) {
    if (null === selection.productId) {
      continue
    }

    if (!selection.product || !selection.price_amount) {
      continue
    }

    const combinationPriceImpact = Number(selection.combinationPriceImpact || 0)

    total +=
      (selection.price_amount + combinationPriceImpact) *
      (selection.quantity || 1)
    regularTotal +=
      (selection.regular_price_amount + combinationPriceImpact) *
      (selection.quantity || 1)
  }

  totalPrice.value = total
  regularTotalPrice.value = regularTotal
}

async function fetchConfigurator() {
  try {
    loading.value = true

    const response = await fetch($r('getConfigurator'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'get-configurator',
        slug: props.id,
      }),
    })
    const data = await response.json()

    if (false === data.success) {
      const errorMessage = $t('Failed to load configurator', {}, 'Error')

      console.error(data.message || errorMessage)
      showAlert('danger', data.message || errorMessage)

      return
    }

    activeStepIndex.value = 0
    configurator.value = data.configurator
    selections.value = []
    steps.value = data.configurator.steps
  } catch (error) {
    console.error(
      $t('Error fetching configurator: %error%', { '%error%': error }, 'Error'),
    )
    showAlert(
      'danger',
      $t('An error occurred while loading the configurator', {}, 'Error'),
    )
  } finally {
    loading.value = false
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat(document.documentElement.lang, {
    style: 'currency',
    currency: prestashop.currency.iso_code,
  }).format(price)
}

function showAlert(type, message) {
  alert.show = true
  alert.type = type
  alert.message = message

  setTimeout(() => {
    closeAlert()
  }, 5000)
}

function closeAlert() {
  alert.show = false
}

provide('activeStepIndex', activeStepIndex)
provide('configurator', configurator)
provide('formatPrice', formatPrice)
provide('loading', loading)
provide('selections', selections)
provide('showAlert', showAlert)
provide('steps', steps)
provide('totalPrice', totalPrice)
provide('regularTotalPrice', regularTotalPrice)
</script>

<template>
  <div class="product-wizard-container">
    <Alert
      :show="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="closeAlert"
    />
    <Transition name="fade" mode="out-in">
      <Loader v-if="loading" />
      <Configurator v-else :activeStepIndex />
    </Transition>
  </div>
</template>

<style scoped lang="scss"></style>
