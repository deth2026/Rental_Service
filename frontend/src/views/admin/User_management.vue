<template>
  <div class="user-management-page">
    <header class="global-header">
      <div class="global-search-pill">
        <svg viewBox="0 0 24 24" role="presentation">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" fill="none" />
          <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>
        <input aria-label="Search here" placeholder="Search here..." />
      </div>
      <div class="header-utility">
        <button class="icon-button" type="button" aria-label="Messages">
          <svg viewBox="0 0 24 24" role="presentation">
            <rect x="3" y="4" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.5" fill="none" />
            <path d="M3 7l9 7 9-7" fill="none" stroke="currentColor" stroke-width="1.5" />
          </svg>
        </button>
        <button class="icon-button" type="button" aria-label="Notifications">
          <svg viewBox="0 0 24 24" role="presentation">
            <path
              d="M12 3c-2.8 0-5 2.2-5 5v3.5L6 13v1h12v-1l-1-1.5V8c0-2.8-2.2-5-5-5z"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
            />
            <path d="M9 18a3 3 0 0 0 6 0" fill="none" stroke="currentColor" stroke-width="1.5" />
          </svg>
        </button>
        <div class="profile-chip">
          <img src="https://i.pravatar.cc/40?img=32" alt="Beau Alexander" />
          <div>
            <p>Beau Alexander</p>
            <span>alexander.@gmail.com</span>
          </div>
          <svg viewBox="0 0 24 24" role="presentation">
            <path d="M7 10l5 5 5-5" fill="none" stroke="#1f2f4a" stroke-width="1.5" stroke-linecap="round" />
          </svg>
        </div>
      </div>
    </header>

    <main class="customer-panel">
      <div class="panel-heading">
        <div>
          <p class="section-label">Customer</p>
          <h1>Customer List</h1>
        </div>
        <div class="panel-actions">
          <div class="panel-search">
            <svg viewBox="0 0 24 24" role="presentation">
              <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.5" fill="none" />
              <path d="M16.5 16.5l4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
            <input aria-label="Search customers" placeholder="Search here..." />
          </div>
          <button class="ghost-button" type="button">
            <span>Filter</span>
            <svg viewBox="0 0 24 24" role="presentation">
              <path d="M6 7h12M10 12h4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
            </svg>
          </button>
          <button class="primary-button" type="button">
            + Add New Customer
          </button>
        </div>
      </div>

      <div class="customer-table">
        <table>
          <thead>
            <tr>
              <th class="checkbox-cell">
                <input type="checkbox" aria-label="Select all customers" />
              </th>
              <th>Name</th>
              <th>E-mail</th>
              <th>Phone</th>
              <th>Location</th>
              <th>Gender</th>
              <th class="align-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="customer in customers" :key="customer.email">
              <td class="checkbox-cell">
                <input type="checkbox" :id="`customer-${customer.id}`" />
              </td>
              <td class="name-cell">
                <img :src="customer.avatar" :alt="customer.name" />
                <span>{{ customer.name }}</span>
              </td>
              <td>{{ customer.email }}</td>
              <td>{{ customer.phone }}</td>
              <td>{{ customer.location }}</td>
              <td>{{ customer.gender }}</td>
              <td class="align-right">
                <button class="ellipsis-button" type="button" aria-label="Open actions">
                  <span></span>
                  <span></span>
                  <span></span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup>
const customers = [
  {
    id: 1,
    name: 'Isabella Taylor',
    email: 'isabellamartinez@example.com',
    phone: '+1901 234 5678',
    location: 'Cape, South Africa',
    gender: 'Female',
    avatar: 'https://i.pravatar.cc/48?img=15',
  },
  {
    id: 2,
    name: 'Lucas Anderson',
    email: 'williamthomas@example.com',
    phone: '+2012 345 6789',
    location: 'Toronto, Canada',
    gender: 'Male',
    avatar: 'https://i.pravatar.cc/48?img=12',
  },
  {
    id: 3,
    name: 'Ava Wilson',
    email: 'oliviaanderson@example.com',
    phone: '+1789 654 3210',
    location: 'Moscow, Russia',
    gender: 'Female',
    avatar: 'https://i.pravatar.cc/48?img=8',
  },
  {
    id: 4,
    name: 'Mia Thomas',
    email: 'charlottegracia@example.com',
    phone: '+2156 789 4321',
    location: 'Berlin, Germany',
    gender: 'Female',
    avatar: 'https://i.pravatar.cc/48?img=18',
  },
  {
    id: 5,
    name: 'Noah Smith',
    email: 'jamesmiller@example.com',
    phone: '+1623 478 9054',
    location: 'Cairo, Egypt',
    gender: 'Male',
    avatar: 'https://i.pravatar.cc/48?img=10',
  },
  {
    id: 6,
    name: 'James White',
    email: 'danielrodriguez@example.com',
    phone: '+2278 901 2345',
    location: 'Bangkok, Thailand',
    gender: 'Male',
    avatar: 'https://i.pravatar.cc/48?img=20',
  },
  {
    id: 7,
    name: 'Mason Lee',
    email: 'benjaminlee@example.com',
    phone: '+1845 321 6789',
    location: 'New Delhi, India',
    gender: 'Male',
    avatar: 'https://i.pravatar.cc/48?img=5',
  },
  {
    id: 8,
    name: 'Olivia Brown',
    email: 'emilyclark@example.com',
    phone: '+1598 753 8642',
    location: 'Rio de Janeiro, Brazil',
    gender: 'Female',
    avatar: 'https://i.pravatar.cc/48?img=31',
  },
  {
    id: 9,
    name: 'Sophia Martinez',
    email: 'sophiawatson@example.com',
    phone: '+1357 246 8023',
    location: 'Paris, France',
    gender: 'Female',
    avatar: 'https://i.pravatar.cc/48?img=52',
  },
  {
    id: 10,
    name: 'Liam Johnson',
    email: 'michaelbrown@example.com',
    phone: '+1456 789 0123',
    location: 'Sydney, Australia',
    gender: 'Male',
    avatar: 'https://i.pravatar.cc/48?img=47',
  },
];
</script>

<style scoped>
.user-management-page {
  min-height: 100vh;
  padding: 32px 40px 48px;
  background: radial-gradient(circle at top right, #f4f7fa 0%, #e8edf3 55%, #dfe4ea 100%);
  font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
  color: var(--text-main);
}

.global-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 32px;
  align-items: center;
  gap: 20px;
}

.global-search-pill {
  flex: 1;
  max-width: 520px;
  display: flex;
  align-items: center;
  padding: 12px 18px;
  border-radius: 999px;
  background: #f7f9fc;
  border: 1px solid rgba(31, 47, 74, 0.1);
  gap: 12px;
}

.global-search-pill svg {
  width: 20px;
  height: 20px;
  color: #8e9ab7;
}

.global-search-pill input {
  flex: 1;
  border: 0;
  outline: none;
  background: transparent;
  font-size: 15px;
  color: var(--text-main);
}

.header-utility {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-button {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: 1px solid rgba(31, 47, 74, 0.12);
  background: #fff;
  display: grid;
  place-items: center;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.icon-button svg {
  width: 20px;
  height: 20px;
  color: #1f2f4a;
}

.icon-button:hover {
  border-color: rgba(31, 47, 74, 0.4);
  transform: translateY(-1px);
}

.profile-chip {
  border-radius: 999px;
  padding: 8px 14px;
  background: #fff;
  border: 1px solid rgba(31, 47, 74, 0.08);
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: var(--shadow-sm);
}

.profile-chip img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.profile-chip p {
  margin: 0;
  font-weight: 700;
}

.profile-chip span {
  display: block;
  font-size: 12px;
  color: #6e7f95;
}

.profile-chip svg {
  width: 16px;
  height: 16px;
}

.customer-panel {
  background: #fff;
  border-radius: 28px;
  padding: 32px;
  box-shadow: var(--shadow-lg);
  border: 1px solid rgba(31, 47, 74, 0.08);
}

.panel-heading {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  gap: 16px;
  flex-wrap: wrap;
}

.section-label {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #6e7f95;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.panel-heading h1 {
  margin: 0;
  font-size: 28px;
  color: var(--text-main);
}

.panel-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.panel-search {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f6f8fb;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid rgba(31, 47, 74, 0.08);
  min-width: 220px;
}

.panel-search svg {
  width: 18px;
  height: 18px;
  color: #8e9ab7;
}

.panel-search input {
  border: 0;
  outline: none;
  background: transparent;
  font-size: 14px;
  color: #1f2f4a;
  width: 100%;
}

.primary-button {
  background: #1f9ae5;
  color: #fff;
  border: none;
  border-radius: 999px;
  padding: 12px 22px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 12px 20px rgba(31, 154, 229, 0.35);
}

.primary-button:hover {
  background: #1583c2;
}

.ghost-button {
  border: 1px solid rgba(31, 47, 74, 0.15);
  background: #fff;
  border-radius: 999px;
  padding: 10px 16px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.ghost-button svg {
  width: 16px;
  height: 16px;
}

.customer-table {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 15px;
}

th,
td {
  text-align: left;
  padding: 16px 12px;
  border-bottom: 1px solid rgba(31, 47, 74, 0.08);
}

thead th {
  color: #6e7f95;
  font-weight: 600;
  background: rgba(31, 47, 74, 0.02);
}

.checkbox-cell {
  width: 48px;
  text-align: center;
}

.name-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.name-cell img {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  object-fit: cover;
}

.align-right {
  text-align: right;
}

.ellipsis-button {
  border: 0;
  background: transparent;
  display: flex;
  gap: 6px;
  align-items: center;
  justify-content: flex-end;
  cursor: pointer;
}

.ellipsis-button span {
  width: 4px;
  height: 4px;
  border-radius: 50%;
  background: #9aa4b4;
  display: block;
}

.ellipsis-button:focus-visible {
  outline: 2px solid #1f9ae5;
  outline-offset: 2px;
}

@media (max-width: 960px) {
  .user-management-page {
    padding: 24px;
  }

  .global-search-pill {
    max-width: none;
  }

  .panel-heading {
    flex-direction: column;
    align-items: flex-start;
  }

  .panel-actions {
    width: 100%;
    justify-content: flex-start;
  }
}

@media (max-width: 640px) {
  .customer-panel {
    padding: 24px 20px;
  }

  th,
  td {
    padding: 12px 8px;
  }

  .primary-button,
  .ghost-button {
    width: 100%;
    justify-content: center;
  }

  .panel-actions {
    flex-direction: column;
  }
}
</style>
