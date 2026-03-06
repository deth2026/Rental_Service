import axios from 'axios'

// Use relative URL to go through Vite proxy (configured in vite.config.js)
const API_BASE = ''
const STORAGE_BASE = 'http://localhost:8000/storage'

const http = axios.create({
  baseURL: `${API_BASE}/api`,
  headers: { 'Accept': 'application/json' },
})

const userService = {
  /**
   * Fetch a single user's profile.
   * @param {number} id
   */
  async getUser(id) {
    const { data } = await http.get(`/users/${id}`)
    return data
  },

  /**
   * Update profile fields + optional profile picture.
   * @param {number} id
   * @param {FormData} formData  (name, email, phone, profile_picture?)
   */
  async updateProfile(id, formData) {
    const { data } = await http.post(`/users/${id}/update-profile`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return data
  },

  /**
   * Change password with secure validation.
   * @param {number} id
   * @param {{ current_password, new_password, new_password_confirmation }} payload
   */
  async changePassword(id, payload) {
    const { data } = await http.post(`/users/${id}/change-password`, payload)
    return data
  },

  /**
   * Build the full URL for a stored profile picture.
   * @param {string|null} path  e.g. "profile_pictures/abc.jpg"
   * @returns {string|null}
   */
  getAvatarUrl(path) {
    if (!path) return null
    return `${STORAGE_BASE}/${path}`
  },
}

export default userService
