<script setup>
import { ref, computed, onMounted } from 'vue'
import { feedbackApi } from '@/services/api'

const loading = ref(true)
const error = ref(null)

// Fetch feedback from database
const fetchFeedback = async () => {
  try {
    loading.value = true
    const response = await feedbackApi.getAll()
    const data = response.data.data || response.data || []
    
    // Map database fields to expected format
    feedback.value = data.map(f => ({
      id: f.id,
      customer: f.user_id || 'Anonymous',
      date: f.created_at ? new Date(f.created_at).toLocaleDateString('en-GB') : '',
      rating: f.rating || 0,
      comment: f.message || f.subject || '',
      reply: f.admin_reply || ''
    }))
  } catch (err) {
    console.error('Error fetching feedback:', err)
    error.value = 'Failed to load feedback data'
  } finally {
    loading.value = false
  }
}

// Fetch on mount
onMounted(() => {
  fetchFeedback()
})

// Empty feedback data - connected to feedback table
const feedback = ref([])

const newReply = ref('')
const selected = ref(null)

// Calculate average rating
const avg = computed(() => {
  if (!feedback.value.length) return 0
  return (feedback.value.reduce((a, b) => a + b.rating, 0) / feedback.value.length).toFixed(1)
})

const openReply = (f) => { 
  selected.value = f
  newReply.value = f.reply || '' 
}

const closeReply = () => { 
  selected.value = null
  newReply.value = ''
}

const saveReply = () => { 
  if (!selected.value) return
  selected.value.reply = newReply.value
  selected.value = null
}

// Generate stars array for rendering
const getStars = (rating) => {
  return Array(5).fill(0).map((_, i) => i < rating)
}
</script>

<template>
  <div class="reviews-container">
    <!-- Page Title -->
    <h1 class="page-title">Reviews & Feedback</h1>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading reviews...</p>
    </div>
    
    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <button class="retry-btn" @click="fetchFeedback">Retry</button>
    </div>

    <!-- Reviews List -->
    <div v-else class="reviews-list">
      <!-- Empty State -->
      <div v-if="feedback.length === 0" class="empty-state">
        <div class="empty-content">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
          <p>No reviews yet</p>
        </div>
      </div>

      <!-- Review Cards -->
      <div v-else v-for="f in feedback" :key="f.id" class="review-card">
        <div class="review-header">
          <span class="customer-name">{{ f.customer }}</span>
          <span class="review-date">{{ f.date }}</span>
        </div>
        
        <div class="star-rating">
          <span v-for="(filled, index) in getStars(f.rating)" :key="index" class="star" :class="{ filled: filled }">
            ★
          </span>
        </div>
        
        <p class="review-comment">{{ f.comment }}</p>
        
        <div v-if="f.reply" class="admin-reply">
          <span class="reply-label">Admin Reply:</span>
          <span class="reply-text">{{ f.reply }}</span>
        </div>
        
        <button class="reply-btn" @click="openReply(f)">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
          </svg>
          Reply
        </button>
      </div>
    </div>

    <!-- Reply Modal -->
    <div v-if="selected" class="modal-overlay" @click.self="closeReply">
      <div class="modal-content">
        <div class="modal-header">
          <h2>Reply to {{ selected.customer }}</h2>
          <button class="close-btn" @click="closeReply">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="original-review">
            <div class="star-rating small">
              <span v-for="(filled, index) in getStars(selected.rating)" :key="index" class="star" :class="{ filled: filled }">
                ★
              </span>
            </div>
            <p class="review-text">{{ selected.comment }}</p>
          </div>
          
          <div class="reply-form">
            <label for="reply">Your Reply</label>
            <textarea id="reply" v-model="newReply" placeholder="Write your reply here..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="cancel-btn" @click="closeReply">Cancel</button>
          <button class="save-btn" @click="saveReply">Save Reply</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Container */
.reviews-container {
  padding: 20px;
  /* max-width: 900px; */
  margin: 0 auto;
}

/* Page Title */
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 24px;
  text-align: left;
}

/* Reviews List */
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Review Card */
.review-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 24px;
  transition: box-shadow 0.2s;
}

.review-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

/* Review Header */
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.customer-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 1rem;
}

.review-date {
  color: #64748b;
  font-size: 0.875rem;
}

/* Star Rating */
.star-rating {
  display: flex;
  gap: 2px;
  margin-bottom: 12px;
}

.star-rating.small {
  margin-bottom: 8px;
}

.star {
  font-size: 1.25rem;
  color: #d1d5db;
}

.star.filled {
  color: #f59e0b;
}

/* Review Comment */
.review-comment {
  color: #475569;
  font-size: 0.9375rem;
  line-height: 1.6;
  margin: 0;
}

/* Admin Reply */
.admin-reply {
  margin-top: 12px;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 3px solid #3b82f6;
}

.reply-label {
  font-weight: 600;
  color: #3b82f6;
  font-size: 0.8125rem;
  display: block;
  margin-bottom: 4px;
}

.reply-text {
  color: #475569;
  font-size: 0.875rem;
}

/* Reply Button */
.reply-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 12px;
  padding: 8px 14px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #64748b;
  font-size: 0.8125rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.reply-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #3b82f6;
}

.reply-btn svg {
  width: 14px;
  height: 14px;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error State */
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
  color: #dc2626;
}

.retry-btn {
  margin-top: 12px;
  padding: 10px 20px;
  border: 1px solid #dc2626;
  border-radius: 8px;
  background: #fff;
  color: #dc2626;
  font-size: 0.875rem;
  cursor: pointer;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  color: #94a3b8;
}

.empty-content svg {
  width: 48px;
  height: 48px;
  opacity: 0.5;
}

.empty-content p {
  margin: 0;
  font-size: 0.9375rem;
}

/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 1000;
}

/* Modal Content */
.modal-content {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Modal Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.close-btn {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s;
}

.close-btn:hover {
  background: #f1f5f9;
  color: #1e293b;
}

.close-btn svg {
  width: 20px;
  height: 20px;
}

/* Modal Body */
.modal-body {
  padding: 24px;
  overflow-y: auto;
}

.original-review {
  padding: 16px;
  background: #f8fafc;
  border-radius: 8px;
  margin-bottom: 20px;
}

.review-text {
  color: #475569;
  font-size: 0.875rem;
  margin: 8px 0 0;
}

.reply-form label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
  margin-bottom: 8px;
}

.reply-form textarea {
  width: 100%;
  min-height: 120px;
  padding: 12px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.875rem;
  color: #1e293b;
  resize: vertical;
}

.reply-form textarea:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Modal Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
}

.cancel-btn {
  padding: 10px 20px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.cancel-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.save-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  background: #1e293b;
  color: #fff;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}

.save-btn:hover {
  background: #334155;
}
</style>
