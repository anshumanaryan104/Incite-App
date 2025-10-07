import { useState, useEffect } from 'react'
import './App.css'
import Dashboard from './components/Dashboard'
import { adminLogin, saveAdminInfo, getAdminInfo, clearAdminInfo } from './utils/api'

function App() {
  const [isLoggedIn, setIsLoggedIn] = useState(false)
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    // Check if user is already logged in
    const { token } = getAdminInfo()
    if (token) {
      setIsLoggedIn(true)
    }
  }, [])

  const handleLogin = async (e) => {
    e.preventDefault()
    setError('')

    if (!username || !password) {
      setError('Username and password are required')
      return
    }

    setLoading(true)

    try {
      const response = await adminLogin(username, password)

      if (response.success) {
        // Save admin info to localStorage
        saveAdminInfo(response.data.token, response.data.admin)
        setIsLoggedIn(true)
      } else {
        setError(response.message || 'Login failed')
      }
    } catch (error) {
      setError(error.message || 'Something went wrong')
    } finally {
      setLoading(false)
    }
  }

  const handleLogout = () => {
    clearAdminInfo()
    setIsLoggedIn(false)
    setUsername('')
    setPassword('')
    setError('')
  }

  if (isLoggedIn) {
    return <Dashboard onLogout={handleLogout} />
  }

  return (
    <div className="app-container">
      <div className="login-container">
        <div className="logo-container">
          <h1 className="logo">Polymath</h1>
        </div>

        <h2 className="sign-in-title">Sign in</h2>

        <form onSubmit={handleLogin} className="login-form">
          <div className="input-wrapper">
            <div className="input-icon">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M10 10C12.7614 10 15 7.76142 15 5C15 2.23858 12.7614 0 10 0C7.23858 0 5 2.23858 5 5C5 7.76142 7.23858 10 10 10Z" fill="#999"/>
                <path d="M10 12C5.58172 12 2 15.5817 2 20H18C18 15.5817 14.4183 12 10 12Z" fill="#999"/>
              </svg>
            </div>
            <input
              type="text"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              placeholder="Username"
              className="form-input"
              disabled={loading}
            />
          </div>

          <div className="input-wrapper">
            <div className="input-icon">
              <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M15 7H14V5C14 2.24 11.76 0 9 0C6.24 0 4 2.24 4 5V7H3C1.9 7 1 7.9 1 9V18C1 19.1 1.9 20 3 20H15C16.1 20 17 19.1 17 18V9C17 7.9 16.1 7 15 7ZM9 15C7.9 15 7 14.1 7 13C7 11.9 7.9 11 9 11C10.1 11 11 11.9 11 13C11 14.1 10.1 15 9 15ZM12 7H6V5C6 3.34 7.34 2 9 2C10.66 2 12 3.34 12 5V7Z" fill="#999"/>
              </svg>
            </div>
            <input
              type={showPassword ? "text" : "password"}
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="Password"
              className="form-input"
              disabled={loading}
            />
            <button
              type="button"
              className="eye-button"
              onClick={() => setShowPassword(!showPassword)}
            >
              {showPassword ? (
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M12 7C14.76 7 17 9.24 17 12C17 12.65 16.87 13.26 16.64 13.83L19.56 16.75C21.07 15.49 22.26 13.86 22.99 12C21.26 7.61 16.99 4.5 11.99 4.5C10.59 4.5 9.25 4.75 8.01 5.2L10.17 7.36C10.74 7.13 11.35 7 12 7ZM2 4.27L4.28 6.55L4.74 7.01C3.08 8.3 1.78 10.02 1 12C2.73 16.39 7 19.5 12 19.5C13.55 19.5 15.03 19.2 16.38 18.66L16.8 19.08L19.73 22L21 20.73L3.27 3M12 17C9.24 17 7 14.76 7 12C7 11.18 7.19 10.41 7.54 9.72L9.06 11.24C9.03 11.49 9 11.74 9 12C9 13.66 10.34 15 12 15C12.26 15 12.51 14.97 12.76 14.94L14.28 16.46C13.59 16.81 12.82 17 12 17ZM14.97 11.17C14.82 9.77 13.72 8.68 12.33 8.53L14.97 11.17Z" fill="white"/>
                </svg>
              ) : (
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                  <path d="M12 4.5C7 4.5 2.73 7.61 1 12C2.73 16.39 7 19.5 12 19.5C17 19.5 21.27 16.39 23 12C21.27 7.61 17 4.5 12 4.5ZM12 17C9.24 17 7 14.76 7 12C7 9.24 9.24 7 12 7C14.76 7 17 9.24 17 12C17 14.76 14.76 17 12 17ZM12 9C10.34 9 9 10.34 9 12C9 13.66 10.34 15 12 15C13.66 15 15 13.66 15 12C15 10.34 13.66 9 12 9Z" fill="white"/>
                </svg>
              )}
            </button>
          </div>

          {error && <div className="error-message">{error}</div>}

          <button
            type="submit"
            className="sign-in-button"
            disabled={loading}
          >
            {loading ? 'Signing in...' : 'Sign in'}
          </button>
        </form>

        <div className="admin-footer">
          <p>Admin and Super Admin access only</p>
        </div>
      </div>
    </div>
  )
}

export default App
