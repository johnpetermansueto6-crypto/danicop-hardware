# 🚀 START HERE - Danicop Hardware Online

## 📁 Project Organization

Your project is now organized into clear folders:

```
hardware/
│
├── 🏠 ROOT (Public)
│   └── index.php          ← Homepage (START HERE!)
│
├── 👤 customer/ (Customer Pages)
│   ├── checkout.php       ← Checkout
│   ├── orders.php         ← My Orders
│   └── profile.php        ← My Profile
│
├── 🔐 auth/ (Authentication)
│   ├── login.php          ← Login page
│   ├── register.php       ← Registration
│   └── logout.php         ← Logout
│
├── 👨‍💼 admin/ (Admin Panel)
│   ├── dashboard.php      ← Admin Dashboard
│   ├── products.php       ← Manage Products
│   ├── orders.php         ← Manage Orders
│   ├── reports.php        ← Sales Reports
│   └── users.php          ← Manage Staff (Super Admin)
│
├── 🔧 includes/ (Shared Files)
│   └── config.php         ← Database & Functions
│
├── 🛠️ utils/ (Tools)
│   ├── create_user.php    ← Create Users
│   ├── reset_admin.php    ← Reset Admin Password
│   └── test_functionality.php ← Test System
│
├── 📚 docs/ (Documentation)
│   ├── README.md          ← Full Documentation
│   └── database_schema.sql ← Database Setup
│
└── 🖼️ uploads/ (Images)
    └── (Product images)
```

## 🎯 Quick Start

1. **Import Database:**
   - Go to: `http://localhost/phpmyadmin`
   - Import: `docs/database_schema.sql`

2. **Access System:**
   - Homepage: `http://localhost/hardware`
   - Admin: `admin@hardware.com` / `admin123`

3. **If Admin Login Fails:**
   - Visit: `http://localhost/hardware/utils/reset_admin.php`

## 📖 Documentation Files

- **START_HERE.md** ← You are here!
- **PROJECT_STRUCTURE.md** ← Detailed folder structure
- **FILE_INDEX.md** ← Quick file reference
- **docs/README.md** ← Complete documentation

## 🎨 Main Features

✅ **Customer:** Browse, Cart, Order, Track  
✅ **Staff:** Manage Products, Orders, Reports  
✅ **Super Admin:** Everything + User Management  

---

**Everything is organized and ready to use!** 🎉

