import './css/HomeView.css'
import './css/chooserole.css'
import './css/register.css'
import './css/setting.css'
import './assets/user/Booking.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'

import router from './router'
import i18n, { syncAutoTranslateWithCurrentLanguage } from './i18n'

const pinia = createPinia()
createApp(App).use(pinia).use(router).use(i18n).mount('#app')
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
