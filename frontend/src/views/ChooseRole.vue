<script setup>
import { useRouter } from "vue-router";
import "../assets/ChooseRole.css";

const router = useRouter();

const selectRole = (role) => {
  localStorage.setItem("selectedRole", role);
  router.push(`/register?role=${role}`);
};
</script>
<template>
  <main class="role-page">
    <section class="role-box">
      <h1>Welcome to ChooseRole</h1>
      <p class="subtitle">Select your role to continue</p>

      <div class="roles">
        <button type="button" class="role-card" @click="selectRole('customer')">
          <span class="icon-wrap user-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
              <path
                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-3.314 0-6 2.015-6 4.5a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1C18 16.015 15.314 14 12 14Z"
              />
            </svg>
          </span>
          <span class="role-title">Customer</span>
          <span class="role-desc">Browse and book vehicles for your needs</span>
        </button>

        <button
          type="button"
          class="role-card"
          @click="selectRole('shop_owner')"
        >
          <span class="icon-wrap owner-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
              <path
                d="M4 10h16v9a1 1 0 0 1-1 1h-4v-5h-6v5H5a1 1 0 0 1-1-1v-9Zm1.2-5h13.6l1.2 3H4l1.2-3ZM11 20v-3h2v3h-2Z"
              />
            </svg>
          </span>
          <span class="role-title">Shop Owner</span>
          <span class="role-desc">Manage your vehicles and shop</span>
        </button>

        <button type="button" class="role-card" @click="selectRole('admin')">
          <span class="icon-wrap admin-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" focusable="false">
              <path
                d="m19.43 12.98 1.34-1.03a.8.8 0 0 0 .2-1.04l-1.27-2.2a.8.8 0 0 0-.97-.36l-1.58.64a6.8 6.8 0 0 0-1.12-.65l-.24-1.67a.8.8 0 0 0-.79-.67h-2.54a.8.8 0 0 0-.79.67l-.24 1.67c-.39.16-.77.37-1.12.65l-1.58-.64a.8.8 0 0 0-.97.36l-1.27 2.2a.8.8 0 0 0 .2 1.04l1.34 1.03a6.85 6.85 0 0 0 0 1.3l-1.34 1.03a.8.8 0 0 0-.2 1.04l1.27 2.2a.8.8 0 0 0 .97.36l1.58-.64c.35.28.73.49 1.12.65l.24 1.67a.8.8 0 0 0 .79.67h2.54a.8.8 0 0 0 .79-.67l.24-1.67c.39-.16.77-.37 1.12-.65l1.58.64a.8.8 0 0 0 .97-.36l1.27-2.2a.8.8 0 0 0-.2-1.04l-1.34-1.03c.06-.43.06-.87 0-1.3ZM12 15.2a3.2 3.2 0 1 1 0-6.4 3.2 3.2 0 0 1 0 6.4Z"
              />
            </svg>
          </span>
          <span class="role-title">Admin</span>
          <span class="role-desc">Full system administration access</span>
        </button>
      </div>

      <p class="hint">
        Choose your role to access the appropriate registration page
      </p>
      <RouterLink class="back-home" to="/">Back to Home</RouterLink>
    </section>
  </main>
</template>
