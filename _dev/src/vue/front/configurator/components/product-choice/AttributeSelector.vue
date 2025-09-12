<script setup>
import { computed, ref, watch, inject } from 'vue'

const props = defineProps({
  choice: { type: Object, required: true },
})

const matchedCombination = inject('matchedCombination')
const $t = inject('$t')

// Selected attribute per group
const selectedAttributes = ref({}) // { [groupId]: attributeId }

const hasCombinations = computed(
  () =>
    Array.isArray(props.choice?.combinations) &&
    props.choice.combinations.length > 0,
)

// Build groups: { [groupId]: { id, name, isColorGroup, attributes: [{ id, name }] } }
const attributeGroups = computed(() => {
  if (!hasCombinations.value) {
    return {}
  }

  const groups = {}

  for (const comb of props.choice.combinations) {
    for (const att of comb.attributes || []) {
      const gid = Number(att.idAttributeGroup)

      if (!groups[gid]) {
        groups[gid] = {
          id: gid,
          name: att.groupName,
          isColorGroup: !!att.isColorGroup,
          attributes: [],
        }
      }

      if (
        !groups[gid].attributes.some(
          (a) => Number(a.id) === Number(att.idAttribute),
        )
      ) {
        groups[gid].attributes.push({
          id: Number(att.idAttribute),
          name: att.attributeName,
        })
      }
    }
  }

  // sort attributes by name and groups by id
  Object.values(groups).forEach((g) =>
    g.attributes.sort((a, b) => String(a.name).localeCompare(String(b.name))),
  )

  return Object.fromEntries(
    Object.values(groups)
      .sort((a, b) => a.id - b.id)
      .map((g) => [g.id, g]),
  )
})

function initDefaultSelection() {
  if (false === hasCombinations.value) {
    return
  }

  let defaultCombination = props.choice.combinations.find(
    (c) => c.id === props.choice.combinationId,
  )

  if (typeof defaultCombination === 'undefined') {
    defaultCombination = props.choice.combinations[0]
  }

  const init = {}

  for (const att of defaultCombination.attributes || []) {
    init[Number(att.idAttributeGroup)] = Number(att.idAttribute)
  }

  selectedAttributes.value = init
  updateCombinationFromSelection()
}

/**
 * Format: #groupId#attributeId-#groupId#attributeId...
 *
 * @return {string}
 */
function buildAttributeKey() {
  const parts = []
  const gids = Object.keys(selectedAttributes.value)
    .map((k) => Number(k))
    .sort((a, b) => a - b)

  for (const gid of gids) {
    const aid = selectedAttributes.value[gid]

    if (aid != null) {
      parts.push(`#${gid}#${aid}`)
    }
  }

  return parts.join('-')
}

function toCombinationName(comb) {
  if (!comb || !Array.isArray(comb.attributes)) {
    return ''
  }

  // example: "color: blue, size: s"
  return comb.attributes
    .map((a) => `${a.groupName}: ${a.attributeName}`)
    .join(', ')
}

function updateCombinationFromSelection() {
  if (!hasCombinations.value) {
    return
  }

  const key = buildAttributeKey()
  const comb = props.choice.combinations.find((c) => c.attributeKey === key)

  matchedCombination.value = comb || null
  // Store combinationId on choice so Cart.vue payload includes it
  props.choice.combinationId = comb ? Number(comb.id) : 0
  props.choice.combinationName = comb ? toCombinationName(comb) : ''
  props.choice.combinationPriceImpact = comb ? Number(comb.price || 0) : 0
  props.choice.combinationImageUrl = comb ? comb.imageUrl || '' : ''
}

watch(
  () => selectedAttributes.value,
  () => updateCombinationFromSelection(),
  { deep: true },
)

watch(
  () => props.choice,
  () => initDefaultSelection(),
  { immediate: true },
)
</script>

<template>
  <div v-if="hasCombinations" class="drpw:mt-3">
    <label
      v-for="(group, index) in Object.values(attributeGroups)"
      :key="group.id"
      :class="0 < index ? 'drpw:mt-6' : ''"
      class="drpw:floating-label drpw:w-full drpw:mb-0"
      :for="`step-${choice.stepId}__choice-${choice.id}__attribute-group-${group.id}`"
    >
      <span>{{ group.name }}</span>

      <select
        v-model.number="selectedAttributes[group.id]"
        class="drpw:input"
        :id="`step-${choice.stepId}__choice-${choice.id}__attribute-group-${group.id}`"
      >
        <option
          v-for="attr in group.attributes"
          :key="attr.id"
          :value="attr.id"
        >
          {{ attr.name }}
        </option>
      </select>
    </label>

    <div
      v-if="!matchedCombination"
      class="drpw:text-danger drpw:text-sm drpw:mt-3"
    >
      {{ $t('This variant is not available') }}
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
