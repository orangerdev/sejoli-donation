<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              Ridwan Arifandi
 * @since             1.0.2
 * @package           Sejoli_Donation
 *
 * @wordpress-plugin
 * Plugin Name:       Sejoli - Donasi
 * Plugin URI:        https://sejoli.co.id
 * Description:       Implements donation/crowdfunctiong into SEJOLI premium membership WordPress plugin
 * Version:           1.2.2
 * Author:            Ridwan Arifandi
 * Author URI:        https://ridwan-arifandi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sejoli-donation
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.2 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SEJOLI_DONATION_VERSION', '1.2.2' );
define( 'SEJOLI_DONATION_DIR',	 	plugin_dir_path(__FILE__));
define( 'SEJOLI_DONATION_URL',	 	plugin_dir_url(__FILE__));

require SEJOLI_DONATION_DIR . '/third-parties/autoload.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sejoli-donation-activator.php
 */
function activate_sejoli_donation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-donation-activator.php';
	Sejoli_Donation_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sejoli-donation-deactivator.php
 */
function deactivate_sejoli_donation() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-donation-deactivator.php';
	Sejoli_Donation_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sejoli_donation' );
register_deactivation_hook( __FILE__, 'deactivate_sejoli_donation' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sejoli-donation.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sejoli_donation() {

	$plugin = new Sejoli_Donation();
	$plugin->run();

}

/**
 * Plugin update checker
 */

require_once(SEJOLI_DONATION_DIR . 'third-parties/yahnis-elsts/plugin-update-checker/plugin-update-checker.php');

$update_checker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/orangerdev/sejoli-donation',
	__FILE__,
	'sejoli-donation'
);

$update_checker->setBranch('master');

run_sejoli_donation();
