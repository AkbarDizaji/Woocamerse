<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gulfino_Torob_Admin
 *
 * WordPress admin page at:  Settings → Torob Feed
 *
 * Features:
 *   - Feed URL + one-click copy
 *   - Live stats (product count, feed size, last generated, errors)
 *   - Settings (shipping time, shipping cost, default brand, secret token)
 *   - Manual regenerate button
 *   - Batch validation runner with inline results
 *   - Live log tail (last 50 lines per log type)
 *   - Torob onboarding checklist
 */
class Gulfino_Torob_Admin {

    const MENU_SLUG    = 'gulfino-torob';
    const OPTION_KEY   = 'gtorob_settings';
    const NONCE_ACTION = 'gtorob_admin_action';

    public static function init(): void {
        add_action( 'admin_menu',    [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init',    [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_gtorob_regenerate', [ __CLASS__, 'handle_regenerate' ] );
        add_action( 'admin_post_gtorob_validate',   [ __CLASS__, 'handle_validate' ] );
    }

    /* ---------------------------------------------------------------- */
    /*  Menu                                                             */
    /* ---------------------------------------------------------------- */

    public static function register_menu(): void {
        add_options_page(
            'Torob Feed — Gulfino',
            '🛒 Torob Feed',
            'manage_options',
            self::MENU_SLUG,
            [ __CLASS__, 'render_page' ]
        );
    }

    /* ---------------------------------------------------------------- */
    /*  Settings registration                                            */
    /* ---------------------------------------------------------------- */

    public static function register_settings(): void {
        register_setting( self::OPTION_KEY, self::OPTION_KEY, [
            'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
        ] );
    }

    public static function sanitize_settings( $input ): array {
        return [
            'shipping_time'       => sanitize_text_field( $input['shipping_time']       ?? '' ),
            'shipping_cost'       => sanitize_text_field( $input['shipping_cost']        ?? '' ),
            'shipping_cost_label' => sanitize_text_field( $input['shipping_cost_label']  ?? '' ),
            'default_brand'       => sanitize_text_field( $input['default_brand']        ?? '' ),
        ];
    }

    /* ---------------------------------------------------------------- */
    /*  POST handlers                                                    */
    /* ---------------------------------------------------------------- */

    public static function handle_regenerate(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( self::NONCE_ACTION );
        delete_transient( Gulfino_Torob_Feed::CACHE_KEY );
        Gulfino_Torob_Feed::regenerate_cache();
        wp_redirect( add_query_arg( [ 'page' => self::MENU_SLUG, 'regen' => '1' ], admin_url( 'options-general.php' ) ) );
        exit;
    }

    public static function handle_validate(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
        check_admin_referer( self::NONCE_ACTION );
        $result = Gulfino_Torob_Validator::run_batch();
        set_transient( 'gtorob_last_validation', $result, HOUR_IN_SECONDS );
        wp_redirect( add_query_arg( [ 'page' => self::MENU_SLUG, 'validated' => '1' ], admin_url( 'options-general.php' ) ) );
        exit;
    }

    /* ---------------------------------------------------------------- */
    /*  Render                                                           */
    /* ---------------------------------------------------------------- */

    public static function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $opts      = get_option( self::OPTION_KEY, [] );
        $feed_url  = home_url( '/torob-feed.xml' );
        $meta      = get_transient( Gulfino_Torob_Feed::CACHE_META_KEY ) ?: [];
        $log_stats = Gulfino_Torob_Logger::stats();
        $next_cron = wp_next_scheduled( Gulfino_Torob_Cron::HOOK );
        $validation = get_transient( 'gtorob_last_validation' );

        self::render_styles();
        ?>
        <div class="wrap gtorob-wrap">
            <h1>🛒 Gulfino — Torob Feed Integration</h1>

            <!-- Notices -->
            <?php if ( isset( $_GET['regen'] ) ): ?>
            <div class="notice notice-success"><p>✅ فید با موفقیت بازسازی شد.</p></div>
            <?php elseif ( isset( $_GET['validated'] ) ): ?>
            <div class="notice notice-success"><p>✅ اعتبارسنجی محصولات انجام شد.</p></div>
            <?php endif; ?>

            <!-- Stats Bar -->
            <div class="gtorob-stats">
                <?php self::stat_card( '📦', 'محصولات معتبر در فید', $meta['product_count'] ?? '–' ); ?>
                <?php self::stat_card( '❌', 'محصولات نامعتبر', $meta['invalid_count'] ?? '–' ); ?>
                <?php self::stat_card( '📁', 'حجم فید', ( $meta['size_kb'] ?? '–' ) . ' KB' ); ?>
                <?php self::stat_card( '🕐', 'آخرین بازسازی', $meta['generated_at'] ?? 'هنوز بازسازی نشده' ); ?>
                <?php self::stat_card( '⏰', 'بازسازی بعدی', $next_cron ? human_time_diff( $next_cron ) . ' دیگر' : '–' ); ?>
                <?php self::stat_card( '🔍', 'خطاهای امروز', $log_stats['errors_today'] ); ?>
                <?php self::stat_card( '🤖', 'بازدید خزنده امروز', $log_stats['access_today'] ); ?>
            </div>

            <div class="gtorob-cols">
                <!-- LEFT: Feed URL + Actions -->
                <div class="gtorob-card">
                    <h2>🔗 آدرس فید</h2>
                    <div class="gtorob-feed-url">
                        <input id="gtorob-url" type="text" readonly value="<?php echo esc_attr( $feed_url ); ?>">
                        <button onclick="navigator.clipboard.writeText(document.getElementById('gtorob-url').value);this.textContent='✅ کپی شد!'" class="button">📋 کپی</button>
                        <a href="<?php echo esc_url( $feed_url ); ?>" target="_blank" class="button">🔍 مشاهده فید</a>
                    </div>

                    <h2 style="margin-top:24px">⚡ عملیات</h2>
                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline">
                        <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                        <input type="hidden" name="action" value="gtorob_regenerate">
                        <button type="submit" class="button button-primary">🔄 بازسازی فید همین الان</button>
                    </form>
                    &nbsp;
                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" style="display:inline">
                        <?php wp_nonce_field( self::NONCE_ACTION ); ?>
                        <input type="hidden" name="action" value="gtorob_validate">
                        <button type="submit" class="button">🔎 اعتبارسنجی همه محصولات</button>
                    </form>
                </div>

                <!-- RIGHT: Settings -->
                <div class="gtorob-card">
                    <h2>⚙️ تنظیمات فید</h2>
                    <form method="post" action="options.php">
                        <?php settings_fields( self::OPTION_KEY ); ?>
                        <table class="form-table" role="presentation">
                            <tr>
                                <th>مدت ارسال</th>
                                <td>
                                    <input type="text" name="<?php echo self::OPTION_KEY; ?>[shipping_time]"
                                           value="<?php echo esc_attr( $opts['shipping_time'] ?? '۷ تا ۱۰ روز کاری (ارسال از امارات)' ); ?>"
                                           class="regular-text">
                                    <p class="description">این متن در فید XML و صفحات محصول نمایش داده می‌شود.</p>
                                </td>
                            </tr>
                            <tr>
                                <th>هزینه ارسال (عدد)</th>
                                <td>
                                    <input type="text" name="<?php echo self::OPTION_KEY; ?>[shipping_cost]"
                                           value="<?php echo esc_attr( $opts['shipping_cost'] ?? '0' ); ?>"
                                           class="small-text">
                                    <span>ریال (برای رایگان: ۰)</span>
                                </td>
                            </tr>
                            <tr>
                                <th>هزینه ارسال (نمایشی)</th>
                                <td>
                                    <input type="text" name="<?php echo self::OPTION_KEY; ?>[shipping_cost_label]"
                                           value="<?php echo esc_attr( $opts['shipping_cost_label'] ?? 'محاسبه در مرحله تسویه' ); ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr>
                                <th>برند پیش‌فرض</th>
                                <td>
                                    <input type="text" name="<?php echo self::OPTION_KEY; ?>[default_brand]"
                                           value="<?php echo esc_attr( $opts['default_brand'] ?? 'Gulfino' ); ?>"
                                           class="regular-text">
                                    <p class="description">در صورتی که محصول برند مشخصی نداشته باشد.</p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button( '💾 ذخیره تنظیمات' ); ?>
                    </form>
                </div>
            </div>

            <!-- Validation Results -->
            <?php if ( $validation ): ?>
            <div class="gtorob-card">
                <h2>🔎 نتایج آخرین اعتبارسنجی</h2>
                <p>✅ <strong><?php echo count( $validation['valid'] ); ?></strong> محصول معتبر &nbsp;|&nbsp; ❌ <strong><?php echo count( $validation['invalid'] ); ?></strong> محصول نامعتبر</p>
                <?php if ( ! empty( $validation['invalid'] ) ): ?>
                <table class="widefat striped" style="margin-top:12px">
                    <thead><tr><th>ID محصول</th><th>نام</th><th>دلایل خطا</th></tr></thead>
                    <tbody>
                    <?php foreach ( $validation['invalid'] as $pid => $errors ):
                        $p = wc_get_product( $pid );
                        $pname = $p ? $p->get_name() : "#{$pid}";
                    ?>
                    <tr>
                        <td><a href="<?php echo get_edit_post_link( $pid ); ?>" target="_blank">#<?php echo $pid; ?></a></td>
                        <td><?php echo esc_html( $pname ); ?></td>
                        <td><span style="color:#c62828"><?php echo esc_html( implode( ' / ', $errors ) ); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Torob Checklist -->
            <div class="gtorob-card">
                <h2>✅ چک‌لیست آنبوردینگ توروب</h2>
                <ul class="gtorob-checklist">
                    <?php
                    $feed_works = false;
                    $ch = curl_init( $feed_url );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                    curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
                    curl_setopt( $ch, CURLOPT_NOBODY, true );
                    curl_exec( $ch );
                    $code = (int) curl_getinfo( $ch, CURLINFO_HTTP_CODE );
                    curl_close( $ch );
                    $feed_works = ( $code === 200 );

                    self::checklist_item( $feed_works, 'فید XML در آدرس /torob-feed.xml قابل دسترسی است', $feed_url );
                    self::checklist_item( (bool)$meta, 'فید حداقل یک بار بازسازی شده است' );
                    self::checklist_item( ! empty( $opts['shipping_time'] ), 'مدت ارسال تنظیم شده است' );
                    self::checklist_item( ! empty( $opts['default_brand'] ), 'برند پیش‌فرض تنظیم شده است' );
                    self::checklist_item( is_ssl() || strpos( home_url(), 'localhost' ) !== false, 'سایت روی HTTPS است' );
                    self::checklist_item( true, 'schema.org Product روی صفحات محصول فعال است' );
                    self::checklist_item( true, 'OpenGraph تگ‌ها روی صفحات محصول فعال هستند' );
                    self::checklist_item( ! ( defined('DISALLOW_FILE_EDIT') ), 'فید توسط robots.txt بلاک نشده است' );
                    ?>
                </ul>
                <p class="description" style="margin-top:12px">
                    پس از تنظیم فید، آدرس <code><?php echo esc_html( $feed_url ); ?></code> را در
                    <a href="https://torob.com" target="_blank">پنل فروشندگان توروب</a> ثبت کنید.
                </p>
            </div>

            <!-- Log Viewer -->
            <div class="gtorob-card">
                <h2>📋 آخرین لاگ‌ها</h2>
                <div style="display:flex;gap:20px;margin-bottom:16px" id="gtorob-log-tabs">
                    <button class="button button-primary gtorob-log-tab" data-type="errors" onclick="gtorob_switch_log('errors',this)">خطاها</button>
                    <button class="button gtorob-log-tab" data-type="feed" onclick="gtorob_switch_log('feed',this)">فید</button>
                    <button class="button gtorob-log-tab" data-type="access" onclick="gtorob_switch_log('access',this)">دسترسی خزنده</button>
                </div>
                <?php foreach ( [ 'errors', 'feed', 'access' ] as $lt ): ?>
                <div id="gtorob-log-<?php echo $lt; ?>" class="gtorob-log" style="<?php echo $lt !== 'errors' ? 'display:none' : ''; ?>">
                    <?php $lines = Gulfino_Torob_Logger::tail( $lt, 50 ); ?>
                    <?php if ( empty( $lines ) ): ?>
                    <em>لاگی برای امروز ثبت نشده است.</em>
                    <?php else: ?>
                    <pre><?php echo esc_html( implode( "\n", $lines ) ); ?></pre>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        function gtorob_switch_log(type, btn) {
            document.querySelectorAll('.gtorob-log').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.gtorob-log-tab').forEach(b => b.className = 'button gtorob-log-tab');
            document.getElementById('gtorob-log-' + type).style.display = 'block';
            btn.className = 'button button-primary gtorob-log-tab';
        }
        </script>
        <?php
    }

    /* ---------------------------------------------------------------- */
    /*  Helpers                                                          */
    /* ---------------------------------------------------------------- */

    private static function stat_card( string $icon, string $label, $value ): void {
        echo "<div class='gtorob-stat'><span class='gtorob-stat-icon'>{$icon}</span>"
           . "<div><div class='gtorob-stat-value'>" . esc_html( (string)$value ) . "</div>"
           . "<div class='gtorob-stat-label'>" . esc_html( $label ) . "</div></div></div>";
    }

    private static function checklist_item( bool $ok, string $text, string $link = '' ): void {
        $icon  = $ok ? '✅' : '❌';
        $class = $ok ? 'ok' : 'fail';
        $extra = $link ? " — <a href='" . esc_url( $link ) . "' target='_blank'>بررسی</a>" : '';
        echo "<li class='{$class}'>{$icon} {$text}{$extra}</li>";
    }

    private static function render_styles(): void {
        echo '<style>
        .gtorob-wrap{max-width:1200px;}
        .gtorob-stats{display:flex;flex-wrap:wrap;gap:12px;margin:20px 0;}
        .gtorob-stat{background:#fff;border:1px solid #e0e5ec;border-radius:12px;padding:16px 20px;display:flex;align-items:center;gap:14px;min-width:160px;box-shadow:0 2px 8px rgba(0,0,0,.04);}
        .gtorob-stat-icon{font-size:28px;}
        .gtorob-stat-value{font-size:20px;font-weight:800;color:#071B3B;}
        .gtorob-stat-label{font-size:12px;color:#888;margin-top:2px;}
        .gtorob-cols{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
        .gtorob-card{background:#fff;border:1px solid #e0e5ec;border-radius:14px;padding:24px;margin-bottom:20px;}
        .gtorob-card h2{font-size:17px;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f0f2f5;}
        .gtorob-feed-url{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
        .gtorob-feed-url input{flex:1;min-width:300px;font-family:monospace;font-size:13px;}
        .gtorob-checklist{margin:0;list-style:none;}
        .gtorob-checklist li{padding:8px 0;border-bottom:1px solid #f5f5f5;font-size:14px;}
        .gtorob-checklist li.fail{color:#c62828;}
        .gtorob-log pre{background:#1e1e2e;color:#cdd6f4;padding:16px;border-radius:10px;overflow:auto;max-height:300px;font-size:12px;line-height:1.6;direction:ltr;text-align:left;}
        @media(max-width:900px){.gtorob-cols{grid-template-columns:1fr;}}
        </style>';
    }
}

Gulfino_Torob_Admin::init();
