const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const jwt = require('jsonwebtoken');
const { supabase } = require('../supabase-client');
const { requireAdmin, requireRole, JWT_SECRET } = require('../middleware/auth');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

router.post('/first-setup', async (req, res) => {
    try {
        const { count } = await supabase
            .from('admin_users')
            .select('*', { count: 'exact', head: true });

        if (count > 0) {
            return apiResponse(res, false, null, 'Admin already exists. Use login instead.', 403);
        }

        const { username, email, password } = req.body;

        if (!username || !email || !password) {
            return apiResponse(res, false, null, 'Username, email, and password required', 400);
        }

        if (password.length < 8) {
            return apiResponse(res, false, null, 'Password must be at least 8 characters', 400);
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const { data: admin, error } = await supabase
            .from('admin_users')
            .insert([{
                username,
                email,
                hashed_password: hashedPassword,
                role: 'super_admin'
            }])
            .select()
            .single();

        if (error) throw error;

        console.log(`✅ First admin created: ${admin.username}`);

        apiResponse(res, true, {
            username: admin.username,
            email: admin.email,
            role: admin.role
        }, 'First admin created successfully');

    } catch (error) {
        console.error('Error in first-setup:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.post('/login', async (req, res) => {
    try {
        const { username, password } = req.body;

        if (!username || !password) {
            return apiResponse(res, false, null, 'Username and password required', 400);
        }

        const { data: admin, error } = await supabase
            .from('admin_users')
            .select('*')
            .eq('username', username)
            .eq('is_active', true)
            .single();

        if (error || !admin) {
            return apiResponse(res, false, null, 'Invalid credentials', 401);
        }

        const isPasswordValid = await bcrypt.compare(password, admin.hashed_password);

        if (!isPasswordValid) {
            return apiResponse(res, false, null, 'Invalid credentials', 401);
        }

        const token = jwt.sign(
            {
                userId: admin.id,
                username: admin.username,
                role: admin.role,
                email: admin.email
            },
            JWT_SECRET,
            { expiresIn: '24h' }
        );

        const ip_address = req.ip || req.connection.remoteAddress;
        await supabase
            .from('admin_users')
            .update({
                last_login_at: new Date().toISOString(),
                last_login_ip: ip_address
            })
            .eq('id', admin.id);

        console.log(`✅ Admin login: ${admin.username} from ${ip_address}`);

        apiResponse(res, true, {
            token,
            admin: {
                username: admin.username,
                email: admin.email,
                role: admin.role
            }
        }, 'Login successful');

    } catch (error) {
        console.error('Error in login:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.post('/create-admin', requireAdmin, requireRole(['super_admin']), async (req, res) => {
    try {
        const { username, email, password, role } = req.body;

        if (!username || !email || !password) {
            return apiResponse(res, false, null, 'Username, email, and password required', 400);
        }

        if (password.length < 8) {
            return apiResponse(res, false, null, 'Password must be at least 8 characters', 400);
        }

        const validRoles = ['admin', 'editor'];
        if (role && !validRoles.includes(role)) {
            return apiResponse(res, false, null, `Role must be one of: ${validRoles.join(', ')}`, 400);
        }

        const hashedPassword = await bcrypt.hash(password, 10);

        const { data: newAdmin, error } = await supabase
            .from('admin_users')
            .insert([{
                username,
                email,
                hashed_password: hashedPassword,
                role: role || 'admin'
            }])
            .select()
            .single();

        if (error) {
            if (error.code === '23505') {
                return apiResponse(res, false, null, 'Username or email already exists', 400);
            }
            throw error;
        }

        console.log(`✅ Admin created: ${newAdmin.username} by ${req.admin.username}`);

        apiResponse(res, true, {
            username: newAdmin.username,
            email: newAdmin.email,
            role: newAdmin.role
        }, `Admin ${newAdmin.username} created successfully`);

    } catch (error) {
        console.error('Error in create-admin:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.get('/list', requireAdmin, requireRole(['super_admin']), async (req, res) => {
    try {
        const { data: admins, error } = await supabase
            .from('admin_users')
            .select('id, username, email, role, is_active, last_login_at, created_at')
            .order('created_at', { ascending: false });

        if (error) throw error;

        apiResponse(res, true, admins);

    } catch (error) {
        console.error('Error in list:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.get('/profile', requireAdmin, async (req, res) => {
    try {
        const { data: admin, error } = await supabase
            .from('admin_users')
            .select('id, username, email, role, is_active, last_login_at, created_at')
            .eq('id', req.admin.userId)
            .single();

        if (error) throw error;

        apiResponse(res, true, admin);

    } catch (error) {
        console.error('Error in profile:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.put('/change-password', requireAdmin, async (req, res) => {
    try {
        const { currentPassword, newPassword } = req.body;

        if (!currentPassword || !newPassword) {
            return apiResponse(res, false, null, 'Current and new password required', 400);
        }

        if (newPassword.length < 8) {
            return apiResponse(res, false, null, 'New password must be at least 8 characters', 400);
        }

        const { data: admin, error } = await supabase
            .from('admin_users')
            .select('hashed_password')
            .eq('id', req.admin.userId)
            .single();

        if (error) throw error;

        const isPasswordValid = await bcrypt.compare(currentPassword, admin.hashed_password);

        if (!isPasswordValid) {
            return apiResponse(res, false, null, 'Current password is incorrect', 401);
        }

        const hashedPassword = await bcrypt.hash(newPassword, 10);

        const { error: updateError } = await supabase
            .from('admin_users')
            .update({ hashed_password: hashedPassword })
            .eq('id', req.admin.userId);

        if (updateError) throw updateError;

        console.log(`✅ Password changed: ${req.admin.username}`);

        apiResponse(res, true, null, 'Password changed successfully');

    } catch (error) {
        console.error('Error in change-password:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

router.delete('/:id', requireAdmin, requireRole(['super_admin']), async (req, res) => {
    try {
        const { id } = req.params;

        if (id === req.admin.userId) {
            return apiResponse(res, false, null, 'Cannot delete your own account', 400);
        }

        const { error } = await supabase
            .from('admin_users')
            .delete()
            .eq('id', id);

        if (error) throw error;

        console.log(`✅ Admin deleted by ${req.admin.username}`);

        apiResponse(res, true, null, 'Admin deleted successfully');

    } catch (error) {
        console.error('Error in delete admin:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;