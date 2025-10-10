const express = require('express');
const router = express.Router();
const bcrypt = require('bcrypt');
const { supabase } = require('../supabase-client');

function apiResponse(res, success, data = null, message = null, statusCode = 200) {
    res.status(statusCode).json({ success, data, message });
}

// Check if username exists
router.post('/check-username', async (req, res) => {
    try {
        const { username } = req.body;

        if (!username) {
            return apiResponse(res, false, null, 'Username is required', 400);
        }

        const { data: user, error } = await supabase
            .from('users')
            .select('id, username')
            .eq('username', username.toLowerCase())
            .single();

        if (error && error.code !== 'PGRST116') { // PGRST116 = not found
            throw error;
        }

        apiResponse(res, true, {
            exists: !!user,
            username: user?.username
        });
    } catch (error) {
        console.error('Error checking username:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Login with username and password (Admin only)
router.post('/login', async (req, res) => {
    try {
        const { username, password } = req.body;

        if (!username || !password) {
            return apiResponse(res, false, null, 'Username and password are required', 400);
        }

        // Get admin by username from admin_users table
        const { data: admin, error } = await supabase
            .from('admin_users')
            .select('*')
            .eq('username', username)
            .single();

        if (error || !admin) {
            return apiResponse(res, false, null, 'Invalid username or password', 401);
        }

        // Verify password
        const isPasswordValid = await bcrypt.compare(password, admin.hashed_password);

        if (!isPasswordValid) {
            return apiResponse(res, false, null, 'Invalid username or password', 401);
        }

        // Return admin data
        apiResponse(res, true, {
            id: admin.id,
            username: admin.username,
            email: admin.email || null,
            name: admin.name || admin.username,
            role: admin.role,
            created_at: admin.created_at
        }, 'Admin login successful');
    } catch (error) {
        console.error('Error logging in:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

// Signup with username
router.post('/signup', async (req, res) => {
    try {
        const { username, password, email, name, phone, player_id, fcm_token } = req.body;

        if (!username || !password) {
            return apiResponse(res, false, null, 'Username and password are required', 400);
        }

        if (!email) {
            return apiResponse(res, false, null, 'Email is required', 400);
        }

        if (password.length < 6) {
            return apiResponse(res, false, null, 'Password must be at least 6 characters', 400);
        }

        // Check if username already exists
        const { data: existingUsername } = await supabase
            .from('users')
            .select('id')
            .eq('username', username.toLowerCase())
            .single();

        if (existingUsername) {
            return apiResponse(res, false, null, 'Username already taken', 400);
        }

        // Check if email already exists
        if (email) {
            const { data: existingEmail } = await supabase
                .from('users')
                .select('id')
                .eq('email', email.toLowerCase())
                .single();

            if (existingEmail) {
                return apiResponse(res, false, null, 'Email already registered', 400);
            }
        }

        // Hash password
        const hashedPassword = await bcrypt.hash(password, 10);

        // Create user
        const { data: newUser, error } = await supabase
            .from('users')
            .insert([{
                username: username.toLowerCase(),
                hashed_password: hashedPassword,
                email: email ? email.toLowerCase() : null,
                name: name || username,
                phone: phone || null,
                player_id: player_id || null,
                fcm_token: fcm_token || null
            }])
            .select()
            .single();

        if (error) throw error;

        console.log(`✅ New user registered: ${newUser.username}`);

        apiResponse(res, true, {
            id: newUser.id,
            username: newUser.username,
            email: newUser.email,
            name: newUser.name,
            phone: newUser.phone,
            is_new_user: true,
            created_at: newUser.created_at
        }, 'Signup successful', 201);
    } catch (error) {
        console.error('Error signing up:', error);
        apiResponse(res, false, null, error.message, 500);
    }
});

module.exports = router;