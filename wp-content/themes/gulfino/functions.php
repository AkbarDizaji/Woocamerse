<?php
// Gulfino Theme Functions

add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    load_theme_textdomain('gulfino', get_template_directory() . '/languages');
});

// Remove all default WooCommerce & WordPress stylesheet bloat
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('woocommerce-general');
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('wc-blocks-vendors-style');
    wp_dequeue_style('contact-form-7');
}, 100);

// Remove useless WooCommerce sidebar
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// Currency display
add_filter('woocommerce_currency_symbol', function($s, $c) {
    return $c === 'AED' ? 'درهم' : ($c === 'IRR' ? 'ریال' : 'تومان');
}, 10, 2);

// Disable WooCommerce default breadcrumbs on shop
add_action('init', function() {
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
    remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
    remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
});

// Enqueue global assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('gulfino-fonts', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;800;900&display=swap');
    wp_enqueue_style('gulfino-main', get_template_directory_uri() . '/style.css', [], '1.0');
    wp_enqueue_script('jquery');
    if (function_exists('WC')) {
        wp_enqueue_script('wc-cart-fragments');
    }
});

// WooCommerce: product image size
add_filter('woocommerce_get_image_size_shop_catalog', function($size) {
    return ['width' => 600, 'height' => 600, 'crop' => 1];
});

// Remove WooCommerce's plain-text privacy policy notice from register form
// (we render a nicer version directly in form-login.php)
add_filter('woocommerce_get_privacy_policy_text', '__return_empty_string');

// Hide YITH Quick View & Wishlist plugin default overlays/popups when idle
add_action('wp_head', function () {
    echo '<style>
    /* YITH Quick View: hide modal and overlay until JS opens it */
    #yith-quick-view-modal,
    .yith-quick-view,
    .yith-quick-view-overlay,
    .yith-wcqv-wrapper         { display:none !important; }
    #yith-quick-view-modal.open,
    #yith-quick-view-modal.loading { display:flex !important; }

    /* TI WooCommerce Wishlist: hide any stray popup until triggered */
    .tinv-wishlist-popup:not(.tinv-wishlist-open) { display:none !important; }

    /* Remove any "×" or close-icon rendered outside a modal context */
    .yith-wcqv-head .yith-quick-view-close:not([data-visible]) { display:none; }
    </style>';
}, 1);


// Strip default WooCommerce wrapper styles that break the custom auth page
add_action('wp_head', function () {
    if ( ! function_exists('is_account_page') || ! is_account_page() ) return;
    echo '<style>
    /* Remove default WC wrappers on account page so our custom design shows cleanly */
    .woocommerce, .woocommerce-page { padding: 0 !important; margin: 0 !important; }
    .woocommerce > .woocommerce-notices-wrapper { display: none; }
    body.woocommerce-account .entry-content,
    body.woocommerce-account .woocommerce { background: transparent; }
    </style>';
});

// Override WooCommerce my-account page template with our own
add_filter('woocommerce_account_menu_items', function($items) {
    return [
        'dashboard'       => 'داشبورد',
        'orders'          => 'سفارش‌ها',
        'edit-address'    => 'آدرس‌ها',
        'edit-account'    => 'اطلاعات حساب',
        'customer-logout' => 'خروج',
    ];
});

// Prevent WooCommerce from redirecting administrators away from the admin panel
add_filter('woocommerce_prevent_admin_access', function($prevent) {
    if (current_user_can('administrator')) {
        return false;
    }
    return $prevent;
});

// Disable admin bar for non-administrators
add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) {
        return false;
    }
    return $show;
});
