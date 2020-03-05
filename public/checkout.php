<?php
namespace SejoliDonation\Front;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    SejoliLP
 * @subpackage SejoliLP/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SejoliLP
 * @subpackage SejoliLP/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Checkout {

	/**
	 * Set if currenct checkout page is donation page
	 * @since 	1.0.0
	 * @var 	boolean
	 */
	protected $is_donation_page = false;


	/**
	 * Check requested variabels
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $request_vars = array();


	/**
	 * Set if donation is invalid, lower than min, or higher than maximu
	 * @since	1.0.0
	 * @var 	boolean|string
	 */
	protected $donation_invalid = false;

	/**
	 * Set donation amount
	 * @since	1.0.0
	 * @var 	float
	 */
	protected $amount = 0.0;

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
	 * Check requested variables from POST and set them to $this->request_vars
	 * Hooked via action parse_request, priority 1
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function check_requested_variables() {

		$post_data = wp_parse_args($_POST, array(
			'price'	=> 0
		));

		if(0 !== $post_data['price']) :
			$post_data['price']	= floatval( str_replace('.', '', $post_data['price']) );
		endif;

		$this->request_vars = $post_data;
	}

	/**
	 * Enqueue all css files in donation checkout page
	 * Hooked via action wp_enqueue_scripts, priority 100
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function register_css_files() {

		if(false === $this->is_donation_page) :
			return;
		endif;

		wp_register_style	( 'semantic-ui-progress',	'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/progress.min.css', array('semantic-ui'), '2.4.1', 'all');
		wp_enqueue_style	( $this->plugin_name, 		plugin_dir_url( __FILE__ ) . 'css/sejoli-donation-public.css', array('semantic-ui-progress'), $this->version, 'all' );
	}

	/**
	 * Enqueue all js files in donation checkout page
	 * Hooked via action wp_enqueue_scripts, priority 100
	 * @since 	1.0.0
	 * @return 	void
	 */
	public function register_js_files() {

		wp_register_script	('jquery-mask',				plugin_dir_url( __FILE__ ) . 'js/jquery-mask-plugin.js', array('jquery'), '1.14.16', true);
		wp_register_script 	('semantic-ui-progress',	'https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/components/progress.min.js', array('jquery', 'semantic-ui'), '2.4.1', true);
		wp_enqueue_script	( $this->plugin_name, 		plugin_dir_url( __FILE__ ) . 'js/sejoli-donation-public.js', array( 'jquery', 'jquery-mask', 'semantic-ui-progress' ), $this->version, true );

	}

    /**
     * Set checkout template page
     * Hooked via filter single_template, priority 120
     * @since   1.0.0
     * @param   string  $template  Current template file
     * @return  string  Modified template file
     */
    public function set_checkout_template(string $template) {

        global $post;

        $product = sejolisa_get_product($post->ID);

		if(property_exists($product, 'donation') && false !== $product->donation) :
			$this->is_donation_page = true;
			$template = SEJOLI_DONATION_DIR . 'public/partials/checkout.php';
		endif;

        return $template;
    }

	/**
	 * Set product price
	 * Hooked via filter sejoli/product/price, priority 15
	 * @param 	integer|float  	$price
	 * @param 	WP_Post 		$product
	 * @return 	float
	 */
	public function set_product_price($price, \WP_Post $product) {

		$product_id = $product->ID;
		$active     = boolval( carbon_get_post_meta($product_id, 'donation_active') );

		if(false !== $active && isset($this->request_vars['price'])) :

			$min_price = carbon_get_post_meta($product_id, 'donation_min');
			$max_price = carbon_get_post_meta($product_id, 'donation_max');

			$price     = (0 === $this->request_vars['price']) ? $max_price : $this->request_vars['price'];

			if($min_price > $price) :

				$price                  = $min_price;
				$this->donation_invalid = 'min';

			elseif($max_price < $price) :

				$price                  = $max_price;
				$this->donation_invalid = 'max';

			endif;

		endif;

		return floatval($price);
	}

	/**
	 * Set product price in order detail
	 * Hooked via filter sejoli/order/order-detail, priority 120
	 * @param 	array $order_detail
	 * @return 	array
	 */
	public function set_product_price_in_order(array $order_detail) {

		if(isset($order_detail['meta_data']['donation'])) :
			$order_detail['product']->price = $order_detail['meta_data']['donation'];
		endif;

		return $order_detail;
	}

	/**
	 * Validate the donation
	 * Hooked via filter sejoli/checkout/is-product-valid, priority 10
	 * @since 	1.0.0
	 * @param  	boolean  	$valid
	 * @param  	WP_Post 	$product
	 * @return 	boolean
	 */
	public function validate_donation($valid, \WP_Post $product) {

		if(false !== $this->donation_invalid) :

			$min_price = carbon_get_post_meta($product->ID, 'donation_min');
			$max_price = carbon_get_post_meta($product->ID, 'donation_max');

			if('min' === $this->donation_invalid) :
				sejolisa_set_message(
					sprintf(
						__('Donasi tidak boleh lebih kecil daripada %s', 'sejoli-donation'),
						sejolisa_price_format($min_price)
					)
				);
			elseif('max' === $this->donation_invalid) :
				sejolisa_set_message(
					sprintf(
						__('Donasi tidak boleh lebih besar daripada %s', 'sejoli-donation'),
						sejolisa_price_format($max_price)
					)
				);
			endif;

		endif;

		return $valid;
	}

	/**
	 * Set product price for order total
	 * Hooked via filter sejoli/order/grand-total, priority 101
	 * @since 	1.0.0
	 * @param 	int|float 	$total
	 * @param 	array  		$post_data
	 * @return 	float
	 */
	public function set_grand_total($total, array $post_data) {

		$product = sejolisa_get_product($post_data['product_id']);

		if(false !== $product->donation) :
			$total = (0 === $this->request_vars['price']) ? $product->donation['max'] : $this->request_vars['price'];
			$total = ($product->donation['min'] > $total) ? $product->donation['min'] : $total;
			$total = ($product->donation['max'] < $total) ? $product->donation['max'] : $total;

			$this->amount = $total;
		endif;

		return floatval($total);
	}

	/**
	 * Add donation amount to order meta
	 * Hooked via filter sejoli/order/meta-data, priority 200
	 * @since 	1.0.0
	 * @param 	array 	$order_meta	Array of order meta data
	 * @param 	array 	$order     	Array of order data
	 * @return 	array
	 */
	public function add_donation_amount_to_order_meta(array $order_meta, array $order) {

		if(0 < $this->amount) :
			$order_meta['donation']	= $this->amount;
		endif;

		return $order_meta;
	}

}
