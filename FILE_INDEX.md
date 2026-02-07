# 📋 File Index - Quick Reference

## 🏠 Public Pages (Root Directory)

| File | Purpose | Access |
|------|---------|--------|
| `index.php` | Homepage with products, cart, login/register modals | Public |

## 👤 Customer Pages (`/customer/`)

| File | Purpose | Access |
|------|---------|--------|
| `checkout.php` | Order checkout process | Login Required |
| `orders.php` | Customer order history | Login Required |
| `profile.php` | User profile & settings | Login Required |

## 🔐 Authentication (`/auth/`)

| File | Purpose | Access |
|------|---------|--------|
| `login.php` | Login page (standalone) | Public |
| `register.php` | Registration page (standalone) | Public |
| `logout.php` | Logout handler | Login Required |

## 👨‍💼 Admin Panel (`/admin/`)

| File | Purpose | Access |
|------|---------|--------|
| `dashboard.php` | Admin dashboard with statistics | Admin/Staff |
| `products.php` | Product listing & management | Admin/Staff |
| `product_add.php` | Add new product | Admin/Staff |
| `product_edit.php` | Edit existing product | Admin/Staff |
| `orders.php` | View all orders | Admin/Staff |
| `order_details.php` | Order details & status update | Admin/Staff |
| `reports.php` | Sales reports & analytics | Admin/Staff |
| `users.php` | Staff account management | Super Admin Only |
| `user_add.php` | Add new staff member | Super Admin Only |
| `notifications.php` | System notifications | Admin/Staff |

## 🔧 Shared Files (`/includes/`)

| File | Purpose |
|------|---------|
| `config.php` | Database connection, session, helper functions |

## 🛠️ Utility Scripts (`/utils/`)

| File | Purpose | When to Use |
|------|---------|-------------|
| `create_user.php` | Create user with any role | Setup/Testing |
| `reset_admin.php` | Reset admin password | If admin login fails |
| `test_functionality.php` | System functionality test | Testing/Verification |

## 📚 Documentation (`/docs/`)

| File | Purpose |
|------|---------|
| `README.md` | Complete system documentation |
| `database_schema.sql` | Database structure & sample data |

## 🖼️ Media (`/uploads/`)

| Purpose |
|---------|
| Product images storage |

---

**💡 Tip:** See `PROJECT_STRUCTURE.md` for detailed folder organization!

