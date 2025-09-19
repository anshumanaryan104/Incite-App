// Simplified API Routes for News App (No Authentication)
const express = require('express');
const router = express.Router();
const { supabase } = require('../supabase-client');

// Helper function for API responses
const apiResponse = (res, success, data = null, message = null, statusCode = 200) => {
    res.status(statusCode).json({
        success,
        message,
        data
    });
};

// =====================================================
// ARTICLES ENDPOINTS (Public Access)
// =====================================================

// Get all articles with categories (main feed)
router.get('/blog-list', async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        // Get all active categories
        const { data: categories, error: catError } = await supabase
            .from('categories')
            .select('*')
            .eq('is_active', true)
            .eq('is_feed', true)
            .order('sort_order');

        if (catError) throw catError;

        // Format categories with their articles
        const formattedCategories = await Promise.all(
            categories.map(async (category) => {
                // Get articles for this category
                const { data: articles, error: artError, count } = await supabase
                    .from('articles')
                    .select('*', { count: 'exact' })
                    .eq('category_id', category.id)
                    .eq('status', 'published')
                    .order('published_at', { ascending: false })
                    .range(from, to);

                if (artError) console.error('Article fetch error:', artError);

                return {
                    id: category.id,
                    name: category.name,
                    image: category.image_url,
                    color: category.color,
                    parent_id: category.parent_id,
                    is_featured: category.is_featured ? 1 : 0,
                    is_feed: category.is_feed,
                    data: {
                        blogs: (articles || []).map(article => ({
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
                            views: article.views || 0,
                            is_featured: article.is_featured ? 1 : 0,
                            type: article.type || 'blog',
                            source_name: article.source_name || 'News App'
                        })),
                        current_page: page,
                        first_page_url: `/api/blog-list?page=1`,
                        from: from + 1,
                        last_page: Math.ceil((count || 0) / limit),
                        last_page_url: `/api/blog-list?page=${Math.ceil((count || 0) / limit)}`,
                        next_page_url: page < Math.ceil((count || 0) / limit)
                            ? `/api/blog-list?page=${page + 1}`
                            : null,
                        path: '/api/blog-list',
                        per_page: limit,
                        prev_page_url: page > 1 ? `/api/blog-list?page=${page - 1}` : null,
                        to: Math.min(to + 1, count || 0),
                        total: count || 0
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
        const { id } = req.params;

        // Get article with category info
        const { data: article, error } = await supabase
            .from('articles')
            .select(`
                *,
                categories (
                    id,
                    name,
                    color,
                    slug
                )
            `)
            .eq('id', id)
            .eq('status', 'published')
            .single();

        if (error || !article) {
            return apiResponse(res, false, null, 'Article not found', 404);
        }

        // Increment views
        await supabase.rpc('increment_article_views', { article_id_input: id });

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
            author: article.author_name,
            created_at: article.created_at,
            published_at: article.published_at,
            views: (article.views || 0) + 1,
            likes: article.likes || 0,
            shares: article.shares || 0,
            is_featured: article.is_featured,
            type: article.type || 'blog',
            source_name: article.source_name || 'News App',
            source_url: article.source_url,
            tags: article.tags || []
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
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        // Get all categories
        const { data: categories, error: catError } = await supabase
            .from('categories')
            .select('*')
            .eq('is_active', true)
            .order('sort_order');

        if (catError) throw catError;

        // Format each category with its articles
        const formattedCategories = await Promise.all(
            categories.map(async (category) => {
                const { data: articles, count } = await supabase
                    .from('articles')
                    .select('*', { count: 'exact' })
                    .eq('category_id', category.id)
                    .eq('status', 'published')
                    .order('published_at', { ascending: false })
                    .range(from, to);

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
                        blogs: (articles || []).map(article => ({
                            id: article.id,
                            title: article.title,
                            description: article.description,
                            image: article.featured_image,
                            images: article.images || [article.featured_image],
                            category_name: category.name,
                            category_color: category.color,
                            views: article.views || 0,
                            created_at: article.created_at,
                            published_at: article.published_at,
                            type: article.type || 'blog'
                        })),
                        current_page: page,
                        total: count || 0,
                        per_page: limit,
                        last_page: Math.ceil((count || 0) / limit)
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

// Search articles
router.get('/search', async (req, res) => {
    try {
        const { q, page = 1, limit = 10 } = req.query;

        if (!q) {
            return apiResponse(res, false, null, 'Search query is required', 400);
        }

        const from = (page - 1) * limit;
        const to = from + limit - 1;

        const { data: articles, error, count } = await supabase
            .from('v_articles_full')
            .select('*', { count: 'exact' })
            .or(`title.ilike.%${q}%,description.ilike.%${q}%`)
            .range(from, to);

        if (error) throw error;

        apiResponse(res, true, {
            articles: articles || [],
            total: count || 0,
            page,
            limit,
            totalPages: Math.ceil((count || 0) / limit)
        });
    } catch (error) {
        console.error('Error in /search:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get trending articles
router.get('/trending', async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 5;

        const { data: articles, error } = await supabase
            .from('v_articles_full')
            .select('*')
            .eq('is_trending', true)
            .order('views', { ascending: false })
            .limit(limit);

        if (error) throw error;
        apiResponse(res, true, articles || []);
    } catch (error) {
        console.error('Error in /trending:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get featured articles
router.get('/featured', async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 10;

        const { data: articles, error } = await supabase
            .from('v_articles_full')
            .select('*')
            .eq('is_featured', true)
            .order('published_at', { ascending: false })
            .limit(limit);

        if (error) throw error;
        apiResponse(res, true, articles || []);
    } catch (error) {
        console.error('Error in /featured:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// BOOKMARKS (Device-based, No Auth)
// =====================================================

// Add bookmark
router.post('/bookmarks', async (req, res) => {
    try {
        const { device_id, article_id } = req.body;

        if (!device_id || !article_id) {
            return apiResponse(res, false, null, 'Device ID and Article ID are required', 400);
        }

        const { data, error } = await supabase
            .from('anonymous_bookmarks')
            .upsert([{ device_id, article_id }], {
                onConflict: 'device_id,article_id'
            })
            .select()
            .single();

        if (error) throw error;
        apiResponse(res, true, data, 'Bookmark added');
    } catch (error) {
        console.error('Error adding bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Remove bookmark
router.delete('/bookmarks', async (req, res) => {
    try {
        const { device_id, article_id } = req.body;

        if (!device_id || !article_id) {
            return apiResponse(res, false, null, 'Device ID and Article ID are required', 400);
        }

        const { error } = await supabase
            .from('anonymous_bookmarks')
            .delete()
            .eq('device_id', device_id)
            .eq('article_id', article_id);

        if (error) throw error;
        apiResponse(res, true, null, 'Bookmark removed');
    } catch (error) {
        console.error('Error removing bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get device bookmarks
router.get('/bookmarks/:deviceId', async (req, res) => {
    try {
        const { page = 1, limit = 10 } = req.query;
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        const { data, error, count } = await supabase
            .from('anonymous_bookmarks')
            .select(`
                *,
                articles!inner (
                    *,
                    categories (
                        name,
                        color
                    )
                )
            `, { count: 'exact' })
            .eq('device_id', req.params.deviceId)
            .order('created_at', { ascending: false })
            .range(from, to);

        if (error) throw error;

        const formattedBookmarks = (data || []).map(item => ({
            id: item.articles.id,
            title: item.articles.title,
            description: item.articles.description,
            image: item.articles.featured_image,
            category_name: item.articles.categories?.name,
            category_color: item.articles.categories?.color,
            created_at: item.created_at
        }));

        apiResponse(res, true, {
            bookmarks: formattedBookmarks,
            total: count || 0,
            page,
            limit,
            totalPages: Math.ceil((count || 0) / limit)
        });
    } catch (error) {
        console.error('Error fetching bookmarks:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// INTERACTIONS (Anonymous tracking)
// =====================================================

// Record interaction (view, like, share)
router.post('/interactions', async (req, res) => {
    try {
        const { device_id, article_id, type } = req.body;
        const ip_address = req.ip;
        const user_agent = req.get('user-agent');

        if (!article_id || !type) {
            return apiResponse(res, false, null, 'Article ID and interaction type are required', 400);
        }

        const { data, error } = await supabase
            .from('anonymous_interactions')
            .insert([{
                device_id,
                article_id,
                interaction_type: type,
                ip_address,
                user_agent
            }])
            .select()
            .single();

        if (error) throw error;

        // Update article counters
        if (type === 'like') {
            await supabase
                .from('articles')
                .update({ likes: supabase.raw('likes + 1') })
                .eq('id', article_id);
        } else if (type === 'share') {
            await supabase
                .from('articles')
                .update({ shares: supabase.raw('shares + 1') })
                .eq('id', article_id);
        }

        apiResponse(res, true, data, 'Interaction recorded');
    } catch (error) {
        console.error('Error recording interaction:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// =====================================================
// SETTINGS & CONFIG
// =====================================================

// Get app settings
router.get('/setting-list', async (req, res) => {
    try {
        const { data, error } = await supabase
            .from('app_settings')
            .select('*');

        if (error) throw error;

        // Convert to object format
        const settings = {};
        (data || []).forEach(item => {
            settings[item.setting_key] = item.setting_value;
        });

        apiResponse(res, true, settings);
    } catch (error) {
        console.error('Error fetching settings:', error);
        // Return default settings if database fails
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

// Mock language endpoint - return only English
router.get('/language-list', (req, res) => {
    apiResponse(res, true, [
        { id: 1, language: 'en', name: 'English' }
    ]);
});

// Mock ads endpoint - always return empty
router.get('/ads-list', (req, res) => apiResponse(res, true, []));
router.get('/cms-list', (req, res) => apiResponse(res, true, []));
router.get('/social-media-list', (req, res) => apiResponse(res, true, []));
router.get('/localisation-list', (req, res) => {
    apiResponse(res, true, {
        app_name: "News App",
        home: "Home",
        categories: "Categories",
        bookmarks: "Bookmarks",
        profile: "Profile"
    });
});

// Mock login endpoint (returns dummy data)
router.post('/login', (req, res) => {
    const { email } = req.body;
    apiResponse(res, true, {
        id: 'anonymous',
        name: 'Guest User',
        email: email || 'guest@newsapp.com',
        token: 'no-auth-required'
    }, 'Login successful');
});

// Mock signup endpoint (returns dummy data)
router.post('/signup', (req, res) => {
    const { email, name } = req.body;
    apiResponse(res, true, {
        id: 'anonymous',
        name: name || 'Guest User',
        email: email || 'guest@newsapp.com',
        token: 'no-auth-required'
    }, 'Signup successful');
});

module.exports = router;