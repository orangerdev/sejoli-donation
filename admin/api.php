<?php

namespace SejoliDonation\Admin;

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
class API {

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
	 * Set if current request is valid user
	 * @since	1.0.0
	 * @var		boolean
	 */
	protected $is_user_logged_in = false;

	/**
	 * Check if current request is passed
	 * @since 	1.0.0
	 * @var  	boolean
	 */
	protected $is_passed = true;

	/**
	 * Check if current product donation request is valid
	 * @since 	1.0.0
	 * @var  	boolean
	 */
	protected $is_product_valid = true;

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
	 * Validate product request
	 * @since 	1.0.0
	 * @param  	integer $product_id
	 * @param  	array 	$response
	 * @return 	array
	 */
	protected function validate_product($product_id, $response) {

		if(0 === $product_id) :

			$this->is_product_valid = false;
			$response['messages'][] = __('ID Produk tidak boleh kosong', 'sejoli');

		else :

			$product  = sejolisa_get_product($product_id);

			if(
				!is_a($product, 'WP_Post') ||
				!property_exists($product, 'donation')
			) :
				$this->is_product_valid = false;
				$response['messages'][] = __('Tipe produk yang dipilih bukan donasi', 'sejoli');
			endif;

		endif;

		return $response;
	}

	/**
	 * Login user
	 * Hooked via filter sejoli-api-response/user/login, priority 1
	 * @since 	1.0.0
	 * @param  	array 	$response
	 * @return 	array
	 */
	public function validate_user($response) {

		$params = wp_parse_args($_POST, array(
			'username'	=> NULL,
			'password'	=> NULL
		));

		if(!is_user_logged_in()) :

			$user =	wp_authenticate(
						$params['username'],
						$params['password']
					);

			if(is_wp_error($user)) :
				$response['messages'] = $user->get_error_messages();
			else :
				$this->is_user_logged_in = true;
			endif;

		endif;

		return $response;
	}

    /**
     * Get donation list
     * Hooked via sejoli-api/donation/list, priority 1
     * @since   1.0.0
     * @param   array    $response
     * @param   integer  $product_id
     * @return  array    Response
     */
    public function get_donation_list(array $response, $product_id) {

		$response = $this->validate_user($response);

		if(false !== $this->is_user_logged_in) :

			$product_id = intval($product_id);

			$this->validate_product($product_id, $response);

			if(false !== $this->is_product_valid) :

				$response['code']       = 200;
				$limit                  = (isset($_GET['limit'])) ? intval($_GET['limit']) :  -1;
				$donation               = sejolisa_get_donatur_list($product_id, $product, $limit);
				$response['data']       = $donation;
				$response['total_data'] = count($donation);

				unset($response['messages']);

			endif;

		endif;

        return $response;
    }

	/**
     * Get donation progress
     * Hooked via sejoli-api/donation/progress, priority 1
     * @since   1.0.0
     * @param   array    $response
     * @param   integer  $product_id
     * @return  array    Response
     */
    public function get_donation_progress(array $response, $product_id) {

		$response = $this->validate_user($response);

		if(false !== $this->is_user_logged_in) :

			$product_id = intval($product_id);

			$this->validate_product($product_id, $response);

			if(false !== $this->is_product_valid) :

				$response['code'] = 200;
				$response['data'] = sejolisa_get_donation_progress($product_id);

				unset($response['messages']);

			endif;

		endif;

        return $response;
    }

}
