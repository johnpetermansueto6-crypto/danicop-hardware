# 📁 Danicop Hardware Online - Project Structure

## 📂 Folder Organization

```
hardware/
│
├── 📄 index.php                    # 🏠 Homepage (Landing page with products)
│
├── 📁 customer/                    # 👤 CUSTOMER PAGES
│   ├── checkout.php               # 🛒 Checkout page
│   ├── orders.php                 # 📋 Customer order history
│   └── profile.php                # 👤 User profile management
│
├── 📁 auth/                        # 🔐 AUTHENTICATION
│   ├── login.php                  # 🔐 Login page (standalone)
│   ├── register.php               # ✍️ Registration page (standalone)
│   └── logout.php                 # 🚪 Logout handler
│
├── 📁 admin/                       # 👨‍💼 ADMIN PANEL
│   ├── dashboard.php              # 📊 Admin dashboard
│   ├── products.php               # 📦 Product listing
│   ├── product_add.php            # ➕ Add new product
│   ├── product_edit.php           # ✏️ Edit product
│   ├── orders.php                 # 📋 Order management
│   ├── order_details.php          # 🔍 Order details & status update
│   ├── reports.php                # 📈 Sales reports
│   ├── users.php                  # 👥 Staff management (Super Admin only)
│   ├── user_add.php               # ➕ Add staff member
│   └── notifications.php          # 🔔 System notifications
│
├── 📁 includes/                    # 🔧 SHARED FILES
│   └── config.php                 # ⚙️ Database config & helper functions
│
├── 📁 utils/                       # 🛠️ UTILITY SCRIPTS
│   ├── create_user.php            # 👤 Create user (any role)
│   ├── reset_admin.php            # 🔑 Reset admin password
│   └── test_functionality.php     # ✅ System functionality test
│
├── 📁 docs/                        # 📚 DOCUMENTATION
│   ├── README.md                  # 📖 Main documentation
│   └── database_schema.sql        # 🗄️ Database schema
│
└── 📁 uploads/                     # 🖼️ PRODUCT IMAGES
    └── (product images stored here)
```

## 🎯 File Categories

### 🏠 Public Pages (Root Directory)
- **index.php** - Main homepage with product browsing, search, cart (public access)

### 👤 Customer Pages (`/customer/`)
- **checkout.php** - Order checkout process (login required)
- **orders.php** - Customer order history (login required)
- **profile.php** - User profile & account settings (login required)

### 🔐 Authentication (`/auth/`)
- **login.php** - User login page
- **register.php** - User registration page
- **logout.php** - Logout handler

### 👨‍💼 Admin Panel (`/admin/`)
All admin and staff management features:
- **dashboard.php** - Main admin dashboard with statistics
- **products.php** - Product management (list, delete)
- **product_add.php** - Add new products
- **product_edit.php** - Edit existing products
- **orders.php** - View and filter all orders
- **order_details.php** - Order details and status updates
- **reports.php** - Sales reports and analytics
- **users.php** - Staff account management (Super Admin only)
- **user_add.php** - Add new staff members
- **notifications.php** - System notifications

### 🔧 Shared Files (`/includes/`)
- **config.php** - Database connection, session management, helper functions

### 🛠️ Utility Scripts (`/utils/`)
- **create_user.php** - Quick user creation tool
- **reset_admin.php** - Reset admin password (delete after use!)
- **test_functionality.php** - System testing and verification

### 📚 Documentation (`/docs/`)
- **README.md** - Complete system documentation
- **database_schema.sql** - Database structure and sample data

### 🖼️ Media (`/uploads/`)
- Product images are stored here

## 🔑 Key Files Explained

### ⚙️ config.php
**Location:** `includes/config.php`
- Database connection settings
- Session management
- Helper functions (isLoggedIn, isAdmin, sanitize, etc.)
- Used by ALL PHP files

### 🏠 index.php
**Location:** Root directory
- Main landing page
- Product browsing (no login required)
- Shopping cart functionality
- Login/Register modals
- Search and filter products

### 📊 admin/dashboard.php
**Location:** `admin/dashboard.php`
- Admin/Staff dashboard
- Statistics overview
- Quick links to all admin features
- Recent orders display

## 🚀 Quick Access Guide

### For Customers:
- **Homepage:** `index.php`
- **Login:** Click "Login" button (modal) or `login.php`
- **Register:** Click "Register" button (modal) or `register.php`
- **My Orders:** `orders.php` (requires login)
- **Profile:** `profile.php` (requires login)

### For Admin/Staff:
- **Dashboard:** `admin/dashboard.php` (requires admin login)
- **Manage Products:** `admin/products.php`
- **Manage Orders:** `admin/orders.php`
- **View Reports:** `admin/reports.php`

### For Super Admin:
- **Manage Staff:** `admin/users.php` (Super Admin only)
- **Add Staff:** `admin/user_add.php`

### Utility Tools:
- **Create User:** `utils/create_user.php`
- **Reset Admin:** `utils/reset_admin.php` (delete after use!)
- **Test System:** `utils/test_functionality.php`

## 📝 Important Notes

1. **config.php** is in `includes/` folder - all files reference it correctly
2. **Utility scripts** are in `utils/` - use them for setup/maintenance
3. **Documentation** is in `docs/` folder
4. **Product images** go in `uploads/` folder
5. **Admin panel** is in `admin/` folder - requires admin/staff login

## 🔐 Default Login

- **Email:** admin@hardware.com
- **Password:** admin123
- **Role:** Super Admin

*(Use `utils/reset_admin.php` if password doesn't work)*

---

**Last Updated:** Project organized for clarity and easy navigation! 🎉

