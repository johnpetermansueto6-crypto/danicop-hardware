# Google Maps API Setup Guide

## Step 1: Get Your API Key

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the following APIs:
   - **Maps JavaScript API**
   - **Geocoding API**
   - **Places API**
4. Go to "Credentials" → "Create Credentials" → "API Key"
5. Copy your API key

## Step 2: Configure the API Key

1. Open `includes/config.php`
2. Find the line:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY_HERE');
   ```
3. Replace `YOUR_GOOGLE_MAPS_API_KEY_HERE` with your actual API key:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'AIzaSy...your-actual-key-here');
   ```

## Step 3: Set API Restrictions (Recommended)

For security, restrict your API key:

1. Go to Google Cloud Console → Credentials
2. Click on your API key
3. Under "API restrictions", select "Restrict key"
4. Choose:
   - Maps JavaScript API
   - Geocoding API
   - Places API
5. Under "Website restrictions", add your domain (e.g., `localhost` for development)

## Features Enabled

Once configured, the following features will work:

### Admin Panel
- **Store Locations Management**: Click on map to set store location coordinates
- **Address Autocomplete**: Type address and get suggestions
- **Geocoding**: Automatically convert addresses to coordinates

### Customer Checkout
- **Delivery Address Selection**: Interactive map to select delivery location
- **Address Autocomplete**: Type address and get suggestions
- **Click to Set Location**: Click on map to set exact delivery coordinates
- **Reverse Geocoding**: Get address from map coordinates

### Index Page
- **Store Locations Display**: Show all store locations on map
- **Get Directions**: Link to Google Maps for directions

## Database Updates Required

Run the following SQL files in phpMyAdmin:

1. `docs/add_locations_table.sql` - Creates store_locations table
2. `docs/add_delivery_coordinates.sql` - Adds latitude/longitude to orders table

## Testing

1. Go to Admin Panel → Locations
2. Click "Add Location"
3. Type an address or click on the map
4. Verify coordinates are set correctly
5. Save the location

For customer checkout:
1. Add items to cart
2. Go to checkout
3. Select "Home Delivery"
4. Use the map to set delivery location
5. Verify address and coordinates are saved

