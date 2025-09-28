const express = require('express');
const router = express.Router();
const { supabase } = require('../supabase-client');
const { requireAdmin, requireRole } = require('../middleware/auth');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

// Get all categories (admin view with full details)
router.get('/', requireAdmin, async (req, res) => {
    try {
        const { data: categories, error } = await supabase
            .from('categories')
            .select('*')
            .order('name', { ascending: true });

        if (error) throw error;

        apiResponse(res, true, categories);
    } catch (error) {
        console.error('Error fetching categories:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Create new category
router.post('/', requireAdmin, requireRole(['admin', 'super_admin']), async (req, res) => {
    try {
        const { name, slug, color, description } = req.body;

        if (!name || !slug) {
            return apiResponse(res, false, null, 'Name and slug are required', 400);
        }

        // Check if slug already exists
        const { data: existing } = await supabase
            .from('categories')
            .select('id')
            .eq('slug', slug)
            .single();

        if (existing) {
            return apiResponse(res, false, null, 'Category with this slug already exists', 400);
        }

        const { data: category, error } = await supabase
            .from('categories')
            .insert([{
                name,
                slug,
                color: color || '#3B82F6',
                description: description || null,
                is_active: true
            }])
            .select()
            .single();

        if (error) throw error;

        console.log(`✅ Category created: ${category.name} by admin`);
        apiResponse(res, true, category, 'Category created successfully', 201);
    } catch (error) {
        console.error('Error creating category:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Update category
router.put('/:id', requireAdmin, requireRole(['admin', 'super_admin']), async (req, res) => {
    try {
        const { id } = req.params;
        const { name, slug, color, description, is_active } = req.body;

        if (!name || !slug) {
            return apiResponse(res, false, null, 'Name and slug are required', 400);
        }

        // Check if slug already exists for another category
        const { data: existing } = await supabase
            .from('categories')
            .select('id')
            .eq('slug', slug)
            .neq('id', id)
            .single();

        if (existing) {
            return apiResponse(res, false, null, 'Another category with this slug already exists', 400);
        }

        const updateData = {
            name,
            slug,
            color: color || '#3B82F6',
            description: description || null,
            updated_at: new Date().toISOString()
        };

        if (typeof is_active !== 'undefined') {
            updateData.is_active = is_active;
        }

        const { data: category, error } = await supabase
            .from('categories')
            .update(updateData)
            .eq('id', id)
            .select()
            .single();

        if (error) throw error;

        if (!category) {
            return apiResponse(res, false, null, 'Category not found', 404);
        }

        console.log(`✅ Category updated: ${category.name}`);
        apiResponse(res, true, category, 'Category updated successfully');
    } catch (error) {
        console.error('Error updating category:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Delete category
router.delete('/:id', requireAdmin, requireRole(['super_admin']), async (req, res) => {
    try {
        const { id } = req.params;

        // Check if category has articles
        const { count } = await supabase
            .from('articles')
            .select('*', { count: 'exact', head: true })
            .eq('category_id', id);

        if (count > 0) {
            return apiResponse(res, false, null, `Cannot delete category with ${count} articles. Reassign articles first.`, 400);
        }

        const { error } = await supabase
            .from('categories')
            .delete()
            .eq('id', id);

        if (error) throw error;

        console.log(`✅ Category deleted: ID ${id}`);
        apiResponse(res, true, null, 'Category deleted successfully');
    } catch (error) {
        console.error('Error deleting category:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;