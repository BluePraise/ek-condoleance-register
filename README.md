# Condoleance Register

Modern WordPress plugin for managing memorial pages (condoleances) with virtual candles, photo galleries, and condolence messages.

## Version 2.0.0

Complete rewrite of the Tahlil plugin with modern WordPress standards, PHP 8.0+ features, and enhanced security.

## Features

- **Custom Post Type**: Dedicated condoleance/memorial pages
- **Virtual Candles**: Visitors can light virtual candles in memory
- **Photo Galleries**: Multiple photos per memorial
- **Condolence Comments**: Custom comment system for condolence messages
- **Data Migration**: Seamless migration from old Tahlil plugin
- **REST API**: Modern REST API endpoints for AJAX interactions
- **Block Editor Support**: Full Gutenberg compatibility
- **Security First**: Proper nonce verification, input sanitization, and output escaping
- **GDPR Compliant**: IP anonymization and privacy-focused

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher
- CMB2 plugin (included)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. If migrating from Tahlil, follow the migration prompts

## Migration from Tahlil

The plugin automatically detects old Tahlil data and offers migration:

1. Activate the new plugin
2. Look for the migration notice in the admin
3. Click "Migrate Now" to transfer all data
4. Old data is preserved as backup

## Changelog

### 2.0.0 (2025-11-12)
- Complete rewrite with modern WordPress standards
- PHP 8.0+ with strict typing
- Enhanced security (nonces, sanitization, escaping)
- REST API integration
- Data migration from Tahlil
- Improved admin interface
- GDPR compliance features
- Better code organization and documentation

## Credits

- Original Plugin: Tahlil by Muhammad Uzair Usman
- Refactored for: Erik Korte Uitvaartzorg

## License

GPL v2 or later
