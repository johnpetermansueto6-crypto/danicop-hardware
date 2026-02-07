# 📋 Danicop Hardware Online - System Summary

## 🎯 Quick Overview

**E-commerce system for hardware store management with role-based access control.**

- **Base URL:** `http://mwa/hardware/`
- **Database:** `danicop` (MySQL)
- **Language:** PHP 7.4+
- **Frontend:** Tailwind CSS, Alpine.js, JavaScript

---

## 👥 User Roles

| Role | Access | Dashboard |
|------|--------|-----------|
| **Super Admin** 🔴 | Full system access, staff management, locations | `admin/index.php` |
| **Staff** 👷 | Product & order management | `staff/dashboard.php` |
| **Customer** 👤 | Shopping, orders, profile | `customer/shop.php` |

---

## 📁 Main Directories

```
hardware/
├── index.php              # Homepage (public)
├── admin/                 # Admin panel (AJAX SPA)
│   ├── index.php         # Main dashboard
│   └── content/          # AJAX modules
├── staff/                 # Staff dashboard
├── customer/             # Customer area
├── auth/                 # Login/Register
├── includes/             # Config & helpers
├── utils/                # Setup scripts
├── docs/                 # Documentation
└── uploads/              # Product images
```

---

## 🗄️ Database Tables (8)

1. **users** - User accounts (superadmin, staff, customer)
2. **products** - Product catalog
3. **orders** - Customer orders
4. **order_items** - Order line items
5. **store_locations** - Store locations with coordinates
6. **notifications** - System notifications
7. **delivery_logs** - Delivery tracking
8. **sales_reports** - Sales analytics

---

## ✨ Key Features

✅ **Product Management** - Add/Edit/Delete with image upload  
✅ **Order Management** - Full order lifecycle tracking  
✅ **User Authentication** - Email/Password + Google OAuth  
✅ **Admin Panel** - AJAX-based SPA with SweetAlert2  
✅ **Notifications** - Real-time alerts  
✅ **Maps Integration** - Google Maps for locations  
✅ **Security** - SQL injection & XSS protection  

---

## 🔧 Configuration

**File:** `includes/config.php`

```php
DB_HOST: localhost
DB_NAME: danicop
DB_USER: root
DB_PASS: (empty)

GOOGLE_REDIRECT_URI: http://mwa/hardware/auth/google_callback.php
```

---

## 🚀 Quick Setup

1. **Setup Database:**
   ```bash
   php utils/setup_database.php
   ```

2. **Create Admin:**
   ```bash
   php utils/create_superadmin.php
   ```

3. **Access:**
   - Homepage: `http://mwa/hardware/`
   - Admin: `http://mwa/hardware/admin/`

---

## 📊 System Stats

- **PHP Files:** ~55
- **Database Tables:** 8
- **User Roles:** 3
- **Payment Methods:** 4 (Cash, GCash, PayPal, Cash on Delivery)
- **Order Statuses:** 7

---

## 🔐 Security

- ✅ Prepared statements (SQL injection protection)
- ✅ Input sanitization (XSS protection)
- ✅ Password hashing (bcrypt)
- ✅ Session-based authentication
- ✅ Role-based access control

---

## 📚 Documentation Files

- `docs/SYSTEM_STRUCTURE.md` - Complete system structure
- `docs/database_schema.sql` - Database schema
- `docs/LOGIN_SYSTEM_REVIEW.md` - Authentication details
- `docs/GOOGLE_MAPS_SETUP.md` - Maps API setup

---

**For detailed information, see:** `docs/SYSTEM_STRUCTURE.md`

