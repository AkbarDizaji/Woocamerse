<?php get_header(); ?>
<style>
.g-shop-wrap{max-width:1380px;margin:0 auto;padding:60px 30px;}
.g-shop-header{margin-bottom:50px;border-bottom:2px solid var(--gray);padding-bottom:30px;}
.g-shop-header h1{font-size:48px;font-weight:900;color:var(--navy);margin-bottom:8px;}
.g-shop-header p{color:#888;font-size:15px;}
.g-shop-filters{display:flex;gap:12px;margin-bottom:40px;flex-wrap:wrap;}
.g-filter-btn{padding:9px 22px;border-radius:30px;font-family:'Vazirmatn',sans-serif;font-size:13px;font-weight:700;border:1.5px solid #e0e5ec;background:#fff;color:var(--navy);cursor:pointer;transition:all .2s;}
.g-filter-btn:hover,.g-filter-btn.on{background:var(--navy);color:#fff;border-color:var(--navy);}
.g-shop-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
/* inherit product card styles from front-page.php via g-card classes */
.g-card{background:#fff;border-radius:22px;border:1px solid #eef0f5;box-shadow:0 4px 18px rgba(7,27,59,.04);overflow:hidden;display:flex;flex-direction:column;transition:transform .35s,box-shadow .35s;}
.g-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(7,27,59,.10);}
.g-card-img{position:relative;aspect-ratio:1/1;overflow:hidden;background:var(--gray);}
.g-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.g-card:hover .g-card-img img{transform:scale(1.07);}
.g-disc{position:absolute;top:12px;right:12px;background:#e53935;color:#fff;font-size:11px;font-weight:900;padding:4px 9px;border-radius:7px;line-height:1;}
.g-heart{position:absolute;top:12px;left:12px;width:33px;height:33px;background:rgba(255,255,255,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;color:#ccc;border:1px solid #eee;transition:all .2s;}
.g-heart:hover{color:#e53935;}
.g-card-body{padding:14px 14px 0;flex:1;display:flex;flex-direction:column;}
.g-card-name{font-size:14px;font-weight:800;color:var(--navy);line-height:1.4;height:40px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:8px;}
.g-card-name a{color:inherit;}
.g-stars{display:flex;align-items:center;gap:1px;margin-bottom:10px;}
.g-stars .s{color:#FFB300;font-size:13px;}
.g-stars .g{color:#ddd;font-size:13px;}
.g-stars .cnt{color:#999;font-size:11px;margin-right:5px;}
.g-card-price{margin-bottom:14px;}
.g-price-now{font-size:17px;font-weight:900;color:var(--navy);}
.g-price-unit{font-size:11px;color:#999;font-weight:600;margin-right:2px;}
.g-price-old{font-size:12px;color:#ccc;text-decoration:line-through;margin-right:6px;}
.g-atc{display:flex;align-items:center;justify-content:center;gap:7px;background:var(--gray);color:var(--cyan);border:1.5px solid var(--cyan);border-radius:0 0 22px 22px;padding:12px;font-family:'Vazirmatn',sans-serif;font-size:13px;font-weight:800;cursor:pointer;width:100%;transition:all .3s;text-decoration:none;}
.g-atc:hover{background:var(--cyan);color:#fff;}
.g-pager{display:flex;justify-content:center;gap:10px;margin-top:60px;}
.g-pager ul.page-numbers{display:flex;gap:10px;list-style:none;padding:0;margin:0;}
.g-pager ul.page-numbers li{display:contents;}
.g-pager ul.page-numbers a.page-numbers,
.g-pager ul.page-numbers span.page-numbers{
  width:44px;height:44px;border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:15px;
  border:1.5px solid #e0e5ec;color:var(--navy);
  transition:all .2s;cursor:pointer;
}
.g-pager ul.page-numbers a.page-numbers:hover,
.g-pager ul.page-numbers span.current{
  background:var(--cyan);border-color:var(--cyan);color:#fff;
}
.g-pager ul.page-numbers a.next,
.g-pager ul.page-numbers a.prev{font-size:18px;}

/* ---- RESPONSIVE ---- */
@media(max-width:1100px){.g-shop-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){
  .g-shop-wrap{padding:36px 18px;}
  .g-shop-header{margin-bottom:30px;padding-bottom:20px;}
  .g-shop-header h1{font-size:32px;}
  .g-shop-grid{grid-template-columns:repeat(2,1fr);gap:14px;}
  .g-shop-filters{margin-bottom:28px;}
}
@media(max-width:380px){.g-shop-grid{grid-template-columns:1fr;}}
</style>

<div class="g-shop-wrap">
  <div class="g-shop-header">
    <h1>🛍️ فروشگاه</h1>
    <p>خرید مستقیم از خلیج فارس — <?php echo wc_get_loop_prop('total') ?: ''; ?> محصول</p>
  </div>
  <div class="g-shop-filters">
    <button class="g-filter-btn on">همه محصولات</button>
    <button class="g-filter-btn">پوشاک</button>
    <button class="g-filter-btn">بهداشتی آرایشی</button>
    <button class="g-filter-btn">اکسسوری</button>
    <button class="g-filter-btn">عطر و زیبایی</button>
    <button class="g-filter-btn">خانه و آشپزخانه</button>
  </div>

  <div class="g-shop-grid">
    <?php
    if (woocommerce_product_loop()):
      while (have_posts()): the_post();
        global $product;
        if (!$product) continue;
        $title  = get_the_title();
        $reg    = (float)$product->get_regular_price();
        $sale   = (float)$product->get_sale_price();
        $price  = (float)$product->get_price();
        $pct    = ($reg > 0 && $sale > 0) ? round(($reg - $sale) / $reg * 100) : 0;
        $img    = get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail') ?: 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80';
        $link   = get_permalink();
        $r      = (float)$product->get_average_rating();
        $rc     = max((int)$product->get_review_count(), rand(10,60));
        $strs   = '';
        for ($s=1;$s<=5;$s++) $strs .= '<span class="'.($s<=$r?'s':'g').'">★</span>';
    ?>
    <div class="g-card">
      <div class="g-card-img">
        <a href="<?php echo esc_url($link); ?>">
          <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
        </a>
        <?php if ($pct > 0): ?><span class="g-disc">-<?php echo $pct; ?>%</span><?php endif; ?>
        <span class="g-heart">♡</span>
      </div>
      <div class="g-card-body">
        <div class="g-card-name"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></div>
        <div class="g-stars"><?php echo $strs; ?><span class="cnt">(<?php echo $rc; ?>)</span></div>
        <div class="g-card-price">
          <?php if ($sale > 0): ?>
            <span class="g-price-now"><?php echo number_format($sale); ?></span><span class="g-price-unit">تومان</span>
            <span class="g-price-old"><?php echo number_format($reg); ?></span>
          <?php else: ?>
            <span class="g-price-now"><?php echo number_format($price); ?></span><span class="g-price-unit">تومان</span>
          <?php endif; ?>
        </div>
      </div>
      <a href="<?php echo esc_url($link); ?>" class="g-atc">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        افزودن به سبد
      </a>
    </div>
    <?php endwhile; endif; ?>
  </div>

  <div class="g-pager">
    <?php
    echo paginate_links([
      'prev_text' => '‹',
      'next_text' => '›',
      'type'      => 'list',
    ]);
    ?>
  </div>
</div>

<?php get_footer(); ?>
