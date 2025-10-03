import { useState, useEffect } from 'react'
import './AdminManagement.css'
import AdminForm from './AdminForm'
import { fetchAdmins as fetchAdminsAPI, deleteAdmin as deleteAdminAPI } from '../utils/api'

function AdminManagement({ onBack }) {
  const [showAdminForm, setShowAdminForm] = useState(false)
  const [admins, setAdmins] = useState([])
  const [isLoading, setIsLoading] = useState(true)

  useEffect(() => {
    fetchAdmins()
  }, [])

  const fetchAdmins = async () => {
    setIsLoading(true)
    try {
      const response = await fetchAdminsAPI()
      if (response.success) {
        setAdmins(response.data || [])
      }
    } catch (error) {
      console.error('Error fetching admins:', error)
      alert(error.message || 'Failed to fetch admins')
    } finally {
      setIsLoading(false)
    }
  }

  const handleDelete = async (adminId, username) => {
    if (window.confirm(`Are you sure you want to delete admin "${username}"?`)) {
      try {
        const response = await deleteAdminAPI(adminId)
        if (response.success) {
          alert('Admin deleted successfully')
          fetchAdmins()
        }
      } catch (error) {
        alert(error.message || 'Failed to delete admin')
      }
    }
  }

  const handleAddAdmin = () => {
    setShowAdminForm(true)
  }

  const handleAdminFormBack = (shouldRefresh) => {
    setShowAdminForm(false)
    if (shouldRefresh) {
      fetchAdmins()
    }
  }

  const getRoleBadgeClass = (role) => {
    if (role === 'super_admin') return 'role-badge super-admin'
    if (role === 'editor') return 'role-badge editor'
    return 'role-badge admin'
  }

  const getRoleText = (role) => {
    if (role === 'super_admin') return 'Super Admin'
    if (role === 'editor') return 'Editor'
    return 'Admin'
  }

  if (showAdminForm) {
    return <AdminForm onBack={handleAdminFormBack} />
  }

  return (
    <div className="admin-management-container">
      <header className="admin-management-header">
        <button className="back-button" onClick={onBack}>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/>
          </svg>
        </button>
        <h1>Manage Admins</h1>
      </header>

      <div className="admin-management-content">
        {isLoading ? (
          <div className="loading-state">
            <div className="spinner"></div>
          </div>
        ) : admins.length === 0 ? (
          <div className="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
              <path d="M16 11C17.66 11 18.99 9.66 18.99 8C18.99 6.34 17.66 5 16 5C14.34 5 13 6.34 13 8C13 9.66 14.34 11 16 11ZM8 11C9.66 11 10.99 9.66 10.99 8C10.99 6.34 9.66 5 8 5C6.34 5 5 6.34 5 8C5 9.66 6.34 11 8 11ZM8 13C5.67 13 1 14.17 1 16.5V19H15V16.5C15 14.17 10.33 13 8 13ZM16 13C15.71 13 15.38 13.02 15.03 13.05C16.19 13.89 17 15.02 17 16.5V19H23V16.5C23 14.17 18.33 13 16 13Z" fill="currentColor"/>
            </svg>
            <h3>No admins found</h3>
          </div>
        ) : (
          <div className="admins-list">
            {admins.map((admin) => (
              <div key={admin.id} className="admin-card">
                <div className="admin-card-header">
                  <div className="admin-info">
                    <h3>{admin.username || 'Unknown'}</h3>
                    <p>{admin.email || ''}</p>
                  </div>
                  <button
                    className="delete-button"
                    onClick={() => handleDelete(admin.id, admin.username)}
                  >
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                      <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="currentColor"/>
                    </svg>
                  </button>
                </div>
                <div className="admin-card-footer">
                  <span className={getRoleBadgeClass(admin.role)}>
                    {getRoleText(admin.role)}
                  </span>
                  <div className="meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                      <path d="M11.99 2C6.47 2 2 6.48 2 12C2 17.52 6.47 22 11.99 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 11.99 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM12.5 7H11V13L16.25 16.15L17 14.92L12.5 12.25V7Z" fill="currentColor"/>
                    </svg>
                    <span>
                      {admin.created_at
                        ? new Date(admin.created_at).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                          })
                        : 'Unknown'}
                    </span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      <button className="fab" onClick={handleAddAdmin}>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="white"/>
        </svg>
      </button>
    </div>
  )
}

export default AdminManagement
