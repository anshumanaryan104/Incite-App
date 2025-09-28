# Admin API Documentation

## Authentication Endpoints

### 1. First Setup (One-time only)
**POST** `/api/admin/first-setup`

Creates the first super admin. Only works when no admin exists.

```bash
curl -X POST http://localhost:3000/api/admin/first-setup \
  -H "Content-Type: application/json" \
  -d '{
    "username": "superadmin",
    "email": "admin@newsapp.com",
    "password": "Admin@123"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "username": "superadmin",
    "email": "admin@newsapp.com",
    "role": "super_admin"
  },
  "message": "First admin created successfully"
}
```

---

### 2. Login
**POST** `/api/admin/login`

```bash
curl -X POST http://localhost:3000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "superadmin",
    "password": "Admin@123"
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "admin": {
      "username": "superadmin",
      "email": "admin@newsapp.com",
      "role": "super_admin"
    }
  },
  "message": "Login successful"
}
```

---

### 3. Create New Admin (Super Admin Only)
**POST** `/api/admin/create-admin`

Requires: `super_admin` role

```bash
curl -X POST http://localhost:3000/api/admin/create-admin \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "username": "editor1",
    "email": "editor1@newsapp.com",
    "password": "Editor@123",
    "role": "editor"
  }'
```

**Roles:** `admin`, `editor`

---

### 4. List All Admins (Super Admin Only)
**GET** `/api/admin/list`

```bash
curl -X GET http://localhost:3000/api/admin/list \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 5. Get Own Profile
**GET** `/api/admin/profile`

```bash
curl -X GET http://localhost:3000/api/admin/profile \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

### 6. Change Password
**PUT** `/api/admin/change-password`

```bash
curl -X PUT http://localhost:3000/api/admin/change-password \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "currentPassword": "OldPass@123",
    "newPassword": "NewPass@123"
  }'
```

---

### 7. Delete Admin (Super Admin Only)
**DELETE** `/api/admin/:id`

```bash
curl -X DELETE http://localhost:3000/api/admin/USER_ID \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Article Management Endpoints

### 1. Create Article
**POST** `/api/admin/articles`

Requires: Any admin role

```bash
curl -X POST http://localhost:3000/api/admin/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Breaking News",
    "description": "Short description",
    "content": "<p>Full article content here</p>",
    "category_id": 1,
    "featured_image": "https://example.com/image.jpg",
    "is_featured": true
  }'
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "Breaking News",
    "created_by": "superadmin",
    "status": "published",
    ...
  },
  "message": "Article created by superadmin"
}
```

---

### 2. Update Article
**PUT** `/api/admin/articles/:id`

```bash
curl -X PUT http://localhost:3000/api/admin/articles/10 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "title": "Updated Title",
    "description": "Updated description"
  }'
```

**Tracks:** `updated_by` username

---

### 3. Delete Article (Soft Delete)
**DELETE** `/api/admin/articles/:id`

Requires: `admin` or `super_admin` role

```bash
curl -X DELETE http://localhost:3000/api/admin/articles/10 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Tracks:** `deleted_by` username, `deleted_at` timestamp

---

### 4. List Articles (Admin View)
**GET** `/api/admin/articles`

```bash
curl -X GET 'http://localhost:3000/api/admin/articles?page=1&limit=20&status=published' \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Query Parameters:**
- `page` (default: 1)
- `limit` (default: 20)
- `status` (default: published) - `published`, `draft`, `deleted`

---

### 5. Get Article Audit Logs
**GET** `/api/admin/articles/:id/audit-logs`

View complete history of changes to an article.

```bash
curl -X GET http://localhost:3000/api/admin/articles/10/audit-logs \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "action": "CREATE",
      "admin_username": "superadmin",
      "ip_address": "192.168.1.100",
      "created_at": "2025-09-27T19:26:59Z",
      "old_values": null,
      "new_values": {...}
    }
  ]
}
```

---

## Role Permissions

| Action | super_admin | admin | editor |
|--------|-------------|-------|--------|
| Create admins | ✅ | ❌ | ❌ |
| Delete admins | ✅ | ❌ | ❌ |
| List admins | ✅ | ❌ | ❌ |
| Create articles | ✅ | ✅ | ✅ |
| Update articles | ✅ | ✅ | ✅ |
| Delete articles | ✅ | ✅ | ❌ |
| View audit logs | ✅ | ✅ | ✅ |

---

## Audit Logging

All admin actions are automatically logged with:
- ✅ Admin username
- ✅ IP address
- ✅ User agent (browser info)
- ✅ Timestamp
- ✅ Old & new values (for updates)

**Example:** When admin creates/updates/deletes an article, the system stores:
- `created_by: "superadmin"`
- `updated_by: "editor1"`
- `deleted_by: "admin2"`

---

## Testing

### Test First Setup:
```bash
curl -X POST http://localhost:3000/api/admin/first-setup \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","email":"admin@test.com","password":"Admin@123"}'
```

### Test Login & Save Token:
```bash
TOKEN=$(curl -s -X POST http://localhost:3000/api/admin/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"Admin@123"}' \
  | grep -o '"token":"[^"]*' | cut -d'"' -f4)

echo "Token: $TOKEN"
```

### Test Create Article:
```bash
curl -X POST http://localhost:3000/api/admin/articles \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"Test","description":"Test","content":"<p>Test</p>","category_id":1,"featured_image":"https://example.com/img.jpg","is_featured":true}'
```

---

## Security Notes

1. **JWT Secret:** Change `JWT_SECRET` in `.env` for production
2. **HTTPS:** Always use HTTPS in production
3. **Rate Limiting:** Consider adding rate limiting to login endpoint
4. **Password Policy:** Minimum 8 characters enforced
5. **Audit Logs:** All admin actions are logged with IP and timestamp

---

## Credentials (Development Only)

**First Admin:**
- Username: `superadmin`
- Email: `admin@newsapp.com`
- Password: `Admin@123`

⚠️ **Change immediately in production!**