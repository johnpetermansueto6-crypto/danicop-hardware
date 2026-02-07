# 🚚 Driver System Update - Using Users Table

## 📋 Overview

The delivery system has been updated so that **drivers are staff members with role "driver"** instead of a separate drivers table. This integrates better with your existing user management system.

---

## 🔄 Key Changes

### 1. **Drivers are Users**
- Drivers are now users in the `users` table with `role = 'driver'`
- No separate `drivers` table needed
- Super Admin can add drivers via "Manage Staff" → "Add Staff" → Select "Driver" role

### 2. **Email Credentials**
- When Super Admin creates a driver account, an email is automatically sent with:
  - Name
  - Email
  - Password
  - Login URL

### 3. **Driver Dashboard**
- New dashboard at `driver/dashboard.php`
- Shows active deliveries with full customer details
- Drivers can update delivery status
- View customer name, contact, address, order details

### 4. **Updated Database Schema**
- `delivery_assignments.driver_id` now references `users.id` (not `drivers.id`)
- All queries updated to use `users` table with `role = 'driver'`

---

## 🗄️ Database Updates Required

### Step 1: Update Users Table Role ENUM

Run this SQL to add 'driver' to the role enum:

```sql
ALTER TABLE users MODIFY COLUMN role ENUM('superadmin', 'staff', 'driver', 'customer') NOT NULL DEFAULT 'customer';
```

**File:** `docs/update_users_role_enum.sql`

### Step 2: Run Delivery Module SQL

Run the updated delivery module SQL (already updated to use users table):

```sql
-- Import: docs/add_delivery_module.sql
```

---

## 📁 New Files Created

1. **`driver/dashboard.php`** - Driver dashboard with delivery management
2. **Updated `admin/content/user_add.php`** - Added driver role and email sending
3. **Updated `admin/content/users.php`** - Shows driver role badge
4. **Updated all delivery files** - Now use users table instead of drivers table

---

## 🎯 How It Works

### **Adding a Driver (Super Admin)**

1. Go to **Manage Staff** → **Add Staff**
2. Fill in:
   - Name
   - Email
   - Password
   - **Role: Select "Driver"**
3. Click **Add Staff**
4. System automatically:
   - Creates user account with role='driver'
   - Sends email with credentials to the driver's email

### **Driver Login**

1. Driver receives email with credentials
2. Driver logs in at `auth/login.php`
3. Automatically redirected to `driver/dashboard.php`

### **Driver Dashboard Features**

- **View Active Deliveries:**
  - Order number
  - Customer name, email, phone
  - Delivery address (with map link if coordinates available)
  - Order amount and payment method
  - Current delivery status

- **Update Delivery Status:**
  - Click "Update" button
  - Select new status (picked_up, delivering, delivered, failed)
  - Status updates automatically

- **View Delivery Details:**
  - Click "View" to see full order details

### **Staff Assigning Deliveries**

1. Go to **Deliveries** dashboard
2. Find order in "Orders Needing Assignment"
3. Click **Assign**
4. Select driver from dropdown (shows all users with role='driver')
5. Add optional notes
6. Click **Assign Driver**

---

## 📧 Email Template

When a driver account is created, they receive an email with:

```
Subject: Your Driver Account Credentials - Danicop Hardware

Hello [Driver Name],

Your driver account has been created successfully.

Credentials:
- Name: [Name]
- Email: [Email]
- Password: [Password]

Login URL: http://mwa/hardware/auth/login.php

Once you log in, you will be able to view your assigned deliveries 
and customer information.
```

---

## 🔐 Access Control

- **Super Admin:** Can add drivers, assign deliveries, manage everything
- **Staff:** Can assign deliveries to drivers, update status
- **Driver:** Can only view their own deliveries and update status

---

## ✅ Testing Checklist

- [ ] Run SQL to update users.role ENUM
- [ ] Run delivery module SQL
- [ ] Add a test driver via Manage Staff
- [ ] Check email is sent with credentials
- [ ] Login as driver
- [ ] Verify driver dashboard loads
- [ ] Assign a delivery to driver (as staff)
- [ ] Verify driver sees the delivery
- [ ] Update delivery status as driver
- [ ] Verify status updates correctly

---

## 🔄 Migration from Old System

If you had the old drivers table:

1. **Migrate existing drivers to users table:**
   ```sql
   INSERT INTO users (name, email, password, role, email_verified)
   SELECT name, email, '$2y$10$...', 'driver', 1
   FROM drivers;
   ```
   (Generate password hashes for each driver)

2. **Update delivery_assignments:**
   ```sql
   UPDATE delivery_assignments da
   INNER JOIN drivers d ON da.driver_id = d.id
   INNER JOIN users u ON d.email = u.email AND u.role = 'driver'
   SET da.driver_id = u.id;
   ```

3. **Drop old drivers table:**
   ```sql
   DROP TABLE IF EXISTS drivers;
   ```

---

## 📝 Notes

- Drivers don't need email verification (auto-verified when created by admin)
- Driver status is managed through delivery_assignments, not user table
- All delivery history and statistics work with users table
- Driver dashboard auto-updates when status changes

---

**System is ready!** Drivers are now fully integrated as users. 🎉

