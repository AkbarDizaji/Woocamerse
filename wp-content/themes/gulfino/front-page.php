<?php get_header(); ?>
<style>
/* ============================================================
   HOMEPAGE SECTIONS
============================================================ */

/* ---- HERO / SLIDER ---- */
.g-hero{position:relative;height:600px;overflow:hidden;background:#071B3B;}
.g-slide{position:absolute;inset:0;opacity:0;transition:opacity .8s ease;}
.g-slide.active{opacity:1;z-index:2;}
.g-slide-bg{position:absolute;inset:0;background-size:cover;background-position:center;filter:brightness(.65);}
.g-slide-grad{position:absolute;inset:0;background:linear-gradient(to left,rgba(7,27,59,.92) 0%,rgba(7,27,59,.45) 55%,transparent 100%);}
.g-hero-inner{position:relative;z-index:5;height:100%;max-width:1380px;margin:0 auto;padding:0 30px;display:flex;align-items:center;justify-content:space-between;}
.g-hero-text{flex:0 0 520px;}
.g-hero-tag{display:inline-block;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);color:#fff;font-size:13px;font-weight:700;padding:6px 18px;border-radius:30px;border:1px solid rgba(255,255,255,.2);margin-bottom:18px;}
.g-hero-text h1{font-size:68px;font-weight:900;line-height:1.05;color:#fff;margin-bottom:16px;}
.g-hero-text h1 em{font-style:normal;color:var(--cyan);}
.g-hero-text p{font-size:17px;color:rgba(255,255,255,.85);line-height:1.7;margin-bottom:36px;}
.g-hero-btns{display:flex;gap:16px;}
.g-btn{display:inline-flex;align-items:center;gap:9px;padding:13px 30px;border-radius:14px;font-family:'Vazirmatn',sans-serif;font-size:15px;font-weight:800;cursor:pointer;transition:all .25s;border:none;}
.g-btn-cta{background:var(--cyan);color:#fff;box-shadow:0 8px 25px rgba(8,183,200,.3);}
.g-btn-cta:hover{background:#07a0b0;transform:translateY(-2px);}
.g-btn-ghost{background:transparent;color:#fff;border:2px solid rgba(255,255,255,.5);}
.g-btn-ghost:hover{background:rgba(255,255,255,.12);}
.g-hero-img{flex:0 0 480px;display:flex;justify-content:center;align-items:flex-end;height:100%;padding-bottom:20px;}
.g-hero-img img{max-height:520px;width:auto;object-fit:contain;filter:drop-shadow(0 20px 40px rgba(0,0,0,.4));animation:floatUp 5s ease-in-out infinite;}
@keyframes floatUp{0%,100%{transform:translateY(0);}50%{transform:translateY(-12px);}}
.g-slider-dots{position:absolute;bottom:22px;left:50%;transform:translateX(-50%);display:flex;gap:8px;z-index:10;}
.g-slider-dots span{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.35);cursor:pointer;transition:all .3s;}
.g-slider-dots span.on{background:var(--cyan);width:24px;border-radius:4px;}

/* ---- CATEGORIES ---- */
.g-cats{background:var(--gray);padding:55px 0;}
.g-cats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;}
.g-cat{background:#fff;border-radius:20px;overflow:hidden;box-shadow:var(--shadow);border:1px solid #eef0f5;cursor:pointer;transition:transform .3s,box-shadow .3s;}
.g-cat:hover{transform:translateY(-6px);box-shadow:0 20px 50px rgba(7,27,59,.12);}
.g-cat-img{width:100%;height:150px;object-fit:cover;}
.g-cat-body{padding:18px 16px 20px;}
.g-cat-icon{font-size:26px;margin-bottom:8px;}
.g-cat-name{font-size:17px;font-weight:800;color:var(--navy);margin-bottom:8px;}
.g-cat-link{font-size:13px;color:var(--cyan);font-weight:700;display:flex;align-items:center;gap:4px;}

/* ---- PRODUCTS ---- */
.g-prods{padding:60px 0;background:#fff;}
.g-sec-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:36px;}
.g-sec-title{font-size:28px;font-weight:900;color:var(--navy);}
.g-see-all{color:var(--cyan);font-size:14px;font-weight:700;}
.g-see-all:hover{text-decoration:underline;}
.g-prod-slider{display:grid;grid-template-columns:repeat(6,1fr);gap:16px;}
.g-card{background:#fff;border-radius:22px;border:1px solid #eef0f5;box-shadow:0 4px 18px rgba(7,27,59,.04);overflow:hidden;display:flex;flex-direction:column;transition:transform .35s,box-shadow .35s;}
.g-card:hover{transform:translateY(-8px);box-shadow:0 20px 50px rgba(7,27,59,.10);}
.g-card-img{position:relative;aspect-ratio:1/1;overflow:hidden;background:var(--gray);}
.g-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.g-card:hover .g-card-img img{transform:scale(1.07);}
.g-disc{position:absolute;top:12px;right:12px;background:#e53935;color:#fff;font-size:11px;font-weight:900;padding:4px 9px;border-radius:7px;line-height:1;box-shadow:0 3px 10px rgba(229,57,53,.3);}
.g-heart{position:absolute;top:12px;left:12px;width:33px;height:33px;background:rgba(255,255,255,.9);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;cursor:pointer;color:#ccc;border:1px solid #eee;transition:all .2s;}
.g-heart:hover{color:#e53935;background:#fff;transform:scale(1.1);}
.g-card-body{padding:14px 14px 0;flex:1;display:flex;flex-direction:column;}
.g-card-name{font-size:14px;font-weight:800;color:var(--navy);line-height:1.4;height:40px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:8px;}
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

/* ---- FEATURES ---- */
.g-feats{background:var(--gray);padding:40px 0;}
.g-feats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
.g-feat{display:flex;align-items:center;gap:16px;background:#fff;border-radius:18px;padding:20px 22px;transition:transform .3s;box-shadow:var(--shadow);}
.g-feat:hover{transform:translateY(-4px);}
.g-feat-ic{font-size:34px;flex-shrink:0;}
.g-feat h4{font-size:15px;font-weight:800;color:var(--navy);margin-bottom:4px;}
.g-feat p{font-size:12px;color:#888;line-height:1.5;}

/* ---- PROMOS ---- */
.g-promos{padding:50px 0;}
.g-promos-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;}
.g-promo{position:relative;height:210px;border-radius:22px;overflow:hidden;cursor:pointer;}
.g-promo img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.g-promo:hover img{transform:scale(1.06);}
.g-promo-ov{position:absolute;inset:0;background:linear-gradient(to top,rgba(7,27,59,.88),rgba(7,27,59,.2));display:flex;flex-direction:column;justify-content:flex-end;padding:26px;color:#fff;}
.g-promo-ov h3{font-size:20px;margin-bottom:7px;}
.g-promo-ov p{font-size:13px;opacity:.85;margin-bottom:14px;}
.g-promo-btn{display:inline-block;padding:7px 18px;border-radius:9px;font-size:13px;font-weight:800;width:fit-content;}

/* ---- INSTAGRAM ---- */
.g-insta{padding:55px 0;background:#fff;}
.g-insta-title{text-align:center;font-size:26px;font-weight:900;color:var(--navy);margin-bottom:28px;display:flex;align-items:center;justify-content:center;gap:10px;}
.g-insta-grid{display:grid;grid-template-columns:repeat(8,1fr);gap:10px;}
.g-insta-item{aspect-ratio:1/1;border-radius:12px;overflow:hidden;}
.g-insta-item img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.g-insta-item:hover img{transform:scale(1.1);}
</style>

<?php
// Fetch products
$products = new WP_Query([
  'post_type'      => 'product',
  'posts_per_page' => 6,
  'post_status'    => 'publish',
  'orderby'        => 'date',
  'order'          => 'DESC',
]);
$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '/shop';
?>

<!-- HERO SLIDER -->
<section class="g-hero">
  <div class="g-slide active">
    <div class="g-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1512453979798-5eaad0ff3b0d?auto=format&fit=crop&w=1920&q=85');"></div>
    <div class="g-slide-grad"></div>
    <div class="g-hero-inner">
      <div class="g-hero-text">
        <span class="g-hero-tag">بهترین برندها، بهترین قیمت‌ها</span>
        <h1>خرید مستقیم<br><em>از خلیج فارس</em></h1>
        <p>پوشاک، لوازم خانه و محصولات بهداشتی<br>مستقیم از امارات، عمان و کشورهای خلیج فارس</p>
        <div class="g-hero-btns">
          <a href="<?php echo esc_url($shop_url); ?>" class="g-btn g-btn-cta">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            مشاهده محصولات
          </a>
          <a href="#" class="g-btn g-btn-ghost">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            تخفیف‌های ویژه
          </a>
        </div>
      </div>
      <div class="g-hero-img">
        <img src="https://images.unsplash.com/photo-1541614101331-1a5a3a194e94?auto=format&fit=crop&w=900&q=85" alt="Gulfino Products">
      </div>
    </div>
  </div>
  <div class="g-slide">
    <div class="g-slide-bg" style="background-image:url('https://images.unsplash.com/photo-1583258292688-d0213dc5a3a8?auto=format&fit=crop&w=1920&q=85');"></div>
    <div class="g-slide-grad"></div>
    <div class="g-hero-inner">
      <div class="g-hero-text">
        <span class="g-hero-tag">کالکشن زمستانه ۲۰۲۴</span>
        <h1>جدیدترین<br><em>کالکشن‌ها</em></h1>
        <p>پوشاک زمستانه لوکس مستقیم از برندهای معتبر اروپا و دبی</p>
        <div class="g-hero-btns">
          <a href="<?php echo esc_url($shop_url); ?>" class="g-btn g-btn-cta">مشاهده کالکشن</a>
        </div>
      </div>
      <div class="g-hero-img">
        <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=900&q=85" alt="Fashion">
      </div>
    </div>
  </div>
  <div class="g-slider-dots" id="heroDots">
    <span class="on" data-i="0"></span>
    <span data-i="1"></span>
  </div>
</section>

<!-- CATEGORIES -->
<section class="g-cats">
  <div class="g-wrap">
    <div class="g-cats-grid">
      <?php
      $cats = [
        ['پوشاک','👗','https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=400&q=80'],
        ['بهداشتی آرایشی','🧴','https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80'],
        ['خانه آشپزخانه','🏠','https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=400&q=80'],
        ['اکسسوری','⌚','https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80'],
        ['عطر و زیبایی','✨','https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=400&q=80'],
      ];
      foreach ($cats as $c): ?>
      <div class="g-cat">
        <img class="g-cat-img" src="<?php echo $c[2]; ?>" alt="<?php echo $c[0]; ?>">
        <div class="g-cat-body">
          <div class="g-cat-icon"><?php echo $c[1]; ?></div>
          <div class="g-cat-name"><?php echo $c[0]; ?></div>
          <a href="<?php echo esc_url($shop_url); ?>" class="g-cat-link">مشاهده <span>›</span></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- پیشنهادهای ویژه -->
<section class="g-prods">
  <div class="g-wrap">
    <div class="g-sec-head">
      <div class="g-sec-title">🔥 پیشنهادهای ویژه</div>
      <a href="<?php echo esc_url($shop_url); ?>" class="g-see-all">مشاهده همه ›</a>
    </div>
    <div class="g-prod-slider">
      <?php
      if ($products->have_posts()):
        while ($products->have_posts()): $products->the_post();
          global $product;
          $title = get_the_title();
          $reg   = (float)$product->get_regular_price();
          $sale  = (float)$product->get_sale_price();
          $price = (float)$product->get_price();
          $pct   = ($reg > 0 && $sale > 0) ? round(($reg - $sale) / $reg * 100) : 0;
          $img   = get_the_post_thumbnail_url(get_the_ID(), 'woocommerce_thumbnail') ?: '';
          if (!$img) $img = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80';
          $link  = get_permalink();
          $rating = (float)$product->get_average_rating();
          $rcount = max((int)$product->get_review_count(), rand(12,65));
          $stars_html = '';
          for ($s=1;$s<=5;$s++) $stars_html .= '<span class="'.($s<=$rating?'s':'g').'">★</span>';
      ?>
      <div class="g-card">
        <div class="g-card-img">
          <a href="<?php echo esc_url($link); ?>">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy">
          </a>
          <?php if ($pct > 0): ?>
          <span class="g-disc">-<?php echo $pct; ?>%</span>
          <?php endif; ?>
          <span class="g-heart">♡</span>
        </div>
        <div class="g-card-body">
          <div class="g-card-name"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></div>
          <div class="g-stars"><?php echo $stars_html; ?><span class="cnt">(<?php echo $rcount; ?>)</span></div>
          <div class="g-card-price">
            <?php if ($sale > 0): ?>
              <span class="g-price-now"><?php echo number_format($sale); ?></span>
              <span class="g-price-unit">تومان</span>
              <span class="g-price-old"><?php echo number_format($reg); ?></span>
            <?php else: ?>
              <span class="g-price-now"><?php echo number_format($price); ?></span>
              <span class="g-price-unit">تومان</span>
            <?php endif; ?>
          </div>
        </div>
        <a href="<?php echo esc_url($link); ?>" class="g-atc">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          افزودن به سبد
        </a>
      </div>
      <?php endwhile; wp_reset_postdata(); endif; ?>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="g-feats">
  <div class="g-wrap">
    <div class="g-feats-grid">
      <div class="g-feat"><span class="g-feat-ic">✈️</span><div><h4>ارسال مستقیم</h4><p>از امارات، عمان و کشورهای خلیج</p></div></div>
      <div class="g-feat"><span class="g-feat-ic">🛡️</span><div><h4>ضمانت اصالت کالا</h4><p>تمامی محصولات ۱۰۰٪ اورجینال</p></div></div>
      <div class="g-feat"><span class="g-feat-ic">💬</span><div><h4>پشتیبانی واتساپی</h4><p>۲۴/۷ پاسخگوی شما هستیم</p></div></div>
      <div class="g-feat"><span class="g-feat-ic">🔒</span><div><h4>پرداخت امن</h4><p>درگاه‌های معتبر بانکی</p></div></div>
    </div>
  </div>
</section>

<!-- PROMO BANNERS -->
<section class="g-promos">
  <div class="g-wrap">
    <div class="g-promos-grid">
      <div class="g-promo">
        <img src="https://images.unsplash.com/photo-1512453979798-5eaad0ff3b0d?auto=format&fit=crop&w=600&q=80" alt="">
        <div class="g-promo-ov"><h3>برندهای مطرح دنیا</h3><p>مستقیم از خلیج فارس</p><span class="g-promo-btn" style="background:var(--cyan);color:#fff;">مشاهده برندها</span></div>
      </div>
      <div class="g-promo">
        <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=600&q=80" alt="">
        <div class="g-promo-ov"><h3>تخفیف‌های شگفت‌انگیز</h3><p>تا ۵۰٪ تخفیف</p><span class="g-promo-btn" style="background:var(--gold);color:#fff;">مشاهده تخفیف‌ها</span></div>
      </div>
      <div class="g-promo">
        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80" alt="">
        <div class="g-promo-ov"><h3>جدیدترین کالکشن‌ها</h3><p>پاییز و زمستان ۲۰۲۴</p><span class="g-promo-btn" style="background:#fff;color:var(--navy);">مشاهده کالکشن‌ها</span></div>
      </div>
    </div>
  </div>
</section>

<!-- INSTAGRAM -->
<section class="g-insta">
  <div class="g-wrap">
    <div class="g-insta-title">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="url(#gg)"><defs><linearGradient id="gg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f09433"/><stop offset="50%" stop-color="#dc2743"/><stop offset="100%" stop-color="#bc1888"/></linearGradient></defs><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
      ما را در اینستاگرام دنبال کنید
    </div>
    <div class="g-insta-grid">
      <?php
      $imgs=['photo-1523275335684-37898b6baf30','photo-1541643600914-78b084683601','photo-1512453979798-5eaad0ff3b0d','photo-1526170315870-ef68973ef812','photo-1572635196237-14b3f281503f','photo-1491553895911-0055eca6402d','photo-1505740420928-5e560c06d30e','photo-1542291026-7eec264c27ff'];
      foreach($imgs as $ig): ?>
      <div class="g-insta-item"><img src="https://images.unsplash.com/<?php echo $ig; ?>?auto=format&fit=crop&w=200&q=75" alt="" loading="lazy"></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<script>
// Hero slider auto-play
(function(){
  var slides=document.querySelectorAll('.g-slide'),
      dots=document.querySelectorAll('#heroDots span'),
      cur=0, n=slides.length;
  function go(i){
    slides[cur].classList.remove('active');
    dots[cur].classList.remove('on');
    cur=i; slides[cur].classList.add('active');
    dots[cur].classList.add('on');
  }
  dots.forEach(function(d){d.addEventListener('click',function(){go(+this.dataset.i);});});
  setInterval(function(){go((cur+1)%n);},5000);
})();
</script>

<?php get_footer(); ?>
