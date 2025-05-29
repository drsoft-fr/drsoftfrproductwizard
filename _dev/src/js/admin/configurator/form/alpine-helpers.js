/**
 * Fonctions exportées pour être utilisées dans Alpine.js
 */

// Exposer les fonctions sur l'objet window pour qu'Alpine.js puisse y accéder

// Exposer les fonctions Alpine.js sur l'objet window
window.drsoftfrproductwizard.alpine = {
  // Gestionnaire des étapes
  stepManager: function (initialIdx) {
    return {
      idx: initialIdx,
      addStep() {
        let tpl = document
          .getElementById('step-prototype')
          .innerHTML.replace(/__step__/g, this.idx)

        let alert = document.querySelector('#steps-collection .alert')

        if (alert) {
          alert.remove()
        }

        document
          .getElementById('steps-collection')
          .insertAdjacentHTML('beforeend', tpl)

        window.drsoftfrproductwizard.data.steps.push({
          id: this.idx,
          active: true,
          label: 'Nouvelle étape',
          position: this.idx,
          product_choices: [],
        })

        this.idx++
      },
      removeStep(stepId) {
        const elm = document.getElementById('step-' + stepId + '-card')

        if (
          window.drsoftfrproductwizard.data.steps &&
          window.drsoftfrproductwizard.data.steps.find((s) => s.id === stepId)
        ) {
          window.drsoftfrproductwizard.data.steps =
            window.drsoftfrproductwizard.data.steps.filter(
              (s) => s.id !== stepId,
            )
        }

        elm.remove()
      },
    }
  },

  // Gestionnaire des choix produits
  productChoiceManager: function (initialIdx, stepIdx) {
    return {
      idx: initialIdx,
      addProductChoice() {
        let tpl = document
          .getElementById('step-' + stepIdx + '__product-choice-prototype')
          .innerHTML.replace(/__choice__/g, this.idx)

        let alert = document.querySelector(
          '#step-' + stepIdx + '__product-choices-collection .alert',
        )

        if (alert) {
          alert.remove()
        }

        document
          .getElementById('step-' + stepIdx + '__product-choices-collection')
          .insertAdjacentHTML('beforeend', tpl)

        const step = window.drsoftfrproductwizard.data.steps.find(
          (s) => s.id === parseInt(stepIdx),
        )
        if (step) {
          step.product_choices.push({
            id: this.idx,
            label: 'Nouveau choix',
            active: true,
            is_default: false,
            product_id: null,
            allow_quantity: true,
            forced_quantity: null,
            display_conditions: [],
          })
        }

        setTimeout(initProductSelectors, 100)
        this.idx++
      },
      uncheckOthers(event) {
        const container = event.target.closest('.js-step-block')
        const checkboxes = container.querySelectorAll(
          'input[type=checkbox][name*="[isDefault]"]',
        )

        checkboxes.forEach((cb) => {
          if (cb !== event.target) cb.checked = false
        })
      },
    }
  },

  // Store Alpine pour accéder aux données globales
  initStore: function () {
    // Rendre l'objet global réactif
    window.drsoftfrproductwizard.data = window.Alpine.reactive(
      window.drsoftfrproductwizard.data,
    )

    if (window.Alpine) {
      window.Alpine.store('wizardData', {
        get data() {
          return window.drsoftfrproductwizard.data
        },
        updateName(value) {
          window.drsoftfrproductwizard.data.name = value
        },
        getStep(stepId) {
          return this.data.steps.find((s) => s.id === stepId) || {}
        },
        updateStep(stepId, property, value) {
          const step = this.getStep(stepId)
          if (step) {
            step[property] = value
          }
        },
        updateProductChoice(stepId, choiceId, property, value) {
          const step = this.getStep(stepId)
          if (step) {
            const choice = step.product_choices.find((c) => c.id === choiceId)
            if (choice) {
              choice[property] = value
            }
          }
        },
      })
    }
  },
}

// Exporter une fonction globale pour initialiser Alpine
export function initAlpine() {
  document.addEventListener('alpine:init', () => {
    window.drsoftfrproductwizard.alpine.initStore()
  })
}

// Initialiser Alpine lors du chargement du script
initAlpine()
