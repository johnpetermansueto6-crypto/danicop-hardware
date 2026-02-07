# 📋 User Profile Fields Update

## Overview
This update adds important profile fields to the `users` table to support better user management, delivery tracking, and customer information storage.

## 🆕 New Fields Added

### **Contact & Location Fields**
- `address` (TEXT) - Full address for customers and drivers
- `city` (VARCHAR 100) - City name
- `province` (VARCHAR 100) - Province/State name
- `zipcode` (VARCHAR 10) - Postal/ZIP code

### **Profile Information**
- `profile_picture` (VARCHAR 255) - Path to user's profile picture
- `date_of_birth` (DATE) - Birth date (optional)
- `gender` (ENUM: 'male', 'female', 'other', 'prefer_not_to_say') - Gender (optional)

### **Emergency Contacts**
- `emergency_contact_name` (VARCHAR 255) - Emergency contact person name
- `emergency_contact_phone` (VARCHAR 20) - Emergency contact phone number

### **System Fields**
- `status` (ENUM: 'active', 'inactive', 'suspended') - User account status (default: 'active')
- `updated_at` (TIMESTAMP) - Auto-updated timestamp for tracking changes
- `last_login` (DATETIME) - Last login timestamp

## 📊 Indexes Added
- `idx_status` - For filtering by user status
- `idx_city` - For location-based queries
- `idx_province` - For province-based queries

## 🚀 How to Apply

### Option 1: Using the Utility Script (Recommended)
1. Navigate to: `http://mwa/hardware/utils/add_user_profile_fields.php`
2. The script will automatically:
   - Check if columns already exist (prevents duplicates)
   - Execute all SQL statements
   - Show verification results
   - Display all current columns

### Option 2: Manual SQL Execution
1. Open `docs/add_user_profile_fields.sql`
2. Execute each statement in your MySQL/MariaDB client
3. Note: You may need to handle "Duplicate column" errors manually

## ✅ Benefits

1. **Better Delivery Management**
   - Store customer addresses in user profiles
   - Quick access to delivery locations
   - Support for address breakdown (city, province, zipcode)

2. **Enhanced User Profiles**
   - Profile pictures for better identification
   - Complete user information
   - Emergency contacts for drivers

3. **Account Management**
   - Status field for account control (active/inactive/suspended)
   - Last login tracking for security
   - Updated timestamp for audit trails

4. **Improved Queries**
   - Indexes for faster location-based searches
   - Better filtering capabilities

## 📝 Usage Examples

### Update User Address
```sql
UPDATE users 
SET address = '123 Main Street', 
    city = 'Manila', 
    province = 'Metro Manila', 
    zipcode = '1000'
WHERE id = 1;
```

### Get Active Users in a City
```sql
SELECT * FROM users 
WHERE status = 'active' 
AND city = 'Manila'
ORDER BY name;
```

### Update User Status
```sql
UPDATE users 
SET status = 'suspended' 
WHERE id = 5;
```

## 🔄 Backward Compatibility
- All new fields are **nullable** (except `status` which defaults to 'active')
- Existing code will continue to work
- No breaking changes to existing functionality

## 📌 Notes
- The `address` field can now be used in driver dashboards and customer profiles
- The `status` field allows admins to suspend inactive accounts
- `last_login` should be updated on each successful login
- `updated_at` is automatically maintained by MySQL

