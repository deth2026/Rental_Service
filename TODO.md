# Admin-Only Branch Cleanup - TODO

## Plan Progress:
### 1. Create TODO.md [✅ COMPLETE]

### 2. Edit router/index.js to remove non-admin imports/routes [✅ COMPLETE]
- Remove imports: HomeView, ChooseRole, Login, Register, ShopDashboard, UserDashboard, PromotionView, SettingUser, ShopVehicles, VehiclesByShop, ViewDetail.
- Simplify routes: Keep /admin block + minimal login redirect.
- Update guards to default to /admin.

### 3. Delete non-admin frontend files/dirs [✅ COMPLETE]
```
rm frontend/src/views/HomeView.vue
rm frontend/src/views/ChooseRole.vue
rm -rf frontend/src/views/auth/
rm -rf frontend/src/views/user/
rm -rf frontend/src/views/shop/
rm -rf frontend/src/views/vehicle/
rm frontend/src/components/Navbar.vue
rm frontend/src/services/auth.js
rm frontend/src/services/userService.js
rm -rf frontend/src/i18n/
```

### 4. Delete non-admin CSS [✅ COMPLETE]
```
rm frontend/src/css/HomeView.css
rm frontend/src/css/chooserole.css
rm frontend/src/css/auth.css
rm frontend/src/css/login.css
rm frontend/src/css/register.css
rm frontend/src/css/setting.css
rm frontend/src/css/Booking.css
rm frontend/src/css/Coupons.css
rm frontend/src/css/Demage_Report.css
rm frontend/src/css/Feedback.css
rm frontend/src/css/Loyalty.css
rm frontend/src/css/Myshop.css
rm frontend/src/css/Payment.css
rm frontend/src/css/Logout.css
rm frontend/src/css/ShopVehicle.css
rm frontend/src/css/VehicleByShop.css
rm frontend/src/css/Vehicles.css
rm frontend/src/css/setting_dashboard.css
```

### 5. Delete misc unrelated files [✅ COMPLETE]
```
rm fix-git.ps1
rm remove_background.py
rm screenshot.png
rm -rf screenshots/
rm ../../Downloads/motopremium-reports*.json
```

### 6. Test & Complete [PENDING]
- cd frontend && npm run dev → Access http://localhost:5173/admin/dashboard (direct or login).
- Verify no errors, admin pages functional.
- Backend php artisan serve → API /api/admin/users works.

**Next: Test frontend dev server**

