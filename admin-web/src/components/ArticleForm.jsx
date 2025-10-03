import { useState, useEffect } from 'react'
import './ArticleForm.css'
import { createArticle, updateArticle } from '../utils/api'

function ArticleForm({ article = null, isEdit = false, onBack }) {
  const [isLoading, setIsLoading] = useState(false)
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [content, setContent] = useState('')
  const [imageUrl, setImageUrl] = useState('')
  const [isFeatured, setIsFeatured] = useState(false)

  const [errors, setErrors] = useState({})

  useEffect(() => {
    if (isEdit && article) {
      setTitle(article.title || '')
      setDescription(article.description || '')
      setContent(article.content || '')
      setImageUrl(article.featured_image || '')
      setIsFeatured(article.is_featured || false)
    }
  }, [isEdit, article])

  const validate = () => {
    const newErrors = {}

    if (!title.trim()) {
      newErrors.title = 'Title is required'
    } else if (title.length < 5) {
      newErrors.title = 'Title must be at least 5 characters'
    }

    if (!description.trim()) {
      newErrors.description = 'Description is required'
    } else if (description.length < 10) {
      newErrors.description = 'Description must be at least 10 characters'
    }

    if (!content.trim()) {
      newErrors.content = 'Content is required'
    } else if (content.length < 20) {
      newErrors.content = 'Content must be at least 20 characters'
    }

    if (!imageUrl.trim()) {
      newErrors.imageUrl = 'Image URL is required'
    } else if (!imageUrl.startsWith('http')) {
      newErrors.imageUrl = 'Enter a valid URL'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!validate()) {
      return
    }

    setIsLoading(true)

    try {
      const articleData = {
        title: title.trim(),
        description: description.trim(),
        content: content.trim(),
        featured_image: imageUrl.trim(),
        is_featured: isFeatured
      }

      let response
      if (isEdit) {
        response = await updateArticle(article.id, articleData)
      } else {
        response = await createArticle(articleData)
      }

      if (response.success) {
        alert(isEdit ? 'Article updated successfully' : 'Article created successfully')
        onBack(true)
      }
    } catch (error) {
      alert(error.message || 'Failed to save article')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="article-form-container">
      <header className="article-form-header">
        <button className="back-button" onClick={() => onBack(false)}>
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M20 11H7.83L13.42 5.41L12 4L4 12L12 20L13.41 18.59L7.83 13H20V11Z" fill="currentColor"/>
          </svg>
        </button>
        <h1>{isEdit ? 'Edit Article' : 'Create Article'}</h1>
      </header>

      <div className="article-form-content">
        <form onSubmit={handleSubmit}>
          <div className="form-group">
            <label htmlFor="title">Title</label>
            <input
              id="title"
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              placeholder="Enter article title"
              className={errors.title ? 'error' : ''}
            />
            {errors.title && <span className="error-text">{errors.title}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="description">Short Description</label>
            <textarea
              id="description"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Enter short description"
              rows="3"
              className={errors.description ? 'error' : ''}
            />
            {errors.description && <span className="error-text">{errors.description}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="content">Full Content</label>
            <textarea
              id="content"
              value={content}
              onChange={(e) => setContent(e.target.value)}
              placeholder="Enter full article content"
              rows="10"
              className={errors.content ? 'error' : ''}
            />
            {errors.content && <span className="error-text">{errors.content}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="imageUrl">Featured Image URL</label>
            <input
              id="imageUrl"
              type="url"
              value={imageUrl}
              onChange={(e) => setImageUrl(e.target.value)}
              placeholder="https://example.com/image.jpg"
              className={errors.imageUrl ? 'error' : ''}
            />
            {errors.imageUrl && <span className="error-text">{errors.imageUrl}</span>}
          </div>

          <div className="form-group checkbox-group">
            <label className="checkbox-label">
              <input
                type="checkbox"
                checked={isFeatured}
                onChange={(e) => setIsFeatured(e.target.checked)}
              />
              <span>Mark as Featured</span>
            </label>
          </div>

          <button type="submit" className="submit-button" disabled={isLoading}>
            {isLoading ? 'Saving...' : (isEdit ? 'Update Article' : 'Create Article')}
          </button>
        </form>
      </div>

      {isLoading && (
        <div className="loading-overlay">
          <div className="spinner"></div>
        </div>
      )}
    </div>
  )
}

export default ArticleForm
