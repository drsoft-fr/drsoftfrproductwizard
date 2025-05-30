import '@/css/admin/configurator/form.scss'

window.drsoftfrproductwizard = window.drsoftfrproductwizard || {}
window.drsoftfrproductwizard.data = window.drsoftfrproductwizard.data || {
  steps: [],
  name: '',
  active: true,
  isValid: true,
  loading: false,
}
window.drsoftfrproductwizard.data.isValid =
  window.drsoftfrproductwizard.data.isValid || true
window.drsoftfrproductwizard.data.loading =
  window.drsoftfrproductwizard.data.loading || false
window.drsoftfrproductwizard.routes = window.drsoftfrproductwizard.routes || {}

function initProductSelectors() {
  const elms = document.querySelectorAll(
    'input.js-product-selector:not([data-ts-initialized])',
  )

  if (!elms.length) {
    return
  }

  elms.forEach(function (input) {
    if (!input) {
      return
    }

    if (input.tomselect) {
      input.tomselect.destroy()
    }

    try {
      let selectedId = input.getAttribute('data-product-id')
      let selectedName = input.getAttribute('data-product-name')
      let options = {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        maxOptions: 20,
        maxItems: 1,
        create: false,
        load: function (query, callback) {
          if (!query.length) return callback()
          fetch(
            window.drsoftfrproductwizard.routes.product_search +
              '&q=' +
              encodeURIComponent(query),
          )
            .then((response) => response.json())
            .then((json) => callback(json.items))
            .catch(() => callback())
        },
        onChange: function (value) {
          let hidden = document.querySelector(input.dataset.target)
          if (hidden) hidden.value = value
        },
      }
      let ts = new TomSelect(input, options)

      if (selectedId && selectedName) {
        ts.addOption({ id: selectedId, text: selectedName })
        ts.setValue(selectedId)
        ts.clearOptions()
      }

      input.setAttribute('data-ts-initialized', '1')
    } catch (e) {
      console.error("Erreur lors de l'initialisation de Tom Select:", e)
    }
  })
}

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
      removeProductChoice(stepId, productChoiceId) {
        const elm = document.getElementById(
          `step-${stepId}__product-choice-${productChoiceId}-card`,
        )

        Alpine.store('wizardData').getStep(stepId).product_choices =
          Alpine.store('wizardData')
            .getStep(stepId)
            .product_choices.filter((c) => c.id !== productChoiceId)

        elm.remove()
      },
      uncheckOthers(event) {
        const targetStepId = parseInt(event.target.dataset.stepId || '')
        const targetProductChoiceId = parseInt(
          event.target.dataset.productChoiceId || '',
        )

        Alpine.store('wizardData')
          .getStep(targetStepId)
          .product_choices.forEach((c) => {
            const productChoiceId = c.id

            if (productChoiceId !== targetProductChoiceId) {
              c.is_default = false
            }
          })
      },
    }
  },

  // Gestionnaire des conditions
  conditionsManager: function (initialIdx, productChoiceIdx, stepIdx) {
    return {
      idx: initialIdx,
      showConditions: initialIdx > 0 ? true : false,
      addCondition() {
        let tpl = document
          .getElementById(
            `step-${stepIdx}__product-choice-${productChoiceIdx}__condition-prototype`,
          )
          .innerHTML.replace(/__cond__/g, this.idx)
        document
          .getElementById(
            `step-${stepIdx}__product-choice-${productChoiceIdx}__conditions-collection`,
          )
          .insertAdjacentHTML('beforeend', tpl)

        const productChoice = Alpine.store('wizardData').getProductChoice(
          stepIdx,
          productChoiceIdx,
        )

        if (
          !productChoice ||
          typeof productChoice.display_conditions === 'undefined'
        ) {
          return
        }

        productChoice.display_conditions.push({
          step: 0,
          choice: 0,
        })

        this.idx++
      },
    }
  },

  // Gestionnaire de condition
  conditionManager: function (
    conditionStepIdx,
    conditionChoiceIdx,
    productChoiceIdx,
    stepIdx,
  ) {
    return {
      conditionStepIdx: conditionStepIdx,
      conditionChoiceIdx: conditionChoiceIdx,
      updateChoices(event, step, productChoice) {
        // Synchronise l’étape sélectionnée avec le champ caché
        event.target.parentElement.querySelector(
          `input[name='${step}']`,
        ).value = this.conditionStepIdx

        let select =
          event.target.parentElement.parentElement.querySelector(
            '.js-choice-select',
          )
        let steps = JSON.parse(
          event.target.parentElement.parentElement.getAttribute(
            'data-steps-choices',
          ),
        )
        let selectedStepIdx = this.conditionStepIdx

        select.innerHTML = ''
        let opt = document.createElement('option')
        opt.value = ''
        opt.textContent = 'Choix requis...'
        select.appendChild(opt)

        if (
          selectedStepIdx !== '' &&
          steps[selectedStepIdx] &&
          steps[selectedStepIdx].choices
        ) {
          steps[selectedStepIdx].choices.forEach(function (choice) {
            let option = document.createElement('option')
            option.value = choice.idx
            option.textContent = choice.label
            select.appendChild(option)
          })

          // Reset la valeur du choix quand on change d’étape
          this.conditionChoiceIdx = ''
          setTimeout(() => {
            event.target.parentElement.parentElement.querySelector(
              `input[name='${productChoice}']`,
            ).value = ''
          }, 0)
        }
      },
      syncChoice(event, productChoice) {
        const hiddenInput = event.target.parentElement.querySelector(
          `input[name='${productChoice}']`,
        )

        if (hiddenInput) {
          hiddenInput.value = event.target.value
        } else {
          console.error('Champ caché pour le choix non trouvé')
        }
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
          const id =
            typeof stepId === 'string' && !isNaN(stepId)
              ? parseInt(stepId, 10)
              : stepId

          return this.data.steps.find((s) => s.id === id) || {}
        },
        updateStep(stepId, property, value) {
          const step = this.getStep(stepId)

          if (!step) {
            return
          }

          step[property] = value
        },
        getProductChoice(stepId, productChoiceId) {
          const stepIdNum =
            typeof stepId === 'string' && !isNaN(stepId)
              ? parseInt(stepId, 10)
              : stepId
          const choiceIdNum =
            typeof productChoiceId === 'string' && !isNaN(productChoiceId)
              ? parseInt(productChoiceId, 10)
              : productChoiceId
          const step = this.getStep(stepIdNum)

          if (!step || !step.product_choices) {
            return {}
          }

          return step.product_choices.find((p) => p.id === choiceIdNum) || {}
        },
        updateProductChoice(stepId, choiceId, property, value) {
          const step = this.getStep(stepId)

          if (!step) {
            return
          }

          const choice = step.product_choices.find((c) => c.id === choiceId)

          if (!choice) {
            return
          }

          choice[property] = value
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

document.addEventListener('DOMContentLoaded', function () {
  const stepsList = document.getElementById('steps-collection')
  if (stepsList) {
    new Sortable(stepsList, {
      animation: 150,
      handle: '.step-drag-handle',
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      dragClass: 'sortable-drag',
      onEnd: function () {
        stepsList
          .querySelectorAll('.js-step-block')
          .forEach(function (block, idx) {
            let posInput = block.querySelector('input[name*="[position]"]')
            let badgeElm = block.querySelector('.js-badge-position')
            let stepIdx = parseInt(block.dataset.stepId || '')
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
      onStart: function (evt) {
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
  setTimeout(initProductSelectors, 100)
})
