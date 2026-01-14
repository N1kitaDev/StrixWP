# Strix Google Reviews Admin Panel

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/plugins/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Admin panel companion for the Strix Google Reviews WordPress plugin. Provides developers with comprehensive settings and users with a modern interface for Google Business Profile integration.

## 🚀 Features

### For Developers
- **Admin Settings Panel** - Complete configuration interface
- **Google Maps API Integration** - Configurable API keys with testing
- **Debug Mode** - Advanced logging and troubleshooting
- **Cache Management** - Configurable review data caching
- **System Monitoring** - Dashboard with system status and analytics

### For End Users
- **Local Business Connection** - No external service dependencies
- **Google Places Autocomplete** - Easy business profile search
- **Review Analytics Dashboard** - Comprehensive review statistics
- **Review Management** - Filter and monitor customer feedback
- **Modern UI** - Responsive, mobile-friendly interface

## 📋 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Main Plugin**: [Strix Google Reviews](https://github.com/strixmedia/strix-google-reviews) must be installed
- **Google Maps API Key**: Required for Google Places integration

## 🛠️ Installation

### Automatic Installation (Recommended)
1. Download the plugin zip file
2. Go to WordPress Admin → **Plugins** → **Add New**
3. Click **Upload Plugin** and select the zip file
4. Click **Install Now** and then **Activate**

### Manual Installation
1. Upload the `strix-google-reviews-admin` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress

## ⚙️ Configuration

### For Developers - Initial Setup

1. Navigate to **Strix Reviews Admin** → **Settings** in WordPress admin
2. Enter your **Google Maps API Key**:
   - Go to [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
   - Create or select a project
   - Enable **Places API** and **Maps JavaScript API**
   - Create an API key
   - Restrict the key for security
3. Configure additional settings:
   - **Debug Mode**: Enable for troubleshooting
   - **Cache Time**: Set review data cache duration
   - **Max Reviews**: Limit reviews per business
   - **Enable Replies**: Allow business responses

### API Key Testing
Use the built-in **"Test API Connection"** button to verify your API key configuration.

## 📖 Usage

### For Business Owners

1. Go to **Strix Google Reviews** → **Free Widget Configurator**
2. Click **"Connect"** for Google Business Profile
3. Search for your business using the autocomplete
4. Select your profile and connect
5. View your reviews in the integrated dashboard

### For Developers

Access the admin panel via **Strix Reviews Admin** menu:
- **Settings**: Configure API keys and system preferences
- **Dashboard**: Monitor connected profiles and system status

## 🔧 Technical Details

### Architecture

```
WordPress Admin
├── Strix Reviews Admin (Main Menu)
│   ├── Settings (Developer Configuration)
│   │   ├── Google Maps API Key
│   │   ├── Debug & Cache Settings
│   │   └── System Configuration
│   └── Dashboard (System Monitoring)
│       ├── Connected Profiles
│       ├── Review Statistics
│       └── System Health
│
└── Strix Google Reviews (Client Interface)
    └── Widget Configurator → Popup Connection
```

### API Integration

The plugin integrates with Google services:

- **Google Places API**: Business search and details
- **Google Maps JavaScript API**: Interactive maps (future feature)
- **WordPress AJAX**: Seamless data communication

### Data Flow

```
User Action → WordPress AJAX → Google API → Data Processing → UI Update
```

### Security Features

- **Nonce Verification**: All AJAX requests protected
- **API Key Restriction**: Recommended for production use
- **Input Sanitization**: All user inputs validated
- **WordPress Standards**: Follows WP coding guidelines

## 🎨 Customization

### Styling
Modify CSS in `assets/css/admin-dashboard.css` for custom styling.

### JavaScript
Extend functionality in `assets/js/admin-custom.js`.

### PHP Hooks
```php
// Add custom settings
add_filter('strix_admin_settings_fields', function($fields) {
    $fields['custom_field'] = array(
        'label' => 'Custom Setting',
        'type' => 'text'
    );
    return $fields;
});
```

## 🐛 Troubleshooting

### Common Issues

**Plugin not activating:**
- Ensure main Strix Google Reviews plugin is active
- Check PHP version compatibility

**API connection failed:**
- Verify API key is correct and active
- Check API restrictions in Google Cloud Console
- Ensure required APIs are enabled

**Reviews not loading:**
- Confirm business profile is properly connected
- Check debug logs if enabled
- Verify API quotas and limits

### Debug Mode

Enable debug mode in settings to see detailed logs:
- Check WordPress debug.log
- Monitor browser console for JavaScript errors
- Review network requests in browser dev tools

## 📝 Changelog

### Version 1.0.0
- Initial release
- Admin settings panel for developers
- Google Maps API integration with testing
- Review analytics dashboard
- Business profile connection interface
- Modern responsive UI

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Strix Media

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 📞 Support

- **Documentation**: [Usage Guide](USAGE.md)
- **Issues**: [GitHub Issues](https://github.com/strixmedia/strix-google-reviews-admin/issues)
- **Email**: support@strixmedia.ru

## 🌟 Credits

Developed by [Strix Media](https://strixmedia.ru)

**Contributors:**
- Strix Media Development Team

---

**Made with ❤️ by Strix Media**