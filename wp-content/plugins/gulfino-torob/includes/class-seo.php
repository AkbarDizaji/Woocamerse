<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gulfino_Torob_SEO
 *
 * Injects on single product pages:
 *   - schema.org Product (JSON-LD) with Offer, AggregateRating, shipping info
 *   - OpenGraph product tags
 *   - Canonical URL
 *   - Crawlability meta tags
 *   - Dynamic XML sitemap entry hook
 *
 * Also injects shipping info UX block just above the Add-to-Cart button.
 */
class Gulfino_Torob_SEO {

    public static function init(): void {
        add_action( 'wp_head',                 [ __CLASS__, 'inject_head' ], 5 );
        add_action( 'woocommerce_single_product_summary',
                    [ __CLASS__, 'inject_shipping_info' ], 25 );
        add_filter( 'wp_sitemaps_posts_query_args', [ __CLASS__, 'ensure_products_in_sitemap' ] );
    }

    /* ---------------------------------------------------------------- */
    /*  HEAD – schema.org + OG + canonical                              */
    /* ---------------------------------------------------------------- */

    public static function inject_head(): void {
        if ( ! is_singular( 'product' ) ) return;

        global $post;
        $product = wc_get_product( $post->ID );
        if ( ! $product ) return;

        $opts          = get_option( 'gtorob_settings', [] );
        $shipping_time = $opts['shipping_time'] ?? '۷ تا ۱۰ روز کاری (ارسال از امارات)';
        $shipping_cost = $opts['shipping_cost'] ?? '0';

        $url           = $product->get_permalink();
        $name          = esc_attr( $product->get_name() );
        $desc          = esc_attr( wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ) );
        $img_id        = $product->get_image_id();
        $img_url       = $img_id ? wp_get_attachment_url( $img_id ) : '';
        $sku           = $product->get_sku() ?: (string) $product->get_id();
        $price         = (float) $product->get_price();
        $regular       = (float) $product->get_regular_price();
        $sale          = (float) $product->get_sale_price();
        $in_stock      = $product->is_in_stock();
        $avail         = $in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
        $rating        = (float) $product->get_average_rating();
        $review_count  = (int) $product->get_review_count();
        $brand_attr    = $product->get_attribute( 'brand' )
                         ?: $product->get_attribute( 'برند' )
                         ?: 'Gulfino';

        $terms    = get_the_terms( $post->ID, 'product_cat' );
        $category = $terms && ! is_wp_error( $terms ) ? $terms[0]->name : '';

        // ---- canonical ----
        echo "\n<!-- Gulfino Torob SEO -->\n";
        printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );
        echo '<meta name="robots" content="index, follow">' . "\n";

        // ---- OpenGraph ----
        printf( '<meta property="og:type"        content="product">' . "\n" );
        printf( '<meta property="og:title"       content="%s">' . "\n", $name );
        printf( '<meta property="og:description" content="%s">' . "\n", $desc );
        printf( '<meta property="og:url"         content="%s">' . "\n", esc_attr( $url ) );
        if ( $img_url ) {
            printf( '<meta property="og:image"  content="%s">' . "\n", esc_attr( $img_url ) );
        }
        printf( '<meta property="product:price:amount"   content="%s">' . "\n", $price );
        printf( '<meta property="product:price:currency" content="IRR">' . "\n" );

        // ---- Schema.org JSON-LD ----
        $schema = [
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            'name'        => $product->get_name(),
            'description' => $desc,
            'sku'         => $sku,
            'brand'       => [ '@type' => 'Brand', 'name' => $brand_attr ],
            'category'    => $category,
            'url'         => $url,
            'offers'      => [
                '@type'           => 'Offer',
                'url'             => $url,
                'priceCurrency'   => 'IRR',
                'price'           => (string)(int)$price,
                'itemCondition'   => 'https://schema.org/NewCondition',
                'availability'    => $avail,
                'shippingDetails' => [
                    '@type'              => 'OfferShippingDetails',
                    'shippingRate'       => [
                        '@type'    => 'MonetaryAmount',
                        'value'    => $shipping_cost,
                        'currency' => 'IRR',
                    ],
                    'deliveryTime' => [
                        '@type'   => 'ShippingDeliveryTime',
                        'businessDays' => [
                            '@type'    => 'OpeningHoursSpecification',
                            'dayOfWeek' => [
                                'https://schema.org/Monday',
                                'https://schema.org/Tuesday',
                                'https://schema.org/Wednesday',
                                'https://schema.org/Thursday',
                                'https://schema.org/Saturday',
                            ],
                        ],
                        'handlingTime' => [
                            '@type'   => 'QuantitativeValue',
                            'minValue' => 1,
                            'maxValue' => 2,
                            'unitCode' => 'd',
                        ],
                        'transitTime' => [
                            '@type'   => 'QuantitativeValue',
                            'minValue' => 7,
                            'maxValue' => 10,
                            'unitCode' => 'd',
                        ],
                    ],
                    'doesNotShip' => false,
                    'shippingDestination' => [
                        '@type'           => 'DefinedRegion',
                        'addressCountry'  => 'IR',
                    ],
                ],
            ],
        ];

        if ( $sale > 0 ) {
            $schema['offers']['priceValidUntil'] = date( 'Y-12-31' );
        }

        if ( $img_url ) {
            $schema['image'] = $img_url;
        }

        if ( $rating > 0 && $review_count > 0 ) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $review_count,
                'bestRating'  => 5,
                'worstRating' => 1,
            ];
        }

        echo '<script type="application/ld+json">'
             . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
             . "</script>\n";

        echo "<!-- /Gulfino Torob SEO -->\n";
    }

    /* ---------------------------------------------------------------- */
    /*  Shipping info UX block on product pages                         */
    /* ---------------------------------------------------------------- */

    public static function inject_shipping_info(): void {
        $opts = get_option( 'gtorob_settings', [] );
        $time = $opts['shipping_time'] ?? '۷ تا ۱۰ روز کاری';
        $cost = $opts['shipping_cost_label'] ?? 'محاسبه در مرحله تسویه';
        ?>
        <div class="gtorob-shipping-info">
            <style>
            .gtorob-shipping-info{
                background:#f0fafb; border:1.5px solid #08B7C8; border-radius:14px;
                padding:16px 20px; margin:18px 0; display:flex; flex-direction:column; gap:10px;
            }
            .gtorob-shipping-row{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:#071B3B;}
            .gtorob-shipping-row svg{flex-shrink:0;color:#08B7C8;}
            .gtorob-shipping-badge{
                display:inline-block;background:#08B7C8;color:#fff;font-size:11px;
                font-weight:800;padding:3px 10px;border-radius:20px;margin-right:6px;
            }
            </style>

            <div class="gtorob-shipping-row">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
                <span>
                    <span class="gtorob-shipping-badge">ارسال از امارات</span>
                    <?php echo esc_html( $time ); ?>
                </span>
            </div>

            <div class="gtorob-shipping-row">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>ضمانت اصالت کالا — مستقیم از نمایندگی رسمی</span>
            </div>

            <div class="gtorob-shipping-row">
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span>هزینه ارسال: <?php echo esc_html( $cost ); ?></span>
            </div>
        </div>
        <?php
    }

    /* ---------------------------------------------------------------- */
    /*  Ensure products appear in WP core sitemap                       */
    /* ---------------------------------------------------------------- */

    public static function ensure_products_in_sitemap( array $args ): array {
        // Core sitemap already handles products; hook for future filtering
        return $args;
    }
}

// Boot the class
Gulfino_Torob_SEO::init();
