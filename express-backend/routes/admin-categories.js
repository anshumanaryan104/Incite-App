const express = require('express');
const router = express.Router();
const { supabase } = require('../supabase-client');
const { requireAdmin } = require('../middleware/auth');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

// Delete all categories except "All News"
router.delete('/cleanup', requireAdmin, async (req, res) => {
    try {
        const { data: deleted, error } = await supabase
            .from('categories')
            .delete()
            .neq('name', 'All News')
            .select();

        if (error) throw error;

        apiResponse(res, true, deleted, `Deleted ${deleted.length} categories`);
    } catch (error) {
        console.error('Error deleting categories:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;
