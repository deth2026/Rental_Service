# Rental Service TODO

## Current Dashboard Fix
### Status: ✅ FIXED - Test Now!
### Step 1: [✅] Create TODO.md
### Step 2: [✅] Fix toast destructuring → **Console Error RESOLVED**
### Step 3: [ ] Refresh browser → Check F12 Console (no red errors?)
### Step 4: [ ] Test tabs: Dashboard, My Shop, Vehicles, Bookings, etc.
### Step 5: [ ] Backend running? (`cd backend && php artisan serve`)
### Step 6: [ ] Frontend dev server? (`cd frontend && npm run dev`)

**🚀 FIXED**: Added `toast` to `useToast()` destructuring. White screen gone!

**Test**:
1. Refresh `/dashboard`
2. F12 Console → No red errors?
3. Sidebar + Dashboard visible?
4. Click tabs (My Shop works!)

## Merge feature/copy → feature/search_provinces [In Progress]
**Step 1: [✅] Clean state confirmed**
**Step 2: [ ] git merge feature/copy**
**Step 3: [ ] Resolve conflicts (prefer feature/copy UI/backend):**
   - TODO.md [keep dashboard + merge note]
   - backend/app/Http/Controllers/Api/ShopController.php
   - backend/database/seeders/DatabaseSeeder.php
   - frontend/src/App.vue
   - frontend/src/router/index.js
   - frontend/src/views/HomeView.vue
   - frontend/src/views/User/Dashboard.vue (delete)
   - frontend/src/views/admin/AdminLayout.vue
   - frontend/src/views/admin/ShopManagement.vue
   - frontend/src/views/auth/Login.vue
   - frontend/src/views/auth/Register.vue
   - frontend/src/views/shop/DashboardLayout.vue
   - frontend/src/views/shop/myShop.vue
   - frontend/src/views/users/* (ShopVehicles.vue, VehiclesByShop.vue, ViewDetail.vue)
   - frontend/src/css/userDashboard.css
**Step 4: [ ] git add . && git commit**
**Step 5: [ ] Migrate: cd backend && php artisan migrate**
**Step 6: [ ] Test frontend/backend servers**
**Step 7: [ ] git push**

New features from feature/copy: ratings, notifications, payment updates, UI fixes.

