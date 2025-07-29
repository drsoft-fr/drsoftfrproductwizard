import TomSelect from 'tom-select'

export default function useTomSelect() {
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
          if (!query.length) {
            return callback()
          }

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

          if (!hidden) {
            return
          }

          hidden.value = value
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
