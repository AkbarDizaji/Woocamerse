<?php
/**
 * Single Product — Gulfino custom design.
 *
 * Full-page override (mirrors archive-product.php): renders the header, a custom
 * two-column product layout (gallery + info), description, and related products,
 * then the footer. WooCommerce's own add-to-cart form is reused so simple,
 * variable and grouped products all keep working.
 *
 * @package WooCommerce\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	if ( ! $product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		continue;
	}

	$title   = get_the_title();
	$reg     = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	$on_sale = $product->is_on_sale();
	$pct     = ( $reg > 0 && $sale > 0 ) ? round( ( $reg - $sale ) / $reg * 100 ) : 0;
	$rating  = (float) $product->get_average_rating();
	$rcount  = (int) $product->get_review_count();

	// Gallery: featured image first, then gallery images.
	$image_ids = array();
	if ( $product->get_image_id() ) {
		$image_ids[] = $product->get_image_id();
	}
	$image_ids = array_merge( $image_ids, $product->get_gallery_image_ids() );

	$placeholder = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80';
	$main_img    = $image_ids ? wp_get_attachment_image_url( $image_ids[0], 'large' ) : $placeholder;

	// Star markup.
	$stars = '';
	for ( $s = 1; $s <= 5; $s++ ) {
		$stars .= '<span class="' . ( $s <= round( $rating ) ? 's' : 'g' ) . '">★</span>';
	}

	$cats = wc_get_product_category_list( $product->get_id(), '، ' );
	?>

<div class="g-pdp" dir="rtl">

	<nav class="g-pdp-crumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a>
		<span>›</span>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">فروشگاه</a>
		<span>›</span>
		<span class="cur"><?php echo esc_html( $title ); ?></span>
	</nav>

	<div class="g-pdp-main">

		<!-- GALLERY -->
		<div class="g-pdp-gallery">
			<div class="g-pdp-stage">
				<?php if ( $pct > 0 ) : ?><span class="g-pdp-disc">-<?php echo esc_html( $pct ); ?>%</span><?php endif; ?>
				<img id="gPdpMain" src="<?php echo esc_url( $main_img ); ?>" alt="<?php echo esc_attr( $title ); ?>">
			</div>
			<?php if ( count( $image_ids ) > 1 ) : ?>
			<div class="g-pdp-thumbs">
				<?php foreach ( $image_ids as $i => $id ) :
					$thumb = wp_get_attachment_image_url( $id, 'woocommerce_thumbnail' );
					$full  = wp_get_attachment_image_url( $id, 'large' );
					if ( ! $thumb ) {
						continue;
					}
					?>
					<button type="button" class="g-pdp-thumb<?php echo 0 === $i ? ' on' : ''; ?>" data-full="<?php echo esc_url( $full ); ?>">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $title ); ?>">
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<!-- INFO -->
		<div class="g-pdp-info">
			<?php if ( $cats ) : ?><div class="g-pdp-cat"><?php echo wp_kses_post( $cats ); ?></div><?php endif; ?>
			<h1 class="g-pdp-title"><?php echo esc_html( $title ); ?></h1>

			<div class="g-pdp-rating">
				<span class="g-pdp-stars"><?php echo $stars; // phpcs:ignore ?></span>
				<span class="g-pdp-rcount">
					<?php echo $rcount > 0 ? esc_html( sprintf( '(%d نظر)', $rcount ) ) : 'بدون نظر'; ?>
				</span>
			</div>

			<div class="g-pdp-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

			<?php
			$short = $product->get_short_description();
			if ( $short ) : ?>
				<div class="g-pdp-short"><?php echo wp_kses_post( wpautop( $short ) ); ?></div>
			<?php endif; ?>

			<div class="g-pdp-stock <?php echo $product->is_in_stock() ? 'in' : 'out'; ?>">
				<?php if ( $product->is_in_stock() ) : ?>
					<svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
					موجود در انبار
				<?php else : ?>
					ناموجود
				<?php endif; ?>
			</div>

			<div class="g-pdp-cart">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>

			<ul class="g-pdp-trust">
				<li>
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					ضمانت اصالت کالا
				</li>
				<li>
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
					ارسال مستقیم از خلیج فارس
				</li>
				<li>
					<svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path d="M3 10h11M9 21V3m0 0L4 8m5-5l5 5M21 14h-6m0 0l5 5m-5-5l5-5"/></svg>
					۷ روز ضمانت بازگشت
				</li>
			</ul>
		</div>
	</div>

	<!-- DESCRIPTION -->
	<?php
	$content = apply_filters( 'the_content', $product->get_description() );
	if ( $content ) : ?>
	<section class="g-pdp-desc">
		<h2 class="g-pdp-sec-title">معرفی محصول</h2>
		<div class="g-pdp-desc-body"><?php echo wp_kses_post( $content ); ?></div>
	</section>
	<?php endif; ?>

	<!-- RELATED -->
	<?php
	$related_ids = wc_get_related_products( $product->get_id(), 4 );
	if ( $related_ids ) : ?>
	<section class="g-pdp-related">
		<h2 class="g-pdp-sec-title">محصولات مرتبط</h2>
		<div class="g-pdp-rel-grid">
			<?php foreach ( $related_ids as $rid ) :
				$rp = wc_get_product( $rid );
				if ( ! $rp ) {
					continue;
				}
				$rlink = get_permalink( $rid );
				$rimg  = get_the_post_thumbnail_url( $rid, 'woocommerce_thumbnail' ) ?: $placeholder;
				?>
				<div class="g-rel-card">
					<a href="<?php echo esc_url( $rlink ); ?>" class="g-rel-img">
						<img src="<?php echo esc_url( $rimg ); ?>" alt="<?php echo esc_attr( $rp->get_name() ); ?>" loading="lazy">
					</a>
					<div class="g-rel-body">
						<a href="<?php echo esc_url( $rlink ); ?>" class="g-rel-name"><?php echo esc_html( $rp->get_name() ); ?></a>
						<div class="g-rel-price"><?php echo wp_kses_post( $rp->get_price_html() ); ?></div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

</div>

<style>
.g-pdp{max-width:1380px;margin:0 auto;padding:34px 30px 70px;font-family:'Vazirmatn',sans-serif;color:var(--navy)}
.g-pdp-crumb{display:flex;align-items:center;gap:8px;font-size:13px;color:#8a93a6;margin-bottom:28px}
.g-pdp-crumb a:hover{color:var(--cyan)}
.g-pdp-crumb .cur{color:var(--navy);font-weight:700}

.g-pdp-main{display:grid;grid-template-columns:1fr 1fr;gap:50px;align-items:start}

/* GALLERY */
.g-pdp-stage{position:relative;border-radius:var(--radius);overflow:hidden;background:var(--gray);aspect-ratio:1/1;box-shadow:var(--shadow)}
.g-pdp-stage img{width:100%;height:100%;object-fit:cover}
.g-pdp-disc{position:absolute;top:16px;right:16px;z-index:2;background:#e53935;color:#fff;font-size:14px;font-weight:900;padding:6px 12px;border-radius:9px;line-height:1}
.g-pdp-thumbs{display:flex;gap:12px;margin-top:14px;flex-wrap:wrap}
.g-pdp-thumb{width:78px;height:78px;border-radius:14px;overflow:hidden;border:2px solid #eef0f5;background:var(--gray);cursor:pointer;padding:0;transition:border-color .2s}
.g-pdp-thumb img{width:100%;height:100%;object-fit:cover}
.g-pdp-thumb.on,.g-pdp-thumb:hover{border-color:var(--cyan)}

/* INFO */
.g-pdp-cat{font-size:13px;font-weight:700;color:var(--cyan);margin-bottom:10px}
.g-pdp-cat a{color:inherit}
.g-pdp-title{font-size:30px;font-weight:900;line-height:1.5;margin:0 0 16px}
.g-pdp-rating{display:flex;align-items:center;gap:8px;margin-bottom:20px}
.g-pdp-stars .s{color:#FFB300;font-size:17px}
.g-pdp-stars .g{color:#ddd;font-size:17px}
.g-pdp-rcount{font-size:13px;color:#8a93a6}
.g-pdp-price{font-size:28px;font-weight:900;color:var(--navy);margin-bottom:22px}
.g-pdp-price del{font-size:18px;color:#c2c8d2;font-weight:600;margin-left:10px}
.g-pdp-price ins{text-decoration:none}
.g-pdp-short{font-size:15px;line-height:2;color:#5a6478;margin-bottom:22px;border-right:3px solid var(--gray);padding-right:16px}
.g-pdp-stock{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:700;padding:7px 14px;border-radius:30px;margin-bottom:24px}
.g-pdp-stock.in{background:rgba(8,183,200,.12);color:#069aa8}
.g-pdp-stock.out{background:#fdecea;color:#e53935}

/* Reuse + restyle WooCommerce add-to-cart form */
.g-pdp-cart{margin-bottom:26px}
.g-pdp-cart form.cart{display:flex;flex-wrap:wrap;align-items:center;gap:14px;margin:0}
.g-pdp-cart .quantity{display:flex;align-items:center}
.g-pdp-cart .quantity input.qty{width:74px;height:54px;text-align:center;font-family:'Vazirmatn',sans-serif;font-size:16px;font-weight:700;color:var(--navy);border:1.5px solid #e0e5ec;border-radius:14px;background:#fff;outline:none}
.g-pdp-cart .quantity input.qty:focus{border-color:var(--cyan)}
.g-pdp-cart button.single_add_to_cart_button,
.g-pdp-cart .single_add_to_cart_button{flex:1;min-width:220px;height:54px;display:inline-flex;align-items:center;justify-content:center;gap:9px;background:var(--cyan);color:#fff;border:none;border-radius:14px;font-family:'Vazirmatn',sans-serif;font-size:16px;font-weight:800;cursor:pointer;transition:all .25s;box-shadow:0 10px 24px rgba(8,183,200,.28)}
.g-pdp-cart button.single_add_to_cart_button:hover{background:var(--navy);box-shadow:0 12px 28px rgba(7,27,59,.25)}
.g-pdp-cart .variations{width:100%;border-collapse:collapse;margin-bottom:6px}
.g-pdp-cart .variations td,.g-pdp-cart .variations th{padding:8px 0;text-align:right;vertical-align:middle}
.g-pdp-cart .variations label{font-weight:700;font-size:14px}
.g-pdp-cart .variations select{font-family:'Vazirmatn',sans-serif;font-size:14px;padding:11px 14px;border:1.5px solid #e0e5ec;border-radius:12px;background:#fff;outline:none;min-width:200px}
.g-pdp-cart .woocommerce-variation-price{font-size:22px;font-weight:900;margin:10px 0}
.g-pdp-cart .reset_variations{font-size:13px;color:#8a93a6;margin-right:10px}

.g-pdp-trust{display:flex;flex-wrap:wrap;gap:10px 26px;padding:20px 0 0;border-top:1px solid #eef0f5;margin:0}
.g-pdp-trust li{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;color:#5a6478}
.g-pdp-trust svg{color:var(--cyan);flex-shrink:0}

/* DESCRIPTION */
.g-pdp-sec-title{font-size:22px;font-weight:900;margin:0 0 22px;position:relative;padding-bottom:14px}
.g-pdp-sec-title::after{content:"";position:absolute;bottom:0;right:0;width:54px;height:3px;background:var(--cyan);border-radius:3px}
.g-pdp-desc{margin-top:60px;background:var(--gray);border-radius:var(--radius);padding:38px 40px}
.g-pdp-desc-body{font-size:15px;line-height:2.1;color:#475066}
.g-pdp-desc-body p{margin-bottom:16px}
.g-pdp-desc-body img{border-radius:14px;margin:14px 0}

/* RELATED */
.g-pdp-related{margin-top:64px}
.g-pdp-rel-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px}
.g-rel-card{background:#fff;border-radius:18px;border:1px solid #eef0f5;box-shadow:0 4px 18px rgba(7,27,59,.04);overflow:hidden;transition:transform .35s,box-shadow .35s}
.g-rel-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(7,27,59,.10)}
.g-rel-img{display:block;aspect-ratio:1/1;overflow:hidden;background:var(--gray)}
.g-rel-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.g-rel-card:hover .g-rel-img img{transform:scale(1.07)}
.g-rel-body{padding:14px 16px 18px}
.g-rel-name{display:block;font-size:14px;font-weight:800;line-height:1.5;height:42px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:8px}
.g-rel-name:hover{color:var(--cyan)}
.g-rel-price{font-size:16px;font-weight:900;color:var(--navy)}

@media(max-width:980px){
	.g-pdp-main{grid-template-columns:1fr;gap:30px}
	.g-pdp-rel-grid{grid-template-columns:repeat(2,1fr)}
	.g-pdp-title{font-size:24px}
	.g-pdp-desc{margin-top:44px;padding:28px 24px}
	.g-pdp-related{margin-top:48px}
}
@media(max-width:560px){
	.g-pdp{padding:20px 18px 50px}
	.g-pdp-crumb{margin-bottom:18px;flex-wrap:wrap}
	.g-pdp-title{font-size:21px}
	.g-pdp-price{font-size:24px}
	.g-pdp-cart form.cart{gap:10px}
	.g-pdp-cart .quantity input.qty{width:64px;height:50px}
	/* Let the add-to-cart button take the full row under the quantity */
	.g-pdp-cart button.single_add_to_cart_button,
	.g-pdp-cart .single_add_to_cart_button{flex:1 1 100%;min-width:0;height:50px}
	.g-pdp-cart .variations select{min-width:0;width:100%}
	.g-pdp-thumb{width:64px;height:64px}
	.g-pdp-desc{margin-top:36px;padding:22px 18px}
	.g-pdp-sec-title{font-size:19px;margin-bottom:16px}
}
@media(max-width:400px){
	.g-pdp-rel-grid{grid-template-columns:1fr}
}
</style>

<script>
(function(){
	var main = document.getElementById('gPdpMain');
	var thumbs = document.querySelectorAll('.g-pdp-thumb');
	if(!main || !thumbs.length) return;
	thumbs.forEach(function(t){
		t.addEventListener('click', function(){
			var full = t.getAttribute('data-full');
			if(full) main.src = full;
			thumbs.forEach(function(x){ x.classList.remove('on'); });
			t.classList.add('on');
		});
	});
})();
</script>

<?php
endwhile;

get_footer();
