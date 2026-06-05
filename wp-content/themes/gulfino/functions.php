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

// Currency display — the price the final customer sees is always shown in تومان
// (noon-sync already stores Toman-scaled values), never ریال.
add_filter('woocommerce_currency_symbol', function($s, $c) {
    return $c === 'AED' ? 'درهم' : 'تومان';
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

/**
 * Social contact accounts used for the "order via direct message" checkout panel.
 *
 * Edit these three values with your real accounts:
 *  - instagram: your Instagram username, WITHOUT the leading @ (e.g. 'gulfino.store')
 *  - telegram:  your Telegram username, WITHOUT the leading @ (e.g. 'gulfino_support')
 *  - whatsapp:  your WhatsApp number in full international format, digits only,
 *               no '+' / spaces / dashes (e.g. '989121234567')
 */
function gulfino_order_contacts() {
    return apply_filters('gulfino_order_contacts', [
        'instagram' => 'gulfino.store',
        'telegram'  => 'gulfino_support',
        'whatsapp'  => '96895699131',
    ]);
}

/**
 * Build the Persian order-details message that is pre-filled / copied to the
 * clipboard when the customer contacts us to pay.
 */
function gulfino_order_message() {
    if ( ! function_exists('WC') || ! WC()->cart ) {
        return '';
    }

    $lines   = [];
    $lines[] = 'سلام، می‌خواهم این سفارش را ثبت کنم:';
    $lines[] = '';

    foreach ( WC()->cart->get_cart() as $item ) {
        $product = $item['data'];
        if ( ! $product ) {
            continue;
        }
        $name    = $product->get_name();
        $qty     = $item['quantity'];
        $line_tt = wc_price( $item['line_total'] );
        // wc_price() returns HTML; strip tags for a plain-text message.
        $lines[] = sprintf('• %s × %d — %s', $name, $qty, wp_strip_all_tags($line_tt));
    }

    // Shipping line (if a fee is applied to this cart).
    $fee_total = (float) WC()->cart->get_fee_total();
    if ( $fee_total > 0 ) {
        $lines[] = 'هزینه ارسال: ' . number_format( $fee_total ) . ' تومان';
    } else {
        $lines[] = 'هزینه ارسال: رایگان';
    }

    $lines[] = '';
    $lines[] = 'جمع کل: ' . wp_strip_all_tags( WC()->cart->get_total() );

    return implode("\n", $lines);
}

/**
 * Shipping policy: flat 200,000 تومان, free for orders above 10,000,000 تومان.
 */
function gulfino_shipping_config() {
    return apply_filters('gulfino_shipping_config', [
        'fee'       => 200000,    // flat shipping fee in تومان
        'threshold' => 10000000,  // free shipping for purchases ABOVE this amount
    ]);
}

// Apply the shipping fee to the cart (skipped when the order qualifies for free shipping).
add_action('woocommerce_cart_calculate_fees', function($cart) {
    if (!$cart || (is_admin() && !defined('DOING_AJAX'))) {
        return;
    }

    $cfg      = gulfino_shipping_config();
    $subtotal = (float) $cart->get_subtotal(); // products only, excludes the fee itself

    // "more than" the threshold → free; at or below → charge the flat fee.
    if ($subtotal > 0 && $subtotal <= $cfg['threshold']) {
        $cart->add_fee('هزینه ارسال', $cfg['fee']);
    }
});

/**
 * Persian shipping-policy message shown to the customer on cart & checkout.
 * Returns ready-to-print HTML.
 */
function gulfino_shipping_policy_html() {
    if (!function_exists('WC') || !WC()->cart) {
        return '';
    }

    $cfg       = gulfino_shipping_config();
    $subtotal  = (float) WC()->cart->get_subtotal();
    $fee_fa    = number_format($cfg['fee']);
    $thresh_fa = number_format($cfg['threshold']);

    if ($subtotal > $cfg['threshold']) {
        $msg = sprintf(
            'ارسال این سفارش <strong>رایگان</strong> است 🎉 (خرید بالای %s تومان)',
            $thresh_fa
        );
    } else {
        $remaining = number_format($cfg['threshold'] - $subtotal + 1);
        $msg = sprintf(
            'هزینه ارسال <strong>%s تومان</strong> است. با <strong>%s تومان</strong> خرید بیشتر، ارسال شما <strong>رایگان</strong> می‌شود (خرید بالای %s تومان).',
            $fee_fa,
            $remaining,
            $thresh_fa
        );
    }

    return '<div class="g-ship-policy" dir="rtl">'
        . '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/></svg>'
        . '<span>' . $msg . '</span>'
        . '</div>';
}

// Show the shipping policy on the cart page.
add_action('woocommerce_before_cart', function() {
    echo gulfino_shipping_policy_html(); // phpcs:ignore WordPress.Security.EscapeOutput
});

// Styling for the shipping-policy banner (used on cart and checkout).
add_action('wp_head', function() {
    echo '<style>
    .g-ship-policy{display:flex;align-items:center;gap:10px;max-width:680px;margin:18px auto;padding:14px 18px;
        background:rgba(8,183,200,.10);border:1px solid rgba(8,183,200,.35);border-radius:14px;
        font-family:"Vazirmatn",sans-serif;font-size:13.5px;line-height:1.9;color:#063e45}
    .g-ship-policy svg{color:#08B7C8;flex-shrink:0}
    .g-ship-policy strong{font-weight:800;color:#071B3B}
    </style>';
});
