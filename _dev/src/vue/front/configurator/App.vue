<script setup>
import { inject, onMounted, reactive, ref } from 'vue'
import Alert from '@/vue/front/configurator/components/Alert.vue'

const props = defineProps({
  id: { type: Number, required: true },
})

const $r = inject('$r')
const $t = inject('$t')

const configurator = ref(null)
const loading = ref(true)

const alert = reactive({ show: false, type: 'info', message: '' })

onMounted(fetchConfigurator)

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

    configurator.value = data
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
    <div v-if="loading" class="text-center p-5">
      <div class="spinner-border" role="status">
        <span class="sr-only">{{ $t('Loading...') }}</span>
      </div>
      <p class="mt-3">{{ $t('Loading configurator options...') }}</p>
    </div>
    <div v-else class="row">
      <pre><samp>{{ configurator }}</samp></pre>
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
