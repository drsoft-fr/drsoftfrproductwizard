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

const initSortableStep = () => {
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

const initProductSelectors = () => {
  const elms = document.querySelectorAll(
    'input.js-product-selector:not([data-ts-initialized])',
  )

  if (!elms.length) {
    return
  }

  elms.forEach((input) => {
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
        load(query, callback) {
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
        onChange(value) {
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

window.drsoftfrproductwizard.alpine = {
  stepManager(initialIdx) {
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

  productChoiceManager(initialIdx, stepIdx) {
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

  conditionsManager(initialIdx, productChoiceIdx, stepIdx) {
    return {
      idx: initialIdx,
      showConditions: initialIdx > 0,
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
      removeCondition(stepId, productChoiceId, elmId) {
        const elm = document.getElementById(elmId)
        const stepValue = parseInt(
          elm.querySelector('.js-step-select').value || '',
        )
        const choiceValue = parseInt(
          elm.querySelector('.js-choice-select').value || '',
        )
        const productChoice = Alpine.store('wizardData').getProductChoice(
          stepId,
          productChoiceId,
        )

        productChoice.display_conditions =
          productChoice.display_conditions.filter((c) => {
            return c.step !== stepValue || c.choice !== choiceValue
          })

        elm.remove()
      },
    }
  },

  conditionManager(conditionStepIdx, conditionChoiceIdx) {
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
  initStore() {
    // Rendre l'objet global réactif
    if (!window.Alpine) {
      return
    }

    // Make data object reactive
    window.drsoftfrproductwizard.data = window.Alpine.reactive(
      window.drsoftfrproductwizard.data,
    )

    window.Alpine.store('wizardData', {
      get data() {
        return window.drsoftfrproductwizard.data
      },
      initConditionSelectors() {
        this.initAllStepSelectors()
        this.initAllChoiceSelectors()
      },
      initAllStepSelectors() {
        document.querySelectorAll('.js-step-select').forEach((selector) => {
          this.initStepSelector(selector)
        })
      },
      initAllChoiceSelectors() {
        document.querySelectorAll('.js-choice-select').forEach((selector) => {
          this.initChoiceSelector(selector)
        })
      },
      initStepSelector(selector) {
        const currentStepId = parseInt(selector.dataset.stepId)
        const currentStep = this.getStep(currentStepId)
        const currentStepPosition = currentStep.position || 0

        // Conserver la valeur sélectionnée actuelle
        const selectedValue = selector.value

        // Vider le sélecteur
        selector.innerHTML = ''

        // Ajouter l'option par défaut
        const defaultOption = document.createElement('option')
        defaultOption.value = ''
        defaultOption.textContent = 'Étape...'
        selector.appendChild(defaultOption)

        // Ajouter les étapes avec position inférieure
        this.data.steps.forEach((step) => {
          if (step.position < currentStepPosition) {
            const option = document.createElement('option')
            option.value = step.id
            option.textContent = step.label

            // Restaurer la sélection si elle existe
            if (selectedValue && parseInt(selectedValue) === step.id) {
              option.selected = true
            }

            selector.appendChild(option)
          }
        })

        // Si l'option précédemment sélectionnée n'est plus valide mais existait
        if (
          selectedValue &&
          !Array.from(selector.options).some(
            (opt) => opt.value === selectedValue && opt.value !== '',
          )
        ) {
          const invalidOption = document.createElement('option')
          invalidOption.value = selectedValue
          invalidOption.textContent = '[Étape supprimée/invalide]'
          invalidOption.style.color = 'red'
          invalidOption.selected = true
          selector.appendChild(invalidOption)
        }

        // Synchroniser avec le champ caché associé
        const hiddenInput = selector
          .closest('.js-condition-block')
          .querySelector('input[name$="[step]"]')
        if (hiddenInput) {
          hiddenInput.value = selector.value
        }
      },
      initChoiceSelector(selector) {
        const stepSelector = selector
          .closest('.js-condition-block')
          .querySelector('.js-step-select')
        const selectedStepId = stepSelector ? stepSelector.value : null

        // Conserver la valeur sélectionnée actuelle
        const selectedValue = selector.value

        // Vider le sélecteur
        selector.innerHTML = ''

        // Ajouter l'option par défaut
        const defaultOption = document.createElement('option')
        defaultOption.value = ''
        defaultOption.textContent = 'Choix requis...'
        selector.appendChild(defaultOption)

        // Si une étape est sélectionnée, ajouter ses choix
        if (selectedStepId) {
          const selectedStep = this.getStep(parseInt(selectedStepId))

          if (selectedStep && selectedStep.product_choices) {
            selectedStep.product_choices.forEach((choice) => {
              if (choice.active) {
                const option = document.createElement('option')
                option.value = choice.id
                option.textContent = choice.label

                // Restaurer la sélection si elle existe
                if (selectedValue && parseInt(selectedValue) === choice.id) {
                  option.selected = true
                }

                selector.appendChild(option)
              }
            })
          }
        }

        // Si l'option précédemment sélectionnée n'est plus valide mais existait
        if (
          selectedValue &&
          !Array.from(selector.options).some(
            (opt) => opt.value === selectedValue && opt.value !== '',
          )
        ) {
          const invalidOption = document.createElement('option')
          invalidOption.value = selectedValue
          invalidOption.textContent = '[Choix supprimé/invalide]'
          invalidOption.style.color = 'red'
          invalidOption.selected = true
          selector.appendChild(invalidOption)
        }

        // Synchroniser avec le champ caché associé
        const hiddenInput = selector
          .closest('.js-condition-block')
          .querySelector('input[name$="[choice]"]')
        if (hiddenInput) {
          hiddenInput.value = selector.value
        }
      },
      getStep(stepId) {
        const id =
          typeof stepId === 'string' && !isNaN(stepId)
            ? parseInt(stepId, 10)
            : stepId

        return this.data.steps.find((s) => s.id === id) || {}
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
    })
  },
}

document.addEventListener('alpine:init', () => {
  window.drsoftfrproductwizard.alpine.initStore()
  Alpine.effect(() => {
    // Accéder aux données réactives pour les surveiller
    const data = window.drsoftfrproductwizard.data

    if (!data || !data.steps) return

    // Vérifier le nombre d'étapes et de choix pour détecter les ajouts/suppressions
    const stepsCount = data.steps.length
    let stepsChanged = false
    let conditionsChanged = false

    // Créer une empreinte unique de l'état actuel des étapes
    let stepsFingerprint = data.steps
      .map((step) => {
        // Accéder à chaque propriété pour la surveiller
        const stepId = step.id
        const stepPosition = step.position
        const stepActive = step.active
        const stepLabel = step.label

        // Vérifier le nombre de choix de produits
        const choicesCount = step.product_choices
          ? step.product_choices.length
          : 0

        // Surveiller les choix de produits
        if (step.product_choices) {
          step.product_choices.forEach((choice) => {
            const choiceId = choice.id
            const choiceLabel = choice.label
            const choiceActive = choice.active
            const choiceIsDefault = choice.is_default
            const choiceProductId = choice.product_id
            const choiceAllowQuantity = choice.allow_quantity
            const choiceForcedQuantity = choice.forced_quantity

            // Vérifier le nombre de conditions pour détecter les ajouts/suppressions
            const conditionsCount = choice.display_conditions
              ? choice.display_conditions.length
              : 0

            // Surveiller les conditions d'affichage
            if (choice.display_conditions) {
              choice.display_conditions.forEach((condition) => {
                const conditionStep = condition.step
                const conditionChoice = condition.choice
              })
            }
          })
        }

        return `${stepId}-${stepPosition}-${stepActive}-${stepLabel}-${choicesCount}`
      })
      .join('|')

    // Stocker la dernière empreinte pour comparer lors des prochaines exécutions
    if (
      !window._lastStepsFingerprint ||
      window._lastStepsFingerprint !== stepsFingerprint
    ) {
      window._lastStepsFingerprint = stepsFingerprint
      stepsChanged = true // Forcer la mise à jour initiale
    }

    // Observer les modifications DOM qui pourraient nécessiter des mises à jour
    const selectors = document.querySelectorAll(
      '.js-step-select, .js-choice-select',
    )
    const selectorsCount = selectors.length

    // Vérifier si le nombre de sélecteurs a changé
    if (
      !window._lastSelectorsCount ||
      window._lastSelectorsCount !== selectorsCount
    ) {
      window._lastSelectorsCount = selectorsCount
      conditionsChanged = true // Forcer la mise à jour initiale
    }

    if (!stepsChanged && !conditionsChanged) {
      return
    }

    // Débounce du rafraîchissement
    if (window._refreshSelectors_timeout) {
      clearTimeout(window._refreshSelectors_timeout)
    }

    window._refreshSelectors_timeout = setTimeout(() => {
      if (Alpine.store('wizardData')) {
        return
      }

      console.log('Rafraîchissement des sélecteurs détecté par Alpine.effect')
      Alpine.store('wizardData').initConditionSelectors()
    }, 50)
  })

  document.addEventListener('DOMContentLoaded', () => {
    Alpine.store('wizardData').initConditionSelectors()
    setTimeout(() => {
      initSortableStep()
      initProductSelectors()
    }, 100)
  })
})
