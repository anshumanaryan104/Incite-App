// Production AWS EC2 Backend
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://15.206.148.126:3000/api'

// Get token from localStorage
const getToken = () => {
  return localStorage.getItem('admin_token')
}

// Get admin info from localStorage
export const getAdminInfo = () => {
  const token = localStorage.getItem('admin_token')
  const username = localStorage.getItem('admin_username')
  const role = localStorage.getItem('admin_role')
  const email = localStorage.getItem('admin_email')

  return { token, username, role, email }
}

// Save admin info to localStorage
export const saveAdminInfo = (token, admin) => {
  localStorage.setItem('admin_token', token)
  localStorage.setItem('admin_username', admin.username)
  localStorage.setItem('admin_role', admin.role)
  localStorage.setItem('admin_email', admin.email)
}

// Clear admin info from localStorage
export const clearAdminInfo = () => {
  localStorage.removeItem('admin_token')
  localStorage.removeItem('admin_username')
  localStorage.removeItem('admin_role')
  localStorage.removeItem('admin_email')
}

// Generic API call function
const apiCall = async (endpoint, options = {}) => {
  const token = getToken()

  const headers = {
    'Content-Type': 'application/json',
    ...options.headers
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  try {
    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
      ...options,
      headers
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Something went wrong')
    }

    return data
  } catch (error) {
    throw error
  }
}

// Auth APIs
export const adminLogin = async (username, password) => {
  return apiCall('/admin/login', {
    method: 'POST',
    body: JSON.stringify({ username, password })
  })
}

// Article APIs
export const fetchArticles = async (page = 1, limit = 50) => {
  return apiCall(`/admin/articles?page=${page}&limit=${limit}`, {
    method: 'GET'
  })
}

export const createArticle = async (articleData) => {
  return apiCall('/admin/articles', {
    method: 'POST',
    body: JSON.stringify(articleData)
  })
}

export const updateArticle = async (id, articleData) => {
  return apiCall(`/admin/articles/${id}`, {
    method: 'PUT',
    body: JSON.stringify(articleData)
  })
}

export const deleteArticle = async (id) => {
  return apiCall(`/admin/articles/${id}`, {
    method: 'DELETE'
  })
}

// Admin Management APIs
export const fetchAdmins = async () => {
  return apiCall('/admin/list', {
    method: 'GET'
  })
}

export const createAdmin = async (adminData) => {
  return apiCall('/admin/create-admin', {
    method: 'POST',
    body: JSON.stringify(adminData)
  })
}

export const deleteAdmin = async (id) => {
  return apiCall(`/admin/${id}`, {
    method: 'DELETE'
  })
}
