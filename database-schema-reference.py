"""
NEWS APP - COMPLETE DATABASE SCHEMA REFERENCE
Supabase PostgreSQL Database Schema
Generated: October 2025

This file documents the complete database schema with field types and descriptions.
Use this as a reference for API development and database queries.
"""

# =====================================================
# 1. CATEGORIES TABLE
# =====================================================
CATEGORIES_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "name": str,                        # Category name (e.g., "All News")
    "slug": str | None,                 # URL-friendly slug (unique)
    "description": str | None,          # Category description
    "color": str,                       # Hex color code (default: "#FF6B6B")
    "image": str | None,                # Category image URL
    "parent_id": int | None,            # Parent category ID (for subcategories)
    "is_active": bool,                  # Whether category is active (default: true)
    "is_featured": bool,                # Featured on homepage (default: false)
    "is_feed": bool,                    # Show in feed (default: false)
    "sort_order": int,                  # Display order (default: 0)
    "created_at": str,                  # ISO 8601 datetime with timezone
    "updated_at": str                   # ISO 8601 datetime with timezone
}

# =====================================================
# 2. ARTICLES TABLE
# =====================================================
ARTICLES_SCHEMA = {
    # Basic Info
    "id": int,                          # Primary key, auto-increment
    "title": str,                       # Article title/headline (required)
    "slug": str | None,                 # URL-friendly slug (unique)
    "description": str | None,          # Short summary/excerpt
    "content": str | None,              # Full article content (HTML/Markdown)
    "category_id": int | None,          # Foreign key to categories table

    # Media Fields
    "featured_image": str | None,       # Main article image URL
    "images": list[str] | None,         # Array of image URLs
    "video_url": str | None,            # YouTube/external video URL
    "video_file": str | None,           # Uploaded video file path
    "background_image": str | None,     # Background image for special layouts

    # Article Metadata
    "type": str,                        # article|post|video|quote|ads (default: "article")
    "status": str,                      # draft|published|archived (default: "draft")
    "is_featured": bool,                # Featured article (default: false)
    "is_trending": bool,                # Trending article (default: false)

    # Engagement Metrics
    "views": int,                       # View count (default: 0)
    "likes": int,                       # Like count (default: 0)
    "shares": int,                      # Share count (default: 0)

    # Source Information
    "source_name": str | None,          # Original source name
    "source_url": str | None,           # Original article URL
    "author_name": str | None,          # Article author

    # Publishing Info
    "published_at": str | None,         # ISO 8601 publish datetime
    "schedule_date": str | None,        # ISO 8601 scheduled datetime

    # Voice/TTS Fields
    "voice": str | None,                # TTS voice name
    "accent_code": str,                 # Language/accent code (default: "en")

    # Poll/Voting Fields
    "is_voting_enable": bool,           # Enable poll (default: false)
    "question": dict | None,            # Poll question object (JSONB)
    # {
    #     "id": int,
    #     "question": str,
    #     "options": [
    #         {"id": int, "option": str, "percentage": float}
    #     ]
    # }

    # Additional Metadata
    "tags": list[str] | None,           # Array of tags
    "visibilities": list[str] | None,   # Visibility settings
    "frequency": int,                   # Ad frequency (default: 0)

    # Audit Fields
    "created_by": str | None,           # Admin username who created
    "updated_by": str | None,           # Admin username who updated
    "created_at": str,                  # ISO 8601 creation datetime
    "updated_at": str                   # ISO 8601 update datetime
}

# =====================================================
# 3. USERS TABLE
# =====================================================
USERS_SCHEMA = {
    # Authentication
    "id": int,                          # Primary key, auto-increment
    "username": str,                    # Unique username (required)
    "email": str | None,                # Email address (unique)
    "hashed_password": str,             # Bcrypt hashed password (required)

    # User Profile
    "name": str | None,                 # Display name
    "full_name": str | None,            # Full legal name
    "phone": str | None,                # Phone number
    "profile_picture": str | None,      # Profile picture URL
    "avatar_url": str | None,           # Avatar URL (alternative)
    "bio": str | None,                  # User bio/description

    # User Status
    "is_verified": bool,                # Email verified (default: false)
    "is_active": bool,                  # Account active (default: true)

    # Notification Tokens
    "player_id": str | None,            # OneSignal player ID
    "fcm_token": str | None,            # Firebase Cloud Messaging token

    # User Preferences
    "preferences": dict,                # User preferences object (JSONB, default: {})
    # {
    #     "theme": "light" | "dark",
    #     "language": "en",
    #     "notification_enabled": bool
    # }

    # Timestamps
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str,                  # ISO 8601 datetime
    "last_login_at": str | None         # ISO 8601 datetime
}

# =====================================================
# 4. ADMIN_USERS TABLE
# =====================================================
ADMIN_USERS_SCHEMA = {
    # Authentication
    "id": int,                          # Primary key, auto-increment
    "username": str,                    # Unique username (required)
    "email": str,                       # Email address (unique, required)
    "hashed_password": str,             # Bcrypt hashed password (required)

    # Admin Role
    "role": str,                        # super_admin|admin|editor (default: "admin")

    # Admin Status
    "is_active": bool,                  # Account active (default: true)

    # Login Tracking
    "last_login_at": str | None,        # ISO 8601 datetime
    "last_login_ip": str | None,        # IP address

    # Timestamps
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# =====================================================
# 5. BOOKMARKS TABLE (Device-based)
# =====================================================
BOOKMARKS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "device_id": str,                   # Device identifier (required)
    "article_id": int,                  # Foreign key to articles (required)
    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# 6. USER_BOOKMARKS TABLE
# =====================================================
USER_BOOKMARKS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "user_id": int,                     # Foreign key to users (required)
    "article_id": int,                  # Foreign key to articles (required)
    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# 7. ANONYMOUS_BOOKMARKS TABLE (Legacy)
# =====================================================
ANONYMOUS_BOOKMARKS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "device_id": str,                   # Device identifier (required)
    "article_id": int,                  # Foreign key to articles (required)
    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# 8. INTERACTIONS TABLE
# =====================================================
INTERACTIONS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "user_id": int | None,              # Foreign key to users
    "device_id": str | None,            # Device identifier
    "article_id": int | None,           # Foreign key to articles

    # Interaction Type
    "interaction_type": str,            # Required, one of:
    # - "view"                          # Article viewed
    # - "like"                          # Article liked
    # - "share"                         # Article shared
    # - "share_intent"                  # Share dialog opened
    # - "search"                        # Search performed
    # - "feed_preferences_updated"      # Feed preferences changed
    # - "feed_preferences_removed"      # Feed preferences removed
    # - "notification_settings_check"   # Notification settings accessed
    # - "notifications_viewed"          # Notifications viewed
    # - "token_update"                  # Push token updated

    # Additional Data
    "metadata": dict,                   # Additional data (JSONB, default: {})
    # Examples:
    # {"keyword": "search term", "results_count": 10}
    # {"platform": "android", "token_preview": "abc..."}

    # Request Info
    "ip_address": str | None,           # User IP address
    "user_agent": str | None,           # Browser/app user agent

    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# 9. ANONYMOUS_INTERACTIONS TABLE (Legacy)
# =====================================================
ANONYMOUS_INTERACTIONS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "device_id": str | None,            # Device identifier
    "article_id": int | None,           # Foreign key to articles
    "interaction_type": str,            # view|like|share (required)
    "ip_address": str | None,           # User IP address
    "user_agent": str | None,           # Browser/app user agent
    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# 10. APP_SETTINGS TABLE
# =====================================================
APP_SETTINGS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "setting_key": str,                 # Unique setting key (required)
    "setting_value": str | None,        # Setting value
    "description": str | None,          # Setting description
    "data_type": str,                   # string|number|boolean|json (default: "string")
    "is_public": bool,                  # Accessible via public API (default: true)
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# Default Settings
DEFAULT_APP_SETTINGS = {
    "app_name": "News App",
    "primary_color": "#FF6B6B",
    "secondary_color": "#4ECDC4",
    "app_version": "1.0.0",
    "enable_ads": "0",
    "enable_notifications": "0"
}

# =====================================================
# 11. ABOUT_CONTACT TABLE
# =====================================================
ABOUT_CONTACT_SCHEMA = {
    "id": int,                          # Primary key (single record, id=1)

    # About Section
    "about_title": str,                 # Default: "About Us"
    "about_description": str,           # Short about description
    "about_content": str,               # Full about content
    "about_mission": str,               # Mission statement
    "about_vision": str,                # Vision statement
    "about_established": str,           # Year established
    "about_team_size": str,             # Team size description
    "about_values": list[str],          # Array of core values (JSONB)

    # Contact Section
    "contact_title": str,               # Default: "Contact Us"
    "contact_description": str,         # Contact page description
    "contact_email": str,               # General contact email
    "contact_support_email": str,       # Support email
    "contact_phone": str,               # Phone number
    "contact_whatsapp": str,            # WhatsApp number
    "contact_address": dict,            # Address object (JSONB)
    # {
    #     "line1": str,
    #     "line2": str,
    #     "city": str,
    #     "state": str,
    #     "country": str,
    #     "pincode": str
    # }
    "business_hours": dict,             # Business hours object (JSONB)
    # {
    #     "weekdays": str,
    #     "saturday": str,
    #     "sunday": str
    # }
    "contact_response_time": str,       # Expected response time

    # Legal Section
    "privacy_policy_url": str,          # Privacy policy URL/path
    "terms_conditions_url": str,        # Terms & conditions URL/path
    "disclaimer": str,                  # Legal disclaimer

    # App Info
    "app_version": str,                 # Current app version
    "app_developer": str,               # Developer name
    "app_copyright": str,               # Copyright text

    # Timestamps
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# =====================================================
# 12. PUSH_TOKENS TABLE
# =====================================================
PUSH_TOKENS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "user_id": int | None,              # Foreign key to users
    "device_id": str | None,            # Device identifier
    "token": str,                       # FCM/APNS token (unique, required)
    "platform": str,                    # ios|android|web (required)
    "device_info": dict,                # Device information (JSONB, default: {})
    # {
    #     "model": str,
    #     "os_version": str,
    #     "app_version": str
    # }
    "is_active": bool,                  # Token active (default: true)
    "last_used": str,                   # ISO 8601 datetime
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# =====================================================
# 13. NOTIFICATION_SETTINGS TABLE
# =====================================================
NOTIFICATION_SETTINGS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "user_id": int,                     # Foreign key to users (unique, required)

    # Main Settings
    "notification_enabled": bool,       # Master notification toggle (default: true)
    "permission_status": str,           # granted|denied|not_determined|provisional

    # Push Notification Settings
    "push_enabled": bool,               # Push notifications enabled (default: true)
    "push_permission": str,             # Push permission status (default: "granted")
    "fcm_token": str | None,            # FCM token
    "push_updated_at": str | None,      # ISO 8601 datetime

    # Email Settings
    "email_enabled": bool,              # Email notifications (default: false)
    "email": str | None,                # Email address
    "email_verified": bool,             # Email verified (default: false)

    # Notification Preferences
    "pref_breaking_news": bool,         # Breaking news alerts (default: true)
    "pref_daily_digest": bool,          # Daily digest (default: false)
    "pref_bookmark_reminders": bool,    # Bookmark reminders (default: true)
    "pref_new_categories": bool,        # New categories (default: true)
    "pref_trending": bool,              # Trending articles (default: true)
    "pref_recommendations": bool,       # Personalized recommendations (default: false)

    # Device Settings
    "device_sound": bool,               # Notification sound (default: true)
    "device_vibration": bool,           # Vibration (default: true)
    "device_badge": bool,               # Badge count (default: true)
    "quiet_hours_enabled": bool,        # Quiet hours (default: false)
    "quiet_hours_start": str,           # Time (default: "22:00")
    "quiet_hours_end": str,             # Time (default: "08:00")

    # Schedule Settings
    "schedule_morning": bool,           # Morning brief (default: false)
    "schedule_morning_time": str,       # Time (default: "08:00")
    "schedule_evening": bool,           # Evening summary (default: false)
    "schedule_evening_time": str,       # Time (default: "18:00")

    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# =====================================================
# 14. USER_FEEDS TABLE (Optional - Not Currently Used)
# =====================================================
USER_FEEDS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "user_id": int | None,              # Foreign key to users
    "device_id": str | None,            # Device identifier
    "category_ids": list[int],          # Array of category IDs (required)
    "category_names": list[str] | None, # Array of category names (cached)
    "preferences": dict,                # Additional preferences (JSONB, default: {})
    "is_active": bool,                  # Active (default: true)
    "created_at": str,                  # ISO 8601 datetime
    "updated_at": str                   # ISO 8601 datetime
}

# =====================================================
# 15. AUDIT_LOGS TABLE
# =====================================================
AUDIT_LOGS_SCHEMA = {
    "id": int,                          # Primary key, auto-increment
    "table_name": str,                  # Table name (required)
    "record_id": int | None,            # Record ID that was modified
    "action": str,                      # CREATE|UPDATE|DELETE (required)
    "admin_username": str,              # Admin who performed action (required)
    "old_values": dict | None,          # Previous values (JSONB)
    "new_values": dict | None,          # New values (JSONB)
    "ip_address": str | None,           # Admin IP address
    "user_agent": str | None,           # Admin browser/client
    "created_at": str                   # ISO 8601 datetime
}

# =====================================================
# API RESPONSE SCHEMAS
# =====================================================

# Article List Response (from /api/view-all-post)
ARTICLE_LIST_RESPONSE = {
    "success": bool,                    # Request success status
    "message": str | None,              # Success/error message
    "data": [
        {
            "id": int,
            "name": str,                # Category name ("All News")
            "image": str | None,
            "color": str,               # Category color
            "parent_id": int | None,
            "is_featured": int,         # 0 or 1
            "is_feed": bool,
            "data": {
                "blogs": [
                    {
                        "id": int,
                        "title": str,
                        "description": str | None,
                        "content": str | None,
                        "category": str,
                        "category_name": str,
                        "category_color": str,
                        "image": str | None,
                        "images": list[str],
                        "created_at": str,
                        "schedule_date": str,
                        "views": int,
                        "is_featured": int,
                        "type": str,
                        "source_name": str
                    }
                ],
                "current_page": int,
                "first_page_url": str,
                "from": int,
                "last_page": int,
                "last_page_url": str,
                "next_page_url": str | None,
                "path": str,
                "per_page": int,
                "prev_page_url": str | None,
                "to": int,
                "total": int
            },
            "created_at": str,
            "updated_at": str
        }
    ]
}

# Article Detail Response (from /api/blog-detail/:id)
ARTICLE_DETAIL_RESPONSE = {
    "success": bool,
    "message": str | None,
    "data": {
        "id": int,
        "title": str,
        "description": str | None,
        "content": str | None,
        "category": str | None,
        "category_name": str | None,
        "category_color": str | None,
        "image": str | None,
        "images": list[str],
        "video_url": str | None,
        "author": str | None,
        "created_at": str,
        "published_at": str | None,
        "views": int,
        "likes": int,
        "shares": int,
        "is_featured": bool,
        "type": str,
        "source_name": str,
        "source_url": str | None,
        "tags": list[str]
    }
}

# Bookmark List Response (from /api/get-bookmarks)
BOOKMARK_LIST_RESPONSE = {
    "success": bool,
    "message": str | None,
    "data": {
        "bookmarks": [
            {
                "id": int,
                "title": str,
                "description": str | None,
                "content": str | None,
                "image": str | None,
                "images": list[str],
                "category_id": int | None,
                "category_name": str | None,
                "category_color": str | None,
                "category_slug": str | None,
                "author": str | None,
                "views": int,
                "likes": int,
                "shares": int,
                "is_featured": bool,
                "published_at": str,
                "bookmarked_at": str,
                "source_name": str,
                "tags": list[str],
                "bookmark_type": str  # "user" or "device"
            }
        ],
        "bookmark_source": str,  # "user_bookmarks" or "device_bookmarks"
        "pagination": {
            "current_page": int,
            "per_page": int,
            "total": int,
            "last_page": int,
            "from": int,
            "to": int
        }
    }
}

# User Profile Response (from /api/get-profile)
USER_PROFILE_RESPONSE = {
    "success": bool,
    "message": str | None,
    "data": {
        "id": str | int,                # User ID or guest ID
        "name": str,
        "email": str | None,
        "phone": str | None,
        "profile_picture": str | None,
        "bio": str | None,
        "is_guest": bool,
        "is_verified": bool | None,     # Only for registered users
        "device_id": str | None,        # Only for guest users
        "preferences": {
            "notification_enabled": bool,
            "theme": str,               # "light" or "dark"
            "language": str             # ISO 639-1 code
        },
        "stats": {
            "total_bookmarks": int,
            "articles_read": int,
            "member_since": str         # ISO 8601 datetime
        }
    }
}

# Admin Login Response (from /api/admin/login)
ADMIN_LOGIN_RESPONSE = {
    "success": bool,
    "message": str,
    "data": {
        "token": str,                   # JWT token
        "admin": {
            "id": int,
            "username": str,
            "email": str,
            "name": str,
            "role": str,                # super_admin|admin|editor
            "created_at": str
        }
    }
}

# User Login Response (from /api/login)
USER_LOGIN_RESPONSE = {
    "success": bool,
    "message": str,
    "data": {
        "token": str,                   # JWT token
        "admin": {
            "id": int,
            "username": str,
            "email": str | None,
            "name": str,
            "role": str,
            "created_at": str
        }
    }
}

# Error Response (all endpoints)
ERROR_RESPONSE = {
    "success": bool,                    # Always false
    "message": str,                     # Error description
    "data": None,
    "error": str | None                 # Error code/type
}

# =====================================================
# ENUMS AND CONSTANTS
# =====================================================

ARTICLE_TYPES = ["article", "post", "video", "quote", "ads"]
ARTICLE_STATUSES = ["draft", "published", "archived"]
ADMIN_ROLES = ["super_admin", "admin", "editor"]
INTERACTION_TYPES = [
    "view", "like", "share", "share_intent", "search",
    "feed_preferences_updated", "feed_preferences_removed",
    "notification_settings_check", "notifications_viewed", "token_update"
]
PUSH_PLATFORMS = ["ios", "android", "web"]
PERMISSION_STATUSES = ["granted", "denied", "not_determined", "provisional"]
SETTING_DATA_TYPES = ["string", "number", "boolean", "json"]
AUDIT_ACTIONS = ["CREATE", "UPDATE", "DELETE"]

# =====================================================
# USAGE EXAMPLES
# =====================================================

"""
# Example 1: Creating an article
article = {
    "title": "Breaking News: AI Advancement",
    "description": "Major breakthrough in AI technology",
    "content": "<p>Full article content here...</p>",
    "featured_image": "https://example.com/image.jpg",
    "images": ["https://example.com/image1.jpg", "https://example.com/image2.jpg"],
    "category_id": 1,
    "type": "article",
    "status": "published",
    "is_featured": True,
    "author_name": "John Doe",
    "published_at": "2025-10-22T10:30:00Z"
}

# Example 2: User signup
user = {
    "username": "johndoe",
    "email": "john@example.com",
    "password": "securepassword123",  # Will be hashed
    "name": "John Doe",
    "phone": "+1234567890"
}

# Example 3: Adding a bookmark
bookmark = {
    "user_id": 123,           # For logged-in users
    # OR
    "device_id": "abc-123",   # For anonymous users
    "article_id": 456
}

# Example 4: Tracking interaction
interaction = {
    "user_id": 123,
    "article_id": 456,
    "interaction_type": "view",
    "metadata": {"source": "home_feed"},
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0..."
}

# Example 5: Updating notification settings
notification_settings = {
    "user_id": 123,
    "notification_enabled": True,
    "pref_breaking_news": True,
    "pref_daily_digest": False,
    "device_sound": True,
    "quiet_hours_enabled": True,
    "quiet_hours_start": "22:00",
    "quiet_hours_end": "08:00"
}
"""
