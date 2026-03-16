<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useAdminStore } from '../../stores/adminStore.js'
import CountUp from '../../components/CountUp.vue'

const admin = useAdminStore()
const animated = ref(false)
const liveTick = ref(0)
let liveTimer = null

const isLive = computed(() => admin.state.source === 'demo')

const fmtMoney = (value) =>
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(
    Number(value || 0)
  )

const fmtMoney2 = (value) =>
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(
    Number(value || 0)
  )

const fmtPct = (value) => `${Number(value || 0).toFixed(1)}%`

const last6Months = computed(() => {
  const now = new Date()
  const months = []
  for (let i = 5; i >= 0; i -= 1) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1)
    months.push({
      key: `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`,
      label: d.toLocaleDateString('en-US', { month: 'short' }),
      month: d.getMonth(),
      year: d.getFullYear(),
    })
  }
  return months
})

const monthlySeries = computed(() => {
  const buckets = last6Months.value.map((m) => ({ ...m, revenue: 0, bookings: 0 }))
  const index = new Map(buckets.map((b, i) => [b.key, i]))

  admin.state.bookings.forEach((b) => {
    const d = new Date(b.created_at)
    if (Number.isNaN(d.getTime())) return
    const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
    const i = index.get(key)
    if (i == null) return
    buckets[i].revenue += Number(b.gross_amount || 0)
    buckets[i].bookings += 1
  })

  const maxRevenue = Math.max(1, ...buckets.map((b) => b.revenue))
  const maxBookings = Math.max(1, ...buckets.map((b) => b.bookings))

  return buckets.map((b) => ({
    ...b,
    revenuePct: (b.revenue / maxRevenue) * 100,
    bookingsPct: (b.bookings / maxBookings) * 100,
  }))
})

const bookingLine = computed(() => {
  const points = monthlySeries.value.map((m, idx) => ({ x: idx, y: m.bookings }))
  const maxY = Math.max(1, ...points.map((p) => p.y))
  const minY = 0
  const width = 520
  const height = 210
  const padX = 26
  const padY = 18
  const innerW = width - padX * 2
  const innerH = height - padY * 2

  const toSvg = (p) => {
    const x = padX + (points.length === 1 ? 0 : (p.x / (points.length - 1)) * innerW)
    const y = padY + (1 - (p.y - minY) / (maxY - minY || 1)) * innerH
    return { ...p, sx: x, sy: y }
  }

  const svgPoints = points.map(toSvg)
  const poly = svgPoints.map((p) => `${p.sx},${p.sy}`).join(' ')
  return { width, height, svgPoints, poly }
})

const topShops = computed(() => {
  const map = new Map()
  admin.state.bookings.forEach((b) => {
    const shopId = String(b.shop_id || '')
    map.set(shopId, (map.get(shopId) || 0) + Number(b.gross_amount || 0))
  })
  return admin.state.shops
    .map((s) => ({ id: s.id, name: s.name, revenue: map.get(String(s.id)) || 0 }))
    .sort((a, b) => b.revenue - a.revenue)
    .slice(0, 5)
})

const metricCards = computed(() => {
  const ytd = admin.ytd.value
  const k = isLive.value ? liveTick.value : 0

  const gross = Number(ytd.gross || 0) + k * 230
  const avg = Number(ytd.avgBookingValue || 0) + k * 0.12
  const commission = Number(ytd.commission || 0) + k * 35
  const refundRate = Math.max(0, Number(ytd.refundRate || 0) + Math.sin(k / 3) * 0.2)

  return [
    { label: 'Total Revenue (YTD)', raw: gross, formatter: fmtMoney, trend: '+24%', tint: 'tint-blue' },
    { label: 'Avg. Booking Value', raw: avg, formatter: fmtMoney2, trend: '+6%', tint: 'tint-cyan' },
    { label: 'Platform Commission', raw: commission, formatter: fmtMoney, trend: '+18%', tint: 'tint-purple' },
    { label: 'Refund Rate', raw: refundRate, formatter: fmtPct, trend: '-0.8%', tint: 'tint-slate' },
  ]
})

const triggerAnimation = async () => {
  animated.value = false
  await nextTick()
  requestAnimationFrame(() => {
    animated.value = true
  })
}


const exportAll = () => {
  const payload = JSON.stringify(
    {
      generated_at: new Date().toISOString(),
      revenue_ytd: admin.ytd.value,
      monthly_series: monthlySeries.value,
    },
    null,
    2
  )
  const blob = new Blob([payload], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'chong-choul-reports.json'
  a.click()
  URL.revokeObjectURL(url)
}

onMounted(async () => {
  await admin.load().catch(() => {})
  triggerAnimation()

  if (isLive.value) {
    liveTimer = window.setInterval(() => {
      liveTick.value += 1
      triggerAnimation()
    }, 2400)
  }
})

watch(
  () => admin.state.bookings.length,
  () => triggerAnimation()
)

onBeforeUnmount(() => {
  if (liveTimer) window.clearInterval(liveTimer)
  liveTimer = null
})
</script>

<template>
  <section class="admin-page">
    <header class="page-head">
      <div>
        <h1 class="page-title">Reports &amp; Analytics</h1>
        <p class="page-subtitle">Comprehensive insights into platform performance and growth metrics.</p>
      </div>

      <div class="head-actions">
        <span v-if="isLive" class="live-chip"><span class="live-dot" aria-hidden="true"></span> Live</span>
        <button type="button" class="btn btn-ghost">
          <i class="fa-regular fa-calendar" aria-hidden="true"></i>
          <span>Date Range</span>
          <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </button>
        <button type="button" class="btn btn-primary" @click="exportAll">
          <i class="fa-solid fa-download" aria-hidden="true"></i>
          <span>Export All</span>
        </button>
      </div>
    </header>

    <div class="stat-grid four">
      <article v-for="card in metricCards" :key="card.label" class="stat-card compact">
        <div class="stat-label">{{ card.label }}</div>
        <div class="stat-row">
          <div class="stat-value"><CountUp :value="Number(card.raw || 0)" :formatter="card.formatter" /></div>
          <div class="stat-trend trend-up"><span>{{ card.trend }}</span></div>
        </div>
      </article>
    </div>

    <section class="grid-2 wide">
      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Monthly Revenue Trend</h2>
        </div>

        <div class="chart-bars report" :class="{ animated }">
          <div v-for="m in monthlySeries" :key="m.key" class="bar-col">
            <div class="bar">
              <div class="bar-fill" :style="{ height: `${m.revenuePct}%` }"></div>
            </div>
            <div class="bar-label">{{ m.label }}</div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Booking Volume</h2>
        </div>

        <div class="line-chart" :class="{ animated }">
          <svg :width="bookingLine.width" :height="bookingLine.height" viewBox="0 0 520 210" role="img" aria-label="Booking volume line chart">
            <g class="grid">
              <line v-for="i in 5" :key="i" :x1="26" :x2="494" :y1="18 + (i - 1) * 43" :y2="18 + (i - 1) * 43"></line>
            </g>
            <polyline class="line" :points="bookingLine.poly"></polyline>
            <circle v-for="p in bookingLine.svgPoints" :key="p.x" class="dot" :cx="p.sx" :cy="p.sy" r="4"></circle>
          </svg>
          <div class="line-labels">
            <span v-for="m in monthlySeries" :key="m.key">{{ m.label }}</span>
          </div>
        </div>
      </section>
    </section>


    <section class="grid-2 wide">
      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Top Performing Shops</h2>
        </div>
        <div class="list">
          <div v-for="(shop, idx) in topShops" :key="shop.id" class="list-row">
            <div class="rank">{{ idx + 1 }}</div>
            <div class="list-main">{{ shop.name }}</div>
            <div class="list-end">
              <strong>{{ fmtMoney(shop.revenue) }}</strong>
              <span class="trend-up">+{{ 5 + idx * 3 }}%</span>
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="card-head">
          <h2 class="card-title">Available Reports</h2>
        </div>
        <div class="reports-list">
          <div class="report-row">
            <div class="report-icon tint-cyan"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></div>
            <div class="report-main">
              <div class="report-name">Revenue Report</div>
              <div class="muted small">Complete earnings breakdown by shop and period</div>
            </div>
            <div class="muted small">Generate Oct 15</div>
          </div>
          <div class="report-row">
            <div class="report-icon tint-blue"><i class="fa-regular fa-clock" aria-hidden="true"></i></div>
            <div class="report-main">
              <div class="report-name">Fleet Utilization</div>
              <div class="muted small">Vehicle usage rates and idle time analysis</div>
            </div>
            <div class="muted small">Generate Oct 14</div>
          </div>
          <div class="report-row">
            <div class="report-icon tint-green"><i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i></div>
            <div class="report-main">
              <div class="report-name">Growth Analytics</div>
              <div class="muted small">User acquisition and retention metrics</div>
            </div>
            <div class="muted small">Generate Oct 12</div>
          </div>
          <div class="report-row">
            <div class="report-icon tint-purple"><i class="fa-regular fa-chart-bar" aria-hidden="true"></i></div>
            <div class="report-main">
              <div class="report-name">Booking Trends</div>
              <div class="muted small">Daily booking volume and conversion funnel</div>
            </div>
            <div class="muted small">Generate Oct 10</div>
          </div>
        </div>
      </section>
    </section>
  </section>
</template>

<style scoped>
.live-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 999px;
  border: 1px solid var(--mp-border);
  background: rgba(34, 211, 238, 0.10);
  color: var(--mp-muted);
  font-weight: 800;
  font-size: 12px;
}

.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.18);
}

@media (prefers-reduced-motion: no-preference) {
  .live-dot {
    animation: pulse 1.6s ease-in-out infinite;
  }

  @keyframes pulse {
    0% { transform: scale(1); opacity: 1; }
    60% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
  }
}
</style>
