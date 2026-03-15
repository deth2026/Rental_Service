import './css/HomeView.css'
import './css/chooserole.css'
import './css/register.css'
import './css/setting.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import i18n, { syncAutoTranslateWithCurrentLanguage } from './i18n'
import CommonFooter from './components/CommonFooter.vue'

const app = createApp(App)
app.component('CommonFooter', CommonFooter)
app.use(router).use(i18n).mount('#app')
syncAutoTranslateWithCurrentLanguage()

router.afterEach(() => {
  // Re-apply auto translation for newly rendered SPA route content.
  const delays = [120, 450, 1000]
  delays.forEach((delay) => {
    setTimeout(() => {
      syncAutoTranslateWithCurrentLanguage()
    }, delay)
  })
})
