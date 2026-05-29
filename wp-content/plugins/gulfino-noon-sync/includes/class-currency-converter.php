<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Currency_Converter
 *
 * Fetches OMR rate from TGJU and applies markup formula.
 */
class Gulfino_Noon_Currency_Converter {

    const TGJU_API = 'https://api.tgju.org/v1/market/indicator/summary-table-data/price_omr';
    const MARKUP_PERCENT = 1.8;
    const MARGIN_PERCENT = 15;
    const CACHE_KEY = 'gnoon_omr_toman_rate';
    const CACHE_TTL = HOUR_IN_SECONDS;

    /**
     * Get OMR to Toman rate with 1.8% markup applied.
     */
    public static function get_omr_toman_rate(): float {
        $cached = get_transient( self::CACHE_KEY );
        if ( $cached !== false ) {
            return (float) $cached;
        }

        $rate = self::fetch_tgju_rate();
        set_transient( self::CACHE_KEY, $rate, self::CACHE_TTL );
        return $rate;
    }

    /**
     * Convert OMR product price to final WooCommerce Toman price.
     */
    public static function convert_price( float $omr_price, ?float $rate = null ): int {
        $rate = $rate ?? self::get_omr_toman_rate();
        $final = $omr_price * $rate * ( 1 + ( self::MARGIN_PERCENT / 100 ) );
        return (int) round( $final );
    }

    private static function fetch_tgju_rate(): float {
        $response = wp_remote_get(
            self::TGJU_API,
            [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'TGJU API request failed: ' . $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            throw new RuntimeException( sprintf( 'TGJU API returned HTTP %d', $code ) );
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $body ) ) {
            throw new RuntimeException( 'Invalid TGJU API response.' );
        }

        $rial_rate = self::extract_rial_rate( $body );
        if ( $rial_rate <= 0 ) {
            throw new RuntimeException( 'Could not parse OMR rate from TGJU response.' );
        }

        // TGJU reports OMR in Rial; convert to Toman (1 Toman = 10 Rial).
        $toman_rate = $rial_rate / 10;
        return $toman_rate * ( 1 + ( self::MARKUP_PERCENT / 100 ) );
    }

    /**
     * @param array<string, mixed> $body
     */
    private static function extract_rial_rate( array $body ): float {
        $paths = [
            [ 'data', 'summary', 'p' ],
            [ 'data', 'summary', 'price' ],
            [ 'data', 'p' ],
            [ 'summary', 'p' ],
        ];

        foreach ( $paths as $path ) {
            $node = $body;
            foreach ( $path as $key ) {
                if ( ! is_array( $node ) || ! isset( $node[ $key ] ) ) {
                    $node = null;
                    break;
                }
                $node = $node[ $key ];
            }
            if ( $node !== null ) {
                $value = self::parse_number( $node );
                if ( $value > 0 ) {
                    return $value;
                }
            }
        }

        return 0.0;
    }

    /**
     * @param mixed $value
     */
    private static function parse_number( $value ): float {
        if ( is_numeric( $value ) ) {
            return (float) $value;
        }

        $clean = preg_replace( '/[^\d.]/', '', (string) $value );
        return $clean !== '' ? (float) $clean : 0.0;
    }
}
