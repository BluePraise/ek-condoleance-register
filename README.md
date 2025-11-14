# Condoleance Register

Modern WordPress plugin for managing memorial pages (condoleances) with virtual candles, photo galleries, and condolence messages.

## Version 2.0.0

Complete rewrite of the Tahlil plugin with modern WordPress standards, PHP 8.0+ features, and enhanced security.

## Features

### Core Functionality
- **Custom Post Type**: Dedicated condoleance/memorial pages with custom fields
- **Virtual Candles**: AJAX-powered candle lighting with real-time updates
- **Photo Galleries**: Multiple photos per memorial using CMB2
- **Condolence Comments**: Custom comment system for condolence messages
- **Data Migration**: Seamless migration from old Tahlil plugin

### Frontend Display
- **Custom Templates**: Beautiful single and archive templates
- **Shortcodes**: Two powerful shortcodes for displaying memorials and candle widgets
  - `[condoleance_register]` - Grid/list display with pagination
  - `[light_a_candle]` - Interactive candle widget
- **Responsive Design**: Mobile-first, fully responsive layouts
- **Pagination**: Built-in pagination for archives and shortcodes

### Technical
- **REST API**: Modern REST API endpoints for AJAX interactions
- **Security First**: Proper nonce verification, input sanitization, and output escaping
- **PHP 8.0+**: Modern PHP with strict typing and best practices
- **Block Editor Support**: Full Gutenberg compatibility
- **GDPR Compliant**: IP anonymization and privacy-focused

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- CMB2 plugin (included)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. If migrating from Tahlil, follow the migration prompts

## Usage

### Creating a Memorial

1. Go to **Condoleance Register** → **Add New** in WordPress admin
2. Enter the person's name as the title
3. Fill in birth and death dates
4. Add memorial text in the content editor
5. Set a featured image (memorial photo)
6. Add additional photos to the gallery
7. Publish

### Using Shortcodes

See [SHORTCODES.md](SHORTCODES.md) for complete documentation.

**Display a grid of memorials:**
```
[condoleance_register per_page="9" columns="3"]
```

**Add a candle widget:**
```
[light_a_candle post_id="123" show_names="yes"]
```

### Templates

The plugin includes custom templates that automatically apply to:
- Single condoleance pages: `templates/single-condoleance.php`
- Condoleance archives: `templates/archive-condoleance.php`

To override these templates in your theme, copy them to:
- `your-theme/condoleance-register/single-condoleance.php`
- `your-theme/condoleance-register/archive-condoleance.php`

## Migration from Tahlil

The plugin automatically detects old Tahlil data and offers migration:

1. Activate the new plugin
2. Look for the migration notice in the admin
3. Click "Migrate Now" to transfer all data
4. Old data is preserved as backup

## Changelog

### 2.0.0 (2025-11-12)
- Complete rewrite with modern WordPress standards
- PHP 8.0+ with strict typing and namespacing
- Enhanced security (nonces, sanitization, escaping)
- REST API and AJAX integration for virtual candles
- Data migration from Tahlil plugin
- Custom single and archive templates with beautiful design
- Two powerful shortcodes with full customization
- Responsive, mobile-first CSS with grid layouts
- Pagination support for archives and shortcodes
- Photo gallery with lightbox support
- Improved admin interface with CMB2 meta boxes
- GDPR compliance features
- Better code organization and comprehensive documentation

## Credits

- Original Plugin: Tahlil by Muhammad Uzair Usman
- Refactored for: Erik Korte Uitvaartzorg

## License

GPL v2 or later
