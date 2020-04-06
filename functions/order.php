<?php

use Carbon\Carbon;

/**
 * Get donation progress by product
 * @since   1.0.0
 * @param   integer $product_id
 * @return  array   Response with total donation, donation progress and time type
 */
function sejolisa_get_donation_progress($product_id) {

    $key        = 'total_donation_product-' . $product_id;
    $total      = get_transient($key);
    $time_type  = carbon_get_post_meta($product_id, 'donation_goal_limit');
    $goal       = intval(carbon_get_post_meta($product_id, 'donation_goal'));
    $limit_date = carbon_get_post_meta($product_id, 'donation_goal_limit_date');

    if(false === $total) :

        $total         = 0;
        $query         = \SejoliSA\Model\Order::reset();

        $now           = Carbon::now();
        $show_progress = boolval(carbon_get_post_meta($product_id, 'donation_show_progress'));

        if(false !== $show_progress) :

            switch($time_type) :
                case 'weekly' :
                    $query = $query->set_filter('updated_at', $now->startOfWeek()->format('Y-m-d H:i:s'), '>=')
                                   ->set_filter('updated_at', $now->endOfWeek()->format('Y-m-d H:i:s'), '<=');
                    break;

                case 'monthly' :
                    $query = $query->set_filter('updated_at', $now->startOfMonth()->format('Y-m-d H:i:s'), '>=')
                                   ->set_filter('updated_at', $now->endOfMonth()->format('Y-m-d H:i:s'), '<=');
                    break;

                case 'yearly' :
                    $query = $query->set_filter('updated_at', $now->startOfYear()->format('Y-m-d H:i:s'), '>=')
                                   ->set_filter('updated_at', $now->endOfYear()->format('Y-m-d H:i:s'), '<=');
                    break;

                case 'custom' :

                    $product = get_post($product_id);
                    $query   = $query->set_filter('updated_at', $product->post_date, '>=')
                                   ->set_filter('updated_at', $limit_date.' 00:00:00', '<=');

            endswitch;

        endif;

        $response = $query->set_filter('product_id', $product_id)
                        ->set_filter('status', ['completed'])
                        ->get_total_omset()
                        ->respond();

        if(false !== $response['valid']) :
            $total = $response['total'];
        endif;

        set_transient($key, $total, 30 * DAY_IN_SECONDS);

    endif;

    return array(
        'total'   => sejolisa_price_format($total),
        'percent' => (0 === $goal) ? 0 : ceil($total / $goal * 100),
        'type'    => $time_type,
        'goal'    => sejolisa_price_format($goal),
    );
}

if(!function_exists('sejolisa_get_sensored_string')) :

/**
 * Change all chars except first and last
 * @note    We will move this function to main sejoli plugin in version 1.3.3
 * @since   1.0.0
 * @param  string   $string           Given string
 * @param  string   $replace_char     Characater that will replace
 * @return string   String that has been replaced
 */
function sejolisa_get_sensored_string(string $string, $replace_char = '*') {

    $words = explode(' ', $string);

    foreach($words as $i => $word) :

        $length    = strlen($word);
        $words[$i] = substr($word, 0, 1).str_repeat('*', $length - 2).substr($word, $length - 1, 1);

    endforeach;

    return implode(' ', $words);
}

endif;

/**
 * Get donatur list
 * @since   1.0.0
 * @since   1.2.0        Add human time difference in donatur list
 * @param   integer         $product_id
 * @param   null|WP_Post    $product
 * @param   integer         $limt           Limit to show total donation list
 * @return  array
 */
function sejolisa_get_donatur_list($product_id, $product = NULL, $limit = 0) {

    if(
        !is_a($product, 'WP_Post') ||
        !property_exists($product, 'donation') ||
        !array_key_exists('total_list', $product->donation)
    ) :
        $limit_list  = intval(carbon_get_post_meta($product_id, 'donation_total_list'));
        $is_sensored = boolval(carbon_get_post_meta($product_id, 'donation_list_sensor_name'));
    else :
        $limit_list  = $product->donation['total_list'];
        $is_sensored = $product->donation['list_sensor_name'];
    endif;

    $key          = 'donation_list_product-' . $product_id;
    $donatur_list = false;get_transient($key);

    if(false === $donatur_list) :

        $response  = \SejoliSA\Model\Order::reset()
                        ->set_filter('product_id', $product_id)
                        ->set_filter('status', ['completed'])
                        ->get()
                        ->respond();

        Carbon::setLocale('id');

        if(false !== $response['valid']) :

            foreach($response['orders'] as $order) :

                $donatur_list[$order->ID] = array(
                    'name'       => $order->user_name,
                    'total'      => sejolisa_price_format($order->grand_total),
                    'human_time' => Carbon::parse(Carbon::now())->diffForHumans($order->updated_at),
                );

            endforeach;

            set_transient($key, $donatur_list, 30 * DAY_IN_SECONDS);

        endif;

    endif;

    if(
        false !== $is_sensored &&
        is_array($donatur_list) &&
        0 < count($donatur_list)
    ) :

        foreach( (array) $donatur_list as $i => $list) :
            $donatur_list[$i]['name'] = sejolisa_get_sensored_string($list['name']);
        endforeach;

    endif;

    if(0 === $limit) :
        $donatur_list = array_slice($donatur_list, 0, $limit_list);
    elseif( 0 < $limit) :
        $donatur_list = array_slice($donatur_list, 0, $limit);
    endif;

    return $donatur_list;

}
