/**
 * Retourne la valeur d'un champ de formulaire en tant que nombre entier'
 *
 * @param value
 *
 * @return {number|null}
 */
export default function getIdOrNull(value) {
  if (typeof value === 'number' && !isNaN(value)) {
    return value
  }

  if (typeof value !== 'string') {
    return null
  }

  if (value === '') {
    return null
  }

  if (null === value.match(/^virtual-[0-9]+$/)) {
    return parseInt(value)
  }

  return value
}
