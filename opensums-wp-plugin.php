<?php
/**
 * Dutyman plugin for WordPress.
 *
 * @wordpress-plugin
 * Plugin Name:       OpenSums WP Plugin 
 * Plugin URI:        https://github.com/opensums/opensums-wp-plugin
 * Description:       Template for plugins for WordPress (TM) created by OpenSums.
 * Version:           1.0.0-dev
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            OpenSums
 * Author URI:        https://opensums.com/
 * Text Domain:       opensums-wp-plugin
 * License:           MIT
 * License URI:       https://github.com/opensums/opensums-wp-plugin/LICENSE
 */

namespace OpenSumsWpPlugin;

defined('WPINC') || die;

require_once(__DIR__.'/vendor/autoload.php');

$config = Config::instance('opensums-wp-plugin', '1.0.0-dev');

/**
 * Register a callback to be called when a user selects 'activate' on the
 * Plugins admin page.
 */
\register_activation_hook(__FILE__, [Install::class, 'activate']);

/**
 * Register a callback to be called when a user selects 'deactivate' on the
 * Plugins admin page.
 */
\register_deactivation_hook(__FILE__, [Install::class, 'deactivate']);

/**
 * Register a callback to be called when a user selects 'uninstall' on the
 * Plugins admin page. This hook must be serializable (so cannot be a closure).
 */
\register_uninstall_hook(__FILE__, [Install::class, 'uninstall']);
