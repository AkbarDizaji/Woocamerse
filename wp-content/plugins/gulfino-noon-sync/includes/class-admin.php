<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Admin
 *
 * WooCommerce admin page: WooCommerce → Noon Sync
 */
class Gulfino_Noon_Admin {

    const MENU_SLUG    = 'gulfino-noon-sync';
    const OPTION_KEY   = 'gnoon_settings';
    const NONCE_ACTION = 'gnoon_admin_action';

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_gnoon_run_sync', [ __CLASS__, 'handle_run_sync' ] );

        // Background worker fired via non-blocking loopback, authenticated by a
        // one-time token (the loopback request carries no admin auth cookie).
        add_action( 'admin_post_nopriv_gnoon_run_worker', [ __CLASS__, 'handle_worker' ] );
        add_action( 'admin_post_gnoon_run_worker', [ __CLASS__, 'handle_worker' ] );

        // Admin-only display of the original noon source link on the product editor.
        add_action( 'add_meta_boxes', [ __CLASS__, 'register_source_metabox' ] );
    }

    /**
     * Register the "noon source link" meta box on the product editor.
     * Only added for users who can manage WooCommerce; the link is never shown
     * to customers on the storefront.
     */
    public static function register_source_metabox(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        add_meta_box(
            'gnoon_source_url',
            'منبع noon (فقط مدیر)',
            [ __CLASS__, 'render_source_metabox' ],
            'product',
            'side',
            'default'
        );
    }

    /**
     * @param WP_Post $post
     */
    public static function render_source_metabox( $post ): void {
        $url = get_post_meta( $post->ID, Gulfino_Noon_Product_Importer::META_SOURCE_URL, true );

        if ( empty( $url ) ) {
            echo '<p style="color:#888;margin:0;">این محصول از noon وارد نشده است.</p>';
            return;
        }

        echo '<p style="margin:0 0 8px;color:#555;">لینک صفحهٔ اصلی محصول در noon. فقط برای مدیران قابل مشاهده است و به مشتری نمایش داده نمی‌شود.</p>';
        printf(
            '<a href="%1$s" target="_blank" rel="noopener" style="word-break:break-all;font-size:12px;">%1$s</a>',
            esc_url( $url )
        );
    }

    public static function register_menu(): void {
        add_submenu_page(
            'woocommerce',
            'Noon Sync — Gulfino',
            'Noon Sync',
            'manage_woocommerce',
            self::MENU_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting(
            self::OPTION_KEY,
            self::OPTION_KEY,
            [
                'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
            ]
        );
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function sanitize_settings( $input ): array {
        $input = is_array( $input ) ? $input : [];

        return [
            'enabled'          => ! empty( $input['enabled'] ) ? 1 : 0,
            'scraper_api_key'  => sanitize_text_field( $input['scraper_api_key'] ?? '' ),
        ];
    }

    public static function handle_run_sync(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_die( esc_html__( 'Unauthorized', 'gulfino-noon-sync' ) );
        }

        check_admin_referer( self::NONCE_ACTION );

        // Dispatch the sync in the background so the browser doesn't sit through
        // the full scrape/import (which exceeds the gateway timeout and returns
        // a 504). A one-time token authorises the loopback worker request.
        $token = wp_generate_password( 24, false );
        set_transient( 'gnoon_run_token', $token, 5 * MINUTE_IN_SECONDS );

        wp_remote_post(
            admin_url( 'admin-post.php' ),
            [
                'blocking'  => false,
                'timeout'   => 0.01,
                'sslverify' => false,
                'body'      => [
                    'action' => 'gnoon_run_worker',
                    'token'  => $token,
                ],
            ]
        );

        wp_safe_redirect(
            add_query_arg(
                [
                    'page'    => self::MENU_SLUG,
                    'queued'  => '1',
                ],
                admin_url( 'admin.php' )
            )
        );
        exit;
    }

    /**
     * Background worker: runs the full sync out of the user's request cycle.
     */
    public static function handle_worker(): void {
        $token    = isset( $_POST['token'] ) ? sanitize_text_field( wp_unslash( $_POST['token'] ) ) : '';
        $expected = get_transient( 'gnoon_run_token' );

        if ( ! $expected || ! is_string( $token ) || ! hash_equals( $expected, $token ) ) {
            exit;
        }
        delete_transient( 'gnoon_run_token' );

        // Keep running even though the dispatching request has already closed.
        ignore_user_abort( true );
        if ( function_exists( 'set_time_limit' ) ) {
            @set_time_limit( 0 );
        }

        Gulfino_Noon_Sync::run();
        exit;
    }

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $opts      = get_option( self::OPTION_KEY, [ 'enabled' => 1 ] );
        $last_run  = get_option( 'gnoon_last_run', [] );
        $log_stats = Gulfino_Noon_Logger::stats();
        $next_cron = wp_next_scheduled( Gulfino_Noon_Cron::HOOK );
        $cron_cmd  = sprintf(
            '0 3 * * * curl -s %s > /dev/null 2>&1',
            esc_url( site_url( 'wp-cron.php?doing_wp_cron' ) )
        );

        self::render_styles();
        ?>
        <div class="wrap gnoon-wrap">
            <h1>Noon.com Daily Product Sync</h1>

            <?php if ( isset( $_GET['queued'] ) ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>Sync started in the background. Refresh this page in a minute to see results in the log below.</p>
                </div>
            <?php endif; ?>

            <div class="gnoon-stats">
                <?php self::stat_card( 'Last Run', $last_run['time'] ?? 'Never' ); ?>
                <?php self::stat_card( 'Imported', $last_run['stats']['imported'] ?? '–' ); ?>
                <?php self::stat_card( 'Skipped', $last_run['stats']['skipped'] ?? '–' ); ?>
                <?php self::stat_card( 'Failed', $last_run['stats']['failed'] ?? '–' ); ?>
                <?php self::stat_card( 'Next Cron', $next_cron ? human_time_diff( $next_cron ) . ' from now' : 'Not scheduled' ); ?>
                <?php self::stat_card( 'Log Lines Today', $log_stats['sync_today'] ); ?>
                <?php self::stat_card( 'Errors Today', $log_stats['errors_today'] ); ?>
            </div>

            <div class="gnoon-grid">
                <div class="gnoon-card">
                    <h2>Settings</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields( self::OPTION_KEY ); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">Enable Daily Sync</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[enabled]" value="1" <?php checked( ! empty( $opts['enabled'] ) ); ?>>
                                        Run sync automatically once every 24 hours
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">ScraperAPI Key</th>
                                <td>
                                    <input type="text" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[scraper_api_key]"
                                           value="<?php echo esc_attr( $opts['scraper_api_key'] ?? '' ); ?>"
                                           class="regular-text" placeholder="Leave blank to use direct requests">
                                    <p class="description">
                                        noon.com blocks server IPs. Get a free key at
                                        <a href="https://www.scraperapi.com/" target="_blank">scraperapi.com</a>
                                        (1,000 free requests/month). Without this key, syncs will time out.
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button( 'Save Settings' ); ?>
                    </form>

                    <hr>

                    <h3>Manual Sync</h3>
                    <p>Scrape top 15 products from Health &amp; Personal Care and Perfumes on noon.com Oman, then import new products.</p>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                        <input type="hidden" name="action" value="gnoon_run_sync">
                        <?php submit_button( 'Run Sync Now', 'secondary', 'submit', false ); ?>
                    </form>
                </div>

                <div class="gnoon-card">
                    <h2>System Cron</h2>
                    <p><code>DISABLE_WP_CRON</code> is enabled in <code>wp-config.php</code>. Add this server cron job so scheduled syncs run:</p>
                    <textarea readonly rows="3" class="large-text code"><?php echo esc_textarea( $cron_cmd ); ?></textarea>
                    <p class="description">Runs daily at 3:00 AM server time. Adjust as needed.</p>

                    <h3>Categories Synced</h3>
                    <ul>
                        <li><strong>Health &amp; Personal Care</strong> → بهداشت و مراقبت شخصی</li>
                        <li><strong>Perfumes &amp; Fragrances</strong> → عطر و ادکلن</li>
                    </ul>

                    <h3>Pricing Formula</h3>
                    <p>OMR price × TGJU OMR/Toman rate × 1.018 (+1.8%) × 1.15 (+15% margin)</p>
                </div>
            </div>

            <div class="gnoon-card gnoon-logs">
                <h2>Recent Sync Log</h2>
                <pre><?php echo esc_html( implode( "\n", Gulfino_Noon_Logger::tail( 'sync', 50 ) ) ?: 'No log entries yet.' ); ?></pre>
            </div>

            <div class="gnoon-card gnoon-logs">
                <h2>Recent Errors</h2>
                <pre><?php echo esc_html( implode( "\n", Gulfino_Noon_Logger::tail( 'errors', 50 ) ) ?: 'No errors logged.' ); ?></pre>
            </div>
        </div>
        <?php
    }

    private static function stat_card( string $label, $value ): void {
        ?>
        <div class="gnoon-stat">
            <div class="gnoon-stat-value"><?php echo esc_html( (string) $value ); ?></div>
            <div class="gnoon-stat-label"><?php echo esc_html( $label ); ?></div>
        </div>
        <?php
    }

    private static function render_styles(): void {
        ?>
        <style>
            .gnoon-wrap { max-width: 1200px; }
            .gnoon-stats {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
                gap: 12px;
                margin: 20px 0;
            }
            .gnoon-stat {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 6px;
                padding: 14px;
                text-align: center;
            }
            .gnoon-stat-value { font-size: 20px; font-weight: 700; color: #1d2327; }
            .gnoon-stat-label { font-size: 12px; color: #646970; margin-top: 4px; }
            .gnoon-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 20px;
            }
            @media (max-width: 900px) {
                .gnoon-grid { grid-template-columns: 1fr; }
            }
            .gnoon-card {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 6px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .gnoon-card h2 { margin-top: 0; }
            .gnoon-logs pre {
                background: #1d2327;
                color: #c3c4c7;
                padding: 16px;
                border-radius: 4px;
                overflow-x: auto;
                max-height: 300px;
                font-size: 12px;
                line-height: 1.5;
            }
        </style>
        <?php
    }
}

Gulfino_Noon_Admin::init();
