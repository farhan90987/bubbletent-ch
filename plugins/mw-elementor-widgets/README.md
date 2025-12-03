# MW Helper Plugin For Book A Bubble

A comprehensive WordPress plugin that extends Elementor with custom widgets, Google Tag Manager/GA4 integration, and advanced booking management features for the Book A Bubble platform.

**Version:** 1.0.3  
**Author:** Mathes IT-Consulting  
**License:** GPL v2 or later  
**Website:** https://mathesconsulting.de

---

## 📋 Table of Contents

- [Requirements](#requirements)
- [Features](#features)
- [Installation](#installation)
- [Elementor Widgets](#elementor-widgets)
- [Shortcodes](#shortcodes)
- [Admin Features](#admin-features)
- [Code Structure](#code-structure)
- [API Reference](#api-reference)

---

## Requirements

- **WordPress:** 6.2 or higher
- **PHP:** 7.2 or higher
- **Required Plugins:**
  - Elementor
  - Elementor Pro
  - Listeo Core
  - Smoobu Sync WP

---

## Features

### 🎨 Elementor Widgets
- **Hero Background Slider** - Full-width hero section with background image carousel
- **MW Country Map** - Interactive map widget with region/country selection and carousel
- **Listing Grid** - Dynamic grid display of listings with filtering and tag-based loading
- **MW Loop Carousel** - Carousel widget for displaying loop template items

### 🏷️ Shortcodes
- `[mwew_search_form]` - Search form with date picker and country selection
- Listing archive template customization

### 📊 Google Tag Manager & GA4 Integration
- GTM script injection (head and body)
- Event tracking for bookings, listings, vouchers, and sessions
- Customizable tracking settings via admin panel
- Event data collection and transmission

### 📅 Booking Management
- Order metadata handling and storage
- Calendar availability checking
- Coupon/discount management
- Tax exemption handling
- Price updates and calculations
- Product checkout integration

### 🗺️ Map Builder
- Admin interface for creating and managing maps
- Region-based map configuration
- Listing information mapping
- Interactive map rendering

### 🔧 Additional Features
- Carousel linking and synchronization
- Listing repository with filtering
- Multi-language support (DE, EN, FR)
- Date picker with Easepick library
- Listing filter functionality
- Logger utility for debugging

---

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin panel
3. Configure GTM/GA4 settings under **Settings > GTM + GA4**
4. Access Map Builder under **MW Map Builder** in the admin menu

---

## Elementor Widgets

### 1. Hero Background Slider
**File:** `inc/elementor/widgets/hero-slider/mw-hero-slider.php`

Displays a full-width hero section with background image carousel and text overlays.

**Features:**
- Multiple background images with carousel
- 7 customizable text lines
- Check-in/Check-out date labels
- Modal integration
- Responsive design

**CSS:** `inc/elementor/widgets/hero-slider/css/mw-slider.css`  
**JS:** `inc/elementor/widgets/hero-slider/js/widgets.js`, `modal.js`

---

### 2. MW Country Map
**File:** `inc/elementor/widgets/area-map/country-map.php`

Interactive map widget with region tabs and carousel listings.

**Features:**
- Region-based tabs
- Map data visualization
- Carousel with listings
- Autoplay controls
- Navigation and pagination

**CSS:** `inc/elementor/widgets/area-map/css/styles.css`  
**JS:** `inc/elementor/widgets/area-map/js/main.js`

---

### 3. Listing Grid
**File:** `inc/elementor/widgets/listing-grid/listing-grid-widget.php`

Dynamic grid display of listings with tag-based filtering.

**Features:**
- Tag-based filtering
- AJAX loading
- Responsive grid layout
- Customizable columns
- Loading states

**CSS:** `inc/elementor/widgets/listing-grid/css/listing-grid.css`  
**JS:** `inc/elementor/widgets/listing-grid/js/listing-grid.js`  
**Action Handler:** `inc/elementor/widgets/listing-grid/listing-grid-action.php`

---

### 4. MW Loop Carousel
**File:** `inc/elementor/widgets/loop-carousel/template-loop-carousel.php`

Carousel widget for displaying loop template items.

**Features:**
- Template-based carousel
- Owl Carousel integration
- Autoplay and navigation controls
- Responsive breakpoints

**CSS:** `inc/elementor/widgets/loop-carousel/template-loop-carousel.css`

---

## Shortcodes

### Search Form Shortcode
**File:** `inc/shortcodes/mw-search-form-shortcode.php`

```
[mwew_search_form]
```

**Features:**
- Check-in/Check-out date picker
- Country selection dropdown
- Radius search slider
- Form submission handling

**Related Files:**
- `inc/shortcodes/mw-search-action.php` - AJAX handler for search
- `inc/shortcodes/listeo-core-listing.php` - Listing query and filtering

---

## Admin Features

### GTM + GA4 Settings
**File:** `inc/admin/gtm-ga4-settings.php`

Admin settings page for configuring Google Tag Manager and GA4 tracking.

**Settings:**
- GTM Container ID
- GA4 Measurement ID
- Event tracking enable/disable
- Custom event configuration

---

### Map Builder
**Files:**
- `inc/admin/pages/map-builder.php` - Main map builder interface
- `inc/admin/pages/new-map-builder.php` - New map creation page
- `inc/admin/templates/map-builder-template.php` - Map template rendering
- `inc/admin/templates/map-builder-list.php` - Map list display
- `inc/admin/map-builder-actions.php` - AJAX actions for map operations
- `inc/admin/map-image-field.php` - Image field handler

**Features:**
- Create and edit maps
- Region configuration
- Listing mapping
- Map data storage and retrieval

---

## Code Structure

```
mw-elementor-widgets/
├── index.php                          # Plugin entry point
├── autoloader.php                     # PSR-4 autoloader
├── inc/
│   ├── mwew-init.php                 # Main plugin initialization
│   ├── admin/                        # Admin functionality
│   │   ├── admin-init.php
│   │   ├── gtm-ga4-settings.php
│   │   ├── map-builder-actions.php
│   │   ├── map-image-field.php
│   │   ├── pages/
│   │   │   ├── map-builder.php
│   │   │   └── new-map-builder.php
│   │   └── templates/
│   │       ├── map-builder-list.php
│   │       └── map-builder-template.php
│   ├── database/
│   │   └── listing-maps-db.php       # Database operations for maps
│   ├── elementor/
│   │   ├── elementor-init.php        # Widget registration
│   │   └── widgets/
│   │       ├── hero-slider/
│   │       ├── area-map/
│   │       ├── listing-grid/
│   │       └── loop-carousel/
│   ├── google-tags/                  # GTM & GA4 integration
│   │   ├── google-tags-init.php
│   │   ├── gtm-script.php
│   │   ├── booking-tracker.php
│   │   ├── listing-tracker.php
│   │   ├── voucher-tracker.php
│   │   ├── session-tracker.php
│   │   ├── event-tracking.php
│   │   └── utilities.php
│   ├── helper/
│   │   └── carousel-linker.php       # Carousel synchronization
│   ├── logger/
│   │   └── logger.php                # Logging utility
│   ├── orders/                       # Order/Booking management
│   │   ├── order-meta-init.php
│   │   ├── order-meta-save.php
│   │   ├── order-meta.php
│   │   ├── order-coupon.php
│   │   ├── order-price-update.php
│   │   ├── product-checkout.php
│   │   └── tax-exempt.php
│   ├── services/                     # Business logic services
│   │   ├── calendar-availability.php
│   │   ├── listing-repo.php
│   │   ├── map-repo.php
│   │   └── order-repo.php
│   └── shortcodes/
│       ├── shortcodes-init.php
│       ├── mw-search-form-shortcode.php
│       ├── mw-search-action.php
│       └── listeo-core-listing.php
├── assets/
│   ├── css/                          # Stylesheets
│   ├── js/                           # JavaScript files
│   └── images/                       # Image assets
├── templates/
│   ├── archive-listing.php           # Listing archive template
│   └── archive-listing-split.php     # Split layout template
└── languages/                        # Translation files
    ├── mwew-de.po
    ├── mwew-en.po
    ├── mwew-fr.po
    └── mwew.pot
```

---

## API Reference

### Core Classes

#### `MWEW\Inc\Mwew_Init`
**File:** `inc/mwew-init.php`

Main plugin initialization class.

**Methods:**
- `__construct()` - Initialize all plugin components
- `load_styles()` - Enqueue frontend styles
- `load_scripts()` - Enqueue frontend scripts
- `load_admin_script()` - Enqueue admin scripts
- `load_admin_style()` - Enqueue admin styles
- `load_textdomain()` - Load translation files
- `activate()` - Plugin activation hook
- `deactivate()` - Plugin deactivation hook
- `uninstall()` - Plugin uninstall hook

---

### Services

#### `MWEW\Inc\Services\Calendar_Availability`
**File:** `inc/services/calendar-availability.php`

Manages listing availability checking.

**Methods:**
- `is_available($listing_id, $check_in, $check_out)` - Check if listing is available
- `get_busy_dates_by_order_id($order_id)` - Get booked dates for an order

#### `MWEW\Inc\Services\Listing_Repo`
**File:** `inc/services/listing-repo.php`

Listing data repository.

**Methods:**
- `get_countries_by_region()` - Get countries grouped by region
- `get_listings_by_tag($tag_id)` - Get listings by tag

#### `MWEW\Inc\Services\Map_Repo`
**File:** `inc/services/map-repo.php`

Map data repository.

**Methods:**
- `get_region_by_id($region_id)` - Get region information
- `get_listing_info_by_id($map_id)` - Get listing info for map

#### `MWEW\Inc\Services\Order_Repo`
**File:** `inc/services/order-repo.php`

Order/Booking data repository.

---

### Database

#### `MWEW\Inc\Database\Listing_Maps_DB`
**File:** `inc/database/listing-maps-db.php`

Database operations for maps.

**Methods:**
- `maybe_upgrade()` - Create/upgrade database tables
- `get_by_id($map_id)` - Get map by ID
- `drop_table()` - Drop database tables on uninstall

---

### Google Tags

#### `MWEW\Inc\Google_Tags\Google_Tags_Init`
**File:** `inc/google-tags/google-tags-init.php`

Initializes all tracking components.

#### `MWEW\Inc\Google_Tags\GTM_Script`
**File:** `inc/google-tags/gtm-script.php`

Injects GTM scripts into page head and body.

#### `MWEW\Inc\Google_Tags\Booking_Tracker`
**File:** `inc/google-tags/booking-tracker.php`

Tracks booking-related events.

#### `MWEW\Inc\Google_Tags\Listing_Tracker`
**File:** `inc/google-tags/listing-tracker.php`

Tracks listing view events.

#### `MWEW\Inc\Google_Tags\Voucher_Tracker`
**File:** `inc/google-tags/voucher-tracker.php`

Tracks voucher usage events.

#### `MWEW\Inc\Google_Tags\Session_Tracker`
**File:** `inc/google-tags/session-tracker.php`

Tracks user session data.

---

### Orders

#### `MWEW\Inc\Orders\Order_Meta_Init`
**File:** `inc/orders/order-meta-init.php`

Initializes order metadata handling.

**Related Files:**
- `order-meta-save.php` - Save order metadata
- `order-meta.php` - Order metadata operations
- `order-coupon.php` - Coupon handling
- `order-price-update.php` - Price calculations
- `product-checkout.php` - Checkout integration
- `tax-exempt.php` - Tax exemption logic

---

## Assets

### JavaScript Files
- `assets/js/owl.carousel.min.js` - Carousel library
- `assets/js/easepick-picker.js` - Date picker initialization
- `assets/js/date-keeper.js` - Date tracking for events
- `assets/js/listing-filter.js` - Listing filter functionality
- `assets/js/script.js` - Main plugin script
- `assets/js/map-builder.js` - Map builder interface
- `assets/js/map-builder-action.js` - Map builder actions

### CSS Files
- `assets/css/admin-style.css` - Admin panel styles

---

## Hooks & Filters

### Actions
- `wp_enqueue_scripts` - Load frontend styles and scripts
- `admin_enqueue_scripts` - Load admin styles and scripts
- `plugins_loaded` - Load text domain
- `wp_head` - Inject GTM script
- `wp_body_open` - Inject GTM noscript
- `wp_footer` - Inject tracking events
- `admin_menu` - Register admin pages

### Filters
- `template_include` - Override listing archive template
- `wpml_elementor_widgets_to_translate` - Register translatable widget strings
- `query_vars` - Add custom query variables

---

## Constants

- `MWEW_VERSION` - Plugin version (1.0.0)
- `MWEW_DB_VERSION` - Database version (1.0.0)
- `MWEW_DIR_PATH` - Plugin directory path
- `MWEW_PATH_URL` - Plugin URL

---

## Multi-Language Support

Supported languages:
- English (en)
- German (de)
- French (fr)

Translation files located in `/languages/` directory.

---

## Support & Documentation

- **Author:** Mathes IT-Consulting
- **Website:** https://mathesconsulting.de
- **Author URI:** https://mathesconsulting.de

---

## License

This plugin is licensed under the GPL v2 or later. See LICENSE file for details.

---

**Last Updated:** 2025-10-23

