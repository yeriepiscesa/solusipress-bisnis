<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://solusipress.com/
 * @since             1.0.0
 * @package           Solusipress_Bisnis
 *
 * @wordpress-plugin
 * Plugin Name:       SolusiPress Bisnis
 * Plugin URI:        https://solusipress.com/bisnis
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           0.9.2
 * Author:            Yerie Piscesa
 * Author URI:        https://solusipress.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       solusipress-bisnis
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( defined( 'STDIN' ) ) {
    if ( !( defined( 'WP_CLI' ) && WP_CLI ) ) {
        require_once '../../../wp-load.php';
    }
} else {
    if ( ! defined( 'WPINC' ) ) {
	    die;
    }
    
	if ( !session_id() ) @session_start();
}

require __DIR__ . '/vendor/autoload.php';

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SOLUSIPRESS_BISNIS_VERSION', '0.9.2' );

/*
site key : 	6LffPNEUAAAAAM1lgDfEB0eyOx58eT46ZU_08Kg8
secret : 6LffPNEUAAAAAG-lQ7GtxrG2u58DpBvxnfUfcBxN	
*/

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-solusipress-bisnis-activator.php
 */
function activate_solusipress_bisnis( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-solusipress-bisnis-activator.php';
	Solusipress_Bisnis_Activator::activate( $network_wide );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-solusipress-bisnis-deactivator.php
 */
function deactivate_solusipress_bisnis() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-solusipress-bisnis-deactivator.php';
	Solusipress_Bisnis_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_solusipress_bisnis' );
register_deactivation_hook( __FILE__, 'deactivate_solusipress_bisnis' );

function solusipress_bisnis_on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    if ( is_plugin_active_for_network( 'solusipress-bisnis/solusipress-bisnis.php' ) ) {
        switch_to_blog( $blog_id );
        Solusipress_Bisnis_Activator::do_activation();
        restore_current_blog();
    }
}
add_action( 'wpmu_new_blog', 'solusipress_bisnis_on_create_blog', 10, 6 );

function solusipress_bisnis_on_delete_blog( $tables ) {    
    global $wpdb;
    $tables[] = $wpdb->prefix . 'spb_accounts';
    $tables[] = $wpdb->prefix . 'spb_cash_flows';
    $tables[] = $wpdb->prefix . 'spb_contact_messages';
    $tables[] = $wpdb->prefix . 'spb_contact_types';
    $tables[] = $wpdb->prefix . 'spb_contacts';
    $tables[] = $wpdb->prefix . 'spb_transfers';
    $tables[] = $wpdb->prefix . 'spb_debts';
    $tables[] = $wpdb->prefix . 'spb_debt_payments';
    return $tables;    
}
add_filter( 'wpmu_drop_tables', 'on_delete_blog' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-solusipress-bisnis.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_solusipress_bisnis() {

	$plugin = new Solusipress_Bisnis();
	$plugin->run();

}
run_solusipress_bisnis();

if ( defined( 'STDIN' ) ) {
	if( isset( $argv[1] ) && $argv[1] == 'test-process' ) {
		// do some test here.		
	}
};
