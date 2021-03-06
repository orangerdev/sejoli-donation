<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       Ridwan Arifandi
 * @since      1.0.0
 *
 * @package    Sejoli_Donation
 * @subpackage Sejoli_Donation/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sejoli_Donation
 * @subpackage Sejoli_Donation/includes
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class Sejoli_Donation {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_Donation_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SEJOLI_DONATION_VERSION' ) ) {
			$this->version = SEJOLI_DONATION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-donation';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_Donation_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_Donation_i18n. Defines internationalization functionality.
	 * - Sejoli_Donation_Admin. Defines all hooks for the admin area.
	 * - Sejoli_Donation_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-donation-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-donation-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/api.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/order.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/product.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/checkout.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/endpoint.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/public.php';


		/**
		 * Functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/order.php';

		$this->loader = new Sejoli_Donation_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_Donation_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_Donation_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new SejoliDonation\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );

		$api = new SejoliDonation\Admin\API( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli-api-response/user/login',		$api, 'user_login', 	   1);
		$this->loader->add_filter( 'sejoli-api-response/donation/list',		$api, 'get_donation_list', 1, 2);
		$this->loader->add_filter( 'sejoli-api-response/donation/progress',	$api, 'get_donation_progress', 1, 2);

		$product = new SejoliDonation\Admin\Product( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields',		$product, 'set_product_fields', 	12);
		$this->loader->add_filter( 'sejoli/product/meta-data',	$product, 'set_product_metadatas', 	100, 2);

		$order = new SejoliDonation\Admin\Order( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/order/set-status/completed',			$order, 'refresh_donation_cache', 100);
		$this->loader->add_action( 'sejoli/order/set-status/refunded', 			$order, 'refresh_donation_cache', 100);
		$this->loader->add_action( 'sejoli/order/set-status/cancelled', 		$order, 'refresh_donation_cache', 100);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		// Later we will remove this class to a standalone plugin
		$endpoint = new SejoliDonation\Front\Endpoint( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',				$endpoint, 'set_endpoint', 		1);
		$this->loader->add_action( 'query_vars',		$endpoint, 'set_query_vars',	999);
		$this->loader->add_action( 'template_redirect',	$endpoint, 'check_parse_query', 100);

		$public = new SejoliDonation\Front( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'enqueue_scripts' );

		$checkout = new SejoliDonation\Front\Checkout( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts',				$checkout, 'register_css_files',		100);
		$this->loader->add_action( 'wp_enqueue_scripts',				$checkout, 'register_js_files',			100);
		$this->loader->add_action( 'parse_request',						$checkout, 'check_requested_variables',	1);
		$this->loader->add_filter( 'single_template',					$checkout, 'set_checkout_template', 	120);
		$this->loader->add_filter( 'sejoli/checkout/is-product-valid',	$checkout, 'validate_donation',			10, 2);
		$this->loader->add_filter( 'sejoli/product/price',				$checkout, 'set_product_price',			300, 2);
		$this->loader->add_filter( 'sejoli/order/order-detail',			$checkout, 'set_product_price_in_order',		100);
		$this->loader->add_filter( 'sejoli/order/grand-total',			$checkout, 'set_grand_total',					101, 2);
		$this->loader->add_filter( 'sejoli/order/meta-data', 			$checkout, 'add_donation_amount_to_order_meta', 200, 2);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sejoli_Donation_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
