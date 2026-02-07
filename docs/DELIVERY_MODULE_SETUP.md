# 🚚 Delivery Management Module - Setup Guide

## 📋 Overview

Complete Delivery Management Module integrated into your existing Danicop Hardware system. This module allows Super Admin and Staff to manage drivers, assign orders, and track deliveries.

---

## 🗄️ Database Setup

### Step 1: Run SQL Migration

Execute the SQL file to create all necessary tables:

```bash
# Option 1: Via phpMyAdmin
# Import: docs/add_delivery_module.sql

# Option 2: Via Command Line
mysql -u root -p danicop < docs/add_delivery_module.sql
```

### Step 2: Verify Tables Created

Check that these tables exist:
- ✅ `drivers`
- ✅ `delivery_assignments`
- ✅ `delivery_history`

---

## 📁 Files Created

### Admin Content Files (`admin/content/`)

1. **deliveries.php** - Main delivery dashboard
2. **delivery_assign.php** - Assign driver to order
3. **delivery_assign_handler.php** - AJAX handler for assignments
4. **delivery_update.php** - AJAX handler for status updates
5. **delivery_reassign.php** - AJAX handler for reassignments
6. **delivery_get_drivers.php** - AJAX endpoint for driver list
7. **delivery_view.php** - View delivery details (popup)
8. **delivery_history.php** - Delivery history logs
9. **drivers.php** - Driver management list
10. **driver_add.php** - Add new driver form
11. **driver_edit.php** - Edit driver form
12. **driver_view.php** - Driver details page

---

## 🎯 Features

### 1. Driver Management
- ✅ Add/Edit/Delete drivers
- ✅ Set driver status (available, delivering, off_duty, unavailable)
- ✅ Track driver performance (total deliveries, active deliveries)
- ✅ View driver details with delivery history

### 2. Order Assignment
- ✅ View orders needing assignment (confirmed/preparing status)
- ✅ Assign driver to order
- ✅ Automatic status updates:
  - Order → `out_for_delivery`
  - Driver → `delivering`

### 3. Delivery Tracking
- ✅ Status workflow: `assigned` → `picked_up` → `delivering` → `delivered`/`failed`
- ✅ Update delivery status with SweetAlert2
- ✅ Reassign deliveries to different drivers
- ✅ View pending deliveries dashboard

### 4. Delivery History
- ✅ Complete audit trail of all delivery actions
- ✅ Filter by driver, date, order
- ✅ Track status changes and reassignments

### 5. Notifications
- ✅ Automatic notifications when:
  - Driver is assigned
  - Delivery status is updated
  - Delivery is reassigned

---

## 🚀 Usage Guide

### Accessing Delivery Management

1. **Login as Super Admin or Staff**
2. **Navigate to "Deliveries" in sidebar**
3. **Main Dashboard shows:**
   - Statistics cards (Active, Today Delivered, Available Drivers, Pending)
   - Pending Deliveries table
   - Orders Needing Assignment
   - Available Drivers
   - Recent Delivery Logs

### Assigning a Driver

1. Go to **Deliveries** dashboard
2. Find order in **"Orders Needing Assignment"** section
3. Click **"Assign"** button
4. Select driver from dropdown
5. Add optional notes
6. Click **"Assign Driver"**

### Updating Delivery Status

1. Find delivery in **"Pending Deliveries"** section
2. Click **Update** icon (green edit button)
3. Select new status from dropdown
4. Status updates automatically

### Managing Drivers

1. Click **"Manage Drivers"** button
2. **Add Driver:** Click "Add Driver" → Fill form → Submit
3. **Edit Driver:** Click "Edit" → Modify → Update
4. **View Details:** Click "View" → See statistics and history
5. **Delete Driver:** Click "Delete" → Confirm (only if no active deliveries)

---

## 🔄 Status Workflow

```
Order Status: confirmed/preparing
    ↓
Assign Driver
    ↓
Order Status: out_for_delivery
Driver Status: delivering
Delivery Status: assigned
    ↓
Update: picked_up
    ↓
Update: delivering
    ↓
Update: delivered
    ↓
Order Status: completed
Driver Status: available
Driver Total Deliveries: +1
```

**Failed Path:**
```
delivering → failed
    ↓
Driver Status: available
(Order can be reassigned)
```

---

## 📊 Database Schema

### `drivers` Table
- `id` - Primary key
- `name` - Driver name
- `phone` - Contact number
- `email` - Email (optional)
- `status` - available/delivering/off_duty/unavailable
- `vehicle_type` - Motorcycle/Van/Truck/etc
- `license_number` - Driver license
- `total_deliveries` - Counter (auto-incremented)
- `created_at`, `updated_at`

### `delivery_assignments` Table
- `id` - Primary key
- `order_id` - Foreign key to orders
- `driver_id` - Foreign key to drivers
- `assigned_by` - User who assigned (foreign key to users)
- `status` - assigned/picked_up/delivering/delivered/failed
- `notes` - Optional notes
- `delivery_started_at` - When status changed to delivering
- `delivery_completed_at` - When status changed to delivered
- `created_at`, `updated_at`

### `delivery_history` Table
- `id` - Primary key
- `assignment_id` - Foreign key to delivery_assignments
- `action` - assigned/status_update/reassigned
- `previous_status` - Previous delivery status
- `new_status` - New delivery status
- `notes` - Additional notes
- `updated_by` - User who made change (foreign key to users)
- `timestamp` - When change occurred

---

## 🎨 UI Components

### Dashboard Sections

1. **Statistics Cards** (4 cards)
   - Active Deliveries
   - Today Delivered
   - Available Drivers
   - Pending Assignment

2. **Pending Deliveries Table**
   - Order #, Customer, Driver, Status, Actions

3. **Orders Needing Assignment Table**
   - Order #, Customer, Address, Assign button

4. **Available Drivers Table**
   - Name, Phone, Vehicle, Active, Total, Actions

5. **Recent Delivery Logs Table**
   - Order #, Driver, Action, Status Change, Updated By, Time

---

## 🔔 Notifications

Notifications are automatically created for:
- ✅ New driver assignment
- ✅ Delivery status updates
- ✅ Driver reassignments

All notifications appear in the existing **Notifications** section of admin panel.

---

## 🔄 Auto-Refresh

The delivery dashboard auto-refreshes every **10 seconds** to show real-time updates.

---

## 🛡️ Security

- ✅ All AJAX handlers check authentication
- ✅ Role-based access (Super Admin & Staff only)
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Transaction-based updates (data integrity)

---

## 🐛 Troubleshooting

### Issue: "No available drivers"
**Solution:** Add drivers via "Manage Drivers" → "Add Driver"

### Issue: "Order already assigned"
**Solution:** Check if order has active assignment in Pending Deliveries

### Issue: "Cannot delete driver"
**Solution:** Driver has active deliveries. Complete or reassign deliveries first.

### Issue: Status update not working
**Solution:** Check browser console for errors. Verify AJAX endpoints are accessible.

---

## 📝 Notes

- **Driver Status:** Automatically managed by system
- **Order Status:** Automatically updated when delivery is assigned/completed
- **History:** All actions are logged for audit trail
- **Notifications:** Sent to all admins/staff (user_id = NULL)

---

## ✅ Testing Checklist

- [ ] Run SQL migration
- [ ] Add at least 2 drivers
- [ ] Create a test order with delivery method
- [ ] Assign driver to order
- [ ] Update delivery status through workflow
- [ ] Complete delivery (verify order status = completed)
- [ ] View delivery history
- [ ] Test driver reassignment
- [ ] Test driver deletion (with/without active deliveries)
- [ ] Check notifications are created

---

**Module is ready to use!** 🎉

For questions or issues, check the main system documentation or review the code comments.

