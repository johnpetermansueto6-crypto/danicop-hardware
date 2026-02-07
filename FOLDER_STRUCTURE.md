# 📁 Folder Structure by User Role

## 🎯 Role-Based Organization

Your project is now organized by user roles for maximum clarity:

```
hardware/
│
├── 🏠 ROOT (Public Access)
│   └── index.php              # Landing page - anyone can view
│
├── 👤 customer/ (Customer Only)
│   ├── checkout.php          # Checkout process
│   ├── orders.php            # View order history
│   └── profile.php           # Manage profile
│
├── 🔐 auth/ (Authentication)
│   ├── login.php             # User login
│   ├── register.php          # User registration
│   └── logout.php            # Logout handler
│
├── 👨‍💼 admin/ (Admin & Staff)
│   ├── dashboard.php         # Admin dashboard
│   ├── products.php          # Manage products
│   ├── product_add.php       # Add product
│   ├── product_edit.php      # Edit product
│   ├── orders.php            # Manage orders
│   ├── order_details.php     # Order details
│   ├── reports.php           # Sales reports
│   ├── users.php             # Staff management (Super Admin only)
│   ├── user_add.php          # Add staff (Super Admin only)
│   └── notifications.php     # System notifications
│
├── 🔧 includes/ (Shared)
│   └── config.php            # Database & functions
│
├── 🛠️ utils/ (Tools)
│   ├── create_user.php       # Create users
│   ├── reset_admin.php       # Reset admin password
│   └── test_functionality.php # Test system
│
├── 📚 docs/ (Documentation)
│   ├── README.md             # Full documentation
│   └── database_schema.sql   # Database setup
│
└── 🖼️ uploads/ (Media)
    └── (Product images)
```

## 👥 User Access by Role

### 👤 Customer
- ✅ Can access: `index.php`, `customer/*`, `auth/*`
- ❌ Cannot access: `admin/*`

### 👨‍💼 Staff
- ✅ Can access: `index.php`, `customer/*`, `auth/*`, `admin/*` (except user management)
- ❌ Cannot access: `admin/users.php`, `admin/user_add.php`

### 🔴 Super Admin
- ✅ Can access: Everything!

## 🔗 Quick Links

- **Homepage:** `index.php`
- **Customer Login:** `auth/login.php`
- **Customer Dashboard:** `customer/profile.php`
- **Admin Login:** `auth/login.php` (then redirected to `admin/dashboard.php`)
- **Admin Dashboard:** `admin/dashboard.php`

---

**Everything is organized by role for easy navigation!** 🎉

