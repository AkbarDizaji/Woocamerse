<?php
/**
 * Plugin Name:  Gulfino – Noon.com Sync
 * Description:  Daily sync of top noon.com Oman products into WooCommerce with TGJU pricing and Persian translation.
 * Version:      1.0.0
 * Author:       Gulfino Team
 * Text Domain:  gulfino-noon-sync
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GNOON_VERSION', '1.0.0' );
define( 'GNOON_DIR', plugin_dir_path( __FILE__ ) );
define( 'GNOON_URL', plugin_dir_url( __FILE__ ) );
define( 'GNOON_LOG_DIR', GNOON_DIR . 'logs/' );

require GNOON_DIR . 'includes/class-logger.php';
require GNOON_DIR . 'includes/class-noon-scraper.php';
require GNOON_DIR . 'includes/class-currency-converter.php';
require GNOON_DIR . 'includes/class-translator.php';
require GNOON_DIR . 'includes/class-product-importer.php';
require GNOON_DIR . 'includes/class-admin.php';

/* ------------------------------------------------------------------ */
/*  ACTIVATION / DEACTIVATION                                          */
/* ------------------------------------------------------------------ */
register_activation_hook( __FILE__, function () {
    Gulfino_Noon_Cron::schedule();
    Gulfino_Noon_Logger::ensure_log_dir();
} );

register_deactivation_hook( __FILE__, function () {
    Gulfino_Noon_Cron::unschedule();
} );

/* ------------------------------------------------------------------ */
/*  WP-CRON: daily sync                                                */
/* ------------------------------------------------------------------ */
class Gulfino_Noon_Cron {
    const HOOK = 'gnoon_daily_sync';

    public static function schedule(): void {
        if ( ! wp_next_scheduled( self::HOOK ) ) {
            wp_schedule_event( time(), 'daily', self::HOOK );
        }
    }

    public static function unschedule(): void {
        $timestamp = wp_next_scheduled( self::HOOK );
        if ( $timestamp ) {
            wp_unschedule_event( $timestamp, self::HOOK );
        }
    }
}

class Gulfino_Noon_Sync {
    /**
     * Run the full daily sync pipeline.
     *
     * @return array Summary stats.
     */
    public static function run(): array {
        $settings = get_option( Gulfino_Noon_Admin::OPTION_KEY, [ 'enabled' => 1 ] );
        if ( empty( $settings['enabled'] ) ) {
            Gulfino_Noon_Logger::sync( 'Sync skipped: plugin disabled in settings.', 'INFO' );
            return [
                'status'  => 'skipped',
                'message' => 'Plugin is disabled.',
            ];
        }

        if ( ! class_exists( 'WooCommerce' ) ) {
            Gulfino_Noon_Logger::error( 'WooCommerce is not active.' );
            return [
                'status'  => 'error',
                'message' => 'WooCommerce is not active.',
            ];
        }

        $started = microtime( true );
        Gulfino_Noon_Logger::sync( 'Daily sync started.' );

        $stats = [
            'status'    => 'success',
            'scraped'   => 0,
            'imported'  => 0,
            'skipped'   => 0,
            'failed'    => 0,
            'errors'    => [],
        ];

        try {
            $products = Gulfino_Noon_Scraper::fetch_all();
            $stats['scraped'] = count( $products );
            Gulfino_Noon_Logger::sync( sprintf( 'Scraped %d products from noon.com.', $stats['scraped'] ) );

            $rate = Gulfino_Noon_Currency_Converter::get_omr_toman_rate();
            Gulfino_Noon_Logger::sync( sprintf( 'TGJU OMR/Toman rate (with 1.8%% markup): %s', number_format( $rate, 0 ) ) );

            foreach ( $products as $product ) {
                if ( Gulfino_Noon_Product_Importer::exists( $product['sku'] ) ) {
                    $stats['skipped']++;
                    Gulfino_Noon_Logger::sync( sprintf( 'Skipped duplicate SKU: %s', $product['sku'] ), 'SKIP' );
                    continue;
                }

                try {
                    $result = Gulfino_Noon_Product_Importer::import( $product, $rate );
                    if ( $result ) {
                        $stats['imported']++;
                        Gulfino_Noon_Logger::sync( sprintf( 'Imported product #%d SKU: %s', $result, $product['sku'] ), 'OK' );
                    } else {
                        $stats['failed']++;
                        Gulfino_Noon_Logger::error( 'Import returned false.', [ 'sku' => $product['sku'] ] );
                    }
                } catch ( Exception $e ) {
                    $stats['failed']++;
                    $stats['errors'][] = $e->getMessage();
                    Gulfino_Noon_Logger::error( $e->getMessage(), [ 'sku' => $product['sku'] ] );
                }
            }
        } catch ( Exception $e ) {
            $stats['status'] = 'error';
            $stats['errors'][] = $e->getMessage();
            Gulfino_Noon_Logger::error( 'Sync failed: ' . $e->getMessage() );
        }

        $elapsed = round( microtime( true ) - $started, 2 );
        Gulfino_Noon_Logger::sync(
            sprintf(
                'Daily sync finished in %ss. scraped=%d imported=%d skipped=%d failed=%d',
                $elapsed,
                $stats['scraped'],
                $stats['imported'],
                $stats['skipped'],
                $stats['failed']
            )
        );

        update_option(
            'gnoon_last_run',
            [
                'time'   => current_time( 'mysql' ),
                'stats'  => $stats,
                'elapsed' => $elapsed,
            ],
            false
        );

        return $stats;
    }
}

add_action( Gulfino_Noon_Cron::HOOK, [ 'Gulfino_Noon_Sync', 'run' ] );
