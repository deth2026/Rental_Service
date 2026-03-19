<script setup>
import { computed, onMounted, ref } from 'vue'
import { useToast } from '../../composables/useToast.js'

const STORAGE_KEY = 'chong_choul_admin_settings_v1'

const saving = ref(false)
const showResetConfirm = ref(false)
const toast = useToast()

const form = ref({
  security: {
    two_factor: true,
    session_timeout_minutes: 30,
    force_password_reset_days: 90,
  },
  notifications: {
    new_shop_registration_alerts: true,
    payout_processing_reminders: true,
    weekly_performance_summary: false,
  },
  platform: {
    default_currency: 'USD ($)',
    default_language: 'English',
    timezone: 'Asia/Phnom_Penh (UTC+7)',
  },
  fees: {
    standard_commission_rate: 15,
    premium_vehicle_rate: 20,
    promo_commission_rate: 10,
  },
})

const load = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (!raw) return
    const parsed = JSON.parse(raw)
    if (parsed && typeof parsed === 'object') {
      form.value = { ...form.value, ...parsed }
    }
  } catch {
    // ignore
  }
}

const save = async () => {
  if (saving.value) return
  saving.value = true
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(form.value))
    toast.success('Settings saved successfully.')
  } finally {
    saving.value = false
  }
}

const resetDemoData = () => {
  showResetConfirm.value = false
  // Clear various local storage keys that might hold mock data
  const keysToClear = [
    'chong_choul_payouts_v1',
    'chong_choul_last_payout_date',
    'rental_users',
    'rental_bookings'
  ]
  keysToClear.forEach(key => localStorage.removeItem(key))
  toast.success('Platform demo data reset.')
}

const toggle = (path) => {
  const parts = path.split('.')
  let target = form.value
  for (let i = 0; i < parts.length - 1; i += 1) {
    target = target[parts[i]]
  }
  const key = parts[parts.length - 1]
  target[key] = !target[key]
}

const canSave = computed(() => !saving.value)

onMounted(load)
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Settings</h1>
        <p class="page-subtitle">Configure platform preferences, security, and operational parameters.</p>
      </div>
      <div class="head-actions">
        <button type="button" class="btn btn-primary" :disabled="!canSave" @click="save">
          <span v-if="saving">Saving…</span>
          <span v-else>Save All Changes</span>
        </button>
      </div>
    </header>

    <section class="card settings-card">
      <div class="settings-head">
        <div class="settings-icon tint-cyan" aria-hidden="true"><i class="fa-solid fa-shield-halved"></i></div>
        <div>
          <h2 class="card-title">Security &amp; Authentication</h2>
          <p class="card-subtitle">Configure login methods, 2FA, and session policies</p>
        </div>
      </div>

      <div class="settings-grid">
        <div class="settings-row">
          <div class="settings-label">Two-Factor Authentication</div>
          <button type="button" class="switch" :class="{ on: form.security.two_factor }" @click="toggle('security.two_factor')">
            <span class="switch-thumb" aria-hidden="true"></span>
          </button>
        </div>

        <div class="settings-row">
          <div class="settings-label">Session Timeout (minutes)</div>
          <input v-model.number="form.security.session_timeout_minutes" class="pill-input" type="number" min="5" />
        </div>

        <div class="settings-row">
          <div class="settings-label">Force Password Reset (days)</div>
          <input v-model.number="form.security.force_password_reset_days" class="pill-input" type="number" min="1" />
        </div>
      </div>
    </section>

    <section class="card settings-card">
      <div class="settings-head">
        <div class="settings-icon tint-blue" aria-hidden="true"><i class="fa-regular fa-bell"></i></div>
        <div>
          <h2 class="card-title">Notifications</h2>
          <p class="card-subtitle">Manage email and push notification preferences</p>
        </div>
      </div>


      <div class="settings-grid">
        <div class="settings-row">
          <div class="settings-label">New Shop Registration Alerts</div>
          <button type="button" class="switch" :class="{ on: form.notifications.new_shop_registration_alerts }" @click="toggle('notifications.new_shop_registration_alerts')">
            <span class="switch-thumb" aria-hidden="true"></span>
          </button>
        </div>

        <div class="settings-row">
          <div class="settings-label">Payout Processing Reminders</div>
          <button type="button" class="switch" :class="{ on: form.notifications.payout_processing_reminders }" @click="toggle('notifications.payout_processing_reminders')">
            <span class="switch-thumb" aria-hidden="true"></span>
          </button>
        </div>

        <div class="settings-row">
          <div class="settings-label">Weekly Performance Summary</div>
          <button type="button" class="switch" :class="{ on: form.notifications.weekly_performance_summary }" @click="toggle('notifications.weekly_performance_summary')">
            <span class="switch-thumb" aria-hidden="true"></span>
          </button>
        </div>
      </div>
    </section>

    <section class="card settings-card">
      <div class="settings-head">
        <div class="settings-icon tint-green" aria-hidden="true"><i class="fa-solid fa-globe"></i></div>
        <div>
          <h2 class="card-title">Platform Configuration</h2>
          <p class="card-subtitle">General platform settings and localization</p>
        </div>
      </div>

      <div class="settings-grid">
        <div class="settings-row">
          <div class="settings-label">Default Currency</div>
          <select v-model="form.platform.default_currency" class="pill-input">
            <option>USD ($)</option>
            <option>EUR (€)</option>
            <option>KHR (៛)</option>
          </select>
        </div>

        <div class="settings-row">
          <div class="settings-label">Default Language</div>
          <select v-model="form.platform.default_language" class="pill-input">
            <option>English</option>
            <option>Khmer</option>
          </select>
        </div>

        <div class="settings-row">
          <div class="settings-label">Timezone</div>
          <select v-model="form.platform.timezone" class="pill-input">
            <option>Asia/Phnom_Penh (UTC+7)</option>
            <option>CET (UTC+1)</option>
            <option>UTC</option>
          </select>
        </div>
      </div>
    </section>

    <section class="card settings-card">
      <div class="settings-head">
        <div class="settings-icon tint-cyan" aria-hidden="true"><i class="fa-solid fa-coins"></i></div>
        <div>
          <h2 class="card-title">Commission &amp; Fees</h2>
          <p class="card-subtitle">Set platform commission rates and fee structures</p>
        </div>
      </div>

      <div class="settings-grid">
        <div class="settings-row">
          <div class="settings-label">Standard Commission Rate (%)</div>
          <input v-model.number="form.fees.standard_commission_rate" class="pill-input" type="number" min="0" max="100" />
        </div>
        <div class="settings-row">
          <div class="settings-label">Premium Vehicle Rate (%)</div>
          <input v-model.number="form.fees.premium_vehicle_rate" class="pill-input" type="number" min="0" max="100" />
        </div>
        <div class="settings-row">
          <div class="settings-label">Promo Commission Rate (%)</div>
          <input v-model.number="form.fees.promo_commission_rate" class="pill-input" type="number" min="0" max="100" />
        </div>
      </div>
    </section>


    <section class="card danger">
      <div class="danger-head">
        <div class="danger-icon" aria-hidden="true"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div>
          <h2 class="danger-title">Danger Zone</h2>
          <div class="muted small">Reset Platform Data</div>
          <div class="muted small">This will permanently delete all test data and local preferences. This action cannot be undone.</div>
        </div>
      </div>

      <div class="danger-actions">
        <button type="button" class="btn btn-danger" @click="showResetConfirm = true">Reset All Data</button>
      </div>
    </section>

    <div v-if="showResetConfirm" class="modal-backdrop" role="dialog" aria-modal="true">
      <div class="modal">
        <div class="modal-head">
          <div>
            <div class="modal-title">Reset Platform Data?</div>
            <div class="modal-sub">This will permanently delete local test data.</div>
          </div>
          <button type="button" class="icon-action" title="Close" @click="showResetConfirm = false"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-ghost" @click="showResetConfirm = false">Cancel</button>
          <button type="button" class="btn btn-danger" @click="resetDemoData">Reset Data</button>
        </div>
      </div>
    </div>
  </section>
</template>
