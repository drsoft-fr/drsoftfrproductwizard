<script setup>
import { inject } from 'vue'
import NoProduct from '@/vue/front/configurator/components/cart/NoProduct.vue'
import Product from '@/vue/front/configurator/components/cart/Product.vue'
import Total from '@/vue/front/configurator/components/cart/Total.vue'

const selections = inject('selections')
</script>

<template>
  <dl class="drpw:space-y-3">
    <TransitionGroup name="slide-fade">
      <div v-for="(choice, index) in selections" :key="choice.stepId">
        <NoProduct v-if="null === choice.productId" :choice="choice" :index />

        <Product v-else :product="choice.product" :choice="choice" :index />
      </div>
    </TransitionGroup>

    <Total />
  </dl>
</template>

<style scoped lang="scss"></style>
