# Danicop Hardware Online - Web-Based Hardware Ordering & Inventory System

A complete web-based platform for hardware ordering and inventory management built with PHP, MySQL, and Tailwind CSS.

## 🚀 Features

### Customer Features
- Browse products without login
- Search and filter products by category
- Add items to shopping cart
- Place orders with delivery or pickup options
- Track order status
- View order history
- Multiple payment options (Cash on Delivery, Cash on Pickup, GCash, PayPal)

### Admin/Staff Features
- Product management (Add, Edit, Delete)
- Order management with status updates
- Inventory tracking with low-stock alerts
- Sales reports and analytics
- Staff account management (Super Admin only)
- Notification system

## 📋 Requirements

- XAMPP (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge, etc.)

## 🛠️ Installation

1. **Install XAMPP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `docs/database_schema.sql` file
   - Or run the SQL commands manually in phpMyAdmin

3. **Configure Database Connection**
   - Open `includes/config.php`
   - Update database credentials if needed (default: root, no password)

4. **(Optional) Enable Google Login**
   - Create OAuth credentials in Google Cloud Console
   - Set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI` in `includes/config.php`
   - Run `docs/add_google_auth_and_profile_fields.sql` in phpMyAdmin to add the required columns to the `users` table`

5. **Setup Project**
   - Copy the project folder to `C:\xampp\htdocs\hardware`
   - Or place it in your XAMPP htdocs directory

6. **Set Permissions**
   - Ensure the `uploads` folder has write permissions for image uploads

7. **Access the Application**
   - Open browser and go to: `http://localhost/hardware`
   - Default admin login:
     - Email: `admin@hardware.com`
     - Password: `admin123`
   - If password doesn't work, use: `utils/reset_admin.php`

## 📁 Project Structure

```
hardware/
├── index.php              # 🏠 Landing page / Homepage (Public)
│
├── customer/              # 👤 CUSTOMER PAGES
│   ├── checkout.php      # 🛒 Checkout page
│   ├── orders.php        # 📋 Customer order history
│   └── profile.php       # 👤 User profile management
│
├── auth/                  # 🔐 AUTHENTICATION
│   ├── login.php         # 🔐 User login (standalone)
│   ├── register.php      # ✍️ User registration (standalone)
│   └── logout.php        # 🚪 Logout handler
│
├── admin/                 # 👨‍💼 ADMIN PANEL
│   ├── dashboard.php      # 📊 Admin dashboard
│   ├── products.php       # 📦 Product management
│   ├── product_add.php    # ➕ Add new product
│   ├── product_edit.php   # ✏️ Edit product
│   ├── orders.php         # 📋 Order management
│   ├── order_details.php  # 🔍 Order details & status update
│   ├── reports.php        # 📈 Sales reports
│   ├── users.php          # 👥 Staff management (Super Admin only)
│   ├── user_add.php       # ➕ Add staff member
│   └── notifications.php  # 🔔 System notifications
│
├── includes/              # 🔧 SHARED FILES
│   └── config.php         # ⚙️ Database config & helper functions
│
├── utils/                 # 🛠️ UTILITY SCRIPTS
│   ├── create_user.php    # 👤 Create user (any role)
│   ├── reset_admin.php    # 🔑 Reset admin password
│   └── test_functionality.php  # ✅ System test
│
├── docs/                  # 📚 DOCUMENTATION
│   ├── README.md          # 📖 This file
│   └── database_schema.sql # 🗄️ Database schema
│
└── uploads/               # 🖼️ PRODUCT IMAGES
```

**📋 For detailed file organization, see [PROJECT_STRUCTURE.md](../PROJECT_STRUCTURE.md)**

## 👥 User Roles

### Super Admin
- Full system access
- Manage staff accounts
- All admin and staff features

### Staff
- View and manage orders
- Update product inventory
- View sales reports
- Receive notifications

### Customer
- Browse and purchase products
- Track orders
- View order history

## 🗄️ Database Schema

The system includes the following tables:
- `users` - User accounts (admin, staff, customers)
- `products` - Product catalog
- `orders` - Customer orders
- `order_items` - Order line items
- `delivery_logs` - Delivery tracking
- `sales_reports` - Sales analytics
- `notifications` - System notifications

## 🎨 Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, Tailwind CSS, JavaScript
- **Libraries**: Alpine.js, Chart.js, Font Awesome

## 📱 Mobile Responsive

The entire system is built with mobile-first design using Tailwind CSS, ensuring optimal experience on:
- Mobile phones
- Tablets
- Desktop computers

## 🔐 Security Features

- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Session management
- Role-based access control

## 📝 Order Status Flow

1. **Pending** - Order placed, awaiting confirmation
2. **Confirmed** - Order confirmed by staff
3. **Preparing** - Order being prepared
4. **Out for Delivery** - Order out for delivery
5. **Ready for Pickup** - Order ready for customer pickup
6. **Completed** - Order completed
7. **Cancelled** - Order cancelled

## 🚨 Notifications

The system automatically sends notifications for:
- Low stock alerts (< 10 items)
- New orders
- Order status updates

## 📊 Reports

Admin can generate reports showing:
- Total sales and orders
- Best-selling products
- Orders by status
- Daily sales charts
- Date range filtering

## 🛒 Shopping Cart

- Cart stored in browser localStorage
- Persistent across page refreshes
- Real-time price calculation
- Stock validation

## 🔧 Configuration

Edit `config.php` to customize:
- Database connection settings
- Session configuration
- Application settings

## 📞 Support

For issues or questions, please check:
- Database connection settings
- File permissions (especially uploads folder)
- PHP error logs
- MySQL error logs

## 📄 License

This project is open source and available for educational purposes.

---

**Note**: Remember to change the default admin password after first login!

