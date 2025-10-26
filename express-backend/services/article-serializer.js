/**
 * ARTICLE SERIALIZER
 * Maps Mediastack API data to Supabase database schema
 * Handles data transformation and validation
 */

const { supabase } = require('../supabase-client');
const axios = require('axios');

// AI Summary Service URL
const AI_SUMMARY_URL = process.env.AI_SUMMARY_URL || 'http://localhost:8001';

class ArticleSerializer {
    /**
     * Map Mediastack article to database schema
     * @param {Object} mediastackArticle - Article from Mediastack API
     * @param {string} aiSummary - AI-generated summary (optional)
     * @returns {Object} Article in database format
     */
    serializeArticle(mediastackArticle, aiSummary = null) {
        return {
            // Basic Info
            title: mediastackArticle.title,
            // description = AI summary (if available) OR fallback to original
            description: aiSummary || mediastackArticle.description || this.extractDescription(mediastackArticle.title),
            // content = Original Mediastack description (for reference/AI chatbot)
            content: mediastackArticle.description || '',

            // Media
            featured_image: mediastackArticle.image,
            images: mediastackArticle.image ? [mediastackArticle.image] : [],

            // Metadata
            type: 'article',
            status: 'published',

            // Metrics (start at 0)
            views: 0,

            // Source info
            source_name: mediastackArticle.source,
            source_url: mediastackArticle.url,
            author_name: mediastackArticle.author,

            // Publishing
            published_at: mediastackArticle.published_at,

            // Additional metadata
            tags: this.extractTags(mediastackArticle),

            // Voice/TTS
            accent_code: this.mapLanguageToAccent(mediastackArticle.language),

            // Audit
            created_by: 'mediastack_sync',
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };
    }

    /**
     * Get AI-generated summary from Python service
     * @param {Object} mediastackArticle - Article from Mediastack
     * @returns {Promise<string|null>} AI summary or null if failed
     */
    async getAISummary(mediastackArticle) {
        try {
            console.log(`   🤖 Requesting AI summary from ${AI_SUMMARY_URL}...`);

            const response = await axios.post(
                `${AI_SUMMARY_URL}/api/summarize`,
                {
                    title: mediastackArticle.title,
                    description: mediastackArticle.description,
                    source: mediastackArticle.source,
                    author: mediastackArticle.author,
                    published_at: mediastackArticle.published_at
                },
                {
                    timeout: 30000 // 30 second timeout
                }
            );

            if (response.data && response.data.success) {
                console.log(`   ✅ AI summary generated successfully`);
                return response.data.summary;
            }

            console.log(`   ⚠️ AI summary failed, using original description`);
            return null;

        } catch (error) {
            console.error(`   ❌ AI summary error:`, error.message);
            // Fallback to original description if AI service fails
            return null;
        }
    }

    // Slug generation removed - not needed anymore

    /**
     * Map language code to accent code for TTS
     * @param {string} language - ISO 639-1 language code
     * @returns {string} Accent code
     */
    mapLanguageToAccent(language) {
        const accentMap = {
            'en': 'en',
            'es': 'es',
            'fr': 'fr',
            'de': 'de',
            'it': 'it',
            'pt': 'pt',
            'ar': 'ar',
            'zh': 'zh',
            'ja': 'ja',
            'ko': 'ko',
            'hi': 'hi'
        };

        return accentMap[language] || 'en';
    }

    /**
     * Extract description from title if description is missing
     * @param {string} title - Article title
     * @returns {string} Description
     */
    extractDescription(title) {
        // If no description, use first 150 chars of title
        return title.length > 150
            ? title.substring(0, 147) + '...'
            : title;
    }

    /**
     * Generate full article content
     * Note: Mediastack only provides title, description, and URL
     * Full content would require web scraping (not included here)
     * @param {Object} article - Mediastack article
     * @returns {string} HTML content
     */
    generateContent(article) {
        // Generate basic HTML content from available data
        let content = `<article class="news-article">`;

        if (article.image) {
            content += `<img src="${article.image}" alt="${article.title}" class="article-image" />`;
        }

        content += `<h1>${article.title}</h1>`;

        if (article.description) {
            content += `<p class="lead">${article.description}</p>`;
        }

        if (article.author) {
            content += `<p class="author">By ${article.author}</p>`;
        }

        content += `<p class="source">Source: <a href="${article.url}" target="_blank">${article.source}</a></p>`;

        content += `<p><em>Note: This article was imported from ${article.source}. For the full story, please visit the source link above.</em></p>`;

        content += `</article>`;

        return content;
    }

    /**
     * Extract tags from article metadata
     * @param {Object} article - Mediastack article
     * @returns {Array<string>} Tags
     */
    extractTags(article) {
        const tags = [];

        // Add category as tag
        if (article.category) {
            tags.push(article.category);
        }

        // Add country as tag
        if (article.country) {
            tags.push(article.country.toUpperCase());
        }

        // Add source as tag
        if (article.source) {
            tags.push(article.source);
        }

        return tags;
    }

    // Categories removed - not needed anymore

    /**
     * Check if article already exists in database
     * @param {string} sourceUrl - Article source URL
     * @returns {Promise<boolean>} Whether article exists
     */
    async articleExists(sourceUrl) {
        try {
            const { data, error } = await supabase
                .from('articles')
                .select('id')
                .eq('source_url', sourceUrl)
                .single();

            return !!data && !error;
        } catch (error) {
            return false;
        }
    }

    /**
     * Save serialized article to database
     * @param {Object} serializedArticle - Article in database format
     * @returns {Promise<Object>} Result
     */
    async saveArticle(serializedArticle) {
        try {
            // Check if article already exists
            if (await this.articleExists(serializedArticle.source_url)) {
                return {
                    success: false,
                    duplicate: true,
                    message: 'Article already exists'
                };
            }

            // Insert article
            const { data, error } = await supabase
                .from('articles')
                .insert([serializedArticle])
                .select()
                .single();

            if (error) {
                console.error('Error saving article:', error);
                return {
                    success: false,
                    error: error.message
                };
            }

            return {
                success: true,
                article: data
            };

        } catch (error) {
            console.error('Error in saveArticle:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    /**
     * Batch save multiple articles
     * @param {Array<Object>} mediastackArticles - Articles from Mediastack
     * @returns {Promise<Object>} Batch save result
     */
    async batchSaveArticles(mediastackArticles) {
        const results = {
            total: mediastackArticles.length,
            saved: 0,
            duplicates: 0,
            errors: 0,
            articles: []
        };

        for (const article of mediastackArticles) {
            try {
                console.log(`\n📰 Processing: ${article.title.substring(0, 60)}...`);

                // SKIP AI PROCESSING - Use Mediastack description directly
                // const aiSummary = await this.getAISummary(article);
                const aiSummary = null; // This will use original Mediastack description

                // Pass null for AI summary to use original description
                const serialized = this.serializeArticle(article, aiSummary);
                const result = await this.saveArticle(serialized);

                if (result.success) {
                    results.saved++;
                    results.articles.push(result.article);
                    console.log(`   ✅ Saved successfully (using original Mediastack description)`);
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

        return results;
    }
}

module.exports = new ArticleSerializer();
