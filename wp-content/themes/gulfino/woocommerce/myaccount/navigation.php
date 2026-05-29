<?php
/**
 * Gulfino – Custom My Account Navigation
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$current_user = wp_get_current_user();
$avatar_url   = get_avatar_url( $current_user->ID, [ 'size' => 80 ] );
$display_name = $current_user->display_name ?: $current_user->user_login;
$email        = $current_user->user_email;
?>
<nav class="g-account-nav">
  <!-- User info -->
  <div class="g-account-user">
    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>" class="g-account-avatar">
    <div>
      <div class="g-account-name"><?php echo esc_html( $display_name ); ?></div>
      <div class="g-account-email"><?php echo esc_html( $email ); ?></div>
    </div>
  </div>

  <!-- Nav links -->
  <ul class="g-account-menu">
    <?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
      $is_active = wc_get_account_endpoint_url( $endpoint ) === trailingslashit( get_permalink() ) . $endpoint . '/'
                   || is_wc_endpoint_url( $endpoint )
                   || ( $endpoint === 'dashboard' && is_account_page() && ! is_wc_endpoint_url( false ) );

      $icons = [
        'dashboard'       => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
        'orders'          => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>',
        'edit-address'    => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'edit-account'    => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
        'customer-logout' => '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
      ];
      $icon = $icons[ $endpoint ] ?? '<svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';
    ?>
    <li class="g-account-menu-item <?php echo $is_active ? 'active' : ''; ?> <?php echo $endpoint === 'customer-logout' ? 'logout' : ''; ?>">
      <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>">
        <?php echo $icon; ?>
        <span><?php echo esc_html( $label ); ?></span>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</nav>
