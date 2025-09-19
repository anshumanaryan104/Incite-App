# Supabase Setup Guide for News Feed App

## 📋 Prerequisites
1. Create a Supabase account at https://supabase.com
2. Create a new project
3. Note down your project URL and API keys

## 🚀 Quick Setup

### 1. Database Setup
1. Go to SQL Editor in Supabase Dashboard
2. Copy the entire content from `supabase_schema.sql`
3. Run the SQL to create all tables, indexes, and triggers

### 2. Environment Variables
Create a `.env` file in `/express-backend` folder:

```env
# Supabase Configuration
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-key

# Database (Optional - for direct connection)
DB_HOST=db.your-project.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=your-database-password

# Express Server
PORT=3000
```

### 3. Install Dependencies
```bash
cd express-backend
npm install @supabase/supabase-js
```

### 4. Update Express Server
Replace the mock data endpoints with Supabase-powered endpoints:

```javascript
// In server.js, replace existing routes with:
const apiRoutes = require('./routes/api');
app.use('/api', apiRoutes);
```

## 📊 Database Schema Overview

### Core Tables:
1. **users** - User accounts and profiles
2. **categories** - Article categories
3. **articles** - Main content table
4. **bookmarks** - User saved articles
5. **user_interactions** - Views, likes, shares tracking
6. **comments** - Article comments
7. **polls** - Poll articles
8. **notifications** - Push notifications
9. **advertisements** - Ad management
10. **app_settings** - Global app configuration

### Key Features:
- ✅ Row Level Security (RLS) enabled
- ✅ Automatic timestamp triggers
- ✅ Optimized indexes for performance
- ✅ Views for complex queries
- ✅ Support for multiple content types (articles, videos, polls)

## 🔧 API Endpoints

### Articles
- `GET /api/blog-list` - Get all articles with categories
- `GET /api/blog-detail/:id` - Get single article
- `GET /api/blog-category-list` - Get categories with articles
- `GET /api/trending` - Get trending articles
- `GET /api/featured` - Get featured articles
- `GET /api/search?q=query` - Search articles

### User Management
- `POST /api/signup` - User registration
- `POST /api/login` - User login
- `GET /api/bookmarks/:userId` - Get user bookmarks
- `POST /api/bookmarks` - Add bookmark
- `DELETE /api/bookmarks` - Remove bookmark

### Interactions
- `POST /api/interactions` - Record view/like/share

### Settings
- `GET /api/setting-list` - Get app settings
- `GET /api/language-list` - Get available languages

## 📱 Flutter App Integration

### 1. Add Supabase to Flutter
```yaml
# In pubspec.yaml
dependencies:
  supabase_flutter: ^2.0.0
```

### 2. Initialize Supabase
```dart
// In main.dart
import 'package:supabase_flutter/supabase_flutter.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await Supabase.initialize(
    url: 'YOUR_SUPABASE_URL',
    anonKey: 'YOUR_SUPABASE_ANON_KEY',
  );

  runApp(MyApp());
}
```

### 3. Use Supabase Client
```dart
final supabase = Supabase.instance.client;

// Fetch articles
final response = await supabase
  .from('articles')
  .select('*, categories(*)')
  .eq('status', 'published')
  .order('published_at');
```

## 🎨 Sample Data

### Add Sample Categories
```sql
INSERT INTO categories (name, slug, color, is_featured, is_feed) VALUES
('Technology', 'technology', '#4ECDC4', true, true),
('Sports', 'sports', '#FFA500', true, true),
('Entertainment', 'entertainment', '#9B59B6', false, true);
```

### Add Sample Articles
```sql
INSERT INTO articles (
  title,
  slug,
  description,
  content,
  category_id,
  featured_image,
  status,
  published_at
) VALUES (
  'Sample Article Title',
  'sample-article-title',
  'This is a sample article description',
  'Full article content goes here...',
  1, -- Technology category
  'https://via.placeholder.com/600x400',
  'published',
  NOW()
);
```

## 🔒 Security Best Practices

1. **Enable RLS** - Already configured in schema
2. **Use Service Key only on backend** - Never expose in Flutter app
3. **Validate all inputs** - Sanitize user data
4. **Use prepared statements** - Prevent SQL injection
5. **Rate limiting** - Implement API rate limits

## 📈 Monitoring

### Supabase Dashboard Features:
- Real-time database monitoring
- API usage analytics
- Performance metrics
- Error tracking
- User activity logs

## 🚦 Testing

### Test Database Connection:
```javascript
// In Express backend
const { db } = require('./supabase-client');

async function testConnection() {
  try {
    const categories = await db.getCategories();
    console.log('✅ Database connected:', categories.length, 'categories found');
  } catch (error) {
    console.error('❌ Database connection failed:', error);
  }
}

testConnection();
```

### Test API Endpoints:
```bash
# Get articles
curl http://localhost:3000/api/blog-list

# Get single article
curl http://localhost:3000/api/blog-detail/1

# Search
curl http://localhost:3000/api/search?q=technology
```

## 📝 Notes

- The schema includes support for multiple content types (articles, videos, polls)
- Advertisements table allows for monetization
- User preferences table enables personalization
- Notification system ready for push notifications
- Full-text search capabilities with PostgreSQL

## 🆘 Troubleshooting

### Common Issues:

1. **Connection refused**
   - Check Supabase project is active
   - Verify API keys are correct
   - Check network/firewall settings

2. **Permission denied**
   - Ensure RLS policies are configured
   - Use service key for admin operations

3. **No data returned**
   - Check if tables have data
   - Verify status='published' for articles
   - Check date filters

## 📚 Resources

- [Supabase Documentation](https://supabase.com/docs)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Express.js Guide](https://expressjs.com/en/guide/routing.html)
- [Flutter Supabase Package](https://pub.dev/packages/supabase_flutter)

---

Happy coding! 🎉