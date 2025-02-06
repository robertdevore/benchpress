# WPCom Check

WPCom Check is a WordPress® utility designed to ensure compatibility with WordPress.com-hosted environments. 

It provides automated plugin deactivation and user notifications if a plugin is not supported on WordPress.com. 

This tool is ideal for plugin developers who want to ensure their plugins gracefully handle unsupported hosting environments.

## Features

- Automatically detects if the site is hosted on WordPress.com.
- Deactivates the plugin if unsupported.
- Displays an admin notice with information about the deactivation.
- Prevents activation on unsupported environments.
- Allows developers to provide a custom learn-more link for user education.

## Installation

### Using Composer

Add the package to your project:

```
composer require robertdevore/wpcom-check
```

Include Composer's autoload file in your plugin or theme:

```
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}
```

Instantiate the `WPComPluginHandler` class in your plugin's main file:

```
use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://domain.com/learn-more/' );
```

### Manual Installation

Clone or download the repository from GitHub:

```
git clone https://github.com/robertdevore/wpcom-check.git
```

Include the `WPComPluginHandler.php` file in your project:

```
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}
```

Instantiate the class in your plugin's main file:

```
use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://domain.com/learn-more/' );
```

## Usage

### Parameters

- `**$pluginSlug**`: (string) The plugin slug, typically obtained using `plugin_basename(__FILE__)`.
- `**$learnMoreLink**`: (string) A URL pointing to more information about the deactivation reason or alternative solutions.

### Example

Here is how to use WPCom Check in your plugin:
```
<?php
/**
 * Plugin Name: My Awesome Plugin
 */

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://domain.com/learn-more/' );
```

## How It Works

1. **Detection**: The `WPComPluginHandler` checks if the site is hosted on WordPress.com by inspecting the `IS_WPCOM`constant.

2. **Deactivation**: If the plugin is running in an unsupported environment, it is deactivated automatically.

3. **Admin Notice**: An admin notice is displayed, providing users with a link to learn more about the issue.

4. **Activation Prevention**: The plugin prevents itself from being activated on unsupported environments, displaying a detailed error message.

## Developer Notes

- Ensure you use the correct plugin slug when instantiating the class.
- Customize the learn-more link to provide users with appropriate guidance.
- Use Composer for a streamlined installation and updates.

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your improvements.

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For questions or issues, please create a GitHub issue at [github.com/robertdevore/wpcom-check](https://github.com/robertdevore/wpcom-check/issues).