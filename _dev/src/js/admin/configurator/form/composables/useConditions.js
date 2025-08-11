import { computed, ref } from 'vue'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

/**
 * Composable for managing conditions between product choices and steps
 *
 * @param {String|Number} stepId - The ID of the current step
 * @param {String|Number} productChoiceId - The ID of the current product choice
 *
 * @returns {Object} - Condition management methods and computed properties
 */
export function useConditions(stepId, productChoiceId) {
  const store = useConfiguratorStore()

  /**
   * Add a new condition
   *
   * @returns {Object} - The new condition
   */
  const addCondition = () => {
    return store.addCondition(stepId, productChoiceId)
  }

  // Get the current product choice
  const currentProductChoice = computed(() =>
    store.getProductChoice(stepId, productChoiceId),
  )

  // Get all conditions for the current product choice
  const conditions = computed(() => {
    if (
      !currentProductChoice.value ||
      !currentProductChoice.value.display_conditions
    ) {
      return []
    }

    return currentProductChoice.value.display_conditions
  })

  return {
    addCondition,
    currentProductChoice,
    conditions,
  }
}
