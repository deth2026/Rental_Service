import './assets/tailwind.css'
import './assets/main.css'
import './assets/register.css'
import './assets/dashboard.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

createApp(App).use(router).mount('#app')
