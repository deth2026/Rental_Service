# Login/Register Fix Plan

## Issues Found:
1. CORS supports_credentials is false (needed for auth)
2. API routes mismatch between frontend calls and backend routes
3. AuthController requires 'role' but frontend might not send it properly
4. Response format inconsistency between controllers

## Fix Steps:
- [x] 1. Fix CORS configuration - set supports_credentials to true
- [x] 2. Ensure AuthController handles role field properly (make optional with default 'customer')
- [x] 3. Fix frontend Login.vue to correctly handle the API response
- [x] 4. Fix frontend Register.vue to properly send role and handle response
- [x] 5. Fix API routes (removed merge conflicts, added proper routes)
- [x] 6. Add sanctum guard to auth.php

## Summary of Changes Made:
1. **backend/config/cors.php**: Changed `supports_credentials` from `false` to `true`
2. **backend/config/auth.php**: Added `sanctum` guard for API authentication
3. **backend/app/Http/Controllers/Api/AuthController.php**: Made `role` field nullable with default 'customer'
4. **backend/routes/api.php**: Fixed merge conflicts, added proper auth routes
5. **frontend/src/views/auth/Login.vue**: Updated to try `/api/login` first, handle multiple response formats for token/user
6. **frontend/src/views/auth/Register.vue**: Updated to try `/api/register` first, add fallback role if not provided

## To Test:
1. Start Laravel backend: `cd backend && php artisan serve`
2. Start Vue frontend: `cd frontend && npm run dev`
3. Try registering a new user at `/register`
4. Try logging in at `/login`

