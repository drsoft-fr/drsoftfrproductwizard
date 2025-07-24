<script setup>
import { onMounted, reactive, ref } from 'vue'
import Alert from '@/vue/front/configurator/components/Alert.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const configurator = ref(null)
const loading = ref(true)

const alert = reactive({ show: false, type: 'info', message: '' })

const { drsoftfrproductwizard } = window?.prestashop?.modules || { routes: {} }
const routes = drsoftfrproductwizard.routes || {}

onMounted(fetchConfigurator)

async function fetchConfigurator() {
  try {
    if (typeof routes.getConfigurator === 'undefined') {
      throw new Error('Missing routes.getConfigurator')
    }

    loading.value = true

    const response = await fetch(routes.getConfigurator, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'get-configurator',
        slug: props.id,
      }),
    })
    const data = await response.json()

    if (false === data.success) {
      console.error(data.message || 'Failed to load configurator')
      showAlert('danger', data.message || 'Failed to load configurator')

      return
    }

    configurator.value = data
  } catch (error) {
    console.error('Error fetching configurator:', error)
    showAlert('danger', 'An error occurred while loading the configurator')
  } finally {
    loading.value = false
  }
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
</script>

<template>
  <div
    :id="'configurator-' + id"
    class="product-wizard-container container p-3"
  >
    <Alert
      :show="alert.show"
      :type="alert.type"
      :message="alert.message"
      @close="closeAlert"
    />
    <div v-if="loading" class="col-12 text-center p-5">
      <div class="spinner-border" role="status">
        <span class="sr-only">Chargement...</span>
      </div>
      <p class="mt-3">Chargement des options du configurateur...</p>
    </div>
    <div v-else class="row">
      <pre><samp>{{ configurator }}</samp></pre>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
