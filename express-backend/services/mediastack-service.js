/**
 * MEDIASTACK API SERVICE
 * Handles fetching news articles from Mediastack API
 * Documentation: https://mediastack.com/documentation
 */

const axios = require('axios');

class MediastackService {
    constructor() {
        this.apiKey = process.env.MEDIASTACK_API_KEY;
        this.baseUrl = 'http://api.mediastack.com/v1';

        if (!this.apiKey) {
            console.warn('⚠️ MEDIASTACK_API_KEY not found in .env file');
        }
    }

    /**
     * Fetch latest news articles from Mediastack
     * @param {Object} options - Query parameters
     * @param {string} options.countries - Comma-separated country codes (e.g., 'us,in,gb')
     * @param {string} options.categories - Comma-separated categories (e.g., 'general,business,sports')
     * @param {string} options.languages - Comma-separated language codes (e.g., 'en')
     * @param {number} options.limit - Number of results (max 100)
     * @param {number} options.offset - Pagination offset
     * @param {string} options.keywords - Search keywords
     * @returns {Promise<Object>} Mediastack API response
     */
    async fetchNews(options = {}) {
        if (!this.apiKey) {
            throw new Error('Mediastack API key is not configured');
        }

        const params = {
            access_key: this.apiKey,
            countries: options.countries || 'us,in,gb', // US, India, UK
            categories: options.categories || 'general,business,technology,sports,entertainment,health,science',
            languages: options.languages || 'en',
            limit: options.limit || 50, // Max articles per request (max 100)
            offset: options.offset || 0,
            sort: options.sort || 'published_desc', // Latest first
            ...options
        };

        // Add keywords if provided
        if (options.keywords) {
            params.keywords = options.keywords;
        }

        try {
            console.log('🔄 Fetching news from Mediastack API...');
            console.log('📊 Params:', { ...params, access_key: '***' });

            const response = await axios.get(`${this.baseUrl}/news`, {
                params,
                timeout: 30000 // 30 second timeout
            });

            if (response.data.error) {
                throw new Error(`Mediastack API Error: ${response.data.error.message || response.data.error.code}`);
            }

            const { pagination, data } = response.data;

            console.log(`✅ Fetched ${data?.length || 0} articles from Mediastack`);
            console.log(`📄 Pagination: ${pagination?.offset || 0} - ${pagination?.count || 0} of ${pagination?.total || 0}`);

            return {
                success: true,
                pagination: {
                    limit: pagination?.limit || 0,
                    offset: pagination?.offset || 0,
                    count: pagination?.count || 0,
                    total: pagination?.total || 0
                },
                articles: data || []
            };

        } catch (error) {
            console.error('❌ Mediastack API Error:', error.message);

            if (error.response) {
                console.error('Response status:', error.response.status);
                console.error('Response data:', error.response.data);
            }

            throw new Error(`Failed to fetch from Mediastack: ${error.message}`);
        }
    }

    /**
     * Fetch news by specific category
     * @param {string} category - Category name (general, business, sports, etc.)
     * @param {number} limit - Number of articles
     * @returns {Promise<Object>}
     */
    async fetchByCategory(category, limit = 20) {
        return this.fetchNews({
            categories: category,
            limit: limit
        });
    }

    /**
     * Fetch news by country
     * @param {string} country - Country code (us, in, gb, etc.)
     * @param {number} limit - Number of articles
     * @returns {Promise<Object>}
     */
    async fetchByCountry(country, limit = 20) {
        return this.fetchNews({
            countries: country,
            limit: limit
        });
    }

    /**
     * Search news by keywords
     * @param {string} keywords - Search keywords
     * @param {number} limit - Number of articles
     * @returns {Promise<Object>}
     */
    async searchNews(keywords, limit = 20) {
        return this.fetchNews({
            keywords: keywords,
            limit: limit
        });
    }

    /**
     * Validate article data quality
     * @param {Object} article - Mediastack article
     * @returns {boolean} Whether article meets quality standards
     */
    isValidArticle(article) {
        // Filter out low-quality articles
        const hasTitle = article.title && article.title.length > 10;
        const hasDescription = article.description && article.description.length > 20;
        const hasUrl = article.url && article.url.startsWith('http');
        const hasSource = article.source;

        // Optional: Require image (comment out if too strict)
        const hasImage = article.image && article.image.startsWith('http');

        return hasTitle && hasDescription && hasUrl && hasSource && hasImage;
    }

    /**
     * Get available Mediastack categories
     * @returns {Array<string>}
     */
    getAvailableCategories() {
        return [
            'general',
            'business',
            'entertainment',
            'health',
            'science',
            'sports',
            'technology'
        ];
    }

    /**
     * Get available country codes
     * @returns {Array<Object>}
     */
    getAvailableCountries() {
        return [
            { code: 'us', name: 'United States' },
            { code: 'in', name: 'India' },
            { code: 'gb', name: 'United Kingdom' },
            { code: 'ca', name: 'Canada' },
            { code: 'au', name: 'Australia' },
            { code: 'de', name: 'Germany' },
            { code: 'fr', name: 'France' },
            { code: 'jp', name: 'Japan' },
            { code: 'cn', name: 'China' }
        ];
    }

    /**
     * Check API status and remaining quota
     * @returns {Promise<Object>}
     */
    async checkStatus() {
        try {
            const response = await this.fetchNews({ limit: 1 });
            return {
                status: 'active',
                apiKey: this.apiKey ? 'configured' : 'missing',
                message: 'Mediastack API is working correctly'
            };
        } catch (error) {
            return {
                status: 'error',
                apiKey: this.apiKey ? 'configured' : 'missing',
                message: error.message
            };
        }
    }
}

module.exports = new MediastackService();
