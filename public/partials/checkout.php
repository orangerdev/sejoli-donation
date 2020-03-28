<?php
sejoli_get_template_part( 'checkout/header.php' );
sejoli_get_template_part( 'checkout/header-logo.php' );

$product                  = sejolisa_get_product($post->ID);
$progress                 = sejolisa_get_donation_progress($post->ID);
$use_checkout_description = boolval(carbon_get_post_meta($post->ID, 'display_product_description'));
$display_password = boolval(carbon_get_theme_option('sejoli_registration_display_password'));
?>
<div class="ui text container donation-checkout">

    <?php if(false !== $use_checkout_description) : ?>
    <div class='deskripsi-produk'>
        <?php echo apply_filters('the_content', carbon_get_post_meta($post->ID, 'checkout_product_description')); ?>
    </div>
    <?php endif; ?>

    <?php if(false !== $product->donation['show_progress']) : ?>
    <div class="donation-progress donation-box">
        <h3><?php _e('Perkembangan Donasi', 'sejoli-donation'); ?></h3>
        <h4>
            <?php printf(__('Target donasi : %s', 'sejoli-donation'), sejolisa_price_format($product->donation['goal'])); ?>
            <?php
            if('custom' === $product->donation['goal_limit']):
                printf(
                    __('hingga %s', 'sejoli-donation'),
                    date('d M Y', strtotime($product->donation['goal_limit_date']. ' 00:00:00'))
                );
            endif; ?>
        </h4>
        <div class="ui teal small indicating progress" id='donation-progress-bar' data-percent='<?php echo $progress['percent']; ?>'>
            <div class="bar"></div>
            <div class='label'>
                <?php printf(__('Total : %s', 'sejoli-donation'), $progress['total']);?>
                <?php
                if(in_array($progress['type'], array('weekly', 'monthly', 'yearlye'))) :
                    switch($progress['type']) :
                        case 'weekly' :
                            _e('per minggu ini', 'sejoli-donation');
                            break;

                        case 'monthly' :
                            printf(__('per bulan %s', 'sejoli-donation'), date('F'));
                            break;

                        case 'yearly' :
                            printf(__('per tahun %s', 'sejoli-donation'), date('Y'));
                            break;
                    endswitch;
                endif;
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php
    if(false !== $product->donation['show_list']) :

        $donatur_list = sejolisa_get_donatur_list($post->ID);

        if(is_array($donatur_list) && 0 < count($donatur_list)) :

        ?>
        <div class="donation-list donation-box">
            <h3><?php printf(__('%s Donatur Terbaru', 'ttom'), count($donatur_list)); ?></h3>
            <ul>
                <?php foreach($donatur_list as $list) : ?>
                <li>
                    <span class='donatur-name'><?php echo $list['name']; ?></span>
                    <span class='donatur-payment'><?php echo $list['total']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php

        endif;
    endif;
    ?>

    <div class="produk-dibeli">
        <table class="ui unstackable table">
            <thead>
                <tr>
                    <th colspan='2' style='text-align:center;'>
                        <?php _e('Pengisian Donasi', 'sejoli-donation'); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="ui placeholder">
                            <div class="image header">
                                <div class="line"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="ui placeholder">
                            <div class="paragraph">
                                <div class="line"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">
                        <span class="secure-tagline-icon"><i class="check circle icon"></i> Secure 100%</span>
                        <?php if(false !== $product->form['warranty_label']) : ?>
                        <span class="secure-tagline-icon"><i class="check circle icon"></i> Garansi Uang Kembali</span>
                        <?php endif; ?>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="kode-diskon">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="login">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="informasi-pribadi">
        <div class="data-holder">
        </div>
    </div>
    <div class="metode-pembayaran">
        <h3>Pilih Metode Pembayaran</h3>
        <div class="ui doubling grid data-holder">
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
            <div class="eight wide column">
                <div class="ui placeholder">
                    <div class="paragraph">
                        <div class="line"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="beli-sekarang element-blockable">
        <div class="data-holder">
            <div class="ui fluid placeholder">
                <div class="paragraph">
                    <div class="line"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="affiliate-name" style='padding-top:4rem'>

    </div>
    <div class="alert-holder checkout-alert-holder"></div>
</div>
<script id="produk-dibeli-template" type="text/x-jsrender">
    {{if product}}
        <tr>
            <td colspan='2'>
                <div class="ui stackable grid">
                    {{if product.image}}
                        <div class="four wide column">
                            <img src="{{:product.image}}">
                        </div>
                    <div class="twelve wide column" style='text-align:left;'>
                    {{else}}
                    <div class="sixteen wide column" style='text-align:left;'>
                    {{/if}}
                        <h4>{{:product.title}}</h4>
                        <input type="hidden" id="product_id" name="product_id" value="{{:product.id}}">
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan='2' style='padding-top:0;text-align:left;'>
                <div class="ui icon fluid teal labeled input">
                    <div class="ui teal label">Rp.</div>
                    <input type='text' id='price' name='price' value="{{:product.price}}" />
                    <i class="hand paper outline icon"></i>
                </div>
                <div class="ui info tiny icon message">
                    <i class="info circle icon"></i>
                    <div class="content">
                        <div class="header">
                            <h4><?php _e('Aturan pengisian donasi', 'sejoli-donation'); ?></h4>
                            <p>
                                <?php _e('Besar donasi bisa ditentukan oleh anda sendiri', 'sejoli-donation'); ?><br />
                                <?php printf(__('Minimal donasi: %s', 'sejoli-donation'), sejolisa_price_format($product->donation['min'])); ?><br />
                                <?php printf(__('Maximal donasi: %s', 'sejoli-donation'), sejolisa_price_format($product->donation['max'])); ?><br />
                            </p>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    {{/if}}
    {{if transaction}}
        <tr>
            <td>Biaya Transaksi</td>
            <td>{{:transaction.value}}</td>
        </tr>
    {{/if}}
</script>
<script id="metode-pembayaran-template" type="text/x-jsrender">
    {{if payment_gateway}}
        {{props payment_gateway}}
            <div class="eight wide column">
                <div class="ui radio checkbox {{if key == 0}}checked{{/if}}">
                    <input type="radio" name="payment_gateway" tabindex="0" class="hidden" value="{{>prop.id}}" {{if key == 0}}checked="checked"{{/if}}>
                    <label><img src="{{>prop.image}}" alt="{{>prop.title}}"></label>
                </div>
            </div>
        {{/props}}
    {{/if}}
</script>
<script id="alert-template" type="text/x-jsrender">
    <div class="ui {{:type}} message">
        <i class="close icon"></i>
        <div class="header">
            {{:type}}
        </div>
        {{if messages}}
            <ul class="list">
                {{props messages}}
                    <li>{{>prop}}</li>
                {{/props}}
            </ul>
        {{/if}}
    </div>
</script>
<script id="login-template" type="text/x-jsrender">
    {{if current_user.id}}
        <div class="login-welcome">
            <p>Hai, Saat ini kau sedang menggunakan akun <span class="name">{{:current_user.name}}</span>, <a href="<?php echo wp_logout_url( get_permalink() ); ?>">Logout</a></p>
        </div>
    {{else}}
        <?php if(false !== $product->form['login_field']) : ?>
        <div class="login-form-toggle">
            <p>Sudah mempunyai akun ? <a>Login</a></p>
        </div>
        <form class="ui form login-form">
            <h3>Login</h3>
            <div class="required field">
                <label>Alamat Email</label>
                <input type="email" name="login_email" id="login_email" placeholder="Masukan alamat email">
            </div>
            <div class="required field">
                <label>Password</label>
                <input type="password" name="login_password" id="login_password" placeholder="Masukan password anda">
            </div>
            <button type="submit" class="submit-login massive right ui green button">LOGIN</button>
            <div class="alert-holder login-alert-holder"></div>
        </form>
        <?php endif; ?>
    {{/if}}
</script>
<script id="apply-coupon-template" type="text/x-jsrender">

</script>
<script id="informasi-pribadi-template" type="text/x-jsrender">
    <div class="informasi-pribadi-info">
        <p>Isi data-data di bawah untuk informasi akses di website ini.</p>
    </div>
    <h3>Informasi Pribadi</h3>
    <div class="ui form">
        <div class="required field">
            <label>Nama</label>
            <input type="text" name="user_name" id="user_name" placeholder="Masukan nama anda">
        </div>
        <div class="required field">
            <label>Alamat Email</label>
            <p>Kami akan mengirimkan konfirmasi pembayaran dan password ke alamat ini</p>
            <input type="email" name="user_email" id="user_email" placeholder="Masukan alamat email">
            <div class="alert-holder user-email-alert-holder"></div>
        </div>
        <?php

        if($display_password) : ?>
        <div class="required field">
            <label>Password</label>
            <input type="password" name="user_password" id="user_password" placeholder="Masukan password anda">
        </div>
        <?php endif; ?>
        <div class="required field">
            <label>No Handphone</label>
            <p>Kami akan menggunakan no hp untuk keperluan administrasi</p>
            <input type="text" name="user_phone" id="user_phone" placeholder="Masukan no handphone">
            <div class="alert-holder user-phone-alert-holder"></div>
        </div>
    </div>
</script>
<script id="beli-sekarang-template" type="text/x-jsrender">
    <div class="ui stackable grid">
        <div class="eight wide column">
            <div class="total-bayar">
                <h4>Total Bayar</h4>
                <div class="total-holder">
                    <div class="ui placeholder">
                        <div class="paragraph">
                            <div class="line"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="eight wide column">
            <button data-fb-pixel-event="<?php echo isset( $fb_pixel['links']['submit']['type'] ) ? $fb_pixel['links']['submit']['type'] : ''; ?>" type="submit" class="submit-button massive right floated ui green button">PROSES SEKARANG</button>
        </div>
    </div>
</script>
<script type='text/javascript'>
var checkout,
    delay = 0;

jQuery(document).ready(function($){
    $('#donation-progress-bar').progress();

    checkout = new sejoliSaCheckout();
    checkout.init();

    $(document).on('ready', '#price', function(){
        checkout.getCalculate();
    });


});

jQuery(document).on('keyup', '#price', function(){
    var input;

    clearTimeout(delay);

    delay = setTimeout(function(){
        input = $(this).parent().addClass('loading');
        checkout.getCalculate();
    },2000)
})

</script>
<?php
sejoli_get_template_part( 'checkout/footer-secure.php' );
sejoli_get_template_part( 'checkout/footer.php' );
