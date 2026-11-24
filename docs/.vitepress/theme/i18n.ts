// Strings that the custom theme components render. Pages carry their own text;
// these are the labels inside components shared by the English and Slovak trees.
import { computed } from 'vue'
import { useData } from 'vitepress'

const en = {
  // Suffix of the generated SVG files: diagrams/<name><suffix>-light.svg
  svgSuffix: '',
  fullSize: '(full size)',
  runnableExample: 'Runnable example',
  runnableAria: (name: string) => `Runnable example examples/${name}`,
  runnableText: (name: string) => `The code below is <code>examples/${name}/</code> in the repository. CI runs it on every push against the current code and compares its output with <code>expected-output.txt</code>. Run it yourself from a clone with <code>examples/ci/recipe.sh ${name}</code>.`,
  viewOnGitHub: 'View on GitHub',
}

export type Strings = typeof en

const sk: Strings = {
  svgSuffix: '-sk',
  fullSize: '(plná veľkosť)',
  runnableExample: 'Spustiteľný príklad',
  runnableAria: (name) => `Spustiteľný príklad examples/${name}`,
  runnableText: (name) => `Kód nižšie je <code>examples/${name}/</code> v repozitári. CI ho spustí pri každom pushi proti aktuálnemu kódu a porovná jeho výstup s <code>expected-output.txt</code>. Z klonu repozitára ho spustíte príkazom <code>examples/ci/recipe.sh ${name}</code>.`,
  viewOnGitHub: 'Zobraziť na GitHube',
}

export function stringsFor(lang: string): Strings {
  return lang.startsWith('sk') ? sk : en
}

export function useStrings() {
  const { lang } = useData()
  return computed(() => stringsFor(lang.value))
}
