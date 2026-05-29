<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Logger
 *
 * Daily rotating log files in /logs/ (protected by .htaccess).
 *
 * Log files:
 *   logs/sync-YYYY-MM-DD.log   – sync pipeline events
 *   logs/errors-YYYY-MM-DD.log – runtime errors
 */
class Gulfino_Noon_Logger {

    const MAX_LOG_DAYS = 14;

    public static function ensure_log_dir(): void {
        if ( ! is_dir( GNOON_LOG_DIR ) ) {
            wp_mkdir_p( GNOON_LOG_DIR );
        }
        $htaccess = GNOON_LOG_DIR . '.htaccess';
        if ( ! file_exists( $htaccess ) ) {
            file_put_contents( $htaccess, "Order deny,allow\nDeny from all\n" );
        }
        $index = GNOON_LOG_DIR . 'index.php';
        if ( ! file_exists( $index ) ) {
            file_put_contents( $index, "<?php\n// Silence is golden.\n" );
        }
    }

    public static function sync( string $message, string $level = 'INFO' ): void {
        self::write( 'sync', $level, $message );
    }

    public static function error( string $message, array $context = [] ): void {
        $ctx = $context ? ' | ' . wp_json_encode( $context, JSON_UNESCAPED_UNICODE ) : '';
        self::write( 'errors', 'ERROR', $message . $ctx );
    }

    public static function tail( string $type = 'sync', int $lines = 50 ): array {
        $file = self::path( $type );
        if ( ! file_exists( $file ) ) {
            return [];
        }

        $all = file( $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        return array_slice( $all, -$lines );
    }

    public static function stats(): array {
        $sync   = self::path( 'sync' );
        $errors = self::path( 'errors' );
        return [
            'sync_today'   => file_exists( $sync ) ? count( file( $sync ) ) : 0,
            'errors_today' => file_exists( $errors ) ? count( file( $errors ) ) : 0,
        ];
    }

    private static function write( string $type, string $level, string $message ): void {
        self::ensure_log_dir();
        $file = self::path( $type );

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
        return GNOON_LOG_DIR . "{$type}-{$date}.log";
    }

    private static function rotate( string $type ): void {
        static $checked = [];
        if ( isset( $checked[ $type ] ) ) {
            return;
        }
        $checked[ $type ] = true;

        $cutoff = strtotime( '-' . self::MAX_LOG_DAYS . ' days' );
        foreach ( glob( GNOON_LOG_DIR . "{$type}-*.log" ) ?: [] as $file ) {
            if ( filemtime( $file ) < $cutoff ) {
                @unlink( $file );
            }
        }
    }
}
