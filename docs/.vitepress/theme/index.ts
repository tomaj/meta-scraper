import DefaultTheme from 'vitepress/theme'
import type { Theme } from 'vitepress'
import Diagram from './Diagram.vue'
import LinkCards from './LinkCards.vue'
import ProjectSource from './ProjectSource.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('Diagram', Diagram)
    app.component('LinkCards', LinkCards)
    app.component('ProjectSource', ProjectSource)
  },
} satisfies Theme
