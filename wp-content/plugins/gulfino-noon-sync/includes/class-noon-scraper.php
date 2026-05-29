<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Scraper
 *
 * Fetches noon.com Oman category pages and extracts product data from __NEXT_DATA__.
 */
class Gulfino_Noon_Scraper {

    const LIMIT = 15;

    const CATEGORIES = [
        'health-beauty' => [
            'url'              => 'https://www.noon.com/oman-en/health-beauty/?sort_by=bestselling',
            'slug'             => 'health-beauty',
            'category_fa'      => 'بهداشت و مراقبت شخصی',
            'category_en'      => 'Health & Personal Care',
        ],
        'fragrances' => [
            'url'              => 'https://www.noon.com/oman-en/fragrances/?sort_by=bestselling',
            'slug'             => 'fragrances',
            'category_fa'      => 'عطر و ادکلن',
            'category_en'      => 'Perfumes & Fragrances',
        ],
    ];

    const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
    ];

    /**
     * Fetch top products from all configured categories.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function fetch_all(): array {
        $all     = [];
        $seen    = [];
        $first   = true;

        foreach ( self::CATEGORIES as $key => $category ) {
            if ( ! $first ) {
                self::jitter( 2, 6 );
            }
            $first = false;

            $products = self::fetch_category( $category );
            foreach ( $products as $product ) {
                $sku = $product['sku'] ?? '';
                if ( ! $sku || isset( $seen[ $sku ] ) ) {
                    continue;
                }
                $seen[ $sku ] = true;
                $all[]        = $product;
            }
        }

        return $all;
    }

    /**
     * @param array<string, mixed> $category
     * @return array<int, array<string, mixed>>
     */
    public static function fetch_category( array $category ): array {
        Gulfino_Noon_Logger::sync( sprintf( 'Fetching category: %s', $category['slug'] ) );

        $html = self::request( $category['url'] );
        if ( ! $html ) {
            throw new RuntimeException( sprintf( 'Empty response for category: %s', $category['slug'] ) );
        }

        $data = self::extract_next_data( $html );
        if ( ! $data ) {
            throw new RuntimeException( sprintf( 'Could not parse __NEXT_DATA__ for category: %s', $category['slug'] ) );
        }

        $hits = self::extract_hits( $data );
        if ( empty( $hits ) ) {
            Gulfino_Noon_Logger::sync( sprintf( 'No products found in category: %s', $category['slug'] ), 'WARN' );
            return [];
        }

        $products = [];
        foreach ( array_slice( $hits, 0, self::LIMIT ) as $hit ) {
            $parsed = self::parse_product( $hit, $category );
            if ( $parsed ) {
                $products[] = $parsed;
            }
        }

        Gulfino_Noon_Logger::sync( sprintf( 'Parsed %d products from %s', count( $products ), $category['slug'] ) );
        return $products;
    }

    private static function request( string $url ): string {
        $response = wp_remote_get(
            $url,
            [
                'timeout'    => 45,
                'user-agent' => self::USER_AGENTS[ array_rand( self::USER_AGENTS ) ],
                'headers'    => [
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Cache-Control'   => 'no-cache',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'HTTP request failed: ' . $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            throw new RuntimeException( sprintf( 'HTTP %d for URL: %s', $code, $url ) );
        }

        return (string) wp_remote_retrieve_body( $response );
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function extract_next_data( string $html ): ?array {
        if ( ! preg_match( '/<script id="__NEXT_DATA__" type="application\/json">(.*?)<\/script>/s', $html, $matches ) ) {
            return null;
        }

        $json = html_entity_decode( $matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $data = json_decode( $json, true );
        return is_array( $data ) ? $data : null;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private static function extract_hits( array $data ): array {
        $paths = [
            [ 'props', 'pageProps', 'catalog', 'hits' ],
            [ 'props', 'pageProps', 'catalogData', 'catalog', 'hits' ],
            [ 'props', 'pageProps', 'initialState', 'catalog', 'hits' ],
            [ 'props', 'pageProps', 'data', 'catalog', 'hits' ],
        ];

        foreach ( $paths as $path ) {
            $node = $data;
            foreach ( $path as $key ) {
                if ( ! is_array( $node ) || ! isset( $node[ $key ] ) ) {
                    $node = null;
                    break;
                }
                $node = $node[ $key ];
            }
            if ( is_array( $node ) && ! empty( $node ) ) {
                return $node;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $hit
     * @param array<string, mixed> $category
     * @return array<string, mixed>|null
     */
    private static function parse_product( array $hit, array $category ): ?array {
        $sku = self::first_string(
            $hit['sku'] ?? null,
            $hit['offer_code'] ?? null,
            $hit['product_code'] ?? null,
            $hit['id'] ?? null
        );

        if ( ! $sku ) {
            return null;
        }

        $title = self::first_string(
            $hit['name'] ?? null,
            $hit['title'] ?? null,
            $hit['product_title'] ?? null
        );

        if ( ! $title ) {
            return null;
        }

        $price_omr = self::extract_real_price_omr( $hit );
        if ( $price_omr <= 0 ) {
            Gulfino_Noon_Logger::sync( sprintf( 'Skipping SKU %s: invalid price', $sku ), 'WARN' );
            return null;
        }

        $description = self::first_string(
            $hit['description'] ?? null,
            $hit['long_description'] ?? null,
            $hit['short_description'] ?? null,
            $title
        );

        $images = self::extract_images( $hit, $sku );
        $url    = self::first_string(
            $hit['url'] ?? null,
            $hit['product_url'] ?? null
        );

        if ( $url && strpos( $url, '/' ) === 0 ) {
            $url = 'https://www.noon.com' . $url;
        }

        return [
            'sku'          => (string) $sku,
            'title_en'     => $title,
            'description_en' => $description,
            'price_omr'    => $price_omr,
            'images'       => $images,
            'category_en'  => $category['category_en'],
            'category_fa'  => $category['category_fa'],
            'category_slug'=> $category['slug'],
            'source_url'   => $url ?: '',
        ];
    }

    /**
     * Use original/non-discounted price when available.
     *
     * @param array<string, mixed> $hit
     */
    private static function extract_real_price_omr( array $hit ): float {
        $candidates = [];

        $price_fields = [
            'price',
            'original_price',
            'list_price',
            'was_price',
            'msrp',
            'unit_price',
        ];

        foreach ( $price_fields as $field ) {
            if ( isset( $hit[ $field ] ) ) {
                $candidates[] = self::normalize_price( $hit[ $field ] );
            }
        }

        if ( isset( $hit['sale_price'] ) && isset( $hit['price'] ) ) {
            $sale = self::normalize_price( $hit['sale_price'] );
            $base = self::normalize_price( $hit['price'] );
            if ( $base > 0 ) {
                $candidates[] = max( $base, $sale );
            }
        }

        if ( isset( $hit['price_range'] ) && is_array( $hit['price_range'] ) ) {
            $candidates[] = self::normalize_price( $hit['price_range']['max'] ?? $hit['price_range']['min'] ?? 0 );
        }

        $candidates = array_filter( $candidates, static fn( $v ) => $v > 0 );
        return $candidates ? (float) max( $candidates ) : 0.0;
    }

    /**
     * @param mixed $value
     */
    private static function normalize_price( $value ): float {
        if ( is_array( $value ) ) {
            $value = $value['value'] ?? $value['amount'] ?? $value['price'] ?? 0;
        }

        $price = (float) preg_replace( '/[^\d.]/', '', (string) $value );
        if ( $price <= 0 ) {
            return 0.0;
        }

        // noon sometimes stores prices in baisa (1 OMR = 1000 baisa).
        if ( $price >= 1000 ) {
            return round( $price / 1000, 3 );
        }

        return $price;
    }

    /**
     * @param array<string, mixed> $hit
     * @return array<int, string>
     */
    private static function extract_images( array $hit, string $sku ): array {
        $urls = [];

        $image_keys = $hit['image_keys'] ?? $hit['images'] ?? $hit['image_key'] ?? [];
        if ( is_string( $image_keys ) ) {
            $image_keys = [ $image_keys ];
        }

        if ( is_array( $image_keys ) ) {
            foreach ( $image_keys as $key ) {
                if ( is_array( $key ) ) {
                    $key = $key['key'] ?? $key['url'] ?? '';
                }
                if ( ! is_string( $key ) || $key === '' ) {
                    continue;
                }
                if ( strpos( $key, 'http' ) === 0 ) {
                    $urls[] = $key;
                } else {
                    $urls[] = sprintf( 'https://f.nooncdn.com/p/pnsku/%s/45/%s.jpg', rawurlencode( $sku ), rawurlencode( $key ) );
                }
                if ( count( $urls ) >= 4 ) {
                    break;
                }
            }
        }

        if ( empty( $urls ) && ! empty( $hit['image_url'] ) ) {
            $urls[] = (string) $hit['image_url'];
        }

        return array_values( array_unique( array_slice( $urls, 0, 4 ) ) );
    }

    /**
     * @param mixed ...$values
     */
    private static function first_string( ...$values ): string {
        foreach ( $values as $value ) {
            if ( is_string( $value ) && trim( $value ) !== '' ) {
                return trim( $value );
            }
        }
        return '';
    }

    private static function jitter( int $min_seconds, int $max_seconds ): void {
        sleep( random_int( $min_seconds, $max_seconds ) );
    }
}
