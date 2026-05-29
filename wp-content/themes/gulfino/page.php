<?php get_header(); ?>
<style>
/* ============================================================
   GULFINO – PAGE / ACCOUNT LAYOUT
============================================================ */
.g-page-wrap {
  max-width:1380px; margin:0 auto; padding:50px 30px 80px;
  min-height:70vh;
}

/* ---- Account layout: sidebar + content ---- */
.g-account-layout {
  display:grid;
  grid-template-columns:260px 1fr;
  gap:32px;
  align-items:start;
}

/* ---- Sidebar nav ---- */
.g-account-nav {
  background:#fff;
  border-radius:20px;
  border:1px solid #eef0f5;
  box-shadow:0 4px 24px rgba(7,27,59,.05);
  overflow:hidden;
  position:sticky;
  top:80px;
}
.g-account-user {
  display:flex; align-items:center; gap:14px;
  padding:22px 20px 18px;
  border-bottom:1px solid #f0f2f5;
  background:linear-gradient(135deg,#071B3B,#0a2a55);
  color:#fff;
}
.g-account-avatar {
  width:52px; height:52px; border-radius:50%;
  border:2px solid rgba(255,255,255,.3);
  object-fit:cover; flex-shrink:0;
}
.g-account-name { font-size:15px; font-weight:800; margin-bottom:3px; }
.g-account-email { font-size:12px; opacity:.65; }
.g-account-menu { padding:10px 0; }
.g-account-menu-item a {
  display:flex; align-items:center; gap:12px;
  padding:12px 20px;
  font-size:14px; font-weight:600; color:#444;
  transition:all .2s;
}
.g-account-menu-item a:hover { color:var(--cyan); background:#f8fafb; }
.g-account-menu-item.active a {
  color:var(--cyan); background:#f0fafb;
  border-right:3px solid var(--cyan);
}
.g-account-menu-item.logout a { color:#e53935; }
.g-account-menu-item.logout a:hover { background:#fff5f5; }

/* ---- Main content panel ---- */
.g-account-content {
  background:#fff;
  border-radius:20px;
  border:1px solid #eef0f5;
  box-shadow:0 4px 24px rgba(7,27,59,.05);
  padding:32px 36px;
}

/* ---- Dashboard widgets ---- */
.g-dash-welcome {
  display:flex; justify-content:space-between; align-items:flex-start;
  margin-bottom:28px; gap:20px; flex-wrap:wrap;
}
.g-dash-welcome h2 { font-size:24px; font-weight:900; color:var(--navy); margin-bottom:8px; }
.g-dash-welcome p  { color:#888; font-size:14px; line-height:1.6; }
.g-dash-shop-btn {
  display:inline-flex; align-items:center; gap:8px;
  background:var(--cyan); color:#fff;
  padding:11px 22px; border-radius:12px;
  font-size:14px; font-weight:800;
  transition:background .2s; white-space:nowrap;
  box-shadow:0 6px 20px rgba(8,183,200,.25);
}
.g-dash-shop-btn:hover { background:#07a0b0; }
.g-dash-stats {
  display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:32px;
}
.g-dash-stat {
  background:var(--gray); border-radius:16px; padding:20px;
  text-align:center; border:1px solid #edf0f5;
}
.g-dash-stat-icon {
  width:44px; height:44px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  margin:0 auto 12px;
}
.g-dash-stat-num  { font-size:26px; font-weight:900; color:var(--navy); }
.g-dash-stat-label{ font-size:12px; color:#888; margin-top:4px; }
.g-dash-section   { margin-top:10px; }
.g-dash-section-head {
  display:flex; justify-content:space-between; align-items:center;
  margin-bottom:16px;
}
.g-dash-section-head h3 { font-size:18px; font-weight:800; color:var(--navy); }
.g-dash-section-head a  { color:var(--cyan); font-size:13px; font-weight:700; }
.g-dash-orders { display:flex; flex-direction:column; gap:10px; }
.g-dash-order {
  display:grid; grid-template-columns:90px 1fr 1fr 1fr 100px;
  align-items:center; gap:12px;
  background:var(--gray); border-radius:12px; padding:14px 18px;
  font-size:13px;
}
.g-dash-order-id    { font-weight:800; color:var(--navy); }
.g-dash-order-date  { color:#888; }
.g-dash-order-status{ font-weight:700; }
.g-dash-order-total { font-weight:800; }
.g-dash-order-view  {
  text-align:center; background:#fff;
  border:1.5px solid #e0e5ec; border-radius:8px;
  padding:7px 14px; font-size:12px; font-weight:700;
  color:var(--navy); transition:all .2s;
}
.g-dash-order-view:hover { border-color:var(--cyan); color:var(--cyan); }
.g-dash-empty { text-align:center; padding:50px 20px; color:#888; }
.g-dash-empty h3 { font-size:20px; font-weight:800; color:var(--navy); margin-bottom:10px; }
.g-dash-empty p { margin-bottom:24px; }

/* ---- Default WC form styles inside account ---- */
.g-account-content .woocommerce-form-row label { font-size:13px; font-weight:700; color:#444; margin-bottom:7px; display:block; }
.g-account-content .woocommerce-form-row input:not([type=checkbox]),
.g-account-content .woocommerce-form-row select {
  width:100%; padding:12px 16px; border:1.5px solid #e0e5ec;
  border-radius:12px; font-family:'Vazirmatn',sans-serif; font-size:14px;
  outline:none; transition:border-color .2s; background:#fafbfc;
}
.g-account-content .woocommerce-form-row input:focus { border-color:var(--cyan); background:#fff; }
.g-account-content .woocommerce-Button,
.g-account-content button[type=submit] {
  background:var(--cyan); color:#fff; border:none;
  padding:13px 28px; border-radius:12px;
  font-family:'Vazirmatn',sans-serif; font-size:14px; font-weight:800;
  cursor:pointer; transition:background .2s;
}
.g-account-content .woocommerce-Button:hover,
.g-account-content button[type=submit]:hover { background:#07a0b0; }
.g-account-content table.shop_table { width:100%; border-collapse:collapse; font-size:14px; }
.g-account-content table.shop_table th { padding:12px 14px; background:var(--gray); font-weight:800; font-size:13px; color:var(--navy); border-bottom:2px solid #edf0f5; }
.g-account-content table.shop_table td { padding:14px; border-bottom:1px solid #f0f2f5; }
.g-account-content .woocommerce-orders-table__cell-order-actions a { background:var(--cyan); color:#fff; padding:5px 14px; border-radius:8px; font-size:12px; font-weight:700; display:inline-block; transition:background .2s; }
.g-account-content .woocommerce-orders-table__cell-order-actions a:hover { background:#07a0b0; }
.woocommerce-message, .woocommerce-info {
  background:#f0fafb; border-right:4px solid var(--cyan);
  border-radius:10px; padding:14px 18px; margin-bottom:20px;
  font-size:14px; color:var(--navy);
}
.woocommerce-error {
  background:#fff5f5; border-right:4px solid #e53935;
  border-radius:10px; padding:14px 18px; margin-bottom:20px;
  font-size:14px; color:#c62828; list-style:none;
}

@media (max-width:900px) {
  .g-account-layout { grid-template-columns:1fr; }
  .g-account-nav { position:static; }
  .g-dash-stats { grid-template-columns:repeat(2,1fr); }
  .g-dash-order { grid-template-columns:1fr 1fr; }
}
</style>

<div class="g-page-wrap">
  <?php if ( function_exists('is_account_page') && is_account_page() ) : ?>
  <div class="g-account-layout">
    <div class="g-account-sidebar">
      <?php do_action( 'woocommerce_account_navigation' ); ?>
    </div>
    <div class="g-account-content">
      <?php
      while ( have_posts() ) : the_post();
        the_content();
      endwhile;
      ?>
    </div>
  </div>
  <?php else : ?>
    <?php while ( have_posts() ) : the_post(); ?>
    <div class="g-account-content" style="max-width:900px;margin:0 auto;">
      <h1 style="font-size:36px;font-weight:900;color:var(--navy);margin-bottom:24px;"><?php the_title(); ?></h1>
      <div style="font-size:16px;line-height:2;color:#444;"><?php the_content(); ?></div>
    </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<?php get_footer(); ?>
