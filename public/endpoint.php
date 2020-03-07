<?php
namespace SejoliDonation\Front;

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
class Endpoint {

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
     * Set custom endpoint
     * Hooked via action init, priority 1
     * @since 1.0.0
     */
    public function set_endpoint() {

        add_rewrite_rule( '^sejoli-api/([^/]*)/([^/]*)/([^/]*)/?',	'index.php?sejoli-api=1&controller=$matches[1]&action=$matches[2]&value=$matches[3]','top');
        add_rewrite_rule( '^sejoli-api/([^/]*)/([^/]*)/?',		    'index.php?sejoli-api=1&controller=$matches[1]&action=$matches[2]','top');

    }

    /**
     * Set custom query vars
     * Hooked via filter query_vars, priority 999
     * @since   1.0.0
     * @param   array   $vars
     * @return  array
     */
    public function set_query_vars($vars)
    {

        $vars[] = 'sejoli-api';
		$vars[] = 'controller';
		$vars[] = 'action';
        $vars[] = 'value';

        return $vars;
    }

    /**
     * Render json data
     * @since   1.0.0
     * @param   string $controller
     * @param   string $action
     * @param   string $value
     * @return  void
     */
    protected function set_response($controller, $action, $value){

        // default response
        $response = array(
            'code'     => 404,
            'messages' => array(
                'Request not valid'
            )
        );

        $response = apply_filters('sejoli-api-response/' . $controller . '/' . $action, $response, $value);

        header('Content-Type: application/json');
        echo wp_send_json($response);

        exit;
    }

    /**
     * Check parse query request and handle it
     * Hooked via action parse_query, priority 999
     * @since   1.0.0
     * @return  void
     */
    public function check_parse_query() {

        global $wp_query;

        if(isset($wp_query->query_vars['sejoli-api'])) :

            // For JSON request

            $this->set_response(
                $wp_query->query_vars['controller'],
                $wp_query->query_vars['action'],
                $wp_query->query_vars['value']
            );

        endif;
    }

}
