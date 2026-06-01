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
            'url'              => 'https://www.noon.com/oman-en/health/?sort_by=bestselling',
            'slug'             => 'health-beauty',
            'category_fa'      => 'بهداشت و مراقبت شخصی',
            'category_en'      => 'Health & Personal Care',
        ],
        'fragrances' => [
            'url'              => 'https://www.noon.com/oman-en/beauty/fragrances/?sort_by=bestselling',
            'slug'             => 'fragrances',
            'category_fa'      => 'عطر و ادکلن',
            'category_en'      => 'Perfumes & Fragrances',
        ],
    ];

    const MAX_RETRIES = 3;

    const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
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

        $hits = self::extract_hits( $html );
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
        $last_error = null;

        for ( $attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++ ) {
            if ( $attempt > 1 ) {
                $delay = $attempt * 5; // 10s, 15s back-off
                Gulfino_Noon_Logger::sync( sprintf( 'Retry %d/%d for %s (waiting %ds)', $attempt, self::MAX_RETRIES, $url, $delay ), 'WARN' );
                sleep( $delay );
            }

            try {
                $result = self::do_request( $url );
                return $result;
            } catch ( RuntimeException $e ) {
                $last_error = $e;
                Gulfino_Noon_Logger::error( sprintf( 'Request attempt %d failed: %s', $attempt, $e->getMessage() ) );
            }
        }

        throw new RuntimeException( 'All request attempts failed. Last error: ' . ( $last_error ? $last_error->getMessage() : 'unknown' ) );
    }

    private static function do_request( string $url ): string {
        $settings    = get_option( Gulfino_Noon_Admin::OPTION_KEY, [] );
        $scraper_key = trim( $settings['scraper_api_key'] ?? '' );

        // Route through ScraperAPI if a key is configured.
        if ( $scraper_key !== '' ) {
            $fetch_url = add_query_arg(
                [
                    'api_key'        => $scraper_key,
                    'url'            => rawurlencode( $url ),
                    'render'         => 'false',
                    'country_code'   => 'us',
                ],
                'https://api.scraperapi.com/'
            );
            Gulfino_Noon_Logger::sync( 'Using ScraperAPI proxy.' );
        } else {
            $fetch_url = $url;
        }

        $response = wp_remote_get(
            $fetch_url,
            [
                'timeout'    => 60,
                'user-agent' => self::USER_AGENTS[ array_rand( self::USER_AGENTS ) ],
                'headers'    => [
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Cache-Control'   => 'no-cache',
                    'Referer'         => 'https://www.google.com/',
                ],
            ]
        );

        if ( is_wp_error( $response ) ) {
            throw new RuntimeException( 'HTTP request failed: ' . $response->get_error_message() );
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 403 || $code === 429 ) {
            throw new RuntimeException( sprintf( 'HTTP %d — server blocked the request for URL: %s', $code, $url ) );
        }
        if ( $code < 200 || $code >= 300 ) {
            throw new RuntimeException( sprintf( 'HTTP %d for URL: %s', $code, $url ) );
        }

        $body = (string) wp_remote_retrieve_body( $response );
        if ( strlen( $body ) < 500 ) {
            throw new RuntimeException( sprintf( 'Response too short (%d bytes) — likely blocked.', strlen( $body ) ) );
        }

        return $body;
    }

    /**
     * Extract product "hits" from noon's RSC streaming chunks.
     *
     * noon migrated from the Next.js Pages Router (where catalog data lived in a
     * single <script id="__NEXT_DATA__"> blob) to the App Router, which streams
     * data as JSON-encoded string chunks: self.__next_f.push([N,"<escaped json>"]).
     * We decode and concatenate those chunks, then pull every product object that
     * carries a "sku" key out of the combined payload.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function extract_hits( string $html ): array {
        // Each chunk's second element is a JSON-encoded string literal. A lazy
        // match is used deliberately: a strict string-literal pattern exhausts
        // the PCRE/JIT stack on noon's multi-hundred-KB data chunk.
        if ( ! preg_match_all( '/self\.__next_f\.push\(\[\d+,(".*?")\]\)/s', $html, $matches ) ) {
            return [];
        }

        $payload = '';
        foreach ( $matches[1] as $chunk ) {
            $decoded = json_decode( $chunk, true ); // JS string literal -> raw fragment
            if ( is_string( $decoded ) ) {
                $payload .= $decoded;
            }
        }

        if ( $payload === '' ) {
            return [];
        }

        $hits   = [];
        $seen   = [];
        $offset = 0;
        while ( ( $pos = strpos( $payload, '"sku":"', $offset ) ) !== false ) {
            $offset = $pos + 7;

            $object = self::extract_json_object( $payload, $pos );
            if ( $object === null ) {
                continue;
            }

            $hit = json_decode( $object, true );
            if ( ! is_array( $hit ) || empty( $hit['sku'] ) || empty( $hit['name'] ) ) {
                continue;
            }

            $sku = (string) $hit['sku'];
            if ( isset( $seen[ $sku ] ) ) {
                continue;
            }
            $seen[ $sku ] = true;
            $hits[]       = $hit;
        }

        return $hits;
    }

    /**
     * Return the smallest balanced {...} object enclosing the given position.
     *
     * Walks backward (depth-counting) to the opening brace of the enclosing
     * object, then forward (string-aware) to its matching close.
     */
    private static function extract_json_object( string $s, int $pos ): ?string {
        $depth = 0;
        $start = -1;
        for ( $i = $pos; $i >= 0; $i-- ) {
            $ch = $s[ $i ];
            if ( $ch === '}' ) {
                $depth++;
            } elseif ( $ch === '{' ) {
                if ( $depth === 0 ) {
                    $start = $i;
                    break;
                }
                $depth--;
            }
        }
        if ( $start < 0 ) {
            return null;
        }

        $depth   = 0;
        $in_str  = false;
        $escaped = false;
        $len     = strlen( $s );
        for ( $j = $start; $j < $len; $j++ ) {
            $ch = $s[ $j ];
            if ( $in_str ) {
                if ( $escaped ) {
                    $escaped = false;
                } elseif ( $ch === '\\' ) {
                    $escaped = true;
                } elseif ( $ch === '"' ) {
                    $in_str = false;
                }
                continue;
            }
            if ( $ch === '"' ) {
                $in_str = true;
            } elseif ( $ch === '{' ) {
                $depth++;
            } elseif ( $ch === '}' ) {
                $depth--;
                if ( $depth === 0 ) {
                    return substr( $s, $start, $j - $start + 1 );
                }
            }
        }

        return null;
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

        if ( $url ) {
            if ( strpos( $url, 'http' ) === 0 ) {
                // Already absolute.
                $url = $url;
            } elseif ( strpos( $url, '/' ) === 0 ) {
                $url = 'https://www.noon.com' . $url;
            } else {
                // App Router product objects carry a bare slug, e.g. "yara-edp-100ml".
                $url = 'https://www.noon.com/oman-en/' . ltrim( $url, '/' ) . '/' . rawurlencode( (string) $sku ) . '/p/';
            }
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
                } elseif ( strpos( $key, 'pzsku/' ) === 0 || strpos( $key, 'pnsku/' ) === 0 ) {
                    // App Router keys already carry the full CDN path segment.
                    $urls[] = 'https://f.nooncdn.com/p/' . $key . '.jpg';
                } else {
                    // Legacy bare image key.
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
