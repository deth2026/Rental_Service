<template>
  <footer class="site-footer">
    <div class="footer-wrap">
      <div class="footer-top">
        <div class="footer-brand">
          <div class="footer-brand-title">CHONG CHOUL</div>
          <div class="footer-brand-slogan">Secure rides, fast bookings.</div>

          <div class="footer-social">
            <button class="btn-reset social-btn" type="button">F</button>
            <button class="btn-reset social-btn" type="button">T</button>
            <button class="btn-reset social-btn" type="button">L</button>
            <button class="btn-reset social-btn" type="button">W</button>
            <button class="btn-reset social-btn" type="button">I</button>
          </div>
          <div class="footer-social-label">Follow Us</div>
        </div>

        <div class="footer-nav">
          <div class="footer-nav-links">
            <button class="btn-reset footer-nav-link" type="button">About</button>
            <button class="btn-reset footer-nav-link" type="button">Blog</button>
            <button class="btn-reset footer-nav-link" type="button">Menu</button>
            <button class="btn-reset footer-nav-link" type="button">Services</button>
            <button class="btn-reset footer-nav-link" type="button">FAQ</button>
            <button class="btn-reset footer-nav-link" type="button">Support</button>
          </div>

          <div class="footer-about">
            <div class="footer-section-title">About Us</div>
            <p>
              Secure, fast, and transparent bookings across Cambodia with verified partners.
            </p>
          </div>
        </div>

        <div class="footer-contact">
          <div class="footer-contact-row">
            <div class="footer-contact-label">Call :</div>
            <div class="footer-contact-value">+0123 456 789 00</div>
          </div>
          <div class="footer-contact-row">
            <div class="footer-contact-label">Email:</div>
            <div class="footer-contact-value">user@example.com</div>
          </div>

          <div class="footer-newsletter">
            <input class="footer-input" type="text" placeholder="Write Email" />
            <button class="btn-reset footer-send" type="button">➤</button>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <span>© 2026 Chong Choul. All rights reserved.</span>
        <div class="footer-bottom-links">
          <span>Security</span>
          <span>Accessibility</span>
          <span>Legal</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Footer Styles */
.site-footer {
  margin-top: 28px;
  border-top: 1px solid #d8dee7;
  background: #ffffff;
  color: #0f172a;
}

.footer-wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 22px 2rem 0;
}

.footer-top {
  display: grid;
  grid-template-columns: 1fr 1.6fr 1fr;
  gap: 26px;
  padding: 20px 0 22px;
  border-bottom: 1px solid #e6edf6;
  align-items: start;
}

.footer-brand-title {
  font-size: 1.15rem;
  font-weight: 900;
  letter-spacing: 0.01em;
  color: #0f172a;
}

.footer-brand-slogan {
  margin-top: 6px;
  font-size: 0.88rem;
  font-weight: 700;
  color: #3b82f6;
}

.footer-social {
  margin-top: 14px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.social-btn {
  width: 30px;
  height: 30px;
  border-radius: 999px;
  border: 1px solid #cfd9e6;
  color: #334155;
  font-weight: 800;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.social-btn:first-child {
  border-color: #3b82f6;
  background: #3b82f6;
  color: #ffffff;
}

.social-btn:hover {
  background: #3b82f6;
  border-color: #3b82f6;
  color: #ffffff;
}

.footer-social-label {
  margin-top: 10px;
  font-size: 0.9rem;
  font-weight: 800;
  color: #0f172a;
}

.footer-nav-links {
  display: flex;
  flex-wrap: wrap;
  gap: 18px;
  justify-content: center;
}

.footer-nav-link {
  font-size: 0.9rem;
  font-weight: 800;
  color: #475569;
  transition: color 0.2s ease;
}

.footer-nav-link:first-child {
  color: #3b82f6;
}

.footer-nav-link:hover {
  color: #3b82f6;
}

.footer-about {
  margin-top: 18px;
  padding-top: 18px;
  border-top: 1px solid #e6edf6;
}

.footer-section-title {
  font-size: 1rem;
  font-weight: 900;
  color: #0f172a;
  text-align: center;
}

.footer-about p {
  margin: 10px auto 0;
  max-width: 520px;
  text-align: center;
  color: #52627b;
  line-height: 1.7;
  font-size: 0.92rem;
}

.footer-contact {
  border-left: 1px solid #e6edf6;
  padding-left: 22px;
}

.footer-contact-row {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 10px;
  margin-bottom: 10px;
  align-items: baseline;
}

.footer-contact-label {
  font-weight: 900;
  color: #0f172a;
  font-size: 0.88rem;
}

.footer-contact-value {
  font-weight: 700;
  color: #52627b;
  font-size: 0.88rem;
}

.footer-newsletter {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr auto;
  border: 1px solid #d8e2f0;
  border-radius: 10px;
  overflow: hidden;
  background: #ffffff;
}

.footer-input {
  border: 0;
  background: transparent;
  color: #0f172a;
  height: 36px;
  padding: 0 12px;
  font-size: 0.88rem;
  outline: none;
}

.footer-input::placeholder {
  color: #94a3b8;
}

.footer-send {
  width: 40px;
  background: #3b82f6;
  color: #ffffff;
  font-weight: 900;
}

.footer-bottom {
  margin: 0;
  padding: 14px 0 22px;
  border-top: 0;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  color: #75839b;
  font-size: 0.85rem;
  flex-wrap: wrap;
}

.footer-bottom-links {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.footer-bottom-links span {
  color: #475569;
  font-weight: 800;
  font-size: 0.82rem;
  padding: 6px 10px;
  border-radius: 999px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  cursor: pointer;
  transition:
    background 0.18s ease,
    border-color 0.18s ease,
    color 0.18s ease;
}

.footer-bottom-links span:hover {
  color: #0f172a;
  border-color: #3b82f6;
  background: #eff6ff;
}

/* Responsive Design */
@media (max-width: 768px) {
  .footer-top {
    grid-template-columns: 1fr;
    gap: 20px;
    text-align: center;
  }

  .footer-contact {
    border-left: none;
    border-top: 1px solid #e6edf6;
    padding-left: 0;
    padding-top: 20px;
  }

  .footer-nav-links {
    justify-content: center;
  }

  .footer-bottom {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }

  .footer-bottom-links {
    justify-content: center;
  }
}
</style>
