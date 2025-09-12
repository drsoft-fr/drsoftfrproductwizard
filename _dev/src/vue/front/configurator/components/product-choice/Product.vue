<script setup>
import { ref, inject, provide } from 'vue'
import Action from '@/vue/front/configurator/components/product-choice/Action.vue'
import AttributeSelector from '@/vue/front/configurator/components/product-choice/AttributeSelector.vue'
import Quantity from '@/vue/front/configurator/components/product-choice/Quantity.vue'

const props = defineProps({
  choice: { type: Object, required: true },
  noPictureImage: { type: Object, required: true },
  product: { type: Object, required: true },
})

const emit = defineEmits(['onSelect'])

const formatPrice = inject('formatPrice')
const selected = inject('selected')

const matchedCombination = ref(null)

provide('matchedCombination', matchedCombination)
</script>

<template>
  <div
    class="drpw:card drpw:bg-base-100 drpw:shadow-sm drpw:h-full drpw:transition drpw:border"
    :class="true === selected ? 'drpw:border-success' : 'drpw:border-base-100'"
  >
    <figure>
      <img
        :src="
          matchedCombination?.imageUrl
            ? matchedCombination.imageUrl
            : choice.product.images[0]
              ? choice.product.images[0].medium.url
              : noPictureImage.medium.url
        "
        :alt="choice.product.name"
      />
    </figure>
    <div class="drpw:card-body">
      <h4 class="drpw:card-title">{{ choice.product.name }}</h4>

      <div class="drpw:grow">
        <p
          v-if="true === choice.has_discount"
          class="drpw:text-base-content/50 drpw:line-through"
        >
          {{
            formatPrice(
              (choice.regular_price_amount || 0) +
                (matchedCombination
                  ? Number(matchedCombination.price || 0)
                  : 0),
            )
          }}
        </p>

        <p class="drpw:text-accent drpw:font-bold drpw:text-xl">
          {{
            formatPrice(
              (choice.price_amount || 0) +
                (matchedCombination
                  ? Number(matchedCombination.price || 0)
                  : 0),
            )
          }}
        </p>
      </div>

      <div
        v-if="choice.description"
        v-html="choice.description"
        class="drpw:mt-3 drpw:grow"
      ></div>

      <AttributeSelector :choice />

      <Quantity :choice />

      <Action @click="$emit('onSelect', choice)" />
    </div>
  </div>
</template>

<style scoped lang="scss"></style>
