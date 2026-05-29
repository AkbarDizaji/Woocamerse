<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Translator
 *
 * Translates English text to Persian using Google Translate free endpoint.
 */
class Gulfino_Noon_Translator {

    const API_URL = 'https://translate.googleapis.com/translate_a/single';
    const CACHE_PREFIX = 'gnoon_tr_';
    const CACHE_TTL = WEEK_IN_SECONDS;

    /**
     * Translate text from English to Persian.
     */
    public static function to_persian( string $text ): string {
        $text = trim( wp_strip_all_tags( $text ) );
        if ( $text === '' ) {
            return '';
        }

        $cache_key = self::CACHE_PREFIX . md5( $text );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            return (string) $cached;
        }

        self::jitter( 1, 2 );

        $url = add_query_arg(
            [
                'client' => 'gtx',
                'sl'     => 'en',
                'tl'     => 'fa',
                'dt'     => 't',
                'q'      => $text,
            ],
            self::API_URL
        );

        $response = wp_remote_get(
            $url,
            [
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; GulfinoNoonSync/1.0)',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            Gulfino_Noon_Logger::error( 'Translation failed: ' . $response->get_error_message() );
            return $text;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! is_array( $body ) || empty( $body[0] ) ) {
            Gulfino_Noon_Logger::error( 'Translation returned invalid response.' );
            return $text;
        }

        $translated = '';
        foreach ( $body[0] as $segment ) {
            if ( is_array( $segment ) && isset( $segment[0] ) ) {
                $translated .= $segment[0];
            }
        }

        $translated = trim( $translated );
        if ( $translated === '' ) {
            return $text;
        }

        set_transient( $cache_key, $translated, self::CACHE_TTL );
        return $translated;
    }

    private static function jitter( int $min_seconds, int $max_seconds ): void {
        sleep( random_int( $min_seconds, $max_seconds ) );
    }
}
