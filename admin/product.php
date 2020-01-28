<?php
namespace SejoliDonation\Admin;

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
class Product {

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
     * Add learnpress course data to sejoli product fields
     * Hooked via filter sejoli/product/fields, priority 5
     * @since   1.0.0
     * @param   array   $fields Array of fields
     * @return  array
     */
    public function set_product_fields($fields) {

		$conditional = array(
			'donation-active'	=> array(
				array(
					'field'	=> 'donation_active',
					'value'	=> true
				)
			),
			'donation-progress'	=> array(
				array(
					'field'	=> 'donation_active',
					'value'	=> true
				),
				array(
					'field'	=> 'donation_show_progress',
					'value'	=> true
				)
			)
		);

        $fields[]   = array(
            'title'     => __('Donasi', 'sejoli-donation'),
            'fields'    => array(
				Field::make( 'separator', 'sep_donation' , __('Pengaturan Donasi', 'sejoli'))
					->set_classes('sejoli-with-help'),
					// ->set_help_text('<a href="' . sejolisa_get_admin_help('shipping') . '" class="thickbox sejoli-help">Tutorial <span class="dashicons dashicons-video-alt2"></span></a>'),

                Field::make('html',     'html_info_donation')
                    ->set_html('<div class="sejoli-html-message info"><p>'. __('Pengaturan ini hanya <strong>BERLAKU</strong> jika tipe produk adalah Produk Digital', 'sejoli') . '</p></div>'),

                Field::make('checkbox', 'donation_active', 	__('Aktifkan sistem donasi', 'sejoli-donation'))
					->set_conditional_logic(array(
						[
							'field'	=> 'payment_type',
							'value'	=> 'one-time'
						],[
							'field' => 'product_type',
							'value' => 'digital'
						]
					)),

				Field::make('text',		'donation_min',		__('Nilai minimum donasi', 'sejoli-donation'))
					->set_attribute('type', 'number')
					->set_default_value(10000)
					->set_required(true)
					->set_help_text( __('Diisi dengan nilai minimum donasi. Dalam rupiah', 'sejoli'))
					->set_conditional_logic($conditional['donation-active']),

				Field::make('text',		'donation_max',		__('Nilai maximum donasi', 'sejoli-donation'))
					->set_attribute('type', 'number')
					->set_default_value(1000000)
					->set_required(true)
					->set_help_text( __('Diisi dengan nilai maximum donasi. Dalam rupiah', 'sejoli'))
					->set_conditional_logic($conditional['donation-active']),

				Field::make('checkbox', 'donation_show_progress', __('Tampilkan progress donasi', 'sejoli-donation'))
					->set_conditional_logic($conditional['donation-active']),

				Field::make('text',		'donation_goal',		__('Nilai target donasi', 'sejoli-donation'))
					->set_attribute('type', 'number')
					->set_help_text( __('Kosongkan jika tidak ingin ada target donasi. Dalam rupiah', 'sejoli-donation'))
					->set_conditional_logic($conditional['donation-progress']),

				Field::make('select', 	'donation_goal_limit',	__('Batasan target waktu donasi', 'sejoli-donation'))
					->add_options(array(
						''        => __('Tidak ada batasan waktu', 'donasi'),
						'weekly'  => __('Per minggu', 'donasi'),
						'monthly' => __('Per bulan', 'donasi'),
						'yearly'  => __('Per tahun', 'donasi'),
						'custom'  => __('Waktu ditentukan', 'donasi')
					))
					->set_conditional_logic($conditional['donation-progress']),

				Field::make('date',		'donation_goal_limit_date', __('Batas waktu donasi', 'sejoli-donation'))
					->set_required(true)
					->set_conditional_logic(array(
						array(
							'field'	=> 'donation_goal_limit',
							'value'	=> 'custom'
						)
					))

            )
        );

        return $fields;
    }

    /**
	 * Setup product meta data
	 * Hooked via filter sejoli/product/meta-data, filter 100
	 * @since  1.0.0
	 * @param  WP_Post $product
	 * @param  int     $product_id
	 * @return WP_Post
	 */
    public function set_product_metadata(\WP_Post $product, int $product_id) {

        $courses = carbon_get_post_meta($product_id, 'learnpress_course');

        if(is_array($courses) && 0 < count($courses)) :

            $product->learnpress = array();

            foreach($courses as $course) :
                $product->learnpress[] = $course['id'];
            endforeach;

        endif;

        return $product;
    }

}
