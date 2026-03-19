# Notification Cleanup TODO
Status: [4/11] ✅ Complete

## Plan Steps:
- [ ] 1. Backup current state (git stash)
- [x] 2. Minimize frontend/src/router/index.js
- [x] 3. Update frontend/src/App.vue (notification only)
- [ ] 4. Delete all frontend/src/views/* except User/Notification.vue
- [ ] 5. Delete all frontend/src/components/* except NotificationMenu.vue
- [ ] 6. Delete all frontend/src/composables/* except useNotifications.js
- [ ] 7. Delete all frontend/src/css/*
- [ ] 8. Delete all frontend/src/services/* except notification-related
- [ ] 9. Backend: Keep only NotificationController.php + routes
- [ ] 10. Delete other backend controllers/models
- [ ] 11. Test notification feature + attempt_completion
