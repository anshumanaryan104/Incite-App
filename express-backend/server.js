const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 8000;

// Check if we should use Supabase
const USE_SUPABASE = process.env.SUPABASE_URL && process.env.SUPABASE_ANON_KEY;

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE'],
    allowedHeaders: ['Content-Type', 'Authorization', 'api-token', 'language-code'],
    credentials: true
}));
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Debug logging middleware
app.use((req, res, next) => {
    console.log(`📍 ${req.method} ${req.url}`);
    console.log('Headers:', req.headers);
    next();
});

// Force JSON responses for API routes only
app.use('/api/*', (req, res, next) => {
    res.setHeader('Content-Type', 'application/json; charset=utf-8');
    next();
});

// Database connection
let db;
async function connectDB() {
    try {
        db = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'news_app'
        });
        console.log('✅ MySQL Connected');
    } catch (error) {
        console.error('❌ MySQL Connection Error:', error.message);
        // Continue without database for now
        db = null;
    }
}

// Mock data for testing
const mockSettings = {
    app_name: 'News App',
    primary_color: '#FF6B6B',
    secondary_color: '#4ECDC4',
    app_version: '1.0.0',
    android_schema: 'incite://blog',
    ios_schema: 'incite://blog',
    enable_ads: '0',
    enable_fb_ads: '0',
    enable_os_notifications: '0'
};

const mockBlogs = [
    {
        id: 1,
        title: 'Breaking News: Express Server Working!',
        description: 'Your Express.js backend is now running successfully',
        content: 'This is a test article to verify the API is working correctly.',
        category: 'Technology',
        category_name: 'Technology',
        category_color: '#4ECDC4',
        image: 'https://via.placeholder.com/600x400',
        images: ['https://via.placeholder.com/600x400'],  // Flutter expects array
        created_at: new Date().toISOString(),
        schedule_date: new Date().toISOString(),
        views: 100,
        is_featured: 0,
        type: 'blog',
        source_name: 'Express News'
    },
    {
        id: 2,
        title: 'Flutter App Connected Successfully',
        description: 'The mobile app can now fetch data from Express server',
        content: 'Flutter app is successfully connected to the Express.js backend.',
        category: 'Development',
        category_name: 'Development',
        category_color: '#FF6B6B',
        image: 'https://via.placeholder.com/600x400',
        images: ['https://via.placeholder.com/600x400'],  // Flutter expects array
        created_at: new Date().toISOString(),
        schedule_date: new Date().toISOString(),
        views: 50,
        is_featured: 1,
        type: 'blog',
        source_name: 'Dev News'
    }
];

// API Routes

// Settings API
app.get('/api/setting-list', async (req, res) => {
    if (db) {
        try {
            const [rows] = await db.execute('SELECT * FROM settings LIMIT 1');
            if (rows.length > 0) {
                return res.json({ status: true, data: rows[0] });
            }
        } catch (error) {
            console.error('Database error:', error);
        }
    }
    // Return mock data if no database
    res.json({ success: true, data: mockSettings });
});

// Blog List API - Returns categories with blogs (Flutter app expects this format)
app.get('/api/blog-list', async (req, res) => {
    console.log('📍 /api/blog-list called with headers:', req.headers);

    // Flutter app expects categories array, not direct blogs
    // Each category should have a data object with blogs
    res.json({
        success: true,
        data: [
            {
                id: 1,
                name: "All News",
                image: null,
                color: "#FF6B6B",
                parent_id: null,
                is_featured: 1,
                is_feed: true,
                data: {
                    blogs: mockBlogs,
                    current_page: 1,
                    first_page_url: "http://10.0.2.2:3000/api/blog-list?page=1",
                    from: 1,
                    last_page: 1,
                    last_page_url: "http://10.0.2.2:3000/api/blog-list?page=1",
                    next_page_url: null,
                    path: "http://10.0.2.2:3000/api/blog-list",
                    per_page: 10,
                    prev_page_url: null,
                    to: mockBlogs.length,
                    total: mockBlogs.length
                },
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            }
        ]
    });
});

// Blog Detail API
app.get('/api/blog-detail/:id', async (req, res) => {
    const { id } = req.params;

    if (db) {
        try {
            const [rows] = await db.execute(
                'SELECT * FROM blogs WHERE id = ? AND status = 1',
                [id]
            );
            if (rows.length > 0) {
                return res.json({ status: true, data: rows[0] });
            }
        } catch (error) {
            console.error('Database error:', error);
        }
    }

    // Return mock data
    const blog = mockBlogs.find(b => b.id === parseInt(id));
    if (blog) {
        res.json({ status: true, data: blog });
    } else {
        res.json({ status: false, message: 'Blog not found' });
    }
});

// Blog Category List API
app.get('/api/blog-category-list', (req, res) => {
    console.log('📍 /api/blog-category-list called with headers:', req.headers);

    // Return categories with blog data structure expected by Flutter app
    res.json({
        success: true,
        data: [
            {
                id: 1,
                name: "All News",
                image: null,
                color: "#FF6B6B",
                parent_id: null,
                is_featured: 1,
                is_feed: true,
                data: {
                    blogs: mockBlogs,
                    current_page: 1,
                    first_page_url: "http://10.0.2.2:3000/api/blog-category-list?page=1",
                    from: 1,
                    last_page: 1,
                    last_page_url: "http://10.0.2.2:3000/api/blog-category-list?page=1",
                    next_page_url: null,
                    path: "http://10.0.2.2:3000/api/blog-category-list",
                    per_page: 10,
                    prev_page_url: null,
                    to: mockBlogs.length,
                    total: mockBlogs.length
                },
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            },
            {
                id: 2,
                name: "Technology",
                image: null,
                color: "#4ECDC4",
                parent_id: null,
                is_featured: 0,
                is_feed: true,
                data: {
                    blogs: mockBlogs.filter(b => b.category === 'Technology'),
                    current_page: 1,
                    first_page_url: "http://10.0.2.2:3000/api/blog-category-list?page=1",
                    from: 1,
                    last_page: 1,
                    last_page_url: "http://10.0.2.2:3000/api/blog-category-list?page=1",
                    next_page_url: null,
                    path: "http://10.0.2.2:3000/api/blog-category-list",
                    per_page: 10,
                    prev_page_url: null,
                    to: 1,
                    total: 1
                },
                created_at: new Date().toISOString(),
                updated_at: new Date().toISOString()
            }
        ]
    });
});

// Language List API
app.get('/api/language-list', (req, res) => {
    res.json({
        status: true,
        data: [
            { id: 1, language: 'en', name: 'English' },
            { id: 2, language: 'hi', name: 'Hindi' }
        ]
    });
});

// Ads List API
app.get('/api/ads-list', (req, res) => {
    res.json({
        success: true,
        data: []  // Empty ads for now
    });
});

// CMS List API
app.get('/api/cms-list', (req, res) => {
    res.json({
        success: true,
        data: []  // Empty CMS pages for now
    });
});

// Social Media List API
app.get('/api/social-media-list', (req, res) => {
    res.json({
        success: true,
        data: []  // Empty social media links for now
    });
});

// Localisation List API
app.get('/api/localisation-list', (req, res) => {
    res.json({
        success: true,
        data: {
            "app_name": "News App",
            "home": "Home",
            "categories": "Categories",
            "bookmarks": "Bookmarks",
            "profile": "Profile"
        }
    });
});

// Login API (Mock)
app.post('/api/login', async (req, res) => {
    const { email, password } = req.body;

    // Mock login - accept any credentials for testing
    if (email && password) {
        res.json({
            status: true,
            message: 'Login successful',
            data: {
                id: 1,
                name: 'Test User',
                email: email,
                token: 'test-token-' + Date.now()
            }
        });
    } else {
        res.json({
            status: false,
            message: 'Email and password required'
        });
    }
});

// Signup API (Mock)
app.post('/api/signup', async (req, res) => {
    const { name, email, password } = req.body;

    if (name && email && password) {
        res.json({
            status: true,
            message: 'Signup successful',
            data: {
                id: 1,
                name: name,
                email: email,
                token: 'test-token-' + Date.now()
            }
        });
    } else {
        res.json({
            status: false,
            message: 'All fields required'
        });
    }
});

// Default route
app.get('/', (req, res) => {
    res.json({
        message: 'News App Express Backend',
        status: 'Running',
        apis: {
            settings: '/api/setting-list',
            blogs: '/api/blog-list',
            blogDetail: '/api/blog-detail/:id',
            languages: '/api/language-list',
            login: '/api/login',
            signup: '/api/signup'
        }
    });
});

// Start server
if (USE_SUPABASE) {
    console.log('🔄 Using Supabase for data storage');
    // Load Supabase routes (this replaces all the mock routes above)
    const supabaseRoutes = require('./routes/api-simple');
    // Remove existing routes AND handlers before adding new ones
    const originalStackLength = app._router.stack.length;
    app._router.stack = app._router.stack.filter(r => {
        // Keep only the initial middleware (cors, json, etc)
        // Remove all route handlers and error handlers
        return !r.route && !r.name?.includes('404') && !r.name?.includes('error');
    });

    // Add Supabase routes
    app.use('/api', supabaseRoutes);

    // Add 404 handler after routes
    app.use((req, res) => {
        console.log(`❌ 404 Not Found: ${req.url}`);
        res.status(404).json({
            success: false,
            message: `Route not found: ${req.url}`,
            error: 'NOT_FOUND'
        });
    });

    // Add error handler last
    app.use((err, req, res, next) => {
        console.error('❌ Error:', err);
        res.status(500).json({
            success: false,
            message: 'Internal server error',
            error: err.message
        });
    });

    app.listen(PORT, () => {
        console.log(`
🚀 Express server running at http://localhost:${PORT}
📱 For Flutter app use: http://10.0.2.2:${PORT}
🗄️  Using Supabase Database
        `);
    });
} else {
    // Original MySQL setup

    // Add 404 handler
    app.use((req, res) => {
        console.log(`❌ 404 Not Found: ${req.url}`);
        res.status(404).json({
            success: false,
            message: `Route not found: ${req.url}`,
            error: 'NOT_FOUND'
        });
    });

    // Add error handler
    app.use((err, req, res, next) => {
        console.error('❌ Error:', err);
        res.status(500).json({
            success: false,
            message: 'Internal server error',
            error: err.message
        });
    });

    connectDB().then(() => {
        app.listen(PORT, () => {
            console.log(`
🚀 Express server running at http://localhost:${PORT}
📱 For Flutter app use: http://10.0.2.2:${PORT}
        `);
        });
    });
}