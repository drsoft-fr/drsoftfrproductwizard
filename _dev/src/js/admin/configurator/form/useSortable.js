import Sortable from 'sortablejs'

import getIdOrNull from '@/js/admin/configurator/form/getIdOrNull.js'

export default function useSortable() {
  const stepsList = document.getElementById('steps-collection')

  if (!stepsList) {
    return
  }

  new Sortable(stepsList, {
    animation: 150,
    handle: '.step-drag-handle',
    ghostClass: 'sortable-ghost',
    chosenClass: 'sortable-chosen',
    dragClass: 'sortable-drag',

    onEnd() {
      stepsList.querySelectorAll('.js-step-block').forEach((block, idx) => {
        let posInput = block.querySelector('input[name*="[position]"]')
        let badgeElm = block.querySelector('.js-badge-position')
        let stepIdx = getIdOrNull(block.dataset.stepId || '')

        if (posInput) {
          posInput.value = idx
        }

        if (badgeElm) {
          badgeElm.textContent = idx + 1
        }

        if (
          window.drsoftfrproductwizard.data &&
          window.drsoftfrproductwizard.data.steps
        ) {
          window.drsoftfrproductwizard.data.steps.find(
            (s) => s.id === stepIdx,
          ).position = idx
        }
      })

      document.body.classList.remove('dragging-active')

      document.querySelectorAll('.sortable-list').forEach((l) => {
        l.classList.remove('inactive-list', 'active-list')
      })
    },

    onStart(evt) {
      document.body.classList.add('dragging-active')

      document.querySelectorAll('.sortable-list').forEach((l) => {
        l.classList.add('inactive-list')
      })

      document
        .querySelectorAll('.sortable-list .card-body.collapse.show')
        .forEach((l) => {
          l.classList.remove('show')
        })

      evt.from.classList.remove('inactive-list')
      evt.from.classList.add('active-list')
    },
  })
}
