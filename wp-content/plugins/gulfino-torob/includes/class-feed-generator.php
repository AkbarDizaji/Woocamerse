<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gulfino_Torob_Feed
 *
 * Generates a fully Torob-compliant XML feed and serves it with
 * proper caching, GZIP compression and security headers.
 *
 * Feed URL:  /torob-feed.xml
 * Cache TTL: 15 minutes (refreshed by WP-Cron)
 */
class Gulfino_Torob_Feed {

    const CACHE_KEY     = 'gtorob_feed_xml';
    const CACHE_META_KEY = 'gtorob_feed_meta';
    const CACHE_TTL     = 900; // 15 minutes

    /* ---------------------------------------------------------------- */
    /*  Rewrite rule registration                                        */
    /* ---------------------------------------------------------------- */

    public static function register_rewrite(): void {
        add_rewrite_rule( '^torob-feed\.xml$', 'index.php?gtorob_feed=1', 'top' );
    }

    /* ---------------------------------------------------------------- */
    /*  HTTP response                                                    */
    /* ---------------------------------------------------------------- */

    public static function serve(): void {
        $xml = get_transient( self::CACHE_KEY );

        if ( false === $xml ) {
            $xml = self::generate();
            if ( is_wp_error( $xml ) ) {
                http_response_code( 503 );
                header( 'Content-Type: text/plain; charset=UTF-8' );
                echo 'Feed generation failed. Please try again later.';
                Gulfino_Torob_Logger::error( 'Feed serve failed', [ 'msg' => $xml->get_error_message() ] );
                return;
            }
            set_transient( self::CACHE_KEY, $xml, self::CACHE_TTL );
        }

        // Security & cache headers
        header( 'Content-Type: application/xml; charset=UTF-8' );
        header( 'X-Robots-Tag: noindex' );
        header( 'Cache-Control: public, max-age=900' );
        header( 'Vary: Accept-Encoding' );
        header( 'X-Feed-Generator: Gulfino-Torob/' . GTOROB_VERSION );

        // GZIP if supported
        if ( extension_loaded( 'zlib' ) && ! headers_sent() ) {
            ob_start( 'ob_gzhandler' );
        }

        echo $xml;
    }

    /* ---------------------------------------------------------------- */
    /*  Cache warming (called by cron)                                   */
    /* ---------------------------------------------------------------- */

    public static function regenerate_cache(): void {
        Gulfino_Torob_Logger::feed( 'Cache regeneration started (cron)' );

        $xml = self::generate();

        if ( is_wp_error( $xml ) ) {
            Gulfino_Torob_Logger::error( 'Cache regen failed', [ 'msg' => $xml->get_error_message() ] );
            return;
        }

        set_transient( self::CACHE_KEY, $xml, self::CACHE_TTL );
        set_transient( self::CACHE_META_KEY, [
            'generated_at'   => current_time( 'mysql' ),
            'product_count'  => self::last_count(),
            'invalid_count'  => self::last_invalid_count(),
            'size_kb'        => round( strlen( $xml ) / 1024, 1 ),
        ], self::CACHE_TTL * 4 );

        Gulfino_Torob_Logger::feed( 'Cache regenerated successfully. Products: ' . self::last_count() );
    }

    /* ---------------------------------------------------------------- */
    /*  XML generation                                                   */
    /* ---------------------------------------------------------------- */

    private static int $_last_count   = 0;
    private static int $_last_invalid = 0;

    public static function last_count(): int   { return self::$_last_count; }
    public static function last_invalid_count(): int { return self::$_last_invalid; }

    public static function generate(): string|WP_Error {
        try {
            $opts = get_option( 'gtorob_settings', [] );

            $shipping_cost = $opts['shipping_cost'] ?? '۰ (رایگان)';
            $shipping_time = $opts['shipping_time'] ?? '۷ تا ۱۰ روز کاری (ارسال از امارات)';
            $store_name    = get_bloginfo( 'name' );
            $site_url      = trailingslashit( get_option( 'siteurl' ) );

            // Fetch ALL published products
            $products = wc_get_products( [
                'status'  => 'publish',
                'limit'   => -1,
                'type'    => [ 'simple', 'variable' ],
                'return'  => 'objects',
            ] );

            $validator = new Gulfino_Torob_Validator();
            $valid_count   = 0;
            $invalid_count = 0;

            // Build XML with DOMDocument for proper escaping
            $dom = new DOMDocument( '1.0', 'UTF-8' );
            $dom->formatOutput = true;

            $root = $dom->createElement( 'products' );
            $root->setAttribute( 'store', esc_attr( $store_name ) );
            $root->setAttribute( 'generated', date( 'c' ) );
            $dom->appendChild( $root );

            foreach ( $products as $product ) {
                // Expand variable products into their variations
                $items = $product->is_type( 'variable' )
                    ? self::expand_variable( $product )
                    : [ $product ];

                foreach ( $items as $item ) {
                    if ( ! $validator->validate( $item ) ) {
                        $invalid_count++;
                        continue;
                    }

                    $node = self::build_product_node( $dom, $item, $shipping_time, $shipping_cost );
                    $root->appendChild( $node );
                    $valid_count++;
                }
            }

            self::$_last_count   = $valid_count;
            self::$_last_invalid = $invalid_count;

            return $dom->saveXML();

        } catch ( Throwable $e ) {
            Gulfino_Torob_Logger::error( 'generate() exception', [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] );
            return new WP_Error( 'gtorob_generate_failed', $e->getMessage() );
        }
    }

    /* ---------------------------------------------------------------- */
    /*  Build a single <product> DOM node                               */
    /* ---------------------------------------------------------------- */

    private static function build_product_node(
        DOMDocument $dom,
        WC_Product  $p,
        string      $shipping_time,
        string      $shipping_cost
    ): DOMElement {

        $node = $dom->createElement( 'product' );

        $regular_price = (float) $p->get_regular_price();
        $sale_price    = (float) $p->get_sale_price();
        $current_price = (float) $p->get_price();

        // Availability
        $in_stock  = $p->is_in_stock();
        $backorder = $p->backorders_allowed();
        $avail     = $in_stock ? 'true' : ( $backorder ? 'preorder' : 'false' );

        // Stock count
        $stock_qty = $p->get_stock_quantity();
        if ( $stock_qty === null ) $stock_qty = $in_stock ? 99 : 0;

        // Attributes: color, size, brand
        $color  = self::get_attribute( $p, [ 'رنگ', 'color', 'pa_color', 'pa_rang' ] );
        $size   = self::get_attribute( $p, [ 'سایز', 'size', 'pa_size', 'pa_saiz' ] );
        $brand  = self::get_attribute( $p, [ 'برند', 'brand', 'pa_brand' ] )
                  ?: get_option( 'gtorob_settings', [] )['default_brand'] ?? 'Gulfino';

        // GTIN / barcode
        $gtin = $p->get_meta( '_gtin' ) ?: $p->get_meta( '_barcode' ) ?: '';

        // Category
        $terms    = get_the_terms( $p->get_id(), 'product_cat' );
        $category = '';
        if ( $terms && ! is_wp_error( $terms ) ) {
            $category = implode( ' > ', array_map( fn($t) => $t->name, $terms ) );
        }

        // Image
        $img_id  = $p->get_image_id();
        $img_url = $img_id ? wp_get_attachment_url( $img_id ) : '';

        // Description
        $desc = wp_strip_all_tags( $p->get_short_description() ?: $p->get_description() );
        $desc = wp_trim_words( $desc, 50 );

        // Build child elements
        $fields = [
            'id'               => (string) $p->get_id(),
            'title'            => $p->get_name(),
            'description'      => $desc ?: $p->get_name(),
            'category'         => $category,
            'brand'            => $brand,
            'price'            => (string) (int) $regular_price,
            'discounted_price' => $sale_price > 0 ? (string) (int) $sale_price : '',
            'currency'         => 'IRR',
            'availability'     => $avail,
            'stock_count'      => (string) max( 0, (int) $stock_qty ),
            'image_url'        => $img_url,
            'product_url'      => $p->get_permalink(),
            'shipping_time'    => $shipping_time,
            'shipping_cost'    => $shipping_cost,
            'sku'              => $p->get_sku() ?: (string) $p->get_id(),
            'gtin'             => $gtin,
            'color'            => $color,
            'size'             => $size,
        ];

        foreach ( $fields as $tag => $value ) {
            if ( $value === '' && in_array( $tag, [ 'gtin', 'color', 'size', 'discounted_price' ], true ) ) {
                continue; // omit optional empty fields
            }
            $el = $dom->createElement( $tag );
            $el->appendChild( $dom->createCDATASection( (string) $value ) );
            $node->appendChild( $el );
        }

        return $node;
    }

    /* ---------------------------------------------------------------- */
    /*  Helpers                                                          */
    /* ---------------------------------------------------------------- */

    /** Expand a variable product into all its in-stock variations */
    private static function expand_variable( WC_Product_Variable $parent ): array {
        $variations = [];
        foreach ( $parent->get_available_variations( 'objects' ) as $v ) {
            // Merge parent data into variation for complete info
            $variations[] = $v;
        }
        return $variations ?: [ $parent ];
    }

    /** Get first matching product attribute value */
    private static function get_attribute( WC_Product $p, array $keys ): string {
        foreach ( $keys as $k ) {
            $val = $p->get_attribute( $k );
            if ( $val ) return $val;
        }
        return '';
    }
}
