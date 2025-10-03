import { useState, useEffect } from 'react'
import './Dashboard.css'
import AdminManagement from './AdminManagement'
import ArticleForm from './ArticleForm'
import { fetchArticles as fetchArticlesAPI, deleteArticle as deleteArticleAPI, getAdminInfo } from '../utils/api'

function Dashboard({ onLogout }) {
  const [showAdminManagement, setShowAdminManagement] = useState(false)
  const [showArticleForm, setShowArticleForm] = useState(false)
  const [editingArticle, setEditingArticle] = useState(null)
  const [articles, setArticles] = useState([])
  const [filteredArticles, setFilteredArticles] = useState([])
  const [searchQuery, setSearchQuery] = useState('')
  const [isLoading, setIsLoading] = useState(true)
  const [adminRole, setAdminRole] = useState('')

  useEffect(() => {
    const { role } = getAdminInfo()
    setAdminRole(role)
  }, [])

  useEffect(() => {
    fetchArticles()
  }, [])

  useEffect(() => {
    filterArticles()
  }, [searchQuery, articles])

  const fetchArticles = async () => {
    setIsLoading(true)
    try {
      const response = await fetchArticlesAPI()
      if (response.success) {
        setArticles(response.data.articles || [])
        setFilteredArticles(response.data.articles || [])
      }
    } catch (error) {
      console.error('Error fetching articles:', error)
      alert(error.message || 'Failed to fetch articles')
    } finally {
      setIsLoading(false)
    }
  }

  const filterArticles = () => {
    const query = searchQuery.toLowerCase()
    const filtered = articles.filter(article => {
      const title = (article.title || '').toLowerCase()
      const description = (article.description || '').toLowerCase()
      return query === '' || title.includes(query) || description.includes(query)
    })
    setFilteredArticles(filtered)
  }

  const handleDelete = async (articleId, title) => {
    if (window.confirm(`Are you sure you want to delete "${title}"?`)) {
      try {
        const response = await deleteArticleAPI(articleId)
        if (response.success) {
          alert('Article deleted successfully')
          fetchArticles()
        }
      } catch (error) {
        alert(error.message || 'Failed to delete article')
      }
    }
  }

  const handleEdit = (article) => {
    setEditingArticle(article)
    setShowArticleForm(true)
  }

  const handleAddArticle = () => {
    setEditingArticle(null)
    setShowArticleForm(true)
  }

  const handleArticleFormBack = (shouldRefresh) => {
    setShowArticleForm(false)
    setEditingArticle(null)
    if (shouldRefresh) {
      fetchArticles()
    }
  }

  const handleManageAdmins = () => {
    setShowAdminManagement(true)
  }

  const handleLogout = () => {
    if (window.confirm('Are you sure you want to logout?')) {
      onLogout()
    }
  }

  if (showArticleForm) {
    return (
      <ArticleForm
        article={editingArticle}
        isEdit={!!editingArticle}
        onBack={handleArticleFormBack}
      />
    )
  }

  if (showAdminManagement) {
    return <AdminManagement onBack={() => setShowAdminManagement(false)} />
  }

  return (
    <div className="dashboard-container">
      <header className="dashboard-header">
        <h1>Admin Dashboard</h1>
        <div className="header-actions">
          {adminRole === 'super_admin' && (
            <button className="icon-button" onClick={handleManageAdmins} title="Manage Admins">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M16 11C17.66 11 18.99 9.66 18.99 8C18.99 6.34 17.66 5 16 5C14.34 5 13 6.34 13 8C13 9.66 14.34 11 16 11ZM8 11C9.66 11 10.99 9.66 10.99 8C10.99 6.34 9.66 5 8 5C6.34 5 5 6.34 5 8C5 9.66 6.34 11 8 11ZM8 13C5.67 13 1 14.17 1 16.5V19H15V16.5C15 14.17 10.33 13 8 13ZM16 13C15.71 13 15.38 13.02 15.03 13.05C16.19 13.89 17 15.02 17 16.5V19H23V16.5C23 14.17 18.33 13 16 13Z" fill="currentColor"/>
              </svg>
            </button>
          )}
          <button className="icon-button" onClick={handleLogout} title="Logout">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M17 7L15.59 8.41L18.17 11H8V13H18.17L15.59 15.58L17 17L22 12L17 7ZM4 5H12V3H4C2.9 3 2 3.9 2 5V19C2 20.1 2.9 21 4 21H12V19H4V5Z" fill="currentColor"/>
            </svg>
          </button>
        </div>
      </header>

      <div className="dashboard-content">
        <div className="search-section">
          <div className="search-bar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" className="search-icon">
              <path d="M15.5 14H14.71L14.43 13.73C15.41 12.59 16 11.11 16 9.5C16 5.91 13.09 3 9.5 3C5.91 3 3 5.91 3 9.5C3 13.09 5.91 16 9.5 16C11.11 16 12.59 15.41 13.73 14.43L14 14.71V15.5L19 20.49L20.49 19L15.5 14ZM9.5 14C7.01 14 5 11.99 5 9.5C5 7.01 7.01 5 9.5 5C11.99 5 14 7.01 14 9.5C14 11.99 11.99 14 9.5 14Z" fill="currentColor"/>
            </svg>
            <input
              type="text"
              placeholder="Search articles..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
            />
            {searchQuery && (
              <button className="clear-button" onClick={() => setSearchQuery('')}>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                  <path d="M19 6.41L17.59 5L12 10.59L6.41 5L5 6.41L10.59 12L5 17.59L6.41 19L12 13.41L17.59 19L19 17.59L13.41 12L19 6.41Z" fill="currentColor"/>
                </svg>
              </button>
            )}
          </div>
        </div>

        <div className="articles-section">
          {isLoading ? (
            <div className="loading-state">
              <div className="spinner"></div>
            </div>
          ) : filteredArticles.length === 0 ? (
            <div className="empty-state">
              <svg width="64" height="64" viewBox="0 0 24 24" fill="none">
                <path d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM19 19H5V5H19V19Z" fill="currentColor"/>
                <path d="M7 7H17V9H7V7ZM7 11H17V13H7V11ZM7 15H14V17H7V15Z" fill="currentColor"/>
              </svg>
              <h3>No articles yet</h3>
              <p>Tap + to create your first article</p>
            </div>
          ) : (
            <div className="articles-list">
              {filteredArticles.map((article) => (
                <div key={article.id} className="article-card" onClick={() => handleEdit(article)}>
                  <div className="article-header">
                    <h3>{article.title || 'Untitled'}</h3>
                    <div className="article-actions">
                      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" className="edit-icon">
                        <path d="M3 17.25V21H6.75L17.81 9.94L14.06 6.19L3 17.25ZM20.71 7.04C21.1 6.65 21.1 6.02 20.71 5.63L18.37 3.29C17.98 2.9 17.35 2.9 16.96 3.29L15.13 5.12L18.88 8.87L20.71 7.04Z" fill="currentColor"/>
                      </svg>
                      {(adminRole === 'admin' || adminRole === 'super_admin') && (
                        <button
                          className="delete-button"
                          onClick={(e) => {
                            e.stopPropagation()
                            handleDelete(article.id, article.title)
                          }}
                        >
                          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M6 19C6 20.1 6.9 21 8 21H16C17.1 21 18 20.1 18 19V7H6V19ZM19 4H15.5L14.5 3H9.5L8.5 4H5V6H19V4Z" fill="currentColor"/>
                          </svg>
                        </button>
                      )}
                    </div>
                  </div>
                  <div className="article-meta">
                    <div className="meta-item">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="currentColor"/>
                      </svg>
                      <span>{article.created_by || 'Unknown'}</span>
                    </div>
                    <div className="meta-item">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12C2 17.52 6.47 22 11.99 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 11.99 2ZM12 20C7.58 20 4 16.42 4 12C4 7.58 7.58 4 12 4C16.42 4 20 7.58 20 12C20 16.42 16.42 20 12 20ZM12.5 7H11V13L16.25 16.15L17 14.92L12.5 12.25V7Z" fill="currentColor"/>
                      </svg>
                      <span>{article.date || 'Unknown'}</span>
                    </div>
                    {article.is_featured && (
                      <div className="meta-item featured">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                          <path d="M12 17.27L18.18 21L16.54 13.97L22 9.24L14.81 8.63L12 2L9.19 8.63L2 9.24L7.46 13.97L5.82 21L12 17.27Z" fill="currentColor"/>
                        </svg>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>

      <button className="fab" onClick={handleAddArticle}>
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M19 13H13V19H11V13H5V11H11V5H13V11H19V13Z" fill="white"/>
        </svg>
      </button>
    </div>
  )
}

export default Dashboard
