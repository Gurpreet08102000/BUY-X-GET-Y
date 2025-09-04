<?php
/**
 * Plugin Name:       Simple Buy X Get Y 
 * Description:       Buy any 3 items and auto-add Product Y for free.
 * Version:           1.0.0
 * Author:            Gurpreet Singh
 */


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_notices', function() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="notice notice-error"><p><strong>';
        echo '⚠ WooCommerce is not installed or active. Please install/activate WooCommerce to use this feature.';
        echo '</strong></p></div>';
         deactivate_plugins( plugin_basename( __FILE__ ) );
    }
});

function sbxgy_activate() {
    $defaults = array(
        'enabled'      => false,
        'category_id'  => 0,
        'product_y_id' => 0,
        'threshold'    => 3,
    );
    $opts = get_option( 'sbxgy_settings', array() );
    update_option( 'sbxgy_settings', wp_parse_args( $opts, $defaults ), false );
}
register_activation_hook( __FILE__, 'sbxgy_activate' );




add_action( 'admin_menu', function() {
    add_submenu_page(
        'woocommerce',
        'Buy X Get Y Settings',
        'Buy X Get Y Settings',
        'manage_woocommerce',
        'sbgy',
        'sbgy_page_data'
    );
});


function sbgy_page_data() {

    if ( isset( $_POST['sbgy_save'] ) && check_admin_referer( 'sbgy_save_action', 'sbgy_nonce' ) ) {
        $settings = array(
            'enabled'      => ! empty( $_POST['sbgy_enabled'] ) ? 1 : 0,
            'category_id'  => absint( $_POST['sbgy_category_id'] ?? 0 ),
            'product_y_id' => absint( $_POST['sbgy_product_y_id'] ?? 0 ),
            'threshold'    => max( 1, absint( $_POST['sbgy_threshold'] ?? 3 ) ),
        );
        update_option( 'sbgy_settings', $settings );
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

  
    $settings = get_option( 'sbgy_settings', array(
        'enabled'      => 0,
        'category_id'  => 0,
        'product_y_id' => 0,
        'threshold'    => 3,
    ) );

   
    $terms = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ) );
    ?>
    <div class="wrap">
        <h1>Buy X Get Y Settings</h1>
        <form method="post">
            <?php wp_nonce_field( 'sbgy_save_action', 'sbgy_nonce' ); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Enable Promotion</th>
                    <td>
                        <input type="checkbox" name="sbgy_enabled" value="1" <?php checked( $settings['enabled'], 1 ); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Category (X)</th>
                    <td>
                        <select name="sbgy_category_id">
                            <option value="0">— Select a category —</option>
                            <?php if ( ! is_wp_error( $terms ) ) : ?>
                                <?php foreach ( $terms as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( $settings['category_id'], $term->term_id ); ?>>
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Product Y ID (Gift)</th>
                    <td>
                        <input type="number" name="sbgy_product_y_id" value="<?php echo esc_attr( $settings['product_y_id'] ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row">Threshold (X items needed)</th>
                    <td>
                        <input type="number" name="sbgy_threshold" value="<?php echo esc_attr( $settings['threshold'] ); ?>" min="1" />
                    </td>
                </tr>
            </table>

            <?php submit_button( 'Save Settings', 'primary', 'sbgy_save' ); ?>
        </form>
    </div>
    <?php
}


add_action( 'woocommerce_before_calculate_totals', function( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    $settings = get_option( 'sbgy_settings', array() );
    if ( empty( $settings['enabled'] ) || ! $settings['category_id'] || ! $settings['product_y_id'] ) return;

    $count = 0;
    foreach ( $cart->get_cart() as $item ) {
        $pid = $item['product_id'];
        if ( has_term( $settings['category_id'], 'product_cat', $pid ) && $pid != $settings['product_y_id'] ) {
            $count += $item['quantity'];
        }
    }

   
    $allowed_qty = floor( $count / $settings['threshold'] );
    if ( $allowed_qty > 1 ) {
        $allowed_qty = 1;
    }

    $gift_key = false;

    foreach ( $cart->get_cart() as $key => $item ) {
        if ( (int) $item['product_id'] === (int) $settings['product_y_id'] ) {
            $gift_key = $key;
            $item['data']->set_price( 0 ); 
        }
    }

    if ( $allowed_qty > 0 ) {
        if ( $gift_key ) {
          
            $cart->set_quantity( $gift_key, $allowed_qty, false );
        } else {
            $cart->add_to_cart( (int) $settings['product_y_id'], $allowed_qty );
        }
    } else {
        if ( $gift_key ) {
            $cart->remove_cart_item( $gift_key );
        }
    }
});




add_action( 'wp_enqueue_scripts', function() {
    if ( is_cart() ) {
        wp_enqueue_style('sbgy-style', plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            array(),'1.0');
        wp_enqueue_script('sbgy-script',plugin_dir_url( __FILE__ ) . 'assets/js/custom.js',
            array( 'jquery' ), '1.0',  true);
    }
    wp_register_script(
			'jquery',
			'https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js', 
			false,
			'3.7.1',
			true
		);
		wp_enqueue_script('jquery');
});



function sbgy_floating_progress_notice() {
    $settings = get_option( 'sbgy_settings', array(
        'enabled'      => 0,
        'category_id'  => 0,
        'product_y_id' => 0,
        'threshold'    => 3,
    ) );

    if ( empty( $settings['enabled'] ) || ! $settings['category_id'] || ! $settings['product_y_id'] ) {
        return;
    }

    $cart = WC()->cart;
    if ( ! $cart ) return;

    $count = 0;
    foreach ( $cart->get_cart() as $item ) {
        $pid = $item['product_id'];
        if ( has_term( $settings['category_id'], 'product_cat', $pid ) && $pid != $settings['product_y_id'] ) {
            $count += $item['quantity'];
        }
    }

    if ( $count <= 0 ) return;

    $mod = $count % $settings['threshold'];
    if ( $mod > 0 ) {
        $remaining = $settings['threshold'] - $mod;
        $message = sprintf(
            __( '🎁 Add %d more to unlock your free gift.', 'simple-buyxgety' ),
            $remaining
        );

        echo '<div class="sbgy-floating-notice">' . esc_html( $message ) . '</div>';

    }
}
add_action( 'wp_footer', 'sbgy_floating_progress_notice' );




add_filter( 'woocommerce_add_to_cart_validation', 'restrict_to_one_quantity_per_product', 10, 3 );
function restrict_to_one_quantity_per_product( $passed, $product_id, $quantity ) {
    if ( $quantity > 1 ) {
        wc_add_notice( __( 'You can only add one quantity of each product.', 'woocommerce' ), 'error' );
        return false;
    }
    return $passed;
}


add_filter( 'woocommerce_cart_item_quantity', 'force_single_quantity_in_cart', 10, 3 );
function force_single_quantity_in_cart( $product_quantity, $cart_item_key, $cart_item ) {
    $product_quantity = '1'; 
    return $product_quantity;
}

add_action( 'woocommerce_before_calculate_totals', 'lock_cart_quantities_to_one' );
function lock_cart_quantities_to_one( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( $cart_item['quantity'] > 1 ) {
            $cart->set_quantity( $cart_item_key, 1 );
            wc_add_notice( __( 'Each product can only be added once.', 'woocommerce' ), 'notice' );
        }
    }
}
