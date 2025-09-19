// API Routes using Supabase
const express = require('express');
const router = express.Router();
const { db } = require('../supabase-client');

// Middleware for API response formatting
const apiResponse = (res, success, data = null, message = null, statusCode = 200) => {
    res.status(statusCode).json({
        success,
        message,
        data
    });
};

// =====================================================
// ARTICLES ENDPOINTS
// =====================================================

// Get all articles with categories (main feed)
router.get('/blog-list', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;

        const categories = await db.getCategories();
        const articlesData = await db.getArticles(page, limit);

        // Format response to match Flutter app structure
        const formattedCategories = await Promise.all(
            categories.filter(cat => cat.is_feed).map(async (category) => {
                const categoryArticles = await db.getArticlesByCategory(category.slug, 1, limit);

                return {
                    id: category.id,
                    name: category.name,
                    image: category.image_url,
                    color: category.color,
                    parent_id: category.parent_id,
                    is_featured: category.is_featured ? 1 : 0,
                    is_feed: category.is_feed,
                    data: {
                        blogs: categoryArticles.articles.map(article => ({
                            id: article.id,
                            title: article.title,
                            description: article.description,
                            content: article.content,
                            category: category.name,
                            category_name: category.name,
                            category_color: category.color,
                            image: article.featured_image,
                            images: article.images || [article.featured_image],
                            created_at: article.created_at,
                            schedule_date: article.published_at,
                            views: article.views,
                            is_featured: article.is_featured ? 1 : 0,
                            type: article.type || 'blog',
                            source_name: article.source_name || 'News App'
                        })),
                        current_page: categoryArticles.page,
                        first_page_url: `/api/blog-list?page=1`,
                        from: (categoryArticles.page - 1) * limit + 1,
                        last_page: categoryArticles.totalPages,
                        last_page_url: `/api/blog-list?page=${categoryArticles.totalPages}`,
                        next_page_url: categoryArticles.page < categoryArticles.totalPages
                            ? `/api/blog-list?page=${categoryArticles.page + 1}`
                            : null,
                        path: '/api/blog-list',
                        per_page: limit,
                        prev_page_url: categoryArticles.page > 1
                            ? `/api/blog-list?page=${categoryArticles.page - 1}`
                            : null,
                        to: Math.min(categoryArticles.page * limit, categoryArticles.total),
                        total: categoryArticles.total
                    },
                    created_at: category.created_at,
                    updated_at: category.updated_at
                };
            })
        );

        apiResponse(res, true, formattedCategories);
    } catch (error) {
        console.error('Error in /blog-list:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get single article detail
router.get('/blog-detail/:id', async (req, res) => {
    try {
        const article = await db.getArticleById(req.params.id);

        if (!article) {
            return apiResponse(res, false, null, 'Article not found', 404);
        }

        // Format response
        const formattedArticle = {
            id: article.id,
            title: article.title,
            description: article.description,
            content: article.content,
            category: article.categories?.name,
            category_name: article.categories?.name,
            category_color: article.categories?.color,
            image: article.featured_image,
            images: article.images || [article.featured_image],
            video_url: article.video_url,
            author: article.users?.name,
            author_avatar: article.users?.avatar_url,
            created_at: article.created_at,
            published_at: article.published_at,
            views: article.views,
            likes: article.likes,
            shares: article.shares,
            is_featured: article.is_featured,
            type: article.type || 'blog',
            source_name: article.source_name || 'News App',
            source_url: article.source_url,
            tags: article.tags,
            read_time: article.read_time
        };

        apiResponse(res, true, formattedArticle);
    } catch (error) {
        console.error('Error in /blog-detail:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get categories list
router.get('/blog-category-list', async (req, res) => {
    try {
        const categories = await db.getCategories();
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;

        // Format each category with its articles
        const formattedCategories = await Promise.all(
            categories.map(async (category) => {
                const articlesData = await db.getArticlesByCategory(category.slug, page, limit);

                return {
                    id: category.id,
                    name: category.name,
                    slug: category.slug,
                    image: category.image_url,
                    color: category.color,
                    parent_id: category.parent_id,
                    is_featured: category.is_featured ? 1 : 0,
                    is_feed: category.is_feed,
                    data: {
                        blogs: articlesData.articles.map(article => ({
                            id: article.id,
                            title: article.title,
                            description: article.description,
                            image: article.featured_image,
                            images: article.images || [article.featured_image],
                            category_name: category.name,
                            category_color: category.color,
                            views: article.views,
                            created_at: article.created_at,
                            published_at: article.published_at,
                            type: article.type || 'blog'
                        })),
                        current_page: articlesData.page,
                        total: articlesData.total,
                        per_page: articlesData.limit,
                        last_page: articlesData.totalPages
                    },
                    created_at: category.created_at,
                    updated_at: category.updated_at
                };
            })
        );

        apiResponse(res, true, formattedCategories);
    } catch (error) {
        console.error('Error in /blog-category-list:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get trending articles
router.get('/trending', async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 5;
        const articles = await db.getTrendingArticles(limit);
        apiResponse(res, true, articles);
    } catch (error) {
        console.error('Error in /trending:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get featured articles
router.get('/featured', async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 10;
        const articles = await db.getFeaturedArticles(limit);
        apiResponse(res, true, articles);
    } catch (error) {
        console.error('Error in /featured:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Search articles
router.get('/search', async (req, res) => {
    try {
        const { q, page = 1, limit = 10 } = req.query;

        if (!q) {
            return apiResponse(res, false, null, 'Search query is required', 400);
        }

        const results = await db.searchArticles(q, page, limit);
        apiResponse(res, true, results);
    } catch (error) {
        console.error('Error in /search:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// USER ENDPOINTS
// =====================================================

// User signup
router.post('/signup', async (req, res) => {
    try {
        const { name, email, password } = req.body;

        if (!name || !email || !password) {
            return apiResponse(res, false, null, 'All fields are required', 400);
        }

        // Check if user exists
        const existingUser = await db.getUserByEmail(email);
        if (existingUser) {
            return apiResponse(res, false, null, 'User already exists', 400);
        }

        // Create user (in production, hash the password!)
        const userData = {
            name,
            email,
            password_hash: password // TODO: Hash this in production
        };

        const user = await db.createUser(userData);

        apiResponse(res, true, {
            id: user.id,
            name: user.name,
            email: user.email,
            token: user.api_token
        }, 'Signup successful');
    } catch (error) {
        console.error('Error in /signup:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// User login
router.post('/login', async (req, res) => {
    try {
        const { email, password } = req.body;

        if (!email || !password) {
            return apiResponse(res, false, null, 'Email and password are required', 400);
        }

        const user = await db.getUserByEmail(email);

        if (!user) {
            return apiResponse(res, false, null, 'Invalid credentials', 401);
        }

        // TODO: Verify password hash in production
        if (user.password_hash !== password) {
            return apiResponse(res, false, null, 'Invalid credentials', 401);
        }

        apiResponse(res, true, {
            id: user.id,
            name: user.name,
            email: user.email,
            token: user.api_token
        }, 'Login successful');
    } catch (error) {
        console.error('Error in /login:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// BOOKMARKS ENDPOINTS
// =====================================================

// Add bookmark
router.post('/bookmarks', async (req, res) => {
    try {
        const { user_id, article_id } = req.body;

        if (!user_id || !article_id) {
            return apiResponse(res, false, null, 'User ID and Article ID are required', 400);
        }

        const bookmark = await db.addBookmark(user_id, article_id);
        apiResponse(res, true, bookmark, 'Bookmark added');
    } catch (error) {
        console.error('Error adding bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Remove bookmark
router.delete('/bookmarks', async (req, res) => {
    try {
        const { user_id, article_id } = req.body;

        if (!user_id || !article_id) {
            return apiResponse(res, false, null, 'User ID and Article ID are required', 400);
        }

        await db.removeBookmark(user_id, article_id);
        apiResponse(res, true, null, 'Bookmark removed');
    } catch (error) {
        console.error('Error removing bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get user bookmarks
router.get('/bookmarks/:userId', async (req, res) => {
    try {
        const { page = 1, limit = 10 } = req.query;
        const bookmarks = await db.getUserBookmarks(req.params.userId, page, limit);
        apiResponse(res, true, bookmarks);
    } catch (error) {
        console.error('Error fetching bookmarks:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// INTERACTIONS ENDPOINTS
// =====================================================

// Record interaction (view, like, share)
router.post('/interactions', async (req, res) => {
    try {
        const { user_id, article_id, type, data } = req.body;

        if (!article_id || !type) {
            return apiResponse(res, false, null, 'Article ID and interaction type are required', 400);
        }

        const interaction = await db.recordInteraction(user_id, article_id, type, data);
        apiResponse(res, true, interaction, 'Interaction recorded');
    } catch (error) {
        console.error('Error recording interaction:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// SETTINGS ENDPOINTS
// =====================================================

// Get app settings
router.get('/setting-list', async (req, res) => {
    try {
        const settings = await db.getAppSettings();
        apiResponse(res, true, settings);
    } catch (error) {
        console.error('Error fetching settings:', error);
        // Return mock settings if database fails
        apiResponse(res, true, {
            app_name: 'News App',
            primary_color: '#FF6B6B',
            secondary_color: '#4ECDC4',
            app_version: '1.0.0',
            enable_ads: '0',
            enable_notifications: '0'
        });
    }
});

// Other endpoints (placeholder for now)
router.get('/ads-list', (req, res) => {
    apiResponse(res, true, []);
});

router.get('/cms-list', (req, res) => {
    apiResponse(res, true, []);
});

router.get('/social-media-list', (req, res) => {
    apiResponse(res, true, []);
});

router.get('/localisation-list', (req, res) => {
    apiResponse(res, true, {
        app_name: "News App",
        home: "Home",
        categories: "Categories",
        bookmarks: "Bookmarks",
        profile: "Profile"
    });
});

router.get('/language-list', (req, res) => {
    apiResponse(res, true, [
        { id: 1, language: 'en', name: 'English' },
        { id: 2, language: 'hi', name: 'Hindi' }
    ]);
});

module.exports = router;