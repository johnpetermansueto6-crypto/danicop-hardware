# 📊 Driver System - Complete SQL Schema

## 🗄️ Database Structure

### **Tables Overview**

| Table | Purpose | Key Fields |
|-------|---------|------------|
| **users** | All users including drivers | `id`, `role` (ENUM: superadmin, staff, **driver**, customer) |
| **delivery_assignments** | Links orders to drivers | `order_id`, `driver_id` (→ users.id), `status` |
| **delivery_history** | Audit trail | `assignment_id`, `action`, `updated_by` |

---

## 📋 Complete SQL Schema

### **1. Users Table (Updated)**

```sql
-- Add 'driver' to role ENUM
ALTER TABLE users 
MODIFY COLUMN role ENUM('superadmin', 'staff', 'driver', 'customer') 
NOT NULL DEFAULT 'customer';
```

**Key Points:**
- Drivers are users with `role = 'driver'`
- No separate drivers table needed
- Drivers can login like any other user

---

### **2. Delivery Assignments Table**

```sql
CREATE TABLE delivery_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    driver_id INT NOT NULL,              -- References users.id (role='driver')
    assigned_by INT NOT NULL,            -- Staff/admin who assigned
    status ENUM('assigned', 'picked_up', 'delivering', 'delivered', 'failed'),
    notes TEXT,
    delivery_started_at DATETIME,
    delivery_completed_at DATETIME,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (driver_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);
```

**Status Workflow:**
```
assigned → picked_up → delivering → delivered/failed
```

**Relationships:**
- `order_id` → `orders.id`
- `driver_id` → `users.id` (where `users.role = 'driver'`)
- `assigned_by` → `users.id` (staff/admin)

---

### **3. Delivery History Table**

```sql
CREATE TABLE delivery_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,         -- References delivery_assignments.id
    action VARCHAR(100),                -- 'assigned', 'status_update', 'reassigned'
    previous_status VARCHAR(50),
    new_status VARCHAR(50),
    notes TEXT,
    updated_by INT NOT NULL,            -- User who made change
    timestamp TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES delivery_assignments(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);
```

**Purpose:**
- Complete audit trail
- Track all status changes
- Record who made changes
- Track reassignments

---

## 🔗 Relationships Diagram

```
users (role='driver')
    ↓ (driver_id)
delivery_assignments
    ↓ (order_id)
orders
    ↓ (user_id)
users (role='customer')

delivery_history
    ↓ (assignment_id)
delivery_assignments
```

---

## 📝 Key SQL Queries

### **Get All Drivers**
```sql
SELECT id, name, email, phone 
FROM users 
WHERE role = 'driver';
```

### **Get Available Drivers (No Active Deliveries)**
```sql
SELECT 
    u.id,
    u.name,
    u.email,
    COUNT(da.id) as active_deliveries
FROM users u
LEFT JOIN delivery_assignments da 
    ON u.id = da.driver_id 
    AND da.status IN ('assigned', 'picked_up', 'delivering')
WHERE u.role = 'driver'
GROUP BY u.id
HAVING active_deliveries = 0;
```

### **Get Driver's Active Deliveries**
```sql
SELECT 
    da.*,
    o.order_number,
    o.delivery_address,
    u.name as customer_name,
    u.phone as customer_phone
FROM delivery_assignments da
INNER JOIN orders o ON da.order_id = o.id
INNER JOIN users u ON o.user_id = u.id
WHERE da.driver_id = ? 
    AND da.status IN ('assigned', 'picked_up', 'delivering');
```

### **Get Delivery Statistics**
```sql
SELECT 
    COUNT(CASE WHEN status = 'delivered' THEN 1 END) as completed,
    COUNT(CASE WHEN status IN ('assigned', 'picked_up', 'delivering') THEN 1 END) as active,
    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed
FROM delivery_assignments
WHERE driver_id = ?;
```

---

## 🚀 Quick Setup

**Run this single file:**
```sql
-- File: docs/DRIVER_SYSTEM_SCHEMA.sql
-- Contains all necessary SQL
```

**Or run step by step:**
1. `docs/update_users_role_enum.sql` - Update users table
2. `docs/add_delivery_module.sql` - Create delivery tables

---

## ✅ Verification Queries

### **Check Role Enum**
```sql
SELECT COLUMN_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'danicop' 
    AND TABLE_NAME = 'users' 
    AND COLUMN_NAME = 'role';
-- Should show: enum('superadmin','staff','driver','customer')
```

### **Check Tables Exist**
```sql
SHOW TABLES LIKE 'delivery%';
-- Should show: delivery_assignments, delivery_history
```

### **Check Foreign Keys**
```sql
SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'danicop'
    AND TABLE_NAME IN ('delivery_assignments', 'delivery_history');
```

---

## 📊 Table Structure Summary

### **delivery_assignments**
- **Primary Key:** `id`
- **Foreign Keys:** 
  - `order_id` → `orders.id`
  - `driver_id` → `users.id` (role='driver')
  - `assigned_by` → `users.id`
- **Indexes:** order_id, driver_id, status, created_at
- **Unique:** One active assignment per order

### **delivery_history**
- **Primary Key:** `id`
- **Foreign Keys:**
  - `assignment_id` → `delivery_assignments.id`
  - `updated_by` → `users.id`
- **Indexes:** assignment_id, timestamp, action

---

## 🔄 Data Flow

1. **Order Created** → `orders` table
2. **Staff Assigns Driver** → `delivery_assignments` created
3. **Status Updates** → `delivery_assignments.status` updated + `delivery_history` logged
4. **Delivery Completed** → `orders.status = 'completed'` + `delivery_history` logged

---

**Complete schema is ready!** Run `docs/DRIVER_SYSTEM_SCHEMA.sql` to set up everything. 🎉

