/**
 * MEDIASTACK SYNC ROUTES
 * API endpoints for syncing news articles from Mediastack to database
 */

const express = require('express');
const router = express.Router();
const mediastackService = require('../services/mediastack-service');
const articleSerializer = require('../services/article-serializer');
const { requireAdmin } = require('../middleware/auth');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

/**
 * GET /api/mediastack/status
 * Check Mediastack API status
 * Public endpoint
 */
router.get('/status', async (req, res) => {
    try {
        const status = await mediastackService.checkStatus();
        apiResponse(res, status.status === 'active', status);
    } catch (error) {
        console.error('Error checking Mediastack status:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

/**
 * GET /api/mediastack/categories
 * Get available Mediastack categories
 * Public endpoint
 */
router.get('/categories', (req, res) => {
    const categories = mediastackService.getAvailableCategories();
    apiResponse(res, true, { categories });
});

/**
 * GET /api/mediastack/countries
 * Get available country codes
 * Public endpoint
 */
router.get('/countries', (req, res) => {
    const countries = mediastackService.getAvailableCountries();
    apiResponse(res, true, { countries });
});

/**
 * POST /api/mediastack/sync
 * Manually trigger sync from Mediastack to database
 * Admin only - requires authentication
 *
 * Request Body (all optional):
 * {
 *   "countries": "us,in,gb",
 *   "categories": "general,technology",
 *   "limit": 50,
 *   "keywords": "technology"
 * }
 */
router.post('/sync', requireAdmin, async (req, res) => {
    try {
        const {
            countries,
            categories,
            limit,
            keywords
        } = req.body;

        console.log(`🔄 Manual sync triggered by ${req.admin.username}`);

        // Fetch articles from Mediastack
        const response = await mediastackService.fetchNews({
            countries: countries || 'us,in,gb',
            categories: categories || 'general,business,technology,sports,entertainment,health,science',
            limit: limit || 5,
            keywords: keywords
        });

        if (!response.success || !response.articles || response.articles.length === 0) {
            return apiResponse(res, false, null, 'No articles found from Mediastack', 404);
        }

        // Filter valid articles
        const validArticles = response.articles.filter(article =>
            mediastackService.isValidArticle(article)
        );

        console.log(`✅ Filtered ${validArticles.length} valid articles out of ${response.articles.length}`);

        if (validArticles.length === 0) {
            return apiResponse(res, false, {
                fetched: response.articles.length,
                valid: 0
            }, 'No valid articles to save', 400);
        }

        // Save articles to database
        const saveResults = await articleSerializer.batchSaveArticles(validArticles);

        console.log(`📊 Sync Results:`, saveResults);

        apiResponse(res, true, {
            fetched: response.articles.length,
            valid: validArticles.length,
            saved: saveResults.saved,
            duplicates: saveResults.duplicates,
            errors: saveResults.errors,
            pagination: response.pagination
        }, `Successfully synced ${saveResults.saved} new articles`);

    } catch (error) {
        console.error('❌ Sync error:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

/**
 * POST /api/mediastack/sync-category
 * Sync articles from specific category
 * Admin only
 *
 * Request Body:
 * {
 *   "category": "technology",
 *   "limit": 20
 * }
 */
router.post('/sync-category', requireAdmin, async (req, res) => {
    try {
        const { category, limit } = req.body;

        if (!category) {
            return apiResponse(res, false, null, 'Category is required', 400);
        }

        console.log(`🔄 Syncing category: ${category}`);

        const response = await mediastackService.fetchByCategory(category, limit || 20);

        if (!response.success || !response.articles || response.articles.length === 0) {
            return apiResponse(res, false, null, `No articles found for category: ${category}`, 404);
        }

        const validArticles = response.articles.filter(article =>
            mediastackService.isValidArticle(article)
        );

        const saveResults = await articleSerializer.batchSaveArticles(validArticles);

        apiResponse(res, true, {
            category: category,
            fetched: response.articles.length,
            valid: validArticles.length,
            saved: saveResults.saved,
            duplicates: saveResults.duplicates,
            errors: saveResults.errors
        }, `Synced ${saveResults.saved} articles from ${category}`);

    } catch (error) {
        console.error('❌ Category sync error:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

/**
 * POST /api/mediastack/search-sync
 * Search and sync articles by keywords
 * Admin only
 *
 * Request Body:
 * {
 *   "keywords": "artificial intelligence",
 *   "limit": 20
 * }
 */
router.post('/search-sync', requireAdmin, async (req, res) => {
    try {
        const { keywords, limit } = req.body;

        if (!keywords) {
            return apiResponse(res, false, null, 'Keywords are required', 400);
        }

        console.log(`🔍 Searching and syncing: ${keywords}`);

        const response = await mediastackService.searchNews(keywords, limit || 20);

        if (!response.success || !response.articles || response.articles.length === 0) {
            return apiResponse(res, false, null, `No articles found for: ${keywords}`, 404);
        }

        const validArticles = response.articles.filter(article =>
            mediastackService.isValidArticle(article)
        );

        const saveResults = await articleSerializer.batchSaveArticles(validArticles);

        apiResponse(res, true, {
            keywords: keywords,
            fetched: response.articles.length,
            valid: validArticles.length,
            saved: saveResults.saved,
            duplicates: saveResults.duplicates,
            errors: saveResults.errors
        }, `Synced ${saveResults.saved} articles for "${keywords}"`);

    } catch (error) {
        console.error('❌ Search sync error:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

/**
 * GET /api/mediastack/preview
 * Preview articles from Mediastack without saving
 * Admin only
 */
router.get('/preview', requireAdmin, async (req, res) => {
    try {
        const {
            countries,
            categories,
            limit,
            keywords
        } = req.query;

        const response = await mediastackService.fetchNews({
            countries: countries || 'us',
            categories: categories || 'general',
            limit: parseInt(limit) || 10,
            keywords: keywords
        });

        if (!response.success || !response.articles || response.articles.length === 0) {
            return apiResponse(res, false, null, 'No articles found', 404);
        }

        // Return preview with serialized data
        const preview = response.articles.map(article => ({
            original: article,
            serialized: articleSerializer.serializeArticle(article),
            isValid: mediastackService.isValidArticle(article)
        }));

        apiResponse(res, true, {
            count: preview.length,
            pagination: response.pagination,
            articles: preview
        });

    } catch (error) {
        console.error('❌ Preview error:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

/**
 * POST /api/mediastack/sync-no-ai
 * Sync articles WITHOUT AI summary processing (TEST ENDPOINT)
 * Uses Mediastack description directly as summary
 * Admin only
 *
 * Request Body (all optional):
 * {
 *   "countries": "us,in,gb",
 *   "categories": "general,technology",
 *   "limit": 50,
 *   "keywords": "technology"
 * }
 */
router.post('/sync-no-ai', requireAdmin, async (req, res) => {
    try {
        const {
            countries,
            categories,
            limit,
            keywords
        } = req.body;

        console.log(`🔄 Manual sync (NO AI) triggered by ${req.admin.username}`);

        // Fetch articles from Mediastack
        const response = await mediastackService.fetchNews({
            countries: countries || 'us,in,gb',
            categories: categories || 'general,business,technology,sports,entertainment,health,science',
            limit: limit || 5,
            keywords: keywords
        });

        if (!response.success || !response.articles || response.articles.length === 0) {
            return apiResponse(res, false, null, 'No articles found from Mediastack', 404);
        }

        // Filter valid articles
        const validArticles = response.articles.filter(article =>
            mediastackService.isValidArticle(article)
        );

        console.log(`✅ Filtered ${validArticles.length} valid articles out of ${response.articles.length}`);

        if (validArticles.length === 0) {
            return apiResponse(res, false, {
                fetched: response.articles.length,
                valid: 0
            }, 'No valid articles to save', 400);
        }

        // Save articles WITHOUT AI processing
        const results = {
            total: validArticles.length,
            saved: 0,
            duplicates: 0,
            errors: 0,
            articles: []
        };

        for (const article of validArticles) {
            try {
                console.log(`\n📰 Processing (NO AI): ${article.title.substring(0, 60)}...`);

                // Serialize WITHOUT AI summary (pass null for aiSummary)
                const serialized = articleSerializer.serializeArticle(article, null);
                const result = await articleSerializer.saveArticle(serialized);

                if (result.success) {
                    results.saved++;
                    results.articles.push(result.article);
                    console.log(`   ✅ Saved successfully (using Mediastack description directly)`);
                } else if (result.duplicate) {
                    results.duplicates++;
                    console.log(`   ⏭️  Skipped (duplicate)`);
                } else {
                    results.errors++;
                    console.log(`   ❌ Error: ${result.error}`);
                }
            } catch (error) {
                console.error('   ❌ Error processing article:', error.message);
                results.errors++;
            }
        }

        console.log(`📊 Sync Results (NO AI):`, results);

        apiResponse(res, true, {
            mode: 'NO_AI_PROCESSING',
            fetched: response.articles.length,
            valid: validArticles.length,
            saved: results.saved,
            duplicates: results.duplicates,
            errors: results.errors,
            pagination: response.pagination
        }, `Successfully synced ${results.saved} new articles (without AI processing)`);

    } catch (error) {
        console.error('❌ Sync error:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;
