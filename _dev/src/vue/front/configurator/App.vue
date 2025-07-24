<script setup>
import { onMounted, ref } from 'vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const configurator = ref(null)
const loading = ref(true)

const { drsoftfrproductwizard } = window?.prestashop?.modules || { routes: {} }
const routes = drsoftfrproductwizard.routes || {}

onMounted(fetchConfigurator)

async function fetchConfigurator() {
  try {
    if (typeof routes.getConfigurator === 'undefined')
      throw new Error(
        'Missing routes.getConfigurator',
      )

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

      return
    }

    configurator.value = data
  } catch (error) {
    console.error('Error fetching configurator:', error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div
    :id="'configurator-' + id"
    class="product-wizard-container container p-3"
  >
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
