<script>
export default {
  name: 'vehicle-detail',
  data() {
    return {
      vehicle: null,
      loading: true,
      error: null
    }
  },
  async mounted() {
    await this.fetchVehicle()
  },
  methods: {
    async fetchVehicle() {
      try {
        const vehicleId = this.$route.params.id
        const response = await fetch(`http://localhost:8000/api/vehicles/${vehicleId}`, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        
        if (!response.ok) {
          throw new Error('Failed to fetch vehicle')
        }
        
        this.vehicle = await response.json()
      } catch (error) {
        this.error = error.message
        // Use demo data if API is not available
        this.vehicle = {
          id: 1,
          type: 'Motorbike',
          brand: 'Yamaha',
          model: 'NMAX',
          year: 2024,
          price_per_day: 25,
          fuel_type: 'Petrol',
          transmission: 'Automatic',
          status: 'available',
          image_url: 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=800'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<template>
  <div class="vehicle-detail-container">
    <nav class="navbar">
      <div class="nav-logo">
        <div class="logo-icon"></div>
        <span class="logo-text">ChongChoul</span>
      </div>
      <div class="nav-links">
        <a href="#">How it works</a>
        <a href="#">Vehicles</a>
        <a href="#">Top Shops</a>
        <a href="#" class="partner-link">Become a Partner</a>
      </div>
      <div class="nav-auth">
        <button class="btn-login"><a href="/login">Login</a></button>
        <button class="btn-signup"><a href="/register">Sign Up</a></button>
      </div>
    </nav>

    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading vehicle details...</p>
    </div>

    <div v-else-if="error" class="error-container">
      <p>{{ error }}</p>
      <button @click="fetchVehicle" class="retry-btn">Retry</button>
    </div>

    <div v-else class="vehicle-content">
      <div class="vehicle-image-section">
        <img 
          :src="vehicle.image_url || 'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=800'" 
          :alt="vehicle.model" 
          class="vehicle-image"
        />
        <div class="status-badge" :class="vehicle.status">
          {{ vehicle.status }}
        </div>
      </div>

      <div class="vehicle-info-section">
        <div class="vehicle-header">
          <h1>{{ vehicle.brand }} {{ vehicle.model }}</h1>
          <p class="vehicle-type">{{ vehicle.type }}</p>
        </div>

        <div class="vehicle-details">
          <div class="detail-item">
            <span class="detail-label">Year</span>
            <span class="detail-value">{{ vehicle.year }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Fuel Type</span>
            <span class="detail-value">{{ vehicle.fuel_type }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Transmission</span>
            <span class="detail-value">{{ vehicle.transmission }}</span>
          </div>
        </div>

        <div class="vehicle-price">
          <span class="price-label">Price per day</span>
          <span class="price-value">${{ vehicle.price_per_day }}</span>
        </div>

        <div class="vehicle-actions">
          <button class="btn-rent-now">Rent Now</button>
          <button class="btn-contact-shop">Contact Shop</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.vehicle-detail-container {
  min-height: 100vh;
  background: #f8f9fa;
}

.navbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.nav-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.logo-icon {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1a1a2e;
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-links a {
  text-decoration: none;
  color: #4a4a68;
  font-weight: 500;
}

.partner-link {
  color: #667eea !important;
}

.nav-auth {
  display: flex;
  gap: 1rem;
}

.btn-login, .btn-signup {
  padding: 0.5rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-login {
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
}

.btn-login:hover {
  background: #667eea;
  color: white;
}

.btn-signup {
  background: #667eea;
  border: 2px solid #667eea;
  color: white;
}

.btn-signup:hover {
  background: #5568d3;
  border-color: #5568d3;
}

.loading-container, .error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 60vh;
  gap: 1rem;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.vehicle-content {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  max-width: 1200px;
  margin: 2rem auto;
  padding: 0 2rem;
}

.vehicle-image-section {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  background: white;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.vehicle-image {
  width: 100%;
  height: 400px;
  object-fit: cover;
}

.status-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
}

.status-badge.available {
  background: #d4edda;
  color: #155724;
}

.status-badge.rented {
  background: #fff3cd;
  color: #856404;
}

.status-badge.maintenance {
  background: #f8d7da;
  color: #721c24;
}

.vehicle-info-section {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.vehicle-header h1 {
  font-size: 2rem;
  color: #1a1a2e;
  margin: 0 0 0.5rem 0;
}

.vehicle-type {
  color: #667eea;
  font-weight: 600;
  font-size: 1rem;
  margin: 0;
}

.vehicle-details {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin: 2rem 0;
  padding: 1.5rem 0;
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-label {
  font-size: 0.875rem;
  color: #888;
}

.detail-value {
  font-size: 1rem;
  font-weight: 600;
  color: #1a1a2e;
}

.vehicle-price {
  margin: 2rem 0;
}

.price-label {
  display: block;
  font-size: 0.875rem;
  color: #888;
  margin-bottom: 0.25rem;
}

.price-value {
  font-size: 2rem;
  font-weight: 700;
  color: #667eea;
}

.vehicle-actions {
  display: flex;
  gap: 1rem;
}

.btn-rent-now, .btn-contact-shop {
  flex: 1;
  padding: 1rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-rent-now {
  background: #667eea;
  border: none;
  color: white;
}

.btn-rent-now:hover {
  background: #5568d3;
}

.btn-contact-shop {
  background: transparent;
  border: 2px solid #667eea;
  color: #667eea;
}

.btn-contact-shop:hover {
  background: #667eea;
  color: white;
}

.retry-btn {
  padding: 0.5rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

@media (max-width: 768px) {
  .vehicle-content {
    grid-template-columns: 1fr;
  }

  .vehicle-image {
    height: 250px;
  }

  .vehicle-details {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
