/**
 * Retourne la valeur d'un champ de formulaire en tant que nombre entier'
 *
 * @param value
 *
 * @return {number|null}
 */
export default function getIntOrNull(value) {
  if (typeof value === 'number' && !isNaN(value)) {
    return value
  }

  if (isNaN(value)) {
    return null
  }

  if (typeof value !== 'string') {
    return null
  }

  if (value === '') {
    return null
  }

  return parseInt(value)
}
