<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gulfino_Torob_Logger
 *
 * Writes structured log lines to rotating daily log files inside the
 * plugin's /logs/ directory (protected by .htaccess).
 *
 * Log files:
 *   logs/feed-YYYY-MM-DD.log   – feed generation events
 *   logs/access-YYYY-MM-DD.log – crawler access records
 *   logs/errors-YYYY-MM-DD.log – validation / runtime errors
 */
class Gulfino_Torob_Logger {

    const MAX_LOG_DAYS = 14; // auto-rotate: keep last N days

    /* ---- public API ---- */

    public static function feed( string $message, string $level = 'INFO' ): void {
        self::write( 'feed', $level, $message );
    }

    public static function error( string $message, array $context = [] ): void {
        $ctx = $context ? ' | ' . json_encode( $context, JSON_UNESCAPED_UNICODE ) : '';
        self::write( 'errors', 'ERROR', $message . $ctx );
    }

    public static function invalid_product( int $id, string $reason ): void {
        self::write( 'errors', 'INVALID', "product_id={$id} reason=\"{$reason}\"" );
    }

    public static function access( string $ip, string $ua ): void {
        $ua_clean = substr( preg_replace( '/[^\x20-\x7E]/', '', $ua ), 0, 200 );
        self::write( 'access', 'ACCESS', "ip={$ip} ua=\"{$ua_clean}\"" );
    }

    /* ---- read last N lines (for admin UI) ---- */

    public static function tail( string $type = 'errors', int $lines = 50 ): array {
        $file = self::path( $type );
        if ( ! file_exists( $file ) ) return [];

        $all = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        return array_slice( $all, -$lines );
    }

    public static function stats(): array {
        $today  = date( 'Y-m-d' );
        $access = self::path( 'access', $today );
        $errors = self::path( 'errors', $today );
        return [
            'access_today' => file_exists( $access ) ? count( file( $access ) ) : 0,
            'errors_today' => file_exists( $errors ) ? count( file( $errors ) ) : 0,
        ];
    }

    /* ---- internals ---- */

    private static function write( string $type, string $level, string $message ): void {
        $file = self::path( $type );
        $dir  = dirname( $file );

        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
            file_put_contents( $dir . '/.htaccess', "Order deny,allow\nDeny from all\n" );
        }

        $line = sprintf(
            "[%s] [%s] %s\n",
            date( 'Y-m-d H:i:s' ),
            $level,
            $message
        );

        file_put_contents( $file, $line, FILE_APPEND | LOCK_EX );
        self::rotate( $type );
    }

    private static function path( string $type, string $date = '' ): string {
        $date = $date ?: date( 'Y-m-d' );
        return GTOROB_LOG_DIR . "{$type}-{$date}.log";
    }

    /** Delete log files older than MAX_LOG_DAYS */
    private static function rotate( string $type ): void {
        static $checked = [];
        if ( isset( $checked[ $type ] ) ) return;
        $checked[ $type ] = true;

        $cutoff = strtotime( '-' . self::MAX_LOG_DAYS . ' days' );
        foreach ( glob( GTOROB_LOG_DIR . "{$type}-*.log" ) ?: [] as $f ) {
            if ( filemtime( $f ) < $cutoff ) @unlink( $f );
        }
    }
}
