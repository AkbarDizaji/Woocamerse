<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gulfino_Noon_Product_Importer
 *
 * Creates WooCommerce products from scraped noon.com data.
 */
class Gulfino_Noon_Product_Importer {

    const META_NOON_ID = '_noon_product_id';
    const META_SOURCE_URL = '_noon_source_url';
    const META_PRICE_OMR = '_noon_price_omr';

    /**
     * Check if a noon SKU already exists in the database.
     */
    public static function exists( string $sku ): bool {
        if ( $sku === '' ) {
            return false;
        }

        $existing = wc_get_products(
            [
                'limit'      => 1,
                'status'     => [ 'publish', 'draft', 'pending', 'private' ],
                'meta_key'   => self::META_NOON_ID,
                'meta_value' => $sku,
                'return'     => 'ids',
            ]
        );

        return ! empty( $existing );
    }

    /**
     * Import a single product.
     *
     * @param array<string, mixed> $product
     * @return int|false Product ID on success.
     */
    public static function import( array $product, float $omr_toman_rate ) {
        if ( empty( $product['sku'] ) ) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $title_fa       = Gulfino_Noon_Translator::to_persian( (string) $product['title_en'] );
        $description_fa = Gulfino_Noon_Translator::to_persian( (string) $product['description_en'] );
        $category_fa    = Gulfino_Noon_Translator::to_persian( (string) $product['category_en'] );

        if ( $category_fa === '' ) {
            $category_fa = (string) ( $product['category_fa'] ?? 'محصولات noon' );
        }

        $price_toman = Gulfino_Noon_Currency_Converter::convert_price(
            (float) $product['price_omr'],
            $omr_toman_rate
        );

        $category_id = self::get_or_create_category( $category_fa );

        $wc_product = new WC_Product_Simple();
        $wc_product->set_name( $title_fa );
        $wc_product->set_description( wpautop( $description_fa ) );
        $wc_product->set_short_description( wp_trim_words( $description_fa, 30, '...' ) );
        $wc_product->set_regular_price( (string) $price_toman );
        $wc_product->set_status( 'publish' );
        $wc_product->set_catalog_visibility( 'visible' );
        $wc_product->set_manage_stock( false );
        $wc_product->set_stock_status( 'instock' );

        if ( $category_id ) {
            $wc_product->set_category_ids( [ $category_id ] );
        }

        $product_id = $wc_product->save();
        if ( ! $product_id ) {
            return false;
        }

        update_post_meta( $product_id, self::META_NOON_ID, sanitize_text_field( (string) $product['sku'] ) );
        update_post_meta( $product_id, self::META_PRICE_OMR, (float) $product['price_omr'] );

        if ( ! empty( $product['source_url'] ) ) {
            update_post_meta( $product_id, self::META_SOURCE_URL, esc_url_raw( (string) $product['source_url'] ) );
        }

        self::attach_images( $product_id, (array) ( $product['images'] ?? [] ), $title_fa );

        return $product_id;
    }

    /**
     * Get or create a product category by Persian name.
     */
    private static function get_or_create_category( string $name ): int {
        $name = trim( $name );
        if ( $name === '' ) {
            return 0;
        }

        $existing = get_term_by( 'name', $name, 'product_cat' );
        if ( $existing && ! is_wp_error( $existing ) ) {
            return (int) $existing->term_id;
        }

        $result = wp_insert_term(
            $name,
            'product_cat',
            [
                'slug' => sanitize_title( $name ),
            ]
        );

        if ( is_wp_error( $result ) ) {
            Gulfino_Noon_Logger::error( 'Failed to create category: ' . $result->get_error_message(), [ 'name' => $name ] );
            return 0;
        }

        return (int) $result['term_id'];
    }

    /**
     * Sideload up to 4 images and attach to product.
     *
     * @param array<int, string> $image_urls
     */
    private static function attach_images( int $product_id, array $image_urls, string $title ): void {
        $attachment_ids = [];

        foreach ( array_slice( $image_urls, 0, 4 ) as $index => $url ) {
            if ( ! is_string( $url ) || $url === '' ) {
                continue;
            }

            $tmp = download_url( $url );
            if ( is_wp_error( $tmp ) ) {
                Gulfino_Noon_Logger::error( 'Image download failed: ' . $tmp->get_error_message(), [ 'url' => $url ] );
                continue;
            }

            $filename = sanitize_file_name( sprintf( 'noon-%d-%d.jpg', $product_id, $index + 1 ) );
            $file     = [
                'name'     => $filename,
                'tmp_name' => $tmp,
            ];

            $attachment_id = media_handle_sideload( $file, $product_id, $title );
            if ( is_wp_error( $attachment_id ) ) {
                @unlink( $tmp );
                Gulfino_Noon_Logger::error( 'Image sideload failed: ' . $attachment_id->get_error_message(), [ 'url' => $url ] );
                continue;
            }

            $attachment_ids[] = (int) $attachment_id;
        }

        if ( empty( $attachment_ids ) ) {
            return;
        }

        set_post_thumbnail( $product_id, $attachment_ids[0] );

        if ( count( $attachment_ids ) > 1 ) {
            update_post_meta( $product_id, '_product_image_gallery', implode( ',', array_slice( $attachment_ids, 1 ) ) );
        }
    }
}
