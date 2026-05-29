<?php
/**
 * Plugin Name: Gulfino Admin Access Fix
 * Description: Must-use plugin — ensures administrators always reach wp-admin.
 */

// 1. Override WooCommerce's admin-access block: administrators are never blocked.
add_filter( 'woocommerce_prevent_admin_access', '__return_false', 1 );

// 2. Override WooCommerce's admin-bar disable hook for the same reason.
add_filter( 'woocommerce_disable_admin_bar', '__return_false', 1 );

// 3. After WooCommerce login via the front-end form, redirect admins to wp-admin
//    instead of the myaccount page.
add_filter( 'woocommerce_login_redirect', function( $redirect, $user ) {
    if ( $user instanceof WP_User && $user->has_cap( 'manage_options' ) ) {
        return admin_url();
    }
    return $redirect;
}, 10, 2 );

// 4. After the standard WordPress login (wp-login.php), redirect admins to wp-admin.
add_filter( 'login_redirect', function( $redirect_to, $requested_redirect_to, $user ) {
    if ( ! is_wp_error( $user ) && $user->has_cap( 'manage_options' ) ) {
        return admin_url();
    }
    return $redirect_to;
}, 10, 3 );
