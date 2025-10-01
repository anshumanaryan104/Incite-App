const express = require('express');
const router = express.Router();
const { supabase } = require('../supabase-client');
const { requireAdmin, requireRole } = require('../middleware/auth');
const { logAudit } = require('../helpers/audit');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

router.post('/', requireAdmin, async (req, res) => {
    try {
        const {
            title,
            description,
            content,
            // category_id removed
            featured_image,
            images,
            type,
            is_featured
        } = req.body;

        if (!title || !content) {
            return apiResponse(res, false, null, 'Title and content required', 400);
        }

        const slug = title.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '')
            + '-' + Date.now();

        const { data: article, error } = await supabase
            .from('articles')
            .insert([{
                title,
                slug,
                description,
                content,
                // category_id removed
                featured_image,
                images: images || [featured_image],
                type: type || 'article',
                is_featured: is_featured || false,
                status: 'published',
                published_at: new Date().toISOString(),
                created_by: req.admin.username
            }])
            .select()
            .single();

        if (error) throw error;

        await logAudit({
            table_name: 'articles',
            record_id: article.id,
            action: 'CREATE',
            admin_username: req.admin.username,
            new_values: article,
            ip_address: req.ip || req.connection.remoteAddress,
            user_agent: req.headers['user-agent']
        });

        apiResponse(res, true, article, `Article created by ${req.admin.username}`);

    } catch (error) {
        console.error('Error creating article:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.put('/:id', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        const {
            title,
            description,
            content,
            // category_id removed
            featured_image,
            images,
            type,
            is_featured,
            status
        } = req.body;

        const { data: oldArticle } = await supabase
            .from('articles')
            .select('*')
            .eq('id', id)
            .single();

        if (!oldArticle) {
            return apiResponse(res, false, null, 'Article not found', 404);
        }

        const updateData = {
            updated_by: req.admin.username,
            updated_at: new Date().toISOString()
        };

        if (title) updateData.title = title;
        if (description) updateData.description = description;
        if (content) updateData.content = content;
        // category_id removed
        if (featured_image) updateData.featured_image = featured_image;
        if (images) updateData.images = images;
        if (type) updateData.type = type;
        if (is_featured !== undefined) updateData.is_featured = is_featured;
        if (status) updateData.status = status;

        const { data: article, error } = await supabase
            .from('articles')
            .update(updateData)
            .eq('id', id)
            .select()
            .single();

        if (error) throw error;

        await logAudit({
            table_name: 'articles',
            record_id: article.id,
            action: 'UPDATE',
            admin_username: req.admin.username,
            old_values: oldArticle,
            new_values: article,
            ip_address: req.ip || req.connection.remoteAddress,
            user_agent: req.headers['user-agent']
        });

        apiResponse(res, true, article, `Article updated by ${req.admin.username}`);

    } catch (error) {
        console.error('Error updating article:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.delete('/:id', requireAdmin, requireRole(['admin', 'super_admin']), async (req, res) => {
    try {
        const { id } = req.params;

        const { data: oldArticle } = await supabase
            .from('articles')
            .select('*')
            .eq('id', id)
            .single();

        if (!oldArticle) {
            return apiResponse(res, false, null, 'Article not found', 404);
        }

        const { data: article, error } = await supabase
            .from('articles')
            .update({
                status: 'deleted',
                deleted_by: req.admin.username,
                deleted_at: new Date().toISOString()
            })
            .eq('id', id)
            .select()
            .single();

        if (error) throw error;

        await logAudit({
            table_name: 'articles',
            record_id: article.id,
            action: 'DELETE',
            admin_username: req.admin.username,
            old_values: oldArticle,
            new_values: article,
            ip_address: req.ip || req.connection.remoteAddress,
            user_agent: req.headers['user-agent']
        });

        apiResponse(res, true, null, `Article deleted by ${req.admin.username}`);

    } catch (error) {
        console.error('Error deleting article:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.get('/', requireAdmin, async (req, res) => {
    try {
        const page = parseInt(req.query.page) || 1;
        const limit = parseInt(req.query.limit) || 20;
        const status = req.query.status || 'published';
        const from = (page - 1) * limit;
        const to = from + limit - 1;

        const { data: articles, error, count } = await supabase
            .from('articles')
            .select('*', { count: 'exact' })
            .eq('status', status)
            .order('created_at', { ascending: false })
            .range(from, to);

        if (error) throw error;

        apiResponse(res, true, {
            articles,
            pagination: {
                page,
                limit,
                total: count,
                totalPages: Math.ceil(count / limit)
            }
        });

    } catch (error) {
        console.error('Error fetching articles:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.get('/:id/audit-logs', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;

        const { data: logs, error } = await supabase
            .from('audit_logs')
            .select('*')
            .eq('table_name', 'articles')
            .eq('record_id', id)
            .order('created_at', { ascending: false });

        if (error) throw error;

        apiResponse(res, true, logs);

    } catch (error) {
        console.error('Error fetching audit logs:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;