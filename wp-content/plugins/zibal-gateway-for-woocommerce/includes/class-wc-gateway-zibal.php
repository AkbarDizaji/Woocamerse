<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WC_Gateway_Zibal extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'zibal';
        $this->icon               = apply_filters( 'woocommerce_zibal_icon', '' );
        $this->has_fields         = false;
        $this->method_title       = __( 'Zibal', 'zibal-gateway-for-woocommerce' );
        $this->method_description = __( 'Connect your WooCommerce store to Zibal payment gateway.', 'zibal-gateway-for-woocommerce' );

        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables.
        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        $this->merchant     = $this->get_option( 'merchant' ); // 'zibal' for sandbox or your merchant ID
        $this->sandbox      = $this->get_option( 'sandbox' ) === 'yes';

        // Actions.
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_api_wc_gateway_zibal', array( $this, 'check_zibal_response' ) );
    }

    /**
     * Initialize Gateway Settings Form Fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'zibal-gateway-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Zibal Payment', 'zibal-gateway-for-woocommerce' ),
                'default' => 'yes',
            ),
            'title' => array(
                'title'       => __( 'Title', 'zibal-gateway-for-woocommerce' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'zibal-gateway-for-woocommerce' ),
                'default'     => __( 'Zibal', 'zibal-gateway-for-woocommerce' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'zibal-gateway-for-woocommerce' ),
                'type'        => 'textarea',
                'description' => __( 'This controls the description which the user sees during checkout.', 'zibal-gateway-for-woocommerce' ),
                'default'     => __( 'Pay securely via Zibal.', 'zibal-gateway-for-woocommerce' ),
            ),
            'merchant' => array(
                'title'       => __( 'Merchant ID', 'zibal-gateway-for-woocommerce' ),
                'type'        => 'text',
                'description' => __( 'Enter your Zibal Merchant ID. Use "zibal" for testing.', 'zibal-gateway-for-woocommerce' ),
                'default'     => 'zibal',
                'desc_tip'    => true,
            ),
            'sandbox' => array(
                'title'   => __( 'Sandbox Mode', 'zibal-gateway-for-woocommerce' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Sandbox Mode (Uses "zibal" as merchant ID)', 'zibal-gateway-for-woocommerce' ),
                'default' => 'no',
            ),
        );
    }

    /**
     * Process the payment and return the result.
     */
    public function process_payment( $order_id ) {
        $order = wc_get_order( $order_id );

        $amount = $order->get_total();
        $currency = get_woocommerce_currency();
        
        // Zibal expects Rial.
        if ( $currency === 'IRT' || $currency === 'تومان' ) {
            $amount = $amount * 10; // Convert Toman to Rial
        }

        $merchant = $this->sandbox ? 'zibal' : $this->merchant;
        $callback_url = add_query_arg( 'wc-api', 'WC_Gateway_Zibal', home_url( '/' ) );
        $callback_url = add_query_arg( 'order_id', $order_id, $callback_url );

        $api_url = 'https://gateway.zibal.ir/v1/request';

        $data = array(
            'merchant'     => $merchant,
            'amount'       => (int) $amount,
            'callbackUrl'  => $callback_url,
            'description'  => sprintf( __( 'Order #%s', 'zibal-gateway-for-woocommerce' ), $order->get_order_number() ),
            'orderId'      => $order->get_order_number(),
            'mobile'       => $order->get_billing_phone(),
        );

        $response = wp_remote_post( $api_url, array(
            'body'    => json_encode( $data ),
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 45,
        ) );

        if ( is_wp_error( $response ) ) {
            wc_add_notice( __( 'Zibal connection error: ', 'zibal-gateway-for-woocommerce' ) . $response->get_error_message(), 'error' );
            return;
        }

        $result = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $result['result'] ) && $result['result'] == 100 ) {
            $trackId = $result['trackId'];
            $redirect_url = 'https://gateway.zibal.ir/start/' . $trackId;

            // Save trackId to order meta
            $order->update_meta_data( '_zibal_trackId', $trackId );
            $order->save();

            return array(
                'result'   => 'success',
                'redirect' => $redirect_url,
            );
        } else {
            $error_message = isset( $result['message'] ) ? $result['message'] : __( 'Unknown error occurred.', 'zibal-gateway-for-woocommerce' );
            wc_add_notice( __( 'Zibal error: ', 'zibal-gateway-for-woocommerce' ) . $error_message, 'error' );
            return;
        }
    }

    /**
     * Check for Zibal response.
     */
    public function check_zibal_response() {
        $order_id = isset( $_GET['order_id'] ) ? (int) $_GET['order_id'] : 0;
        $trackId  = isset( $_GET['trackId'] ) ? sanitize_text_field( $_GET['trackId'] ) : '';
        $success  = isset( $_GET['success'] ) ? (int) $_GET['success'] : 0;

        if ( ! $order_id || ! $trackId ) {
            wp_die( __( 'Invalid request.', 'zibal-gateway-for-woocommerce' ) );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            wp_die( __( 'Order not found.', 'zibal-gateway-for-woocommerce' ) );
        }

        if ( $success != 1 ) {
            $order->update_status( 'failed', __( 'Payment cancelled by user or failed.', 'zibal-gateway-for-woocommerce' ) );
            wc_add_notice( __( 'Payment was not successful.', 'zibal-gateway-for-woocommerce' ), 'error' );
            wp_redirect( wc_get_checkout_url() );
            exit;
        }

        $api_url = 'https://gateway.zibal.ir/v1/verify';
        $merchant = $this->sandbox ? 'zibal' : $this->merchant;

        $data = array(
            'merchant' => $merchant,
            'trackId'  => $trackId,
        );

        $response = wp_remote_post( $api_url, array(
            'body'    => json_encode( $data ),
            'headers' => array( 'Content-Type' => 'application/json' ),
            'timeout' => 45,
        ) );

        if ( is_wp_error( $response ) ) {
            wc_add_notice( __( 'Zibal verification error: ', 'zibal-gateway-for-woocommerce' ) . $response->get_error_message(), 'error' );
            wp_redirect( wc_get_checkout_url() );
            exit;
        }

        $result = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $result['result'] ) && $result['result'] == 100 ) {
            $ref_id = $result['refNumber'];
            $order->payment_complete( $ref_id );
            $order->add_order_note( sprintf( __( 'Zibal payment successful. Ref ID: %s', 'zibal-gateway-for-woocommerce' ), $ref_id ) );
            wc_reduce_stock_levels( $order_id );
            WC()->cart->empty_cart();

            wp_redirect( $this->get_return_url( $order ) );
            exit;
        } else {
            $error_message = isset( $result['message'] ) ? $result['message'] : __( 'Verification failed.', 'zibal-gateway-for-woocommerce' );
            $order->update_status( 'failed', sprintf( __( 'Zibal verification failed: %s', 'zibal-gateway-for-woocommerce' ), $error_message ) );
            wc_add_notice( __( 'Payment verification failed: ', 'zibal-gateway-for-woocommerce' ) . $error_message, 'error' );
            wp_redirect( wc_get_checkout_url() );
            exit;
        }
    }
}
