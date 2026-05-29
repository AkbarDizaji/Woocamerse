<?php
/**
 * Plugin Name: Zibal Gateway for WooCommerce
 * Description: Connect your WooCommerce store to Zibal payment gateway.
 * Version: 1.0.0
 * Author: Gulfino
 * Author URI: https://gulfino.com
 * Text Domain: zibal-gateway-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Initialize the gateway
 */
add_action( 'plugins_loaded', 'zibal_gateway_init', 11 );

function zibal_gateway_init() {
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
        return;
    }

    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-gateway-zibal.php';

    add_filter( 'woocommerce_payment_gateways', 'add_zibal_gateway' );
}

/**
 * Add the gateway to WooCommerce
 */
function add_zibal_gateway( $gateways ) {
    $gateways[] = 'WC_Gateway_Zibal';
    return $gateways;
}
