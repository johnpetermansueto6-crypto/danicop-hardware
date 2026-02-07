# 🏗️ Danicop Hardware Online - Complete System Structure

## 📋 Table of Contents
1. [System Overview](#system-overview)
2. [File Structure](#file-structure)
3. [Database Schema](#database-schema)
4. [User Roles & Permissions](#user-roles--permissions)
5. [Key Features](#key-features)
6. [Technology Stack](#technology-stack)

---

## 🎯 System Overview

**Danicop Hardware Online** is a PHP-based e-commerce system for hardware store management with role-based access control. The system supports three user roles: Super Admin, Staff, and Customer.

**Base URL:** `http://mwa/hardware/`  
**Database:** `danicop` (MySQL)

---

## 📁 File Structure

```
hardware/
│
├── 🏠 ROOT FILES
│   ├── index.php                    # Homepage (public) - Product catalog, cart, login/register modals
│   ├── README.md                     # Project documentation
│   └── START_HERE.md                 # Quick start guide
│
├── 👤 customer/                      # Customer Area
│   ├── shop.php                      # Product shopping page
│   ├── checkout.php                  # Order checkout process
│   ├── orders.php                    # Customer order history
│   └── profile.php                   # User profile management
│
├── 👨‍💼 admin/                         # Admin Panel (Super Admin & Staff)
│   ├── index.php                     # Main admin dashboard (AJAX-based SPA)
│   ├── content/                     # AJAX-loaded content modules
│   │   ├── dashboard.php            # Dashboard statistics
│   │   ├── products.php             # Product management list
│   │   ├── product_add.php          # Add new product form
│   │   ├── product_edit.php        # Edit product form
│   │   ├── orders.php               # Order management
│   │   ├── reports.php              # Sales reports
│   │   ├── users.php                # Staff management (Super Admin only)
│   │   ├── user_add.php             # Add staff form (Super Admin only)
│   │   ├── locations.php            # Store locations management
│   │   └── notifications.php        # System notifications
│   ├── products.php                 # Legacy product page
│   ├── product_add.php              # Legacy add product
│   ├── product_edit.php             # Legacy edit product
│   ├── orders.php                   # Legacy orders page
│   ├── order_details.php            # Order details view
│   ├── reports.php                  # Legacy reports
│   ├── users.php                    # Legacy staff management
│   ├── user_add.php                 # Legacy add staff
│   ├── dashboard.php                # Legacy dashboard
│   └── notifications.php            # Legacy notifications
│
├── 👷 staff/                         # Staff Dashboard
│   ├── index.php                    # Staff redirector
│   ├── dashboard.php                # Staff dashboard
│   ├── products.php                 # Staff product view
│   ├── orders.php                   # Staff order management
│   ├── order_details.php            # Order details
│   └── notifications.php            # Staff notifications
│
├── 🔐 auth/                          # Authentication
│   ├── login.php                    # Login page (standalone)
│   ├── register.php                 # Registration page
│   ├── logout.php                   # Logout handler
│   ├── verify.php                   # Email verification
│   ├── google_login.php             # Google OAuth login
│   └── google_callback.php          # Google OAuth callback
│
├── 🔧 includes/                      # Shared Components
│   ├── config.php                   # Database config, session, helper functions
│   ├── mailer.php                   # Email sending (PHPMailer)
│   ├── admin_sidebar.php           # Admin sidebar component
│   ├── staff_sidebar.php           # Staff sidebar component
│   └── customer_sidebar.php        # Customer sidebar component
│
├── 🛠️ utils/                         # Utility Scripts
│   ├── setup_database.php          # Database setup wizard
│   ├── add_email_verification.php   # Add email verification columns
│   ├── add_store_locations.php     # Add store locations table
│   ├── create_superadmin.php       # Create superadmin user
│   ├── create_user.php             # Create any user
│   ├── reset_admin.php             # Reset admin password
│   ├── auto_setup.php              # Automated setup
│   ├── setup_maps_api.php          # Google Maps setup
│   └── test_functionality.php     # System testing
│
├── 📚 docs/                         # Documentation
│   ├── database_schema.sql         # Complete database schema
│   ├── add_superadmin.sql          # SQL to add superadmin
│   ├── add_email_verification.sql   # Email verification schema
│   ├── add_google_auth_and_profile_fields.sql
│   ├── add_locations_table.sql     # Store locations schema
│   ├── add_delivery_coordinates.sql
│   ├── README.md                    # Full documentation
│   ├── GOOGLE_MAPS_SETUP.md        # Maps API setup guide
│   └── LOGIN_SYSTEM_REVIEW.md      # Login system documentation
│
├── 📦 PHPMailer/                    # Email Library
│   └── src/
│       ├── PHPMailer.php
│       ├── SMTP.php
│       └── Exception.php
│
└── 🖼️ uploads/                      # Product Images
    └── (Uploaded product images)
```

---

## 🗄️ Database Schema

### **Database Name:** `danicop`

### **Tables:**

#### 1. **users**
- `id` (INT, Primary Key)
- `name` (VARCHAR 255)
- `email` (VARCHAR 255, Unique)
- `password` (VARCHAR 255, Hashed)
- `role` (ENUM: 'superadmin', 'staff', 'customer')
- `email_verified` (TINYINT, Default: 0)
- `verification_code` (VARCHAR 255)
- `verification_expires` (DATETIME)
- `google_id` (VARCHAR 255, Nullable)
- `profile_picture` (VARCHAR 255, Nullable)
- `phone` (VARCHAR 20, Nullable)
- `address` (TEXT, Nullable)
- `created_at` (TIMESTAMP)

#### 2. **products**
- `id` (INT, Primary Key)
- `name` (VARCHAR 255)
- `category` (VARCHAR 100)
- `description` (TEXT)
- `price` (DECIMAL 10,2)
- `stock` (INT, Default: 0)
- `image` (VARCHAR 255, Nullable)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### 3. **orders**
- `id` (INT, Primary Key)
- `user_id` (INT, Foreign Key → users.id)
- `order_number` (VARCHAR 50, Unique)
- `total_amount` (DECIMAL 10,2)
- `payment_method` (ENUM: 'cash_delivery', 'cash_pickup', 'gcash', 'paypal')
- `delivery_method` (ENUM: 'delivery', 'pickup')
- `delivery_address` (TEXT)
- `delivery_latitude` (DECIMAL 10,8, Nullable)
- `delivery_longitude` (DECIMAL 11,8, Nullable)
- `contact_number` (VARCHAR 20)
- `status` (ENUM: 'pending', 'confirmed', 'preparing', 'out_for_delivery', 'ready_for_pickup', 'completed', 'cancelled')
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

#### 4. **order_items**
- `id` (INT, Primary Key)
- `order_id` (INT, Foreign Key → orders.id)
- `product_id` (INT, Foreign Key → products.id)
- `quantity` (INT)
- `price` (DECIMAL 10,2)
- `subtotal` (DECIMAL 10,2)

#### 5. **store_locations**
- `id` (INT, Primary Key)
- `name` (VARCHAR 255)
- `address` (TEXT)
- `latitude` (DECIMAL 10,8)
- `longitude` (DECIMAL 11,8)
- `phone` (VARCHAR 20)
- `hours` (VARCHAR 255)
- `is_active` (TINYINT, Default: 1)
- `created_at` (TIMESTAMP)

#### 6. **notifications**
- `id` (INT, Primary Key)
- `type` (ENUM: 'low_stock', 'order_update', 'new_order', 'system')
- `message` (TEXT)
- `user_id` (INT, Foreign Key → users.id, Nullable)
- `is_read` (TINYINT, Default: 0)
- `created_at` (TIMESTAMP)

#### 7. **delivery_logs**
- `id` (INT, Primary Key)
- `order_id` (INT, Foreign Key → orders.id)
- `delivery_person` (VARCHAR 255, Nullable)
- `status_update` (TEXT)
- `timestamp` (TIMESTAMP)

#### 8. **sales_reports**
- `id` (INT, Primary Key)
- `date` (DATE)
- `total_sales` (DECIMAL 10,2)
- `total_orders` (INT)
- `best_seller` (VARCHAR 255, Nullable)
- `generated_at` (TIMESTAMP)

---

## 👥 User Roles & Permissions

### **1. Super Admin** 🔴
**Access Level:** Full System Access

**Permissions:**
- ✅ All Staff permissions
- ✅ Manage staff accounts (Create, Edit, Delete)
- ✅ Manage store locations
- ✅ View all system reports
- ✅ System configuration

**Dashboard:** `admin/index.php?page=dashboard`

### **2. Staff** 👷
**Access Level:** Operational Management

**Permissions:**
- ✅ View and manage products
- ✅ View and manage orders
- ✅ Update order status
- ✅ View sales reports
- ✅ View notifications
- ❌ Cannot manage staff accounts
- ❌ Cannot manage store locations

**Dashboard:** `staff/dashboard.php`

### **3. Customer** 👤
**Access Level:** Shopping & Orders

**Permissions:**
- ✅ Browse products
- ✅ Add to cart
- ✅ Place orders
- ✅ View own order history
- ✅ Manage profile
- ❌ Cannot access admin panel
- ❌ Cannot manage products/orders

**Dashboard:** `customer/shop.php`

---

## ✨ Key Features

### **1. Product Management**
- ✅ Add/Edit/Delete products
- ✅ Image upload (JPG, PNG, GIF, WEBP)
- ✅ Category management
- ✅ Stock tracking
- ✅ Price management

### **2. Order Management**
- ✅ Order creation from cart
- ✅ Multiple payment methods (Cash, GCash, PayPal)
- ✅ Delivery & Pickup options
- ✅ Order status tracking
- ✅ Order history for customers

### **3. User Authentication**
- ✅ Email/Password login
- ✅ Google OAuth login (optional)
- ✅ Email verification (customers)
- ✅ Password hashing (bcrypt)
- ✅ Session management

### **4. Admin Panel**
- ✅ AJAX-based Single Page Application
- ✅ Real-time dashboard statistics
- ✅ Product management with SweetAlert2
- ✅ Order management
- ✅ Sales reports
- ✅ Staff management (Super Admin only)
- ✅ Store locations management

### **5. Notifications**
- ✅ Low stock alerts
- ✅ Order updates
- ✅ New order notifications
- ✅ System notifications

### **6. Maps Integration**
- ✅ Google Maps API integration
- ✅ Store location mapping
- ✅ Delivery address mapping
- ✅ Geocoding support

### **7. Security Features**
- ✅ SQL injection protection (Prepared statements)
- ✅ XSS protection (Input sanitization)
- ✅ Password hashing (bcrypt)
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ Email verification

---

## 🛠️ Technology Stack

### **Backend:**
- **PHP 7.4+** (Server-side logic)
- **MySQL** (Database)
- **PHPMailer** (Email sending)
- **Session Management** (Authentication)

### **Frontend:**
- **HTML5** (Structure)
- **Tailwind CSS** (Styling via CDN)
- **Alpine.js** (Reactive UI)
- **JavaScript (Vanilla)** (Interactivity)
- **SweetAlert2** (Beautiful dialogs)
- **Leaflet.js** (Maps - OpenStreetMap)

### **Libraries & APIs:**
- **Google Maps API** (Maps & Geocoding)
- **Google OAuth 2.0** (Social login)
- **Font Awesome** (Icons)

### **Development Environment:**
- **XAMPP** (Local development)
- **Apache** (Web server)
- **MySQL** (Database server)

---

## 🔄 System Flow

### **Customer Flow:**
1. Browse products on homepage (`index.php`)
2. Add products to cart (localStorage)
3. Login/Register if needed
4. Proceed to checkout (`customer/checkout.php`)
5. Place order
6. View order history (`customer/orders.php`)

### **Admin Flow:**
1. Login as admin/staff
2. Redirected to admin dashboard (`admin/index.php`)
3. Navigate via sidebar (AJAX content loading)
4. Manage products, orders, reports
5. View notifications

### **Staff Flow:**
1. Login as staff
2. Redirected to staff dashboard (`staff/dashboard.php`)
3. Manage orders and products
4. Update order statuses
5. View notifications

---

## 📊 Database Relationships

```
users (1) ──→ (N) orders
orders (1) ──→ (N) order_items
products (1) ──→ (N) order_items
users (1) ──→ (N) notifications
orders (1) ──→ (N) delivery_logs
```

---

## 🔐 Security Implementation

1. **SQL Injection:** Prepared statements with parameter binding
2. **XSS Protection:** `htmlspecialchars()` and `strip_tags()` on all outputs
3. **Password Security:** `password_hash()` and `password_verify()`
4. **Session Security:** Session validation on every protected page
5. **Access Control:** Role-based checks before page access
6. **File Upload:** MIME type validation, extension checking

---

## 📝 Configuration Files

### **`includes/config.php`**
- Database connection settings
- Google Maps API key
- Google OAuth credentials
- Helper functions (isLoggedIn, getUserRole, isAdmin, etc.)
- Session initialization

### **Environment Variables:**
- `DB_HOST`: `localhost`
- `DB_USER`: `root`
- `DB_PASS`: `` (empty)
- `DB_NAME`: `danicop`
- `GOOGLE_MAPS_API_KEY`: (Set in config)
- `GOOGLE_CLIENT_ID`: (Set in config)
- `GOOGLE_CLIENT_SECRET`: (Set in config)
- `GOOGLE_REDIRECT_URI`: `http://mwa/hardware/auth/google_callback.php`

---

## 🚀 Quick Start

1. **Setup Database:**
   ```bash
   php utils/setup_database.php
   ```

2. **Create Super Admin:**
   ```bash
   php utils/create_superadmin.php
   ```

3. **Configure:**
   - Edit `includes/config.php`
   - Set database credentials
   - Set Google Maps API key (optional)
   - Set Google OAuth credentials (optional)

4. **Access:**
   - Homepage: `http://mwa/hardware/`
   - Admin: `http://mwa/hardware/admin/`
   - Staff: `http://mwa/hardware/staff/`

---

## 📈 System Statistics

- **Total PHP Files:** ~55
- **Database Tables:** 8
- **User Roles:** 3
- **Payment Methods:** 4
- **Order Statuses:** 7
- **Supported Image Formats:** 4 (JPG, PNG, GIF, WEBP)

---

**Last Updated:** 2024  
**Version:** 1.0  
**Maintainer:** Danicop Hardware Development Team

