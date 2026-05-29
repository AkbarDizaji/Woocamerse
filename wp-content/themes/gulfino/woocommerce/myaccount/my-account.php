<?php
/**
 * Gulfino – My Account wrapper
 * Suppress the built-in navigation output here; our page.php sidebar handles it.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Only render the content — navigation is rendered by page.php sidebar
do_action( 'woocommerce_account_content' );
