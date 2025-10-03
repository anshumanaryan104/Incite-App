# News App - Project Documentation

## Project Structure Overview

```
news_app/
├── express-backend/          # Backend API Server (Node.js + Express)
├── admin-web/               # Admin Panel (React Web App)
├── Flutter-App-Code/        # Mobile App (Flutter/Dart)
└── claude.md               # This documentation file
```

---

## 📁 Folder Details

### 1. express-backend/ - Backend API Server
**Purpose:** REST API server jo mobile app aur admin panel ko data provide karta hai

**Technology Stack:**
- Node.js + Express.js
- Supabase (PostgreSQL database)
- JWT Authentication

**Key Files:**
- server.js - Main server file, routes setup
- supabase-client.js - Database connection configuration
- routes/
  - api-simple.js - Public APIs (articles, bookmarks, etc.)
  - admin-articles.js - Admin APIs for article management
  - admin-categories.js - Category management APIs
  - user-auth.js - User authentication endpoints
- middleware/auth.js - JWT authentication middleware

**How to Run:**
```bash
cd express-backend
npm install
npm run dev
```
Server runs on: http://localhost:3000

**Important APIs:**
- /api/view-all-post - Get all articles
- /api/blog-detail/:id - Get single article
- /api/admin/articles - Admin article CRUD
- /api/login - User login
- /api/signup - User signup

---

### 2. admin-web/ - Admin Panel
**Purpose:** Web-based admin panel article aur categories manage karne ke liye

**Technology Stack:**
- React.js (Vite)
- React Router
- Axios for API calls

**Key Files:**
- src/App.jsx - Main app component with routing
- src/components/
  - Dashboard.jsx - Admin dashboard with articles list
  - ArticleForm.jsx - Create/Edit article form
  - AdminManagement.jsx - Admin users management
  - AdminForm.jsx - Create/Edit admin form
- src/utils/api.js - API helper functions

**How to Run:**
```bash
cd admin-web
npm install
npm run dev
```
Admin panel runs on: http://localhost:5173

**Login Credentials:**
- Username: superadmin
- Password: admin123

**Features:**
- Article CRUD (Create, Read, Update, Delete)
- Admin User Management
- Featured Articles

---

### 3. Flutter-App-Code/incite-3.0/ - Mobile App
**Purpose:** News reading mobile application (Android/iOS)

**Technology Stack:**
- Flutter/Dart
- Provider for state management
- Cached Network Images
- Supabase integration

**Key Files & Folders:**
- lib/
  - main.dart - App entry point
  - pages/main/
    - blog.dart - Single article view with image rendering
    - blog_wrap.dart - Articles list wrapper
  - api_controller/
    - app_provider.dart - State management
    - blog_controller.dart - Blog/Article APIs
    - user_controller.dart - User authentication
  - model/
    - blog.dart - Article/Blog data model
    - home.dart - Home page models
  - urls/url.dart - API endpoint URLs
  - widgets/ - Reusable UI components

**How to Run:**
```bash
cd Flutter-App-Code/incite-3.0
flutter pub get
flutter run
```

**Important Configuration:**
- API Base URL: Check lib/urls/url.dart
- For Android Emulator: http://10.0.2.2:3000
- For Physical Device: Use your computer's IP

---


### Database Structure
**Articles Table Fields:**
- id - Primary key
- title - Article title
- description - Short description
- content - Full article content
- featured_image - Main image URL
- images - Array of image URLs (must match featured_image for app to work)
- status - published/draft
- is_featured - Boolean
- published_at - Publish date
- created_at, updated_at - Timestamps

---

## 🚀 Quick Start Guide

### 1. Start Backend Server
```bash
cd express-backend
npm run dev
```

### 2. Start Admin Panel
```bash
cd admin-web
npm run dev
```

### 3. Run Flutter App
```bash
cd Flutter-App-Code/incite-3.0
flutter run
```

---

## 📝 Development Notes

### Adding New Article (Admin Panel):
1. Go to http://localhost:5173
2. Login with admin credentials
3. Click "Add New Article"
4. Fill title, description, content, image URL
5. Click Save

### Updating Article Image:
- When you update featured_image in admin panel, images array automatically updates
- No need to manually update both fields

### Flutter App API Configuration:
- Check lib/urls/url.dart for API endpoints
- For Android emulator, use http://10.0.2.2:3000
- For real device, use computer's IP address

---


## 📊 Key Features

### Mobile App Features:
- Article browsing with images
- Bookmark articles (device-based)
- Text-to-speech for articles
- Category filtering
- Share articles (currently not working)
- Ask AI (coming soon)

### Admin Panel Features:
- Article CRUD operations
- Image upload support
- Featured articles management
- Admin user management
- Audit logs

---

## 🛠 Tech Stack Summary

| Component | Technology |
|-----------|-----------|
| Backend | Node.js, Express, Supabase |
| Admin Panel | React.js, Vite |
| Mobile App | Flutter, Dart |
| Database | PostgreSQL (Supabase) |
| Authentication | JWT |
| State Management | Provider (Flutter) |

---



Last Updated: October 2025
Project: News App MVP
