<script setup>
import { computed, inject } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'
import StepList from '@/vue/admin/configurator/components/step/StepList.vue'

const $t = inject('$t')

const emit = defineEmits(['submit', 'cancel'])

const store = useConfiguratorStore()

const isValid = computed(() => store.isValid)
const isLoading = computed(() => store.loading)

const updateName = (event) => {
  store.name = event.target.value
}

const updateActive = (event) => {
  store.active = event.target.checked
}

const handleSubmit = (event) => {
  event.preventDefault()
  emit('submit')
}

const handleCancel = () => {
  emit('cancel')
}
</script>

<template>
  <form @submit="handleSubmit" class="configurator-form">
    <div class="card">
      <div class="card-header">
        <h3>{{ $t('General information') }}</h3>
      </div>
      <div class="card-body">
        <div class="form-group">
          <label for="configurator-name" class="form-label">{{
            $t('Name of scenario')
          }}</label>
          <input
            type="text"
            id="configurator-name"
            class="form-control"
            :value="store.name"
            @input="updateName"
            :placeholder="$t('Name of scenario')"
            required
          />
        </div>
        <div class="form-check mt-3">
          <input
            type="checkbox"
            id="configurator-active"
            class="form-check-input"
            :checked="store.active"
            @change="updateActive"
          />
          <label for="configurator-active" class="form-check-label">
            {{ $t('Active') }}
          </label>
        </div>
      </div>
    </div>
    <StepList />
    <div class="mt-3">
      <button
        type="submit"
        class="btn btn-success me-2"
        :disabled="!isValid || isLoading"
      >
        <i class="material-icons" v-if="isLoading">refresh</i>
        <i class="material-icons" v-else>save</i>
        {{ $t('Save') }}
      </button>
      <button
        type="button"
        class="btn btn-secondary ml-2"
        @click="handleCancel"
        :disabled="isLoading"
      >
        <i class="material-icons">arrow_back</i>
        {{ $t('Back') }}
      </button>
    </div>
  </form>
</template>

<style scoped lang="scss">
.configurator-form {
  position: relative;
}
</style>
