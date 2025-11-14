# Condoleance Register Shortcodes

## Available Shortcodes

### 1. Condoleance Register (List/Grid)

Display a list or grid of condoleance memorials with pagination.

**Shortcode:**
```
[condoleance_register]
```

**Attributes:**
- `per_page` - Number of memorials per page (default: 10)
- `show_pagination` - Show/hide pagination (default: yes)
- `columns` - Number of columns in grid layout (default: 3, options: 2, 3, or 4)

**Examples:**

Basic usage:
```
[condoleance_register]
```

Show 6 memorials per page with 2 columns:
```
[condoleance_register per_page="6" columns="2"]
```

Show 12 memorials without pagination:
```
[condoleance_register per_page="12" show_pagination="no"]
```

4-column grid:
```
[condoleance_register columns="4"]
```

---

### 2. Light a Candle Widget

Display a widget to light virtual candles for a specific memorial.

**Shortcode:**
```
[light_a_candle]
```

**Attributes:**
- `post_id` - The condoleance post ID (default: current post)
- `show_count` - Show/hide candle count (default: yes)
- `show_names` - Show/hide list of recent candle lighters (default: no)

**Examples:**

Basic usage (on single condoleance page):
```
[light_a_candle]
```

For a specific memorial by ID:
```
[light_a_candle post_id="123"]
```

Show count but hide names:
```
[light_a_candle show_count="yes" show_names="no"]
```

Show both count and recent names:
```
[light_a_candle show_count="yes" show_names="yes"]
```

Hide count, only show button:
```
[light_a_candle show_count="no"]
```

---

## Template Files

The plugin includes custom templates for:

1. **Single Condoleance:** `templates/single-condoleance.php`
   - Displays individual memorial page
   - Shows birth/death dates, photos, gallery, candle widget, and comments

2. **Archive Condoleance:** `templates/archive-condoleance.php`
   - Displays list of all memorials
   - Grid layout with pagination
   - Shows summary cards with candle counts and comment counts

These templates are automatically used for condoleance post type pages and can be overridden by your theme if needed.

---

## Usage Examples

### In a Page

Create a memorial page with a grid of condoleances:
```
<h1>In Loving Memory</h1>
<p>Honor those we've lost by lighting a virtual candle.</p>

[condoleance_register per_page="9" columns="3"]
```

### In a Sidebar Widget

Add a candle widget for a featured memorial:
```
[light_a_candle post_id="456" show_names="yes"]
```

### Multiple Shortcodes

Combine shortcodes on a single page:
```
<div class="memorial-section">
    <h2>Featured Memorial</h2>
    [light_a_candle post_id="123" show_names="yes"]
</div>

<div class="all-memorials">
    <h2>All Memorials</h2>
    [condoleance_register per_page="6" columns="2"]
</div>
```

---

## Styling

All shortcodes use the plugin's CSS (`assets/css/frontend.css`) which includes:
- Responsive grid layouts
- Beautiful card designs
- Hover effects and transitions
- Mobile-friendly styling

You can override styles in your theme's CSS by targeting the following classes:
- `.condoleance-register-list`
- `.condoleance-grid`
- `.condoleance-card`
- `.condoleance-candle-widget`
- `.condoleance-pagination`
