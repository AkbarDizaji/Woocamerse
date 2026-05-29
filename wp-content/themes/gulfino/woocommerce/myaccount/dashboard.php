<?php
/**
 * Gulfino – My Account Dashboard
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$current_user  = wp_get_current_user();
$display_name  = $current_user->display_name ?: $current_user->user_login;
$orders        = wc_get_orders( [ 'customer' => get_current_user_id(), 'limit' => 3, 'status' => [ 'processing', 'completed', 'on-hold' ] ] );
$total_orders  = wc_get_customer_order_count( get_current_user_id() );
?>

<div class="g-dash-welcome">
  <div>
    <h2>سلام <?php echo esc_html( $display_name ); ?> 👋</h2>
    <p>خوش آمدید به پنل کاربری Gulfino. سفارش‌ها، آدرس‌ها و اطلاعات حساب خود را اینجا مدیریت کنید.</p>
  </div>
  <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="g-dash-shop-btn">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    ادامه خرید
  </a>
</div>

<!-- Stats row -->
<div class="g-dash-stats">
  <div class="g-dash-stat">
    <div class="g-dash-stat-icon" style="background:#e8f9fb;">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#08B7C8" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
    </div>
    <div class="g-dash-stat-num"><?php echo (int) $total_orders; ?></div>
    <div class="g-dash-stat-label">سفارش کل</div>
  </div>
  <div class="g-dash-stat">
    <div class="g-dash-stat-icon" style="background:#fff8ec;">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#D5A54E" stroke-width="2"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
    </div>
    <div class="g-dash-stat-num">—</div>
    <div class="g-dash-stat-label">علاقه‌مندی‌ها</div>
  </div>
  <div class="g-dash-stat">
    <div class="g-dash-stat-icon" style="background:#f0fdf4;">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#22c55e" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <?php
    $completed = wc_get_orders( [
        'customer' => get_current_user_id(),
        'status'   => [ 'completed' ],
        'limit'    => -1,
        'return'   => 'ids',
    ] );
    ?>
    <div class="g-dash-stat-num"><?php echo count( $completed ); ?></div>
    <div class="g-dash-stat-label">تحویل شده</div>
  </div>
  <div class="g-dash-stat">
    <div class="g-dash-stat-icon" style="background:#fef2f2;">
      <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="#ef4444" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <div class="g-dash-stat-num">
      <?php
      $addresses = WC()->countries->get_allowed_countries();
      $has_addr  = get_user_meta( get_current_user_id(), 'billing_address_1', true );
      echo $has_addr ? '✓' : '—';
      ?>
    </div>
    <div class="g-dash-stat-label">آدرس ذخیره‌شده</div>
  </div>
</div>

<!-- Recent orders -->
<?php if ( $orders ) : ?>
<div class="g-dash-section">
  <div class="g-dash-section-head">
    <h3>آخرین سفارش‌ها</h3>
    <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>">مشاهده همه ›</a>
  </div>
  <div class="g-dash-orders">
    <?php foreach ( $orders as $order ) :
      $status       = $order->get_status();
      $status_label = wc_get_order_status_name( $status );
      $status_color = [
        'processing' => '#08B7C8',
        'completed'  => '#22c55e',
        'on-hold'    => '#D5A54E',
        'cancelled'  => '#ef4444',
        'pending'    => '#888',
      ][ $status ] ?? '#888';
    ?>
    <div class="g-dash-order">
      <div class="g-dash-order-id">#<?php echo $order->get_order_number(); ?></div>
      <div class="g-dash-order-date"><?php echo wc_format_datetime( $order->get_date_created() ); ?></div>
      <div class="g-dash-order-status" style="color:<?php echo $status_color; ?>">
        <?php echo esc_html( $status_label ); ?>
      </div>
      <div class="g-dash-order-total"><?php echo $order->get_formatted_order_total(); ?></div>
      <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="g-dash-order-view">جزئیات</a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php else : ?>
<div class="g-dash-empty">
  <div style="font-size:48px; margin-bottom:16px;">🛍️</div>
  <h3>هنوز سفارشی ثبت نکرده‌اید</h3>
  <p>اولین خرید خود را از فروشگاه Gulfino ثبت کنید.</p>
  <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="g-dash-shop-btn">رفتن به فروشگاه</a>
</div>
<?php endif;

do_action( 'woocommerce_account_dashboard' );
?>
