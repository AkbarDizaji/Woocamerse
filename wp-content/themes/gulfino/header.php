<!DOCTYPE html>
<html dir="rtl" lang="fa-IR" <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php wp_head(); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
<style>
/* ============================================================
   GULFINO GLOBAL STYLES — Pure custom, zero WordPress default
============================================================ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
  --navy:#071B3B; --cyan:#08B7C8; --gold:#D5A54E;
  --gray:#F5F7FA; --radius:20px;
  --shadow:0 8px 40px rgba(7,27,59,.08);
}
html { scroll-behavior: smooth; }
body {
  font-family:'Vazirmatn',sans-serif;
  background:#fff; color:#1a1a2e;
  direction:rtl; overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}
a { text-decoration:none; color:inherit; }
img { display:block; max-width:100%; }
ul { list-style:none; }
.g-wrap { max-width:1380px; margin:0 auto; padding:0 30px; }

/* ---- Remove default WP/WooCommerce junk ---- */
.woocommerce-breadcrumb, .woocommerce-result-count,
.woocommerce-ordering, .related, .upsells,
.woocommerce-tabs { display:none !important; }
.woocommerce-notices-wrapper:empty { display:none; }

/* -------- TOPBAR -------- */
.g-topbar {
  background:var(--navy); color:#fff;
  font-size:13px; font-weight:500; padding:9px 0;
}
.g-topbar-inner {
  max-width:1380px; margin:0 auto; padding:0 30px;
  display:flex; justify-content:space-between; align-items:center;
}
.g-topbar-r { display:flex; gap:28px; align-items:center; }
.g-topbar-l { display:flex; gap:20px; align-items:center; }
.g-ti { display:flex; align-items:center; gap:6px; cursor:pointer; transition:color .2s; }
.g-ti:hover { color:var(--gold); }
.g-badge {
  background:var(--cyan); color:#fff; font-size:10px; font-weight:900;
  padding:2px 6px; border-radius:20px; min-width:18px; text-align:center;
}

/* -------- NAVBAR -------- */
/* WordPress adds margin-top:32px to <html> when admin bar is shown,
   so top:0 here naturally sits just below the admin bar. */
.g-header {
  background:#fff; padding:12px 0;
  box-shadow:0 2px 20px rgba(0,0,0,.06);
  position:sticky; top:0; z-index:9990;
}
.g-nav-inner {
  max-width:1380px; margin:0 auto; padding:0 30px;
  display:flex; align-items:center; gap:24px;
}
.g-search-box {
  background:var(--gray); border-radius:14px;
  display:flex; align-items:center;
  padding:10px 18px; gap:10px; width:290px;
  border:1.5px solid #edf0f5; flex-shrink:0;
}
.g-search-box input {
  background:transparent; border:none; outline:none;
  font-family:'Vazirmatn',sans-serif; font-size:14px;
  color:#555; width:100%;
}
.g-logo {
  font-size:40px; font-weight:900; color:var(--navy);
  letter-spacing:-2px; margin:0 auto; flex-shrink:0;
}
.g-logo span { color:var(--cyan); }
.g-nav-links {
  display:flex; gap:32px; font-size:15px; font-weight:700;
  flex-shrink:0;
}
.g-nav-links a { color:var(--navy); transition:color .2s; white-space:nowrap; }
.g-nav-links a:hover { color:var(--cyan); }
.g-nav-links a.active {
  color:var(--cyan);
  border-bottom:2.5px solid var(--cyan); padding-bottom:2px;
}

/* Hamburger toggle — hidden on desktop */
.g-nav-toggle {
  display:none; background:none; border:none; cursor:pointer;
  padding:8px; color:var(--navy); line-height:0; flex-shrink:0;
}

/* ---- Responsive ---- */
@media (max-width:980px) {
  .g-topbar-r { display:none; }
  .g-nav-links { gap:18px; font-size:14px; }
  .g-search-box { width:200px; }
}
@media (max-width:768px) {
  .g-nav-inner { gap:12px; }
  .g-nav-toggle { display:flex; align-items:center; }
  .g-search-box { display:none; }
  .g-logo { font-size:30px; }
  /* Nav links become a collapsible dropdown below the header */
  .g-nav-links {
    position:absolute; top:100%; right:0; left:0;
    flex-direction:column; gap:0;
    background:#fff; box-shadow:0 12px 24px rgba(0,0,0,.08);
    border-top:1px solid #eef0f5;
    max-height:0; overflow:hidden;
    transition:max-height .3s ease;
  }
  .g-header.open .g-nav-links { max-height:340px; }
  .g-nav-links a {
    padding:14px 30px; font-size:15px;
    border-bottom:1px solid #f4f6f9; white-space:normal;
  }
  .g-nav-links a.active {
    border-bottom:1px solid #f4f6f9; padding-bottom:14px;
    background:rgba(8,183,200,.06);
  }
}
</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- TOPBAR -->
<div class="g-topbar">
  <div class="g-topbar-inner">
    <div class="g-topbar-r">
      <span class="g-ti">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
        ارسال مستقیم از امارات، عمان و کشورهای خلیج فارس
      </span>
      <span class="g-ti">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        ضمانت اصالت کالا
      </span>
    </div>
    <div class="g-topbar-l">
      <a href="<?php echo function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '/my-account'; ?>" class="g-ti">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        ورود / ثبت‌نام
      </a>
      <a href="<?php echo function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '#'; ?>" class="g-ti">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
      </a>
      <a href="<?php echo function_exists('wc_get_page_permalink') ? wc_get_page_permalink('cart') : '#'; ?>" class="g-ti" style="position:relative;">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        <?php if (function_exists('WC')): ?>
        <span class="g-badge"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : '0'; ?></span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</div>

<!-- NAVBAR -->
<header class="g-header">
  <div class="g-nav-inner">
    <button type="button" class="g-nav-toggle" id="gNavToggle" aria-label="منو" aria-expanded="false">
      <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div class="g-search-box">
      <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#aaa" stroke-width="2.5"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" placeholder="جستجو در محصولات...">
    </div>
    <a href="<?php echo home_url('/'); ?>" class="g-logo">Gulf<span>ino</span></a>
    <nav class="g-nav-links">
      <a href="<?php echo home_url('/'); ?>" <?php if (is_front_page()) echo 'class="active"'; ?>>خانه</a>
      <a href="<?php echo function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '/shop'; ?>" <?php if (function_exists('is_shop') && is_shop()) echo 'class="active"'; ?>>فروشگاه</a>
      <a href="<?php echo function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '/shop'; ?>">دسته‌بندی‌ها</a>
      <a href="<?php echo get_permalink(31); ?>" <?php if (is_page(31)) echo 'class="active"'; ?>>درباره ما</a>
      <a href="<?php echo get_permalink(32); ?>" <?php if (is_page(32)) echo 'class="active"'; ?>>تماس با ما</a>
    </nav>
  </div>
</header>
<script>
(function(){
  var t = document.getElementById('gNavToggle'),
      h = document.querySelector('.g-header');
  if(!t || !h) return;
  t.addEventListener('click', function(){
    var open = h.classList.toggle('open');
    t.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
</script>
