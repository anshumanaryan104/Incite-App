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

// Get categories list (only category info, no articles)
router.get('/blog-category-list', async (req, res) => {
    try {
        // Get all active categories
        const { data: categories, error: catError } = await supabase
            .from('categories')
            .select('*')
            .eq('is_active', true)
            .order('sort_order');

        if (catError) throw catError;

        // Format categories without articles
        const formattedCategories = categories.map(category => ({
            id: category.id,
            name: category.name,
            slug: category.slug,
            image: category.image_url,
            color: category.color,
            parent_id: category.parent_id,
            is_featured: category.is_featured ? 1 : 0,
            is_feed: category.is_feed,
            sort_order: category.sort_order,
            created_at: category.created_at,
            updated_at: category.updated_at
        }));

        apiResponse(res, true, formattedCategories);
    } catch (error) {
        console.error('Error in /blog-category-list:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get articles by category slug
router.get('/articles/by-category/:slug', async (req, res) => {
    try {
        const { slug } = req.params;
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 10;
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        // First get the category by slug
        const { data: category, error: catError } = await supabase
            .from('categories')
            .select('*')
            .eq('slug', slug)
            .eq('is_active', true)
            .single();

        if (catError || !category) {
            return apiResponse(res, false, null, 'Category not found', 404);
        }

        // Get articles for this category with pagination
        const { data: articles, error: artError, count } = await supabase
            .from('articles')
            .select('*', { count: 'exact' })
            .eq('category_id', category.id)
            .eq('status', 'published')
            .order('published_at', { ascending: false })
            .range(from, to);

        if (artError) throw artError;

        // Format articles
        const formattedArticles = (articles || []).map(article => ({
            id: article.id,
            title: article.title,
            description: article.description,
            content: article.content,
            category: category.name,
            category_name: category.name,
            category_color: category.color,
            category_slug: category.slug,
            image: article.featured_image,
            images: article.images || [article.featured_image],
            author: article.author_name,
            created_at: article.created_at,
            published_at: article.published_at,
            views: article.views || 0,
            likes: article.likes || 0,
            shares: article.shares || 0,
            is_featured: article.is_featured,
            type: article.type || 'blog',
            source_name: article.source_name || 'News App',
            tags: article.tags || []
        }));

        apiResponse(res, true, {
            category: {
                id: category.id,
                name: category.name,
                slug: category.slug,
                color: category.color,
                image: category.image_url
            },
            articles: formattedArticles,
            pagination: {
                current_page: page,
                per_page: limit,
                total: count || 0,
                last_page: Math.ceil((count || 0) / limit),
                from: from + 1,
                to: Math.min(to + 1, count || 0),
                first_page_url: `/api/articles/by-category/${slug}?page=1`,
                last_page_url: `/api/articles/by-category/${slug}?page=${Math.ceil((count || 0) / limit)}`,
                next_page_url: page < Math.ceil((count || 0) / limit)
                    ? `/api/articles/by-category/${slug}?page=${page + 1}`
                    : null,
                prev_page_url: page > 1
                    ? `/api/articles/by-category/${slug}?page=${page - 1}`
                    : null
            }
        });
    } catch (error) {
        console.error('Error in /articles/by-category:', error);
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

// Add bookmark (supports both tables)
router.post('/add-bookmark', async (req, res) => {
    try {
        const { user_id, device_id, article_id } = req.body;

        if (!article_id) {
            return apiResponse(res, false, null, 'Article ID is required', 400);
        }

        if (!user_id && !device_id) {
            return apiResponse(res, false, null, 'User ID or Device ID is required', 400);
        }

        let data, error;

        // If user_id provided, use user_bookmarks table
        if (user_id) {
            // Check if bookmark already exists for user
            const { data: existing } = await supabase
                .from('user_bookmarks')
                .select('id')
                .eq('user_id', user_id)
                .eq('article_id', article_id)
                .single();

            if (existing) {
                return apiResponse(res, false, null, 'Bookmark already exists', 409);
            }

            // Add new user bookmark
            const result = await supabase
                .from('user_bookmarks')
                .insert([{ user_id, article_id }])
                .select()
                .single();

            data = result.data;
            error = result.error;
        }
        // Otherwise use device_id with bookmarks table
        else if (device_id) {
            // Check if bookmark already exists for device
            const { data: existing } = await supabase
                .from('bookmarks')
                .select('id')
                .eq('device_id', device_id)
                .eq('article_id', article_id)
                .single();

            if (existing) {
                return apiResponse(res, false, null, 'Bookmark already exists', 409);
            }

            // Add new device bookmark
            const result = await supabase
                .from('bookmarks')
                .insert([{ device_id, article_id }])
                .select()
                .single();

            data = result.data;
            error = result.error;
        }

        if (error) throw error;
        apiResponse(res, true, data, 'Bookmark added successfully');
    } catch (error) {
        console.error('Error adding bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Remove bookmark (supports both tables)
router.post('/remove-bookmark', async (req, res) => {
    try {
        const { user_id, device_id, article_id } = req.body;

        if (!article_id) {
            return apiResponse(res, false, null, 'Article ID is required', 400);
        }

        if (!user_id && !device_id) {
            return apiResponse(res, false, null, 'User ID or Device ID is required', 400);
        }

        let error;

        // If user_id provided, remove from user_bookmarks table
        if (user_id) {
            const result = await supabase
                .from('user_bookmarks')
                .delete()
                .eq('user_id', user_id)
                .eq('article_id', article_id);

            error = result.error;
        }
        // Otherwise remove from device bookmarks table
        else if (device_id) {
            const result = await supabase
                .from('bookmarks')
                .delete()
                .eq('device_id', device_id)
                .eq('article_id', article_id);

            error = result.error;
        }

        if (error) throw error;
        apiResponse(res, true, null, 'Bookmark removed successfully');
    } catch (error) {
        console.error('Error removing bookmark:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get bookmarks (using both bookmarks and user_bookmarks tables)
router.get('/get-bookmarks', async (req, res) => {
    try {
        // Get user_id and device_id from headers or query params
        const user_id = req.headers['user-id'] || req.query.user_id;
        const device_id = req.headers['device-id'] || req.query.device_id;

        // If neither user_id nor device_id provided, return error
        if (!user_id && !device_id) {
            return apiResponse(res, false, null, 'User ID or Device ID is required', 400);
        }

        const { page = 1, limit = 10 } = req.query;
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        let data, error, count;

        // Priority: If user_id is provided, use user_bookmarks table (logged-in users)
        if (user_id) {
            const result = await supabase
                .from('user_bookmarks')
                .select(`
                    *,
                    articles!inner (
                        *,
                        categories (
                            id,
                            name,
                            color,
                            slug
                        )
                    )
                `, { count: 'exact' })
                .eq('user_id', user_id)
                .order('created_at', { ascending: false })
                .range(from, to);

            data = result.data;
            error = result.error;
            count = result.count;

            // If no data found for user, also check device bookmarks for migration
            if (!error && (!data || data.length === 0) && device_id) {
                const deviceResult = await supabase
                    .from('bookmarks')
                    .select(`
                        *,
                        articles!inner (
                            *,
                            categories (
                                id,
                                name,
                                color,
                                slug
                            )
                        )
                    `, { count: 'exact' })
                    .eq('device_id', device_id)
                    .order('created_at', { ascending: false })
                    .range(from, to);

                if (!deviceResult.error && deviceResult.data && deviceResult.data.length > 0) {
                    // Optionally migrate device bookmarks to user bookmarks here
                    console.log('Found device bookmarks for logged-in user - consider migration');
                    data = deviceResult.data;
                    count = deviceResult.count;
                }
            }
        }
        // Otherwise use device_id with bookmarks table (anonymous users)
        else if (device_id) {
            const result = await supabase
                .from('bookmarks')
                .select(`
                    *,
                    articles!inner (
                        *,
                        categories (
                            id,
                            name,
                            color,
                            slug
                        )
                    )
                `, { count: 'exact' })
                .eq('device_id', device_id)
                .order('created_at', { ascending: false })
                .range(from, to);

            data = result.data;
            error = result.error;
            count = result.count;
        }

        if (error) throw error;

        // Format the bookmarks response
        const formattedBookmarks = (data || []).map(bookmark => ({
            id: bookmark.articles.id,
            title: bookmark.articles.title,
            description: bookmark.articles.description,
            content: bookmark.articles.content,
            image: bookmark.articles.featured_image,
            images: bookmark.articles.images || [bookmark.articles.featured_image],
            category_id: bookmark.articles.categories?.id,
            category_name: bookmark.articles.categories?.name,
            category_color: bookmark.articles.categories?.color,
            category_slug: bookmark.articles.categories?.slug,
            author: bookmark.articles.author_name,
            views: bookmark.articles.views || 0,
            likes: bookmark.articles.likes || 0,
            shares: bookmark.articles.shares || 0,
            is_featured: bookmark.articles.is_featured,
            published_at: bookmark.articles.published_at,
            bookmarked_at: bookmark.created_at || new Date().toISOString(),
            source_name: bookmark.articles.source_name || 'News App',
            tags: bookmark.articles.tags || [],
            bookmark_type: user_id ? 'user' : 'device' // Indicate which table was used
        }));

        apiResponse(res, true, {
            bookmarks: formattedBookmarks,
            bookmark_source: user_id ? 'user_bookmarks' : 'device_bookmarks',
            pagination: {
                current_page: parseInt(page),
                per_page: parseInt(limit),
                total: count || 0,
                last_page: Math.ceil((count || 0) / limit) || 1,
                from: count > 0 ? from + 1 : 0,
                to: Math.min(to + 1, count || 0)
            }
        });
    } catch (error) {
        console.error('Error fetching bookmarks:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get device bookmarks (original endpoint)
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

// About and Contact Information API
router.get('/about-contact', async (req, res) => {
    try {
        // First try to fetch from database
        const { data: dbData, error } = await supabase
            .from('about_contact')
            .select('*')
            .eq('id', 1)
            .single();

        if (error || !dbData) {
            // If database fetch fails, return static data as fallback
            console.log('⚠️ about_contact table not found or empty, using static data');

            const aboutContactData = {
                about: {
                    title: "About Us",
                    description: "Welcome to News App - Your trusted source for latest news and updates",
                    content: "We are dedicated to bringing you the most relevant and timely news from around the world. Our team of journalists and editors work around the clock to ensure you stay informed about what matters most.",
                    mission: "To deliver accurate, unbiased, and comprehensive news coverage to our readers",
                    vision: "To be the most trusted digital news platform",
                    established: "2024",
                    team_size: "Growing team of passionate individuals",
                    values: [
                        "Accuracy and Truth",
                        "Unbiased Reporting",
                        "Reader First Approach",
                        "Innovation in News Delivery"
                    ]
                },
                contact: {
                    title: "Contact Us",
                    description: "We'd love to hear from you",
                    email: "contact@newsapp.com",
                    support_email: "support@newsapp.com",
                    phone: "+91 98765 43210",
                    whatsapp: "+91 98765 43210",
                    address: {
                        line1: "News App Headquarters",
                        line2: "Tech Park, Building A",
                        city: "Mumbai",
                        state: "Maharashtra",
                        country: "India",
                        pincode: "400001"
                    },
                    business_hours: {
                        weekdays: "9:00 AM - 6:00 PM",
                        saturday: "9:00 AM - 2:00 PM",
                        sunday: "Closed"
                    },
                    response_time: "We typically respond within 24 hours"
                },
                legal: {
                    privacy_policy_url: "/privacy-policy",
                    terms_conditions_url: "/terms-conditions",
                    disclaimer: "All news content is for informational purposes only"
                },
                app_info: {
                    version: "1.0.0",
                    last_updated: new Date().toISOString(),
                    developer: "News App Team",
                    copyright: `© ${new Date().getFullYear()} News App. All rights reserved.`
                }
            };

            apiResponse(res, true, aboutContactData);
            return;
        }

        // Format database data into API response structure
        const aboutContactData = {
            about: {
                title: dbData.about_title,
                description: dbData.about_description,
                content: dbData.about_content,
                mission: dbData.about_mission,
                vision: dbData.about_vision,
                established: dbData.about_established,
                team_size: dbData.about_team_size,
                values: dbData.about_values || []
            },
            contact: {
                title: dbData.contact_title,
                description: dbData.contact_description,
                email: dbData.contact_email,
                support_email: dbData.contact_support_email,
                phone: dbData.contact_phone,
                whatsapp: dbData.contact_whatsapp,
                address: dbData.contact_address || {},
                business_hours: dbData.business_hours || {},
                response_time: dbData.contact_response_time
            },
            legal: {
                privacy_policy_url: dbData.privacy_policy_url,
                terms_conditions_url: dbData.terms_conditions_url,
                disclaimer: dbData.disclaimer
            },
            app_info: {
                version: dbData.app_version,
                last_updated: dbData.updated_at || new Date().toISOString(),
                developer: dbData.app_developer,
                copyright: dbData.app_copyright || `© ${new Date().getFullYear()} News App. All rights reserved.`
            }
        };

        apiResponse(res, true, aboutContactData);
    } catch (error) {
        console.error('Error in /about-contact:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get User Profile API
router.get('/get-profile', async (req, res) => {
    try {
        const user_id = req.query.user_id || req.headers['x-user-id'];
        const device_id = req.query.device_id || req.headers['x-device-id'];

        // For anonymous/device-based users
        if (!user_id && device_id) {
            const profileData = {
                id: 'guest_' + device_id.substring(0, 8),
                name: 'Guest User',
                email: null,
                phone: null,
                profile_picture: null,
                bio: null,
                is_guest: true,
                device_id: device_id,
                preferences: {
                    notification_enabled: false,
                    theme: 'light',
                    language: 'en'
                },
                stats: {
                    total_bookmarks: 0,
                    articles_read: 0,
                    member_since: new Date().toISOString()
                }
            };

            // Get bookmark count for device
            const { count: bookmarkCount } = await supabase
                .from('bookmarks')
                .select('*', { count: 'exact', head: true })
                .eq('device_id', device_id);

            profileData.stats.total_bookmarks = bookmarkCount || 0;

            apiResponse(res, true, profileData);
            return;
        }

        // For authenticated users - try to fetch from users table
        if (user_id) {
            // First check if users table exists and has data
            const { data: userData, error: userError } = await supabase
                .from('users')
                .select('*')
                .eq('id', user_id)
                .single();

            if (!userError && userData) {
                // Get bookmark count for user
                const { count: bookmarkCount } = await supabase
                    .from('user_bookmarks')
                    .select('*', { count: 'exact', head: true })
                    .eq('user_id', user_id);

                // Get articles read count from interactions
                const { count: readCount } = await supabase
                    .from('interactions')
                    .select('*', { count: 'exact', head: true })
                    .eq('user_id', user_id)
                    .eq('interaction_type', 'view');

                const profileData = {
                    id: userData.id,
                    name: userData.name || userData.full_name || 'User',
                    email: userData.email,
                    phone: userData.phone || null,
                    profile_picture: userData.profile_picture || userData.avatar_url || null,
                    bio: userData.bio || null,
                    is_guest: false,
                    is_verified: userData.is_verified || false,
                    preferences: userData.preferences || {
                        notification_enabled: true,
                        theme: 'light',
                        language: 'en'
                    },
                    stats: {
                        total_bookmarks: bookmarkCount || 0,
                        articles_read: readCount || 0,
                        member_since: userData.created_at || new Date().toISOString()
                    }
                };

                apiResponse(res, true, profileData);
                return;
            }

            // Fallback for authenticated users if users table doesn't exist
            const profileData = {
                id: user_id,
                name: 'Registered User',
                email: req.query.email || 'user@newsapp.com',
                phone: null,
                profile_picture: null,
                bio: null,
                is_guest: false,
                is_verified: false,
                preferences: {
                    notification_enabled: true,
                    theme: 'light',
                    language: 'en'
                },
                stats: {
                    total_bookmarks: 0,
                    articles_read: 0,
                    member_since: new Date().toISOString()
                }
            };

            // Get bookmark count
            const { count: bookmarkCount } = await supabase
                .from('user_bookmarks')
                .select('*', { count: 'exact', head: true })
                .eq('user_id', user_id);

            profileData.stats.total_bookmarks = bookmarkCount || 0;

            apiResponse(res, true, profileData);
            return;
        }

        // No user_id or device_id provided
        apiResponse(res, false, null, 'User ID or Device ID is required', 400);

    } catch (error) {
        console.error('Error in /get-profile:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Update User Profile API
router.post('/update-profile', async (req, res) => {
    try {
        const user_id = req.body.user_id || req.headers['x-user-id'];

        if (!user_id) {
            apiResponse(res, false, null, 'User ID is required for profile update', 400);
            return;
        }

        // Extract update fields from request body
        const {
            name,
            email,
            phone,
            bio,
            profile_picture,
            notification_enabled,
            theme,
            language
        } = req.body;

        // Build update object for users table (only include fields that exist)
        const updateData = {};

        // Basic profile fields (only name and email typically exist in users table)
        if (name !== undefined) updateData.name = name;
        if (email !== undefined) updateData.email = email;

        // First check if users table exists
        const { data: existingUser, error: checkError } = await supabase
            .from('users')
            .select('*')
            .eq('id', user_id)
            .single();

        if (checkError) {
            // If users table doesn't exist or user not found, create mock response
            console.log('⚠️ Users table not found or user does not exist');

            // Return mock updated data
            const mockUpdatedProfile = {
                id: user_id,
                name: name || 'User ' + user_id,
                email: email || `user${user_id}@newsapp.com`,
                phone: phone || null,
                bio: bio || null,
                profile_picture: profile_picture || null,
                is_guest: false,
                is_verified: false,
                preferences: {
                    notification_enabled: notification_enabled === true || notification_enabled === 'true' || true,
                    theme: theme || 'light',
                    language: language || 'en'
                },
                updated_at: new Date().toISOString(),
                message: 'Profile update simulated (users table not available)'
            };

            apiResponse(res, true, mockUpdatedProfile, 'Profile updated successfully (simulated)');
            return;
        }

        // Update user in database
        const { data: updatedUser, error: updateError } = await supabase
            .from('users')
            .update(updateData)
            .eq('id', user_id)
            .select()
            .single();

        if (updateError) {
            console.error('Error updating user profile:', updateError);
            apiResponse(res, false, null, 'Failed to update profile: ' + updateError.message, 500);
            return;
        }

        // Get updated stats
        const { count: bookmarkCount } = await supabase
            .from('user_bookmarks')
            .select('*', { count: 'exact', head: true })
            .eq('user_id', user_id);

        const { count: readCount } = await supabase
            .from('interactions')
            .select('*', { count: 'exact', head: true })
            .eq('user_id', user_id)
            .eq('interaction_type', 'view');

        // Return updated profile data
        const updatedProfile = {
            id: updatedUser.id,
            name: updatedUser.name || 'User',
            email: updatedUser.email,
            phone: phone || null,  // From request body
            bio: bio || null,      // From request body
            profile_picture: profile_picture || null,  // From request body
            is_guest: false,
            is_verified: false,
            preferences: {
                notification_enabled: notification_enabled === true || notification_enabled === 'true' || true,
                theme: theme || 'light',
                language: language || 'en'
            },
            stats: {
                total_bookmarks: bookmarkCount || 0,
                articles_read: readCount || 0,
                member_since: updatedUser.created_at || new Date().toISOString()
            },
            updated_at: new Date().toISOString()
        };

        apiResponse(res, true, updatedProfile, 'Profile updated successfully');

    } catch (error) {
        console.error('Error in /update-profile:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Share Blog API - Generate shareable card data for article
router.get('/share-blog', async (req, res) => {
    try {
        const articleId = req.query.id;

        if (!articleId) {
            apiResponse(res, false, null, 'Article ID is required', 400);
            return;
        }

        // Fetch article from database
        const { data: article, error } = await supabase
            .from('articles')
            .select('*, categories!inner(*)')
            .eq('id', articleId)
            .single();

        if (error || !article) {
            apiResponse(res, false, null, 'Article not found', 404);
            return;
        }

        // Generate share URLs for different platforms
        const baseUrl = req.protocol + '://' + req.get('host');
        const articleUrl = `${baseUrl}/article/${article.slug || article.id}`;
        const encodedUrl = encodeURIComponent(articleUrl);
        const encodedTitle = encodeURIComponent(article.title);
        const encodedDescription = encodeURIComponent(article.description || article.title);

        // Create shareable card data
        const shareData = {
            article: {
                id: article.id,
                title: article.title,
                description: article.description,
                image: article.image,
                category: article.categories ? article.categories.name : 'News',
                author: article.author || 'News App Team',
                published_at: article.published_at || article.created_at,
                url: articleUrl
            },
            share_links: {
                whatsapp: `https://wa.me/?text=${encodedTitle}%20${encodedUrl}`,
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`,
                twitter: `https://twitter.com/intent/tweet?text=${encodedTitle}&url=${encodedUrl}`,
                linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`,
                telegram: `https://t.me/share/url?url=${encodedUrl}&text=${encodedTitle}`,
                email: `mailto:?subject=${encodedTitle}&body=${encodedDescription}%20${encodedUrl}`,
                copy_link: articleUrl
            },
            meta_tags: {
                // Open Graph tags for social media cards
                og_title: article.title,
                og_description: article.description || article.title,
                og_image: article.image,
                og_url: articleUrl,
                og_type: 'article',
                og_site_name: 'News App',

                // Twitter Card tags
                twitter_card: 'summary_large_image',
                twitter_title: article.title,
                twitter_description: article.description || article.title,
                twitter_image: article.image,
                twitter_site: '@newsapp'
            },
            share_message: {
                default: `Check out this article: ${article.title}`,
                whatsapp: `📰 *${article.title}*\n\n${article.description || ''}\n\nRead more: ${articleUrl}`,
                email_subject: `Interesting article: ${article.title}`,
                email_body: `Hi,\n\nI thought you might find this article interesting:\n\n${article.title}\n\n${article.description || ''}\n\nRead full article: ${articleUrl}\n\nBest regards`
            },
            stats: {
                views: article.views || 0,
                shares: article.shares || 0,
                likes: article.likes || 0
            }
        };

        // Optionally track share intent (not actual share completion)
        // This helps understand which articles users intend to share
        if (article.id) {
            const { error: trackError } = await supabase
                .from('interactions')
                .insert({
                    article_id: article.id,
                    interaction_type: 'share_intent',
                    user_id: req.query.user_id || null,
                    device_id: req.query.device_id || req.headers['x-device-id'] || null,
                    created_at: new Date().toISOString()
                });

            if (trackError) {
                console.log('Could not track share intent:', trackError.message);
            }
        }

        apiResponse(res, true, shareData);

    } catch (error) {
        console.error('Error in /share-blog:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Article Search API - Search articles by keyword
router.post('/article-search', async (req, res) => {
    try {
        const { keyword, limit = 20, offset = 0, category_id } = req.body;

        if (!keyword || keyword.trim() === '') {
            apiResponse(res, false, null, 'Keyword is required for search', 400);
            return;
        }

        const searchTerm = keyword.trim().toLowerCase();

        // Build the query
        let query = supabase
            .from('articles')
            .select('*, categories!inner(*)');

        // Use PostgreSQL's ILIKE for case-insensitive pattern matching
        // Search in title, description, and content
        query = query.or(`title.ilike.%${searchTerm}%,description.ilike.%${searchTerm}%,content.ilike.%${searchTerm}%`);

        // Filter by category if provided
        if (category_id) {
            query = query.eq('category_id', category_id);
        }

        // Apply pagination
        query = query
            .range(offset, offset + limit - 1)
            .order('published_at', { ascending: false });

        // Execute the search
        const { data: searchResults, error, count } = await query;

        if (error) {
            console.error('Search error:', error);
            apiResponse(res, false, null, 'Search failed: ' + error.message, 500);
            return;
        }

        // Calculate relevance scores (simple scoring based on where keyword appears)
        const scoredResults = searchResults ? searchResults.map(article => {
            let relevanceScore = 0;

            // Higher score for title matches
            if (article.title && article.title.toLowerCase().includes(searchTerm)) {
                relevanceScore += 3;
            }

            // Medium score for description matches
            if (article.description && article.description.toLowerCase().includes(searchTerm)) {
                relevanceScore += 2;
            }

            // Lower score for content matches
            if (article.content && article.content.toLowerCase().includes(searchTerm)) {
                relevanceScore += 1;
            }

            return {
                id: article.id,
                title: article.title,
                description: article.description,
                image: article.image,
                category: article.categories ? {
                    id: article.categories.id,
                    name: article.categories.name,
                    slug: article.categories.slug
                } : null,
                author: article.author || 'News App Team',
                published_at: article.published_at || article.created_at,
                slug: article.slug,
                views: article.views || 0,
                likes: article.likes || 0,
                relevance_score: relevanceScore
            };
        }) : [];

        // Sort by relevance score (highest first)
        scoredResults.sort((a, b) => b.relevance_score - a.relevance_score);

        // Track search query (optional analytics)
        const { error: trackError } = await supabase
            .from('interactions')
            .insert({
                interaction_type: 'search',
                user_id: req.body.user_id || null,
                device_id: req.body.device_id || req.headers['x-device-id'] || null,
                metadata: { keyword: searchTerm, results_count: scoredResults.length },
                created_at: new Date().toISOString()
            });

        if (trackError) {
            console.log('Could not track search:', trackError.message);
        }

        // Prepare response
        const response = {
            keyword: keyword,
            total_results: scoredResults.length,
            showing: {
                from: offset + 1,
                to: Math.min(offset + limit, scoredResults.length)
            },
            articles: scoredResults,
            search_metadata: {
                searched_at: new Date().toISOString(),
                search_fields: ['title', 'description', 'content'],
                category_filter: category_id || null
            }
        };

        apiResponse(res, true, response);

    } catch (error) {
        console.error('Error in /article-search:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Notification Settings API - Check notification permissions and preferences
router.post('/get-notification-settings', async (req, res) => {
    try {
        const { user_id, device_id, platform } = req.body;

        if (!user_id && !device_id) {
            apiResponse(res, false, null, 'User ID or Device ID is required', 400);
            return;
        }

        // Try to fetch user notification preferences from database
        let userPreferences = null;

        if (user_id) {
            // Check if we have a notification_settings table
            const { data: settings, error } = await supabase
                .from('notification_settings')
                .select('*')
                .eq('user_id', user_id)
                .single();

            if (!error && settings) {
                userPreferences = settings;
            }
        }

        // Default notification settings structure
        const notificationSettings = {
            // Main notification permission status
            notification_enabled: userPreferences?.notification_enabled ?? true,
            permission_status: userPreferences?.permission_status || 'granted', // granted/denied/not_determined/provisional

            // Push notification settings
            push_notifications: {
                enabled: userPreferences?.push_enabled ?? true,
                permission_status: userPreferences?.push_permission || 'granted',
                fcm_token: userPreferences?.fcm_token || null,
                last_updated: userPreferences?.push_updated_at || new Date().toISOString()
            },

            // Email notification settings
            email_notifications: {
                enabled: userPreferences?.email_enabled ?? false,
                email: userPreferences?.email || null,
                verified: userPreferences?.email_verified ?? false
            },

            // In-app notification preferences by category
            notification_preferences: {
                breaking_news: userPreferences?.pref_breaking_news ?? true,
                daily_digest: userPreferences?.pref_daily_digest ?? false,
                bookmark_reminders: userPreferences?.pref_bookmark_reminders ?? true,
                new_categories: userPreferences?.pref_new_categories ?? true,
                trending_articles: userPreferences?.pref_trending ?? true,
                personalized_recommendations: userPreferences?.pref_recommendations ?? false
            },

            // Device-specific settings
            device_settings: {
                sound: userPreferences?.device_sound ?? true,
                vibration: userPreferences?.device_vibration ?? true,
                badge_count: userPreferences?.device_badge ?? true,
                quiet_hours: {
                    enabled: userPreferences?.quiet_hours_enabled ?? false,
                    start_time: userPreferences?.quiet_hours_start || "22:00",
                    end_time: userPreferences?.quiet_hours_end || "08:00"
                }
            },

            // Notification schedule preferences
            schedule: {
                morning_brief: {
                    enabled: userPreferences?.schedule_morning ?? false,
                    time: userPreferences?.schedule_morning_time || "08:00"
                },
                evening_summary: {
                    enabled: userPreferences?.schedule_evening ?? false,
                    time: userPreferences?.schedule_evening_time || "18:00"
                }
            },

            // Platform-specific information
            platform_info: {
                platform: platform || 'unknown',
                app_version: req.body.app_version || '1.0.0',
                os_version: req.body.os_version || null,
                last_permission_check: new Date().toISOString()
            },

            // User/Device identification
            user_info: {
                user_id: user_id || null,
                device_id: device_id || null,
                is_guest: !user_id,
                settings_last_updated: userPreferences?.updated_at || new Date().toISOString()
            },

            // Help text for UI
            permission_prompt: {
                show_prompt: userPreferences?.permission_status === 'denied' || userPreferences?.permission_status === 'not_determined',
                prompt_message: "Enable notifications to stay updated with breaking news and your personalized feed",
                settings_deeplink: platform === 'ios' ? 'app-settings:' : 'android.settings.APP_NOTIFICATION_SETTINGS'
            }
        };

        // Track settings check (analytics)
        if (user_id || device_id) {
            const { error: trackError } = await supabase
                .from('interactions')
                .insert({
                    user_id: user_id || null,
                    device_id: device_id || null,
                    interaction_type: 'notification_settings_check',
                    metadata: {
                        platform: platform,
                        permission_status: notificationSettings.permission_status
                    },
                    created_at: new Date().toISOString()
                });

            if (trackError) {
                console.log('Could not track settings check:', trackError.message);
            }
        }

        apiResponse(res, true, notificationSettings);

    } catch (error) {
        console.error('Error in /get-notification-settings:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Get Notifications API - Fetch user notifications
router.get('/get-notifications', async (req, res) => {
    try {
        const user_id = req.query.user_id || req.headers['x-user-id'];
        const device_id = req.query.device_id || req.headers['x-device-id'];
        const filter = req.query.filter || 'all'; // all, unread, read
        const limit = parseInt(req.query.limit) || 20;
        const offset = parseInt(req.query.offset) || 0;

        if (!user_id && !device_id) {
            apiResponse(res, false, null, 'User ID or Device ID is required', 400);
            return;
        }

        // Mock notifications data (in production, fetch from notifications table)
        const allNotifications = [
            {
                id: 1,
                type: 'breaking_news',
                title: '🔴 Breaking News Alert',
                message: 'Major technology announcement: AI breakthrough changes everything',
                article_id: 2,
                category_id: 2,
                priority: 'high',
                icon: 'breaking_news',
                image_url: null,
                is_read: false,
                action_url: '/article/2',
                created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString() // 2 hours ago
            },
            {
                id: 2,
                type: 'daily_digest',
                title: '📰 Your Morning Brief',
                message: 'Top 5 stories you should read today',
                article_id: null,
                category_id: null,
                priority: 'normal',
                icon: 'daily_digest',
                image_url: null,
                is_read: false,
                action_url: '/daily-digest',
                created_at: new Date(Date.now() - 4 * 60 * 60 * 1000).toISOString() // 4 hours ago
            },
            {
                id: 3,
                type: 'bookmark_reminder',
                title: '📌 Saved Article Reminder',
                message: 'You have 3 unread bookmarked articles',
                article_id: null,
                category_id: null,
                priority: 'low',
                icon: 'bookmark',
                image_url: null,
                is_read: true,
                action_url: '/bookmarks',
                created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString() // 1 day ago
            },
            {
                id: 4,
                type: 'trending',
                title: '🔥 Trending Now',
                message: 'This story is trending in your area',
                article_id: 3,
                category_id: 5,
                priority: 'normal',
                icon: 'trending',
                image_url: null,
                is_read: false,
                action_url: '/article/3',
                created_at: new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString() // 6 hours ago
            },
            {
                id: 5,
                type: 'new_category',
                title: '✨ New Category Available',
                message: 'Sports news is now available in your feed',
                article_id: null,
                category_id: 4,
                priority: 'low',
                icon: 'new',
                image_url: null,
                is_read: true,
                action_url: '/category/sports',
                created_at: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString() // 2 days ago
            }
        ];

        // Filter notifications based on read status
        let filteredNotifications = allNotifications;
        if (filter === 'unread') {
            filteredNotifications = allNotifications.filter(n => !n.is_read);
        } else if (filter === 'read') {
            filteredNotifications = allNotifications.filter(n => n.is_read);
        }

        // Apply pagination
        const paginatedNotifications = filteredNotifications.slice(offset, offset + limit);

        // Calculate counts
        const unreadCount = allNotifications.filter(n => !n.is_read).length;
        const totalCount = allNotifications.length;

        // Format response
        const response = {
            notifications: paginatedNotifications.map(notif => ({
                ...notif,
                time_ago: getTimeAgo(new Date(notif.created_at))
            })),
            counts: {
                total: totalCount,
                unread: unreadCount,
                read: totalCount - unreadCount
            },
            pagination: {
                current_page: Math.floor(offset / limit) + 1,
                per_page: limit,
                total_pages: Math.ceil(filteredNotifications.length / limit),
                showing: {
                    from: offset + 1,
                    to: Math.min(offset + limit, filteredNotifications.length)
                }
            },
            filter_applied: filter,
            last_checked: new Date().toISOString()
        };

        // Track notification fetch (analytics)
        if (user_id || device_id) {
            const { error: trackError } = await supabase
                .from('interactions')
                .insert({
                    user_id: user_id || null,
                    device_id: device_id || null,
                    interaction_type: 'notifications_viewed',
                    metadata: {
                        filter: filter,
                        count: paginatedNotifications.length
                    },
                    created_at: new Date().toISOString()
                });

            if (trackError) {
                console.log('Could not track notification view:', trackError.message);
            }
        }

        apiResponse(res, true, response);

    } catch (error) {
        console.error('Error in /get-notifications:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Helper function to calculate time ago
function getTimeAgo(date) {
    const seconds = Math.floor((new Date() - date) / 1000);

    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return interval + ' years ago';
    if (interval === 1) return '1 year ago';

    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return interval + ' months ago';
    if (interval === 1) return '1 month ago';

    interval = Math.floor(seconds / 86400);
    if (interval > 1) return interval + ' days ago';
    if (interval === 1) return '1 day ago';

    interval = Math.floor(seconds / 3600);
    if (interval > 1) return interval + ' hours ago';
    if (interval === 1) return '1 hour ago';

    interval = Math.floor(seconds / 60);
    if (interval > 1) return interval + ' minutes ago';
    if (interval === 1) return '1 minute ago';

    return 'Just now';
}

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

// POST /api/update-token
// Update push notification token for a device
router.post('/update-token', async (req, res) => {
    const {
        user_id,
        device_id,
        token,
        platform,
        device_info
    } = req.body;

    console.log('📱 Updating push notification token:', {
        user_id,
        device_id,
        platform,
        token_preview: token ? token.substring(0, 20) + '...' : null
    });

    try {
        // Validate required fields
        if (!token) {
            return res.status(400).json({
                success: false,
                message: 'Token is required',
                data: null
            });
        }

        if (!device_id && !user_id) {
            return res.status(400).json({
                success: false,
                message: 'Either device_id or user_id is required',
                data: null
            });
        }

        if (!platform || !['ios', 'android', 'web'].includes(platform)) {
            return res.status(400).json({
                success: false,
                message: 'Valid platform (ios/android/web) is required',
                data: null
            });
        }

        // Check if token already exists
        let existingToken = null;

        // Try to find existing token by device_id or user_id
        const { data: existingTokens, error: checkError } = await supabase
            .from('push_tokens')
            .select('*')
            .or(`device_id.eq.${device_id || ''},user_id.eq.${user_id || ''}`)
            .single();

        if (checkError && checkError.code !== 'PGRST116') { // PGRST116 = no rows returned
            console.log('📝 Note: push_tokens table might not exist yet');
        }

        // Prepare token data
        const tokenData = {
            token,
            platform,
            device_id: device_id || null,
            user_id: user_id || null,
            device_info: device_info || {},
            is_active: true,
            last_used: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };

        let result;

        if (existingTokens) {
            // Update existing token
            const { data, error } = await supabase
                .from('push_tokens')
                .update(tokenData)
                .eq('id', existingTokens.id)
                .select()
                .single();

            if (error) {
                console.error('Error updating token:', error);
                // If table doesn't exist, provide instructions
                if (error.code === '42P01') {
                    return res.json({
                        success: true,
                        message: 'Token update simulated (table needs to be created)',
                        data: {
                            ...tokenData,
                            id: 'simulated_' + Date.now(),
                            created_at: new Date().toISOString(),
                            instructions: 'Run the following SQL in Supabase to create the push_tokens table',
                            sql: `
-- Create push_tokens table
CREATE TABLE IF NOT EXISTS push_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    device_id VARCHAR(255),
    token TEXT NOT NULL UNIQUE,
    platform VARCHAR(50) NOT NULL CHECK (platform IN ('ios', 'android', 'web')),
    device_info JSONB DEFAULT '{}',
    is_active BOOLEAN DEFAULT true,
    last_used TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,

    -- Ensure either user_id or device_id is present
    CONSTRAINT has_identifier CHECK (user_id IS NOT NULL OR device_id IS NOT NULL),
    -- Unique constraint on device_id when present
    CONSTRAINT unique_device UNIQUE (device_id),
    -- Unique constraint on user_id + platform combination
    CONSTRAINT unique_user_platform UNIQUE (user_id, platform)
);

-- Create indexes
CREATE INDEX idx_push_tokens_user_id ON push_tokens(user_id);
CREATE INDEX idx_push_tokens_device_id ON push_tokens(device_id);
CREATE INDEX idx_push_tokens_token ON push_tokens(token);
CREATE INDEX idx_push_tokens_active ON push_tokens(is_active);

-- Grant permissions
GRANT ALL ON push_tokens TO authenticated;
GRANT ALL ON push_tokens TO anon;
GRANT USAGE ON SEQUENCE push_tokens_id_seq TO authenticated;
GRANT USAGE ON SEQUENCE push_tokens_id_seq TO anon;`
                        }
                    });
                }
                throw error;
            }

            result = data;
            console.log('✅ Token updated successfully');
        } else {
            // Insert new token
            tokenData.created_at = new Date().toISOString();

            const { data, error } = await supabase
                .from('push_tokens')
                .insert([tokenData])
                .select()
                .single();

            if (error) {
                console.error('Error inserting token:', error);
                // If table doesn't exist, provide instructions
                if (error.code === '42P01') {
                    return res.json({
                        success: true,
                        message: 'Token registration simulated (table needs to be created)',
                        data: {
                            ...tokenData,
                            id: 'simulated_' + Date.now(),
                            instructions: 'Create push_tokens table in Supabase first'
                        }
                    });
                }
                throw error;
            }

            result = data;
            console.log('✅ Token registered successfully');
        }

        // Track token update in interactions table
        await supabase
            .from('interactions')
            .insert([{
                user_id: user_id || null,
                device_id: device_id || null,
                interaction_type: 'token_update',
                metadata: {
                    platform,
                    token_preview: token.substring(0, 20) + '...'
                }
            }]);

        res.json({
            success: true,
            message: existingTokens ? 'Token updated successfully' : 'Token registered successfully',
            data: {
                id: result?.id || 'simulated_' + Date.now(),
                device_id: result?.device_id || device_id,
                user_id: result?.user_id || user_id,
                platform: result?.platform || platform,
                is_active: result?.is_active !== undefined ? result.is_active : true,
                last_used: result?.last_used || new Date().toISOString()
            }
        });

    } catch (error) {
        console.error('Error in update-token API:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update token',
            error: error.message
        });
    }
});

module.exports = router;