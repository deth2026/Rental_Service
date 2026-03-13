<template>
  <div class="motoride-container">
    <header class="main-header">
      <div class="header-content">
        <div class="brand-wrap">
          <span class="brand-mark" aria-hidden="true"></span>
          <span class="brand-text">Moto Rental</span>
        </div>

        <nav class="top-nav" aria-label="Primary">
          <a href="#">Browse Motorcycles</a>
          <a href="#">My Bookings</a>
          <a href="#">Support</a>
        </nav>

        <div class="top-actions">
          <button class="icon-btn" type="button" aria-label="Notifications">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path
                d="M15 17h5l-1.4-1.4a2 2 0 01-.6-1.4V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M9 17a3 3 0 006 0"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
              />
            </svg>
          </button>
          <div class="avatar">JD</div>
        </div>
      </div>
    </header>

    <main class="content">
      <section class="image-gallery">
        <img
          src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=1200"
          alt="Honda CBR 650R"
          class="main-img"
        />
        <div class="gallery-controls">
          <div class="dots">
            <span></span><span class="active"></span><span></span>
          </div>
          <button class="view-all">View All Photos</button>
        </div>
      </section>

      <div class="grid-layout">
        <div class="details-col">
          <div class="title-section">
            <span class="badge">PREMIUM RENTAL</span>
            <h1>Honda CBR 650R</h1>
            <p class="meta">
              ⭐ 4.9 (128 reviews) • 📍 Downtown, San Francisco
            </p>
          </div>

          <div class="spec-cards">
            <div class="spec-card">
              <span class="icon">⚙️</span>
              <div>
                <small>Engine</small>
                <p>649cc</p>
              </div>
            </div>
            <div class="spec-card">
              <span class="icon">⚡</span>
              <div>
                <small>Power</small>
                <p>94 HP</p>
              </div>
            </div>
            <div class="spec-card">
              <span class="icon">⚖️</span>
              <div>
                <small>Weight</small>
                <p>208 kg</p>
              </div>
            </div>
          </div>

          <section class="description">
            <h3>Description</h3>
            <p>
              The Honda CBR 650R is a high-performance middleweight sports bike
              designed for both track-day thrills and weekend touring...
            </p>
          </section>

          <section class="rental-terms">
            <h3>Rental Terms</h3>
            <ul>
              <li>
                Must be 21+ years old with a valid motorcycle endorsement.
              </li>
              <li>$500 security deposit required (fully refundable).</li>
              <li>200 miles per day included ($0.50/mile after).</li>
            </ul>
          </section>
        </div>

        <aside class="booking-widget">
          <h3>Booking Details</h3>
          <div class="booking-row">
            <span>Pickup Date</span>
            <strong>Oct 24, 10:00 AM</strong>
          </div>
          <div class="booking-row">
            <span>Return Date</span>
            <strong>Oct 26, 10:00 AM</strong>
          </div>
          <hr />
          <div class="price-row">
            <span>$120 x 2 days</span>
            <span>$240.00</span>
          </div>
          <div class="price-row">
            <span>Insurance fee</span>
            <span>$30.00</span>
          </div>
          <div class="total-row">
            <span>Total Price</span>
            <span class="price-blue">$270.00</span>
          </div>
          <button class="book-btn"><router-link to="/booking" class="book-btn">
            Book This Bike
          </router-link></button>
          <p class="disclaimer">No payment taken until booking is confirmed</p>
        </aside>
      </div>
    </main>
  </div>
</template>

<script setup>
// Logic for handling booking or image switching can go here
</script>

<style scoped>
/* Modern Design System */
.motoride-container {
  font-family:
    -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue",
    Arial, sans-serif;
  color: #1a1d23;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  min-height: 100vh;
}

/* Enhanced Header */
.main-header {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  position: sticky;
  top: 0;
  z-index: 50;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1.25rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 3rem;
}

.brand-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.brand-mark {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  border-radius: 12px;
  position: relative;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
  transition: transform 0.3s ease;
}

.brand-mark:hover {
  transform: scale(1.05);
}

.brand-mark::before {
  content: "";
  position: absolute;
  inset: 10px;
  background: white;
  border-radius: 6px;
}

.brand-text {
  font-size: 1.5rem;
  font-weight: 800;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.top-nav {
  display: flex;
  align-items: center;
  gap: 2.5rem;
  flex: 1;
}

.top-nav a {
  color: #64748b;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  position: relative;
}

.top-nav a::after {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 0;
  height: 2px;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  transition: width 0.3s ease;
}

.top-nav a:hover {
  color: #00aeef;
}

.top-nav a:hover::after {
  width: 100%;
}

.top-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-shrink: 0;
}

.icon-btn {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  padding: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.icon-btn:hover {
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  border-color: transparent;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 174, 239, 0.3);
}

.icon-btn:hover svg {
  stroke: white;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
  transition: transform 0.3s ease;
  cursor: pointer;
}

.avatar:hover {
  transform: scale(1.05);
}

/* Main Content */
.content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* Enhanced Image Gallery */
.image-gallery {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  margin-bottom: 3rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
  background: white;
}

.main-img {
  width: 100%;
  height: 500px;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.image-gallery:hover .main-img {
  transform: scale(1.02);
}

.gallery-controls {
  position: absolute;
  bottom: 2rem;
  left: 2rem;
  right: 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dots {
  display: flex;
  gap: 0.5rem;
}

.dots span {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  transition: all 0.3s ease;
}

.dots span.active {
  width: 24px;
  border-radius: 4px;
  background: white;
}

.view-all {
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 12px;
  font-weight: 600;
  color: #1a1d23;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.view-all:hover {
  background: white;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Enhanced Grid Layout */
.grid-layout {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 3rem;
}

/* Title Section */
.title-section {
  margin-bottom: 2.5rem;
}

.badge {
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  display: inline-block;
  margin-bottom: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 12px rgba(0, 174, 239, 0.3);
}

.title-section h1 {
  font-size: 3rem;
  font-weight: 800;
  color: #1a1d23;
  margin: 0 0 1rem 0;
  line-height: 1.1;
}

.meta {
  font-size: 1.1rem;
  color: #64748b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Enhanced Spec Cards */
.spec-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
  margin: 2.5rem 0;
}

.spec-card {
  background: white;
  border: 1px solid #e2e8f0;
  padding: 2rem 1.5rem;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.spec-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
  border-color: #00aeef;
}

.spec-card .icon {
  font-size: 1.5rem;
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.spec-card small {
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: block;
  margin-bottom: 0.25rem;
}

.spec-card p {
  margin: 0;
  font-weight: 700;
  font-size: 1.1rem;
  color: #1a1d23;
}

/* Enhanced Sections */
.description,
.rental-terms {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.description h3,
.rental-terms h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 1.5rem 0;
  position: relative;
  padding-left: 1rem;
}

.description h3::before,
.rental-terms h3::before {
  content: "";
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 24px;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  border-radius: 2px;
}

.description p {
  font-size: 1.05rem;
  line-height: 1.7;
  color: #475569;
  margin: 0;
}

.rental-terms {
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

.rental-terms ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.rental-terms li {
  padding: 1rem 0;
  border-bottom: 1px solid rgba(229, 231, 235, 0.5);
  font-size: 1rem;
  color: #475569;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.rental-terms li::before {
  content: "✓";
  color: #00aeef;
  font-weight: 700;
  font-size: 1.2rem;
  flex-shrink: 0;
  margin-top: 0.1rem;
}

.rental-terms li:last-child {
  border-bottom: none;
}

/* Enhanced Booking Widget */
.booking-widget {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 24px;
  padding: 2.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
  height: fit-content;
  position: sticky;
  top: 2rem;
  transition: all 0.3s ease;
}

.booking-widget:hover {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}

.booking-widget h3 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1d23;
  margin: 0 0 2rem 0;
  text-align: center;
  position: relative;
}

.booking-widget h3::after {
  content: "";
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  border-radius: 2px;
}

.booking-row,
.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 0;
  font-size: 1rem;
  border-bottom: 1px solid #f1f5f9;
}

.booking-row span,
.price-row span {
  color: #64748b;
  font-weight: 500;
}

.booking-row strong,
.price-row span:last-child {
  color: #1a1d23;
  font-weight: 700;
  font-size: 1.05rem;
}

.total-row {
  border-bottom: none;
  border-top: 2px solid #e2e8f0;
  padding-top: 1.5rem;
  margin-top: 0.5rem;
}

.price-blue {
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8rem;
  font-weight: 800;
}

.book-btn {
  width: 100%;
  background: linear-gradient(135deg, #00aeef 0%, #0078a8 100%);
  color: white;
  border: none;
  padding: 1.25rem;
  border-radius: 16px;
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  margin-top: 2rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(0, 174, 239, 0.3);
  position: relative;
  overflow: hidden;
}

.book-btn::before {
  content: "";
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.2),
    transparent
  );
  transition: left 0.5s ease;
}

.book-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 35px rgba(0, 174, 239, 0.4);
}

.book-btn:hover::before {
  left: 100%;
}

.disclaimer {
  text-align: center;
  color: #64748b;
  font-size: 0.9rem;
  margin-top: 1rem;
  font-style: italic;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .grid-layout {
    grid-template-columns: 1fr;
    gap: 2rem;
  }

  .booking-widget {
    position: static;
  }
}

@media (max-width: 768px) {
  .header-content {
    padding: 1rem;
    gap: 1rem;
  }

  .top-nav {
    display: none;
  }

  .content {
    padding: 1rem;
  }

  .title-section h1 {
    font-size: 2rem;
  }

  .spec-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .description,
  .rental-terms,
  .booking-widget {
    padding: 1.5rem;
  }

  .main-img {
    height: 300px;
  }
}

@media (max-width: 480px) {
  .brand-text {
    font-size: 1.2rem;
  }

  .title-section h1 {
    font-size: 1.5rem;
  }

  .gallery-controls {
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
  }

  .view-all {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
}
</style>
