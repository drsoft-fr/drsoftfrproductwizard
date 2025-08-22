<script setup>
import { inject } from 'vue'

const model = defineModel()

const props = defineProps({
  id: { type: String, required: true },
})

const $t = inject('$t')
</script>

<template>
  <div class="mt-3 d-flex flex-column gap-2">
    <label :for="id">{{ $t('Description') }}</label>
    <Editor v-model="model" :id>
      <template v-slot:toolbar>
        <span class="ql-formats">
          <select v-tooltip.bottom="'Font'" class="ql-font"></select>
          <select v-tooltip.bottom="'Size'" class="ql-size"></select>
        </span>
        <span class="ql-formats">
          <button v-tooltip.bottom="'Bold'" class="ql-bold"></button>
          <button v-tooltip.bottom="'Italic'" class="ql-italic"></button>
          <button v-tooltip.bottom="'Underline'" class="ql-underline"></button>
          <button v-tooltip.bottom="'Strike'" class="ql-strike"></button>
        </span>
        <span class="ql-formats">
          <select v-tooltip.bottom="'Color'" class="ql-color"></select>
          <select
            v-tooltip.bottom="'Background'"
            class="ql-background"
          ></select>
        </span>
        <span class="ql-formats">
          <button
            v-tooltip.bottom="'Sub'"
            class="ql-script"
            value="sub"
          ></button>
          <button
            v-tooltip.bottom="'Super'"
            class="ql-script"
            value="super"
          ></button>
        </span>
        <span class="ql-formats">
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="1"
          ></button>
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="2"
          ></button>
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="3"
          ></button>
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="4"
          ></button>
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="5"
          ></button>
          <button
            v-tooltip.bottom="'Header'"
            class="ql-header"
            value="6"
          ></button>
          <button
            v-tooltip.bottom="'Blockquote'"
            class="ql-blockquote"
          ></button>
          <button
            v-tooltip.bottom="'Code block'"
            class="ql-code-block"
          ></button>
        </span>
        <span class="ql-formats">
          <button
            v-tooltip.bottom="'Ordered'"
            class="ql-list"
            value="ordered"
          ></button>
          <button
            v-tooltip.bottom="'List'"
            class="ql-list"
            value="bullet"
          ></button>
          <button
            v-tooltip.bottom="'Indent'"
            class="ql-indent"
            value="-1"
          ></button>
          <button
            v-tooltip.bottom="'Indent'"
            class="ql-indent"
            value="+1"
          ></button>
        </span>
        <span class="ql-formats">
          <button
            v-tooltip.bottom="'Direction'"
            class="ql-direction"
            value="rtl"
          ></button>
          <select v-tooltip.bottom="'Align'" class="ql-align"></select>
        </span>
        <span class="ql-formats">
          <button v-tooltip.bottom="'Link'" class="ql-link"></button>
        </span>
        <span class="ql-formats">
          <button v-tooltip.bottom="'Clean'" class="ql-clean"></button>
        </span>
      </template>
    </Editor>
  </div>
</template>

<style scoped lang="scss"></style>
