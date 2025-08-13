<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://google.com
 * @since             1.0.0
 * @package           We_User_Importer
 *
 * @wordpress-plugin
 * Plugin Name:       User Importer
 * Plugin URI:        https://google.com
 * Description:       import user form excel or csv file ...
 * Version:           1.0.0
 * Author:            AlirezaYaghouti
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       we-user-importer
 * Domain Path:       /languages
 * Requires PHP:      8.1
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define('WE_USER_IMPORTER_VERSION', '1.0.0');
define('WE_USER_IMPORTER_SLUG', 'we-user-importer');
define('WE_USER_IMPORTER_NAME', __('User Importer', WE_USER_IMPORTER_SLUG));
define('WE_EXAMPLE_FILE_URL', plugin_dir_url(__FILE__) . 'example.csv');
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-we-user-importer-activator.php
 */
function activate_we_user_importer()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-we-user-importer-activator.php';
    We_User_Importer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-we-user-importer-deactivator.php
 */
function deactivate_we_user_importer()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-we-user-importer-deactivator.php';
    We_User_Importer_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_we_user_importer');
register_deactivation_hook(__FILE__, 'deactivate_we_user_importer');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-we-user-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_we_user_importer()
{

    $plugin = new We_User_Importer();
    $plugin->run();
}
run_we_user_importer();
