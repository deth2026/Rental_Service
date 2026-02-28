<template>
  <section class="auth-page">
    <div class="auth-card">
      <h1>Create Account</h1>
      <form @submit.prevent="onSubmit" class="auth-form">
        <label>
          <span>Full Name</span>
          <input v-model.trim="form.name" type="text" required />
        </label>

        <label>
          <span>Email</span>
          <input v-model.trim="form.email" type="email" required />
        </label>

        <label>
          <span>Phone</span>
          <input v-model.trim="form.phone" type="text" />
        </label>

        <label>
          <span>Role</span>
          <select v-model="form.role">
            <option value="owner">Owner</option>
            <option value="customer">Customer</option>
          </select>
        </label>

        <label>
          <span>Password</span>
          <input v-model="form.password" type="password" minlength="6" required />
        </label>

        <button :disabled="loading" type="submit">
          {{ loading ? 'Creating...' : 'Register' }}
        </button>
      </form>

      <p v-if="success" class="msg ok">{{ success }}</p>
      <p v-if="error" class="msg err">{{ error }}</p>
      <p class="meta">
        Already have an account?
        <RouterLink to="/login">Login</RouterLink>
      </p>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { registerUser } from '../../services/auth';

const router = useRouter();
const loading = ref(false);
const success = ref('');
const error = ref('');

const form = reactive({
  name: '',
  email: '',
  phone: '',
  role: 'owner',
  password: '',
});

const onSubmit = async () => {
  loading.value = true;
  success.value = '';
  error.value = '';
  try {
    await registerUser({
      name: form.name,
      email: form.email,
      phone: form.phone || null,
      role: form.role,
      password: form.password,
    });
    success.value = 'Registered successfully.';
    router.push('/setting');
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      err.response?.data?.errors?.email?.[0] ||
      err.response?.data?.errors?.password?.[0] ||
      err.message ||
      'Failed to register.';
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 1rem;
}

.auth-card {
  width: min(100%, 460px);
  border: 1px solid #d9e4e7;
  border-radius: 12px;
  padding: 1.25rem;
  background: #fff;
}

.auth-form {
  display: grid;
  gap: 0.75rem;
}

label {
  display: grid;
  gap: 0.35rem;
}

input,
select,
button {
  padding: 0.65rem 0.75rem;
  border-radius: 8px;
  border: 1px solid #cfdde2;
  font: inherit;
}

button {
  background: #0bbde4;
  border-color: #0bbde4;
  color: #fff;
  cursor: pointer;
  font-weight: 600;
}

.msg {
  margin-top: 0.75rem;
  padding: 0.55rem 0.7rem;
  border-radius: 8px;
}

.ok {
  background: #ecfbf3;
  color: #166b45;
}

.err {
  background: #fff1f2;
  color: #9d2f35;
}

.meta {
  margin-top: 0.8rem;
}
</style>
