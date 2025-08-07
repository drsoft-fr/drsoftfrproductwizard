import { onMounted, onBeforeUnmount } from 'vue'
import Sortable from 'sortablejs'
import { useConfiguratorStore } from '@/js/admin/configurator/form/stores/configurator'

/**
 * Composable for adding drag-and-drop functionality to a container
 *
 * @param {String|Function} containerSelector - CSS selector or ref for the container
 * @param {Object} options - Additional options for Sortable
 *
 * @returns {Object} - Sortable instance methods
 */
export function useSortable(containerSelector, options = {}) {
  let sortableInstance = null
  const store = useConfiguratorStore()

  onMounted(() => {
    let container

    if (typeof containerSelector === 'function') {
      // If it's a ref
      container = containerSelector()
    } else if (typeof containerSelector === 'string') {
      // If it's a CSS selector
      container = document.querySelector(containerSelector)
    } else {
      console.error('Invalid container selector provided to useSortable')

      return
    }

    if (!container) {
      console.warn(`Container not found: ${containerSelector}`)

      return
    }

    const defaultOptions = {
      animation: 250,
      handle: '.step-drag-handle',
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      dragClass: 'sortable-drag',

      onEnd: (evt) => {
        const stepItems = container.querySelectorAll('.step-item')
        const orderedStepIds = Array.from(stepItems).map((item) => {
          const stepId = item.dataset.stepId

          return isNaN(stepId) ? stepId : parseInt(stepId, 10)
        })

        store.reorderSteps(orderedStepIds)
        store.setDragging(false)

        document.body.classList.remove('dragging-active')
        document.querySelectorAll('.sortable-list').forEach((list) => {
          list.classList.remove('inactive-list', 'active-list')
        })
      },

      onStart: (evt) => {
        store.setDragging(true)

        document.body.classList.add('dragging-active')
        document.querySelectorAll('.sortable-list').forEach((list) => {
          list.classList.add('inactive-list')
        })

        evt.from.classList.remove('inactive-list')
        evt.from.classList.add('active-list')
      },

      ...options,
    }

    sortableInstance = new Sortable(container, defaultOptions)
  })

  onBeforeUnmount(() => {
    if (!sortableInstance) {
      return
    }

    sortableInstance.destroy()
    sortableInstance = null
  })

  return {
    getSortableInstance: () => sortableInstance,
  }
}
