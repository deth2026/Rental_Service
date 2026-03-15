<script setup>
import { ref, computed, onMounted } from 'vue'
import { feedbackApi } from '@/services/api'
import '../../css/Feedback.css'

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


