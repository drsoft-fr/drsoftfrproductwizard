import { computed } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

export function useQuantityRule(stepId, productChoiceId) {
  const store = useConfiguratorStore()

  const currentProductChoice = computed(() =>
    store.getProductChoice(stepId, productChoiceId),
  )

  /**
   * Check if the current product choice is virtual
   */
  const isVirtual = computed(() => {
    return (
      currentProductChoice.value &&
      currentProductChoice.value.is_virtual === true
    )
  })

  return {
    currentProductChoice,
    isVirtual,
  }
}
