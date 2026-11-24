<script setup lang="ts">
// A diagram from public/diagrams/<name>-light.svg and -dark.svg, the one matching the colour scheme.
// Slovak pages use the <name>-sk-*.svg variants with Slovak labels.
import { computed } from 'vue'
import { withBase } from 'vitepress'
import { useStrings } from './i18n'

const props = defineProps<{ name: string, alt: string }>()
const t = useStrings()
const file = (variant: string) => withBase(`/diagrams/${props.name}${t.value.svgSuffix}-${variant}.svg`)
const light = computed(() => file('light'))
const dark = computed(() => file('dark'))
</script>

<template>
  <figure class="ms-diagram">
    <a :href="light" target="_blank" rel="noopener" :aria-label="`${alt} ${t.fullSize}`">
      <img class="ms-diagram-light" :src="light" :alt="alt" loading="lazy" decoding="async">
      <img class="ms-diagram-dark" :src="dark" :alt="alt" loading="lazy" decoding="async">
    </a>
  </figure>
</template>
