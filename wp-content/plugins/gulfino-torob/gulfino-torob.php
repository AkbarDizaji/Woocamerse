<?php
/**
 * Plugin Name:  Gulfino – Torob Integration
 * Description:  Production-ready Torob XML feed, auto-sync, SEO schema, validation and logging.
 * Version:      1.0.0
 * Author:       Gulfino Team
 * Text Domain:  gulfino-torob
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GTOROB_VERSION',  '1.0.0' );
define( 'GTOROB_DIR',      plugin_dir_path( __FILE__ ) );
define( 'GTOROB_URL',      plugin_dir_url( __FILE__ ) );
define( 'GTOROB_LOG_DIR',  GTOROB_DIR . 'logs/' );

require GTOROB_DIR . 'includes/class-logger.php';
require GTOROB_DIR . 'includes/class-validator.php';
require GTOROB_DIR . 'includes/class-feed-generator.php';
require GTOROB_DIR . 'includes/class-seo.php';
require GTOROB_DIR . 'includes/class-admin.php';

/* ------------------------------------------------------------------ */
/*  ACTIVATION / DEACTIVATION                                          */
/* ------------------------------------------------------------------ */
register_activation_hook( __FILE__, function () {
    Gulfino_Torob_Feed::register_rewrite();
    flush_rewrite_rules();
    Gulfino_Torob_Cron::schedule();
} );

register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
    Gulfino_Torob_Cron::unschedule();
} );

/* ------------------------------------------------------------------ */
/*  REWRITE RULE  →  /torob-feed.xml                                   */
/* ------------------------------------------------------------------ */
add_action( 'init', [ 'Gulfino_Torob_Feed', 'register_rewrite' ] );
add_filter( 'query_vars', function ( $vars ) {
    $vars[] = 'gtorob_feed';
    return $vars;
} );
add_action( 'template_redirect', function () {
    if ( ! get_query_var( 'gtorob_feed' ) ) return;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    Gulfino_Torob_Logger::access( $ip, $_SERVER['HTTP_USER_AGENT'] ?? '' );

    // Rate limiting: max 60 requests / minute per IP
    $rk = 'gtorob_rl_' . md5( $ip );
    $hits = (int) get_transient( $rk );
    if ( $hits > 60 ) {
        http_response_code( 429 );
        header( 'Retry-After: 60' );
        exit( 'Too Many Requests' );
    }
    set_transient( $rk, $hits + 1, 60 );

    Gulfino_Torob_Feed::serve();
    exit;
} );

/* ------------------------------------------------------------------ */
/*  WP-CRON: every 15 minutes                                          */
/* ------------------------------------------------------------------ */
add_filter( 'cron_schedules', function ( $s ) {
    $s['every_15_minutes'] = [
        'interval' => 900,
        'display'  => 'هر ۱۵ دقیقه',
    ];
    return $s;
} );

class Gulfino_Torob_Cron {
    const HOOK = 'gtorob_sync';

    public static function schedule() {
        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time(), 'every_15_minutes', self::HOOK );
        }
    }

    public static function unschedule() {
        $ts = wp_next_scheduled( self::HOOK );
        if ( $ts ) wp_unschedule_event( $ts, self::HOOK );
    }
}

add_action( Gulfino_Torob_Cron::HOOK, function () {
    Gulfino_Torob_Feed::regenerate_cache();
} );
