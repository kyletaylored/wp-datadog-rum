<?php
/**
 * Plugin Name: WP Datadog RUM
 * Plugin URI: https://github.com/kyletaylored/wp-datadog-rum
 * Description: Integrate Datadog RUM with WordPress
 * Version: 0.1
 * Author: Kyle Taylor
 * Author URI: https://kyletaylor.dev
 *
 * Original Author: Ilan Rabinovitch
 * Original Author URI: http://www.fonz.net
 */

if ( ! defined( 'WDR_PLUGIN_FILE' ) ) {
	define( 'WDR_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'WDR_PLUGIN_BASENAME' ) ) {
  define( 'WDR_PLUGIN_BASENAME', plugin_basename( WDR_PLUGIN_FILE ) );
}

require_once __DIR__ . '/src/RUM_Integration.php';
require_once __DIR__ . '/src/RUM_Admin.php';

use WP_Datadog_RUM\RUM_Integration;
use WP_Datadog_RUM\RUM_Admin;

// Initialize the classes
$rum_integration = new RUM_Integration();
$rum_admin = new RUM_Admin();

// Activation / Deactivation hooks
register_activation_hook( __FILE__, 'wp_datadog_rum_activate' );
register_deactivation_hook(__FILE__, 'wp_datadog_rum_deactivate');

function wp_datadog_rum_activate() {
    // Check for environment variables and set default options if they exist
    $default_options = [
        'datadog_rum_site' => $_ENV['DD_SITE'] ?? 'us',
        'datadog_rum_service' => $_ENV['DD_SERVICE'] ?? '', // Add a default if needed
        'datadog_rum_env' => $_ENV['DD_ENV'] ?? '',       // Add a default if needed
        'datadog_rum_client_token' => $_ENV['DD_RUM_CLIENT_TOKEN'] ?? '',
        'datadog_rum_app_id' => $_ENV['DD_RUM_APP_ID'] ?? '',
    ];

    foreach ($default_options as $option_name => $option_value) {
        if (!empty($option_value) && !get_option($option_name)) {
            update_option($option_name, $option_value);
        }
    }
}

function wp_datadog_rum_deactivate()
{
    global $wpdb;

    // Get all option names starting with 'datadog_rum_'
    $options_to_delete = $wpdb->get_col( 
        $wpdb->prepare(
            "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
            'datadog_rum_%'
        )
    );

    foreach ($options_to_delete as $option_name) {
        delete_option($option_name);
    }
}

