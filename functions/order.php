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
    $goal       = carbon_get_post_meta($product_id, 'donation_goal');
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
        'percent' => ceil($total / $goal * 100),
        'type'    => $time_type
    );
}
