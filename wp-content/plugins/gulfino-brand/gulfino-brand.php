<?php
/**
 * Plugin Name: Gulfino Brand Identity
 * Description: Ultra-luxury UI for Gulfino. Completely overrides default WordPress/WooCommerce styles.
 * Version: 3.0
 * Author: Gulfino AI
 */

function gulfino_enqueue_styles() {
    wp_enqueue_style('vazirmatn-font', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100;300;400;500;700;800;900&display=swap');

    $custom_css = "
        :root {
            --g-navy: #071B3B;
            --g-cyan: #08B7C8;
            --g-gold: #D5A54E;
            --g-white: #FFFFFF;
            --g-gray: #F5F7FA;
            --g-radius: 24px;
            --g-shadow: 0 15px 45px rgba(7, 27, 59, 0.08);
            --g-glass: rgba(255, 255, 255, 0.85);
        }

        body {
            font-family: 'Vazirmatn', sans-serif !important;
            background-color: var(--g-white);
            color: var(--g-navy);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* --- LUXURY TOP BAR --- */
        .gulfino-top-bar {
            background: var(--g-navy);
            color: #fff;
            padding: 10px 0;
            font-size: 13px;
            font-weight: 500;
        }
        .gulfino-top-bar-inner {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }
        .g-top-right { display: flex; gap: 25px; align-items: center; }
        .g-top-left { display: flex; gap: 20px; align-items: center; }
        .g-top-icon { font-size: 16px; cursor: pointer; transition: color 0.3s; }
        .g-top-icon:hover { color: var(--g-cyan); }

        /* --- PREMIUM NAVBAR --- */
        .main-header-bar {
            background: transparent !important;
            padding: 20px 0 !important;
            border: none !important;
        }
        .gulfino-navbar {
            background: var(--g-white);
            border-radius: 50px;
            box-shadow: var(--g-shadow);
            max-width: 1340px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 40px;
            border: 1px solid rgba(0,0,0,0.03);
        }
        .ast-site-identity .site-title a {
            color: var(--g-navy) !important;
            font-weight: 900 !important;
            font-size: 38px !important;
            letter-spacing: -1.5px;
            text-transform: none !important;
        }
        .gulfino-nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
            font-weight: 700;
            font-size: 15px;
        }
        .gulfino-nav-links a {
            color: var(--g-navy);
            text-decoration: none;
            transition: color 0.3s;
        }
        .gulfino-nav-links a:hover { color: var(--g-cyan); }

        .g-search-box {
            background: var(--g-gray);
            border-radius: 25px;
            padding: 8px 20px;
            display: flex;
            align-items: center;
            width: 300px;
            border: 1px solid #eef0f5;
        }
        .g-search-box input {
            background: transparent;
            border: none;
            padding: 5px 10px;
            width: 100%;
            font-size: 14px;
            outline: none;
        }

        /* --- CINEMATIC HERO --- */
        .g-hero-section {
            position: relative;
            min-height: 85vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #000;
        }
        .g-hero-bg {
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1512453979798-5eaad0ff3b0d?auto=format&fit=crop&w=1920&q=80') center/cover;
            opacity: 0.7;
            filter: brightness(0.8);
        }
        .g-hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to left, rgba(7, 27, 59, 0.9), transparent);
        }
        .g-hero-content {
            position: relative;
            z-index: 5;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            padding: 0 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .g-hero-text { flex: 1; color: #fff; }
        .g-hero-text h1 { font-size: 85px; line-height: 1; margin-bottom: 25px; }
        .g-hero-text p { font-size: 24px; opacity: 0.9; margin-bottom: 45px; }
        .g-hero-visual { flex: 1; position: relative; display: flex; justify-content: flex-end; }
        .g-hero-visual img {
            width: 110%;
            transform: perspective(1000px) rotateY(-10deg);
            filter: drop-shadow(0 30px 60px rgba(0,0,0,0.5));
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: perspective(1000px) rotateY(-10deg) translateY(0); }
            50% { transform: perspective(1000px) rotateY(-10deg) translateY(-20px); }
        }

        /* --- PRODUCT CARDS REDESIGN --- */
        .woocommerce ul.products li.product {
            border-radius: var(--g-radius) !important;
            background: #fff !important;
            padding: 0 !important;
            border: 1px solid #f0f2f5 !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .woocommerce ul.products li.product:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 60px rgba(7, 27, 59, 0.12) !important;
        }
        .woocommerce ul.products li.product .woocommerce-loop-product__link {
            padding: 20px;
            display: block;
        }
        .woocommerce ul.products li.product img {
            border-radius: 18px !important;
            margin-bottom: 20px !important;
            transition: transform 0.6s ease;
            aspect-ratio: 1/1;
            object-fit: cover;
        }
        .woocommerce ul.products li.product:hover img {
            transform: scale(1.05);
        }
        .woocommerce-loop-product__title {
            font-size: 17px !important;
            font-weight: 800 !important;
            color: var(--g-navy) !important;
            margin-bottom: 10px !important;
            height: 48px;
            overflow: hidden;
        }
        .woocommerce ul.products li.product .price {
            font-size: 19px !important;
            font-weight: 900 !important;
            color: var(--g-cyan) !important;
            margin-bottom: 20px !important;
        }
        .woocommerce ul.products li.product .button {
            background: var(--g-navy) !important;
            color: #fff !important;
            border-radius: 0 !important;
            padding: 18px !important;
            font-weight: 800 !important;
            font-size: 15px !important;
            width: 100% !important;
            margin: 0 !important;
            text-transform: none !important;
            transition: background 0.3s;
        }
        .woocommerce ul.products li.product .button:hover {
            background: var(--g-cyan) !important;
        }

        /* --- LUXURY FOOTER --- */
        .g-footer {
            background: var(--g-navy);
            color: #fff;
            padding: 100px 0 50px;
        }
        .g-footer-grid {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.5fr;
            gap: 60px;
            padding: 0 30px;
        }
        .g-footer h4 { color: var(--g-gold); margin-bottom: 30px; font-size: 20px; }
        .g-footer ul { list-style: none; padding: 0; opacity: 0.8; line-height: 2.2; }
        .g-footer-wa {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 25px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* --- CATEGORY CARDS --- */
        .g-cat-card {
            position: relative;
            height: 400px;
            border-radius: 30px;
            overflow: hidden;
            cursor: pointer;
            box-shadow: var(--g-shadow);
            transition: all 0.4s ease;
        }
        .g-cat-card:hover {
            transform: scale(1.02);
            box-shadow: 0 30px 70px rgba(7, 27, 59, 0.2);
        }
        .g-cat-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }
        .g-cat-card:hover img {
            transform: scale(1.1);
        }
        .g-cat-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(7, 27, 59, 0.9), transparent);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 30px;
            color: #fff;
        }

        /* Hide Astra Defaults */
        .ast-header-break-point .main-header-bar { border-bottom: none !important; }
        .woocommerce-breadcrumb, .woocommerce-result-count, .woocommerce-ordering { display: none !important; }
    ";
    wp_add_inline_style('astra-theme-css', $custom_css);
}
add_action('wp_enqueue_scripts', 'gulfino_enqueue_styles', 30);

// Custom Structure
function gulfino_render_header() {
    ?>
    <div class="gulfino-top-bar">
        <div class="gulfino-top-bar-inner">
            <div class="g-top-right">
                <span>✈️ ارسال مستقیم از امارات، عمان و کشورهای خلیج فارس</span>
                <span>🛡️ ضمانت اصالت کالا</span>
            </div>
            <div class="g-top-left">
                <span class="g-top-icon">❤️</span>
                <span class="g-top-icon">👤 ورود / ثبت‌نام</span>
                <span class="g-top-icon" style="position:relative;">🛒 <small style="position:absolute; top:-8px; right:-12px; background:var(--g-cyan); padding:2px 6px; border-radius:10px; font-size:10px;">3</small></span>
            </div>
        </div>
    </div>
    <div class="main-header-bar">
        <div class="gulfino-navbar">
            <div class="g-search-box">
                <span>🔍</span>
                <input type="text" placeholder="جستجو در محصولات...">
            </div>
            <ul class="gulfino-nav-links">
                <li><a href="/">خانه</a></li>
                <li><a href="/shop">فروشگاه</a></li>
                <li><a href="#">دسته‌بندی‌ها</a></li>
                <li><a href="#">درباره ما</a></li>
                <li><a href="#">تماس با ما</a></li>
            </ul>
            <div class="ast-site-identity">
                <h1 class="site-title"><a href="/">Gulfino</a></h1>
            </div>
        </div>
    </div>
    <?php
}
remove_action('astra_header', 'astra_header_markup');
add_action('astra_header', 'gulfino_render_header');

// Add Rating and Badges
add_action('woocommerce_before_shop_loop_item_title', function() {
    echo '<span class="g-wishlist-btn" style="position:absolute; top:20px; left:20px; z-index:10; background:rgba(255,255,255,0.9); width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; box-shadow:0 5px 15px rgba(0,0,0,0.1); cursor:pointer;">♡</span>';
}, 5);

add_filter('woocommerce_sale_flash', function($html) {
    return '<span class="onsale" style="background:var(--g-gold) !important; border-radius:8px !important; padding:4px 12px !important; font-weight:800 !important; top:20px !important; right:20px !important; left:auto !important; min-height:auto !important; line-height:1.4 !important;">ویژه</span>';
});
