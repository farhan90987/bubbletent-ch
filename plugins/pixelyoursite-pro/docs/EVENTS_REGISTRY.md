# Events Registry Documentation

## Overview

The **Events Registry** is a unified interface for managing events and their parameters across all platforms (Facebook, TikTok, Pinterest, Google Analytics, Bing, etc.) in the PixelYourSite plugin.

## Purpose

Before the Events Registry, event definitions were scattered across multiple files and classes:
- `CustomEvent::$tikTokEvents` for TikTok events
- `CustomEvent::$GAEvents` for Google Analytics events
- `renderFacebookEventTypeInput()` for Facebook events
- `renderPinterestEventTypeInput()` for Pinterest events

This made it difficult to:
- Add new platforms
- Maintain consistency
- Extend event definitions
- Use events in different parts of the plugin (EST API, admin UI, etc.)

The Events Registry solves these problems by providing a **single source of truth** for all event definitions.

## Architecture

### Main Class: `PYS_Events_Registry`

Location: `includes/class-events-registry.php`

The registry is implemented as a singleton class that stores all event definitions in a structured format.

### Data Structure

```php
[
  'platform_slug' => [
    'event_slug' => [
      'label' => 'Event Label',
      'fields' => ['field1', 'field2', ...]
    ]
  ]
]
```

### Supported Platforms

- `facebook` - Facebook/Meta Pixel
- `tiktok` - TikTok Pixel
- `pinterest` - Pinterest Tag
- `google_analytics` - Google Analytics 4 (grouped by category)
- `bing` - Bing Ads UET Tag

## Usage

### Basic Usage

#### Get Registry Instance

```php
$registry = \PixelYourSite\PYS_Events_Registry();
```

#### Get All Events for a Platform

```php
$facebook_events = $registry->get_events_for_platform('facebook');
// Returns: ['AddToCart' => ['label' => 'AddToCart', 'fields' => [...]], ...]
```

#### Get Specific Event Data

```php
$event_data = $registry->get_event_data('facebook', 'Purchase');
// Returns: ['label' => 'Purchase', 'fields' => ['content_ids', 'value', 'currency', ...]]
```

#### Get Event Fields

```php
$fields = $registry->get_event_fields('facebook', 'Purchase');
// Returns: ['content_ids', 'value', 'currency', 'num_items']
```

#### Get Event Options for Dropdown

```php
$options = $registry->get_event_options('facebook');
// Returns: ['AddToCart' => 'AddToCart', 'Purchase' => 'Purchase', ...]
```

### Advanced Usage

#### Register Custom Event

```php
$registry->register_event('facebook', 'MyCustomEvent', [
    'label' => 'My Custom Event',
    'fields' => ['custom_field1', 'custom_field2']
]);
```

#### Register Multiple Events for a Platform

```php
$registry->register_platform_events('my_platform', [
    'event1' => [
        'label' => 'Event 1',
        'fields' => ['field1', 'field2']
    ],
    'event2' => [
        'label' => 'Event 2',
        'fields' => ['field3']
    ]
]);
```

#### Get Events for EST API

```php
// For most platforms (flat array)
$events = $registry->get_events_for_est_api('facebook');
// Returns: [
//   ['value' => 'AddToCart', 'label' => 'AddToCart', 'fields' => [...]],
//   ['value' => 'Purchase', 'label' => 'Purchase', 'fields' => [...]]
// ]

// For Google Analytics (grouped array)
$events = $registry->get_events_for_est_api('google_analytics');
// Returns: [
//   ['group' => 'E-commerce', 'events' => [...]],
//   ['group' => 'Engagement', 'events' => [...]]
// ]
```

## Backward Compatibility

The Events Registry maintains full backward compatibility with the old system through compatibility functions.

### Compatibility Functions

Location: `includes/functions-events-registry-compat.php`

#### Using Old Functions (Still Work!)

```php
// Old way - still works!
\PixelYourSite\Events\renderFacebookEventTypeInput($event, 'facebook_event_type');
\PixelYourSite\Events\renderPinterestEventTypeInput($event, 'pinterest_event_type');
\PixelYourSite\Events\renderTikTokEventTypeInput($event, 'tiktok_event_type');
```

These functions now use the Events Registry under the hood.

#### Helper Functions

```php
// Get event fields
$fields = \PixelYourSite\Events\get_event_fields('facebook', 'Purchase');

// Check if event exists
$exists = \PixelYourSite\Events\event_exists('facebook', 'Purchase');

// Get event label
$label = \PixelYourSite\Events\get_event_label('facebook', 'Purchase');

// Validate event parameters
$validated = \PixelYourSite\Events\validate_event_params('facebook', 'Purchase', $params);

// Get all supported platforms
$platforms = \PixelYourSite\Events\get_supported_platforms();
```

## Adding a New Platform

### Example: Adding Snapchat Pixel

1. **Add events to the registry:**

```php
// In includes/class-events-registry.php, add a new init method:

private function init_snapchat_events() {
    $this->events['snapchat'] = array(
        'PAGE_VIEW' => array(
            'label' => 'Page View',
            'fields' => array()
        ),
        'ADD_CART' => array(
            'label' => 'Add to Cart',
            'fields' => array('item_ids', 'price', 'currency')
        ),
        'PURCHASE' => array(
            'label' => 'Purchase',
            'fields' => array('transaction_id', 'price', 'currency', 'number_items')
        ),
    );
}

// Call it in init_events():
private function init_events() {
    $this->init_facebook_events();
    $this->init_tiktok_events();
    $this->init_pinterest_events();
    $this->init_google_analytics_events();
    $this->init_bing_events();
    $this->init_snapchat_events(); // Add this line
    
    do_action( 'pys_events_registry_init', $this );
}
```

2. **Use in EST API:**

```php
// In includes/class-est-api.php, add Snapchat module:

if ( Snapchat()->enabled() ) {
    $pixel_ids = Snapchat()->getPixelIDs();
    $pixels = array();
    foreach ( $pixel_ids as $pixel_id ) {
        $pixels[] = array(
            'id' => $pixel_id,
            'name' => 'Snapchat ' . $pixel_id,
        );
    }

    // Get event types from registry
    $event_types = $registry->get_events_for_est_api( 'snapchat' );

    $modules['snapchat'] = array(
        'id' => 'snapchat',
        'name' => 'Snapchat',
        'logo' => PYS_URL . '/dist/images/snapchat-logo.svg',
        'enabled' => Snapchat()->enabled(),
        'configured' => Snapchat()->enabled() && !empty( $pixel_ids ),
        'pixels' => $pixels,
        'event_types' => $event_types,
    );
}
```

3. **Create render function (optional):**

```php
// In includes/functions-custom-event.php:

function renderSnapchatEventTypeInput( &$event, $key ) {
    $registry = PixelYourSite\PYS_Events_Registry();
    $options = $registry->get_event_options( 'snapchat' );
    
    renderSelectInput( $event, $key, $options );
}
```

## Extending Events via Hooks

You can add custom events from your own plugin or theme:

```php
add_action('pys_events_registry_init', function($registry) {
    // Add a custom event to Facebook
    $registry->register_event('facebook', 'MyCustomEvent', [
        'label' => 'My Custom Event',
        'fields' => ['custom_param1', 'custom_param2']
    ]);
    
    // Or add a completely new platform
    $registry->register_platform_events('my_custom_platform', [
        'event1' => [
            'label' => 'Event 1',
            'fields' => ['field1']
        ]
    ]);
});
```

## Benefits

✅ **Single Source of Truth** - All event definitions in one place  
✅ **Easy to Extend** - Add new platforms with minimal code  
✅ **Backward Compatible** - Old code continues to work  
✅ **Type Safe** - Consistent data structure across all platforms  
✅ **EST API Integration** - Seamless integration with Event Setup Tool  
✅ **Validation** - Built-in parameter validation  
✅ **Maintainable** - Easy to update and maintain  

## Migration Guide

### For Plugin Developers

If you were using the old event system:

**Before:**
```php
$tikTokEvents = CustomEvent::$tikTokEvents;
$gaEvents = $customEvent->GAEvents;
```

**After:**
```php
$registry = \PixelYourSite\PYS_Events_Registry();
$tikTokEvents = $registry->get_events_for_platform('tiktok');
$gaEvents = $registry->get_events_for_platform('google_analytics');
```

**Or use compatibility functions:**
```php
$tikTokEvents = \PixelYourSite\Events\get_platform_events('tiktok');
$gaEvents = \PixelYourSite\Events\get_ga_events_legacy();
```

### No Changes Required

If you're using these functions, **no changes are needed**:
- `renderFacebookEventTypeInput()`
- `renderPinterestEventTypeInput()`
- `renderTikTokEventTypeInput()`

They now use the Events Registry automatically!

## Testing

After implementing the Events Registry, test:

1. ✅ Event Setup Tool (EST) loads all modules correctly
2. ✅ Event dropdowns show correct events for each platform
3. ✅ Event fields are correctly populated
4. ✅ Custom events can be created and saved
5. ✅ Old custom events still work
6. ✅ All platforms (Facebook, TikTok, Pinterest, GA, Bing) work correctly

## Future Enhancements

Potential future improvements:

- **Event Validation Rules** - Add validation rules for each field (required, type, format)
- **Event Categories** - Group events by category (e.g., E-commerce, Engagement)
- **Event Metadata** - Add descriptions, examples, documentation links
- **Dynamic Fields** - Support conditional fields based on other field values
- **Event Templates** - Pre-configured event templates for common use cases
- **Import/Export** - Import/export event definitions
- **REST API** - Expose events via REST API for external integrations

## Support

For questions or issues related to the Events Registry:

1. Check this documentation
2. Review the code in `includes/class-events-registry.php`
3. Check compatibility functions in `includes/functions-events-registry-compat.php`
4. Contact the development team

