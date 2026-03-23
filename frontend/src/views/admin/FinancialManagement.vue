<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAdminStore } from '../../stores/adminStore.js'
import CountUp from '../../components/CountUp.vue'

const admin = useAdminStore()

const STORAGE_KEY = 'chong_choul_payouts_v1'
const LAST_PAYOUT_KEY = 'chong_choul_last_payout_date'

const state = ref({ processed_shop_ids: [] })
const isProcessingAll = ref(false)

const loadLocal = () => {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    if (raw) state.value = JSON.parse(raw)
  } catch {
    state.value = { processed_shop_ids: [] }
  }
}

const persistLocal = () => {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(state.value))
}

const lastPayoutDate = computed(() => {
  const stored = localStorage.getItem(LAST_PAYOUT_KEY)
  if (stored) return stored
  return 'Oct 15, 2024'
})

const processedSet = computed(() => new Set(state.value.processed_shop_ids || []))

const periodLabel = computed(() => 'Current Period (Oct 16 - Oct 31)')

const payoutRows = computed(() => {
  const commissionRate = admin.state.commission_rate || 0.15
  const byShop = new Map()
  admin.state.bookings.forEach((b) => {
    const shopId = String(b.shop_id || '')
    const gross = Number(
      b.total_amount ??
      b.total_price ??
      b.gross_amount ??
      b.price ??
      0
    ) || 0
    byShop.set(shopId, (byShop.get(shopId) || 0) + gross)
  })

  const shops = admin.state.shops.map((s) => ({
    id: s.id,
    name: s.name,
    vehicle_type: s.vehicle_type,
    gross: byShop.get(String(s.id)) || 0,
  }))

  const pending = shops
    .filter((s) => s.gross > 150 && !processedSet.value.has(String(s.id)))
    .sort((a, b) => b.gross - a.gross)
    .slice(0, 18)
    .map((s) => {
      const commission = s.gross * commissionRate
      return {
        ...s,
        commission,
        payout: s.gross - commission,
      }
    })

  return pending
})

const summary = computed(() => {
  const pendingAmount = payoutRows.value.reduce((sum, row) => sum + row.payout, 0)
  return {
    pendingAmount,
    shopsToPay: payoutRows.value.length,
  }
})

const fmtMoney = (value) =>
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(Number(value || 0))

const vehicleLabel = (type) => {
  const v = String(type || '').toLowerCase()
  if (v.includes('car')) return 'CAR'
  if (v.includes('bicycle')) return 'BICYCLE'
  return 'MOTORBIKE'
}

const initials = (name) => {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return 'S'
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return `${parts[0][0] || ''}${parts[parts.length - 1][0] || ''}`.toUpperCase()
}

const processShop = (shopId) => {
  const set = new Set(state.value.processed_shop_ids || [])
  set.add(String(shopId))
  state.value.processed_shop_ids = [...set]
  persistLocal()
  localStorage.setItem(LAST_PAYOUT_KEY, new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }))
}

const processAll = async () => {
  if (isProcessingAll.value) return
  isProcessingAll.value = true
  try {
    payoutRows.value.forEach((row) => processShop(row.id))
  } finally {
    isProcessingAll.value = false
  }
}

onMounted(async () => {
  loadLocal()
  await admin.load().catch(() => {})
})
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <div class="breadcrumb muted">FINANCIALS <span class="dot">•</span> PROCESS PAYOUTS</div>
        <h1 class="page-title">Admin Process Payouts Management</h1>
        <p class="page-subtitle">Review and approve pending earnings distribution to rental partners.</p>
      </div>

    </header>


    <div class="split-stats three">
      <section class="card stat-wide">
        <div class="wide-stat">
          <div>
            <div class="stat-label">PENDING PAYOUT AMOUNT</div>
            <div class="wide-value"><CountUp :value="Number(summary.pendingAmount || 0)" :formatter="fmtMoney" /></div>
            <div class="muted small"><i class="fa-regular fa-bell" aria-hidden="true"></i> Next batch scheduled for Oct 31</div>
          </div>
          <div class="stat-icon tint-red" aria-hidden="true"><i class="fa-solid fa-dollar-sign"></i></div>
        </div>
      </section>

      <section class="card stat-wide">
        <div class="wide-stat">
          <div>
            <div class="stat-label">NUMBER OF SHOPS TO PAY</div>
            <div class="wide-value"><CountUp :value="Number(summary.shopsToPay || 0)" /></div>
            <div class="muted small">Out of {{ admin.state.shops.length }} active registered shops</div>
          </div>
          <div class="stat-icon tint-slate" aria-hidden="true"><i class="fa-regular fa-building"></i></div>
        </div>
      </section>

      <section class="card stat-wide">
        <div class="wide-stat">
          <div>
            <div class="stat-label">LAST PAYOUT DATE</div>
            <div class="wide-value">{{ lastPayoutDate }}</div>
            <div class="muted small"><i class="fa-regular fa-circle-check" aria-hidden="true"></i> All transactions were successful</div>
          </div>
          <div class="stat-icon tint-green" aria-hidden="true"><i class="fa-regular fa-calendar"></i></div>
        </div>
      </section>
    </div>

    <section class="card">
      <div class="card-head">
        <h2 class="card-title">Pending Payouts List</h2>
        <div class="filters">
          <button type="button" class="btn btn-chip">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ periodLabel }}
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
          </button>
          <button type="button" class="btn btn-chip">All Shop Categories <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
        </div>
      </div>

      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>SHOP NAME</th>
              <th class="num">TOTAL EARNINGS (CURRENT PERIOD)</th>
              <th class="num">PLATFORM COMMISSION</th>
              <th class="num">NET PAYOUT AMOUNT</th>
              <th class="actions">ACTION</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in payoutRows.slice(0, 4)" :key="row.id">
              <td class="shop-cell">
                <span class="user-bubble" aria-hidden="true">{{ initials(row.name) }}</span>
                <div class="shop-meta">
                  <div class="shop-name">{{ row.name }}</div>
                  <div class="shop-id">{{ vehicleLabel(row.vehicle_type) }} • ID: {{ row.id }}</div>
                </div>
              </td>
              <td class="num">
                <strong>{{ fmtMoney(row.gross) }}</strong>
                <div class="muted small">{{ Math.max(1, Math.round(row.gross / 200)) }} Bookings</div>
              </td>
              <td class="num">
                <span class="money-neg">-{{ fmtMoney(row.commission) }}</span>
                <div class="muted small">{{ Math.round((admin.state.commission_rate || 0.15) * 100) }}% Standard</div>
              </td>
              <td class="num"><span class="money-pos">{{ fmtMoney(row.payout) }}</span></td>
              <td class="actions">
                <button type="button" class="btn btn-soft" @click="processShop(row.id)">Process Payment</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>


      <div class="table-footer">
        <div class="muted">SHOWING 0 TO {{ Math.min(4, payoutRows.length) }} OF {{ payoutRows.length }} PENDING SHOPS</div>
        <div class="pager">
          <button type="button" class="pager-btn is-active">1</button>
          <button type="button" class="pager-btn">2</button>
          <button type="button" class="pager-btn">3</button>
        </div>
      </div>
    </section>

    <section class="card note">
      <div class="note-icon" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></div>
      <div>
        <div class="note-title">Important Note on Payouts</div>
        <div class="note-text">
          Processing payouts will automatically generate bank transfer receipts and notify shop owners via email and platform notification.
          Ensure all dispute claims for the current period are settled before finalizing bulk payments.
        </div>
      </div>
    </section>
  </section>
</template>
