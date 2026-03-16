<<<<<<< HEAD
<<<<<<< HEAD
=======
import './assets/HomeView.css'
import './assets/chooserole.css'
>>>>>>> b11f4a5c301bc31d8d2aac6512ebafd9c7cae5ac
import './assets/register.css'
=======
import './css/HomeView.css'
import './css/chooserole.css'
import './css/register.css'
import './css/setting.css'
>>>>>>> d9950b55d15a8c1d02e11f6e24682b1f5b876a67

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
