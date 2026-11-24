<script setup lang="ts">
// A diagram from public/diagrams/<name>-light.svg and -dark.svg, the one matching the colour scheme.
import { withBase } from 'vitepress'

defineProps<{ name: string, alt: string }>()
</script>

<template>
  <figure class="ms-diagram">
    <a :href="withBase(`/diagrams/${name}-light.svg`)" target="_blank" rel="noopener" :aria-label="`${alt} (full size)`">
      <img class="ms-diagram-light" :src="withBase(`/diagrams/${name}-light.svg`)" :alt="alt" loading="lazy" decoding="async">
      <img class="ms-diagram-dark" :src="withBase(`/diagrams/${name}-dark.svg`)" :alt="alt" loading="lazy" decoding="async">
    </a>
  </figure>
</template>
