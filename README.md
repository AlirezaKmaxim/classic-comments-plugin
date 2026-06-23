# Custom Comment System

A modern, AJAX-powered custom comment system plugin for WordPress. Features a beautifully styled comment form and nested admin reply layouts — designed with Persian (Farsi) language support.

## Features

- **AJAX form submission** — comments are submitted without page reload using the Fetch API
- **Custom comment form** — clean two-column layout with name, email, and message fields
- **Nested admin replies** — visually distinct reply boxes connected to parent comments with an SVG connector
- **Jalali (Persian) date support** — automatically converts dates via `wp_date`, compatible with WP-Jalali / Parsi Date plugins
- **Persian digit conversion** — English digits are automatically converted to Persian (۰-۹)
- **Responsive design** — adapts gracefully to mobile, tablet, and desktop
- **Security** — nonce verification, input sanitization, and email validation
- **Shortcode-driven** — use `[custom_comments]` anywhere to render the system

## Screenshots

*(Add screenshots here)*

## Requirements

- WordPress 5.0+
- PHP 7.0+
- Persian (Jalali) calendar support — optional, for automatic date conversion (e.g. WP-Jalali or Parsi Date plugin)

## Installation

1. Download the plugin and upload the `custom-comments-system` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add the `[custom_comments]` shortcode to any post, page, or widget where you want the comment system to appear.

## Usage

### Shortcode

```
[custom_comments]
```

Place this shortcode in any post or page content. It renders:

- The comment submission form
- A title ("نظرات کاربران")
- The list of existing comments with admin replies

### Template tag (optional)

If you prefer to embed the system in a PHP template:

```php
echo do_shortcode( '[custom_comments]' );
```

## How it works

1. The plugin registers a singleton class (`Custom_Comment_System`) on `plugins_loaded`.
2. Assets (CSS, JS, Vazirmatn font) are registered via `wp_enqueue_scripts` but only enqueued when the shortcode is rendered.
3. The comment form is submitted via AJAX (`admin-ajax.php`) with nonce security.
4. Submitted comments are inserted with `wp_insert_comment` and respect WordPress's comment moderation setting.
5. The display class fetches approved parent comments and their admin replies, rendering them with distinct orange (customer) and cream (admin) boxes.

## File Structure

```
custom-comments-system/
├── assets/
│   ├── css/
│   │   └── style.css          # Plugin styles (form, comments, responsive)
│   └── js/
│       └── script.js          # AJAX submission and DOM handling
├── includes/
│   ├── class-form.php         # Form rendering & AJAX submission handler
│   └── class-display.php      # Comments list rendering & helpers
├── custom-comments-system.php # Main plugin file, constants, shortcode
└── README.md
```

## Customization

### Styling

All styles use CSS custom properties scoped under `#custom-comments-system-container`. Override them in your theme:

```css
#custom-comments-system-container {
    --color-primary: #E7A439;
    --color-primary-light: #FFF6E8;
    --color-text-light: #fdf6eb;
    --color-text-dark: #66614d;
}
```

### Translation

The plugin uses Persian (Farsi) text throughout. To translate, use the `custom-comments-system` text domain with `.po` / `.mo` files in a standard WordPress language directory.

## Changelog

### 1.0.3
- Initial release.

## Author

- **AlirezaKMaxim** — [GitHub](https://github.com/AlirezaKmaxim)

## License

GPL v2 or later
