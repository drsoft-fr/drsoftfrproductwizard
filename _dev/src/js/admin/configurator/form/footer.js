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
