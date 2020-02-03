<?php

namespace SejoliDonation\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       Ridwan Arifandi
 * @since      1.0.0
 *
 * @package    Sejoli_Donation
 * @subpackage Sejoli_Donation/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_Donation
 * @subpackage Sejoli_Donation/admin
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class Order {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Delete transient donation progress data
     * Hooked via action sejoli/order/set-status/completed, priority 100
     * Hooked via action sejoli/order/set-status/refunded, priority 100
     * Hooked via action sejoli/order/set-status/cancelled, priority 100
     * @since   1.0.0
     * @param   array  $order_data
     * @return  void
     */
    public function refresh_donation_cache(array $order_data) {
        $product_id = intval( $order_data['product_id'] );
        $key        = 'total_donation_product-' . $product_id;

        delete_transient( $key );
    }

}
