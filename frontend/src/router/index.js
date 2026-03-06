import { createRouter, createWebHistory } from 'vue-router';
import VehiclesByShop from '../views/User/VehiclesByShop.vue';
import ViewDetail from '../views/User/ViewDetail.vue';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/vehicles'
    },
    {
      path: '/vehicles',
      name: 'vehicles-by-shop',
      component: VehiclesByShop
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-detail',
      component: ViewDetail
    },
    
  ]
})

export default router
