<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Gulfino_Torob_Validator
 *
 * Validates each WooCommerce product before it is included in the feed.
 * Products that fail validation are excluded and logged.
 */
class Gulfino_Torob_Validator {

    /** @var array  Validation errors for the last checked product */
    private array $errors = [];

    /**
     * Validate a single product.
     *
     * @param  WC_Product $product
     * @return bool  true = valid, false = should be excluded
     */
    public function validate( WC_Product $product ): bool {
        $this->errors = [];
        $id = $product->get_id();

        $this->check_price( $product );
        $this->check_image( $product );
        $this->check_title( $product );
        $this->check_url( $product );
        $this->check_status( $product );

        if ( ! empty( $this->errors ) ) {
            foreach ( $this->errors as $reason ) {
                Gulfino_Torob_Logger::invalid_product( $id, $reason );
            }
            return false;
        }

        return true;
    }

    public function get_errors(): array {
        return $this->errors;
    }

    /* ---- individual checks ---- */

    private function check_price( WC_Product $p ): void {
        $price = (float) $p->get_price();
        if ( $price <= 0 ) {
            $this->errors[] = 'price is zero or missing';
        }
        $regular = (float) $p->get_regular_price();
        $sale    = (float) $p->get_sale_price();
        if ( $sale > 0 && $sale >= $regular ) {
            $this->errors[] = 'sale_price must be less than regular_price';
        }
    }

    private function check_image( WC_Product $p ): void {
        $img_id = $p->get_image_id();
        if ( ! $img_id ) {
            $this->errors[] = 'missing product image';
            return;
        }
        $url = wp_get_attachment_url( $img_id );
        if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            $this->errors[] = 'invalid image URL';
        }
    }

    private function check_title( WC_Product $p ): void {
        $title = trim( $p->get_name() );
        if ( strlen( $title ) < 3 ) {
            $this->errors[] = 'title too short (min 3 chars)';
        }
    }

    private function check_url( WC_Product $p ): void {
        $url = $p->get_permalink();
        if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            $this->errors[] = 'invalid product URL';
        }
    }

    private function check_status( WC_Product $p ): void {
        if ( ! $p->is_purchasable() ) {
            $this->errors[] = 'product is not purchasable';
        }
    }

    /* ---- batch validate all published products ---- */

    public static function run_batch(): array {
        $ids = wc_get_products( [
            'status'  => 'publish',
            'limit'   => -1,
            'return'  => 'ids',
        ] );

        $v       = new self();
        $valid   = [];
        $invalid = [];

        foreach ( $ids as $id ) {
            $p = wc_get_product( $id );
            if ( ! $p ) continue;

            if ( $v->validate( $p ) ) {
                $valid[] = $id;
            } else {
                $invalid[ $id ] = $v->get_errors();
            }
        }

        return [ 'valid' => $valid, 'invalid' => $invalid ];
    }
}
