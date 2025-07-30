import '@/css/admin/configurator/form/index.scss'

import Alpine from 'alpinejs'

window.Alpine = Alpine

import getIdOrNull from '@/js/admin/configurator/form/getIdOrNull.js'
import useSortable from '@/js/admin/configurator/form/useSortable.js'
import useTomSelect from '@/js/admin/configurator/form/useTomSelect.js'

window.drsoftfrproductwizard = window.drsoftfrproductwizard || {}
window.drsoftfrproductwizard.data = window.drsoftfrproductwizard.data || {
  steps: [],
  name: '',
  active: true,
  isValid: true,
  loading: false,
  devMode: false,
}
window.drsoftfrproductwizard.data.isValid =
  window.drsoftfrproductwizard.data.isValid || true
window.drsoftfrproductwizard.data.loading =
  window.drsoftfrproductwizard.data.loading || false
window.drsoftfrproductwizard.data.devMode =
  window.drsoftfrproductwizard.data.devMode || false
window.drsoftfrproductwizard.routes = window.drsoftfrproductwizard.routes || {}

window.drsoftfrproductwizard.alpine = {
  stepManager(initialIdx) {
    return {
      idx: initialIdx,

      addStep() {
        this.idx++
        const newObjId = `virtual-${this.idx}`
        const iteration = this.idx

        let tpl = document
          .getElementById('step-prototype')
          .innerHTML.replace(/__step__/g, newObjId)
          .replace(/__iteration__/g, iteration)

        let alert = document.querySelector('#steps-collection .alert')

        if (alert) {
          alert.remove()
        }

        document
          .getElementById('steps-collection')
          .insertAdjacentHTML('beforeend', tpl)

        window.drsoftfrproductwizard.data.steps.push({
          id: newObjId,
          active: true,
          label: 'Nouvelle étape',
          position: iteration,
          product_choices: [],
          is_virtual: true,
        })
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
        this.idx++
        const newObjId = `virtual-${this.idx}`
        const iteration = this.idx

        let tpl = document
          .getElementById('step-' + stepIdx + '__product-choice-prototype')
          .innerHTML.replace(/__choice__/g, newObjId)
          .replace(/__iteration__/g, iteration)

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
          (s) => s.id === stepIdx,
        )
        if (step) {
          step.product_choices.push({
            id: newObjId,
            label: 'Nouveau choix',
            active: true,
            is_default: false,
            product_id: null,
            allow_quantity: true,
            forced_quantity: null,
            display_conditions: [],
            is_virtual: true,
          })
        }

        setTimeout(useTomSelect, 100)
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
        const targetStepId = getIdOrNull(event.target.dataset.stepId || '')
        const targetProductChoiceId = getIdOrNull(
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
        this.idx++
        const iteration = this.idx

        let tpl = document
          .getElementById(
            `step-${stepIdx}__product-choice-${productChoiceIdx}__condition-prototype`,
          )
          .innerHTML.replace(/__iteration__/g, iteration)
        document
          .getElementById(
            `step-${stepIdx}__product-choice-${productChoiceIdx}__conditions-collection`,
          )
          .insertAdjacentHTML('beforeend', tpl)
      },

      removeCondition(stepId, productChoiceId, elmId) {
        const elm = document.getElementById(elmId)
        const stepValue = getIdOrNull(
          elm.querySelector('.js-step-select').value,
        )
        const choiceValue = getIdOrNull(
          elm.querySelector('.js-choice-select').value,
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

  conditionManager(conditionStepIdx, conditionChoiceIdx, condition) {
    return {
      conditionStepIdx,
      conditionChoiceIdx,
      condition,

      updateChoices(event, step) {
        const target = event.target
        const hiddenElm = target.parentElement.querySelector(
          `input[name='${step}']`,
        )

        if (hiddenElm) {
          hiddenElm.value = getIdOrNull(this.conditionStepIdx)
          this.condition.step = getIdOrNull(this.conditionStepIdx)
        } else {
          console.error('Champ caché pour le choix non trouvé')

          return
        }

        window.Alpine.store('wizardData').initChoiceSelector(
          target.parentElement.parentElement.querySelector('.js-choice-select'),
        )
      },

      syncChoice(event, productChoice) {
        const hiddenInput = event.target.parentElement.querySelector(
          `input[name='${productChoice}']`,
        )

        if (hiddenInput) {
          hiddenInput.value = getIdOrNull(this.conditionChoiceIdx)
          this.condition.choice = getIdOrNull(this.conditionChoiceIdx)
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
        const currentStepId = getIdOrNull(selector.dataset.stepId)
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
          if (
            step.position >= currentStepPosition ||
            (typeof step.is_virtual !== 'undefined' && step.is_virtual === true)
          ) {
            return
          }

          const option = document.createElement('option')
          option.value = step.id
          option.textContent = step.label

          // Restaurer la sélection si elle existe
          if (selectedValue && selectedValue === step.id) {
            option.selected = true
          }

          selector.appendChild(option)
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
          const selectedStep = this.getStep(selectedStepId)

          if (
            selectedStep &&
            selectedStep.product_choices &&
            (typeof selectedStep.is_virtual === 'undefined' ||
              selectedStep.is_virtual === false)
          ) {
            selectedStep.product_choices.forEach((choice) => {
              if (
                !choice.active ||
                (typeof choice.is_virtual !== 'undefined' &&
                  choice.is_virtual === true)
              ) {
                return
              }

              const option = document.createElement('option')
              option.value = choice.id
              option.textContent = choice.label

              // Restaurer la sélection si elle existe
              if (selectedValue && selectedValue === choice.id) {
                option.selected = true
              }

              selector.appendChild(option)
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
        const id = getIdOrNull(stepId)

        return this.data.steps.find((s) => s.id === id) || {}
      },

      getProductChoice(stepId, productChoiceId) {
        const stepIdNum = getIdOrNull(stepId)
        const choiceIdNum = getIdOrNull(productChoiceId)
        const step = this.getStep(stepIdNum)

        if (!step || !step.product_choices) {
          return {}
        }

        return step.product_choices.find((p) => p.id === choiceIdNum) || {}
      },

      getCondition(
        stepId,
        productChoiceId,
        conditionStepIdx,
        conditionChoiceIdx,
      ) {
        const choice = this.getProductChoice(stepId, productChoiceId)

        if (!choice || typeof choice.display_conditions === 'undefined') {
          return {}
        }

        const condition = choice.display_conditions.find(
          (c) =>
            c.step === getIdOrNull(conditionStepIdx) &&
            c.choice === getIdOrNull(conditionChoiceIdx),
        )

        if (condition) {
          return condition
        }

        const newCondition = {
          step: null,
          choice: null,
          is_virtual: true,
        }

        choice.display_conditions.push(newCondition)

        return newCondition
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
      useSortable()
      useTomSelect()
    }, 100)
  })
})

Alpine.start()
