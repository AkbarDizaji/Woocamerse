<?php
require_once('wp-load.php');

$home_id = 50;

$data = [
    // 1. CINEMATIC HERO SECTION
    [
        'id' => 'hero_v3',
        'elType' => 'section',
        'settings' => [
            'layout' => 'full_width',
            'padding' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
        'elements' => [
            [
                'id' => 'hero_col_v3',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'hero_html',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '
                            <div class="g-hero-section">
                                <div class="g-hero-bg"></div>
                                <div class="g-hero-overlay"></div>
                                <div class="g-hero-content">
                                    <div class="g-hero-text" style="direction:rtl;">
                                        <span style="background:var(--g-gold); color:#fff; padding:6px 20px; border-radius:30px; font-weight:800; font-size:14px; letter-spacing:1px; display:inline-block; margin-bottom:20px;">LUXURY EXPERIENCE</span>
                                        <h1>خرید مستقیم<br><span style="color:var(--g-cyan);">از خلیج فارس</span></h1>
                                        <p>پوشاک، لوازم خانه و محصولات بهداشتی<br>مستقیم از امارات، عمان و کشورهای خلیج فارس</p>
                                        <div style="display:flex; gap:25px;">
                                            <a href="/shop" class="gulfino-btn-primary" style="font-size:18px; padding:18px 45px;">🛍️ مشاهده محصولات</a>
                                            <a href="#" class="gulfino-btn-outline" style="font-size:18px; padding:18px 45px; border-color:#fff; color:#fff;">🏷️ تخفیف‌های ویژه</a>
                                        </div>
                                    </div>
                                    <div class="g-hero-visual">
                                        <img src="https://images.unsplash.com/photo-1541614101331-1a5a3a194e94?auto=format&fit=crop&w=1000&q=80" alt="Luxury Products">
                                        <div style="position:absolute; bottom:50px; left:-50px; background:rgba(255,255,255,0.1); backdrop-filter:blur(20px); padding:25px; border-radius:24px; border:1px solid rgba(255,255,255,0.2); color:#fff; width:280px; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
                                            <h4 style="margin:0 0 10px; color:var(--g-gold);">برندهای اورجینال</h4>
                                            <p style="margin:0; font-size:13px; opacity:0.8;">ضمانت ۱۰۰٪ اصالت کالا مستقیم از نمایندگی‌های معتبر دبی و ابوظبی.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 2. FEATURES STRIP
    [
        'id' => 'features_v3',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false],
            'background_background' => 'classic',
            'background_color' => '#fff',
        ],
        'elements' => [
            [
                'id' => 'feat_col_v3',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'feat_html',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '
                            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:40px; max-width:1400px; margin:0 auto; padding:0 30px; direction:rtl;">
                                <div style="display:flex; align-items:center; gap:20px; background:var(--g-gray); padding:25px; border-radius:24px; transition:transform 0.3s;" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'none\'">
                                    <span style="font-size:40px;">✈️</span>
                                    <div><h4 style="margin:0; font-size:18px;">ارسال مستقیم</h4><p style="margin:5px 0 0; font-size:13px; opacity:0.7;">از امارات و عمان به سراسر ایران</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:20px; background:var(--g-gray); padding:25px; border-radius:24px; transition:transform 0.3s;" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'none\'">
                                    <span style="font-size:40px;">🛡️</span>
                                    <div><h4 style="margin:0; font-size:18px;">ضمانت اصالت</h4><p style="margin:5px 0 0; font-size:13px; opacity:0.7;">تضمین ۱۰۰٪ اورجینال بودن کالا</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:20px; background:var(--g-gray); padding:25px; border-radius:24px; transition:transform 0.3s;" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'none\'">
                                    <span style="font-size:40px;">💬</span>
                                    <div><h4 style="margin:0; font-size:18px;">پشتیبانی واتساپ</h4><p style="margin:5px 0 0; font-size:13px; opacity:0.7;">پاسخگویی سریع و حرفه‌ای</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:20px; background:var(--g-gray); padding:25px; border-radius:24px; transition:transform 0.3s;" onmouseover="this.style.transform=\'translateY(-5px)\'" onmouseout="this.style.transform=\'none\'">
                                    <span style="font-size:40px;">🔒</span>
                                    <div><h4 style="margin:0; font-size:18px;">پرداخت امن</h4><p style="margin:5px 0 0; font-size:13px; opacity:0.7;">درگاه‌های معتبر و ایمن بانکی</p></div>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 3. CATEGORIES SECTION
    [
        'id' => 'cats_v3',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '80', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false],
            'background_background' => 'classic',
            'background_color' => '#fff',
        ],
        'elements' => [
            [
                'id' => 'cats_col_v3',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'cats_title',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => 'دسته‌بندی‌های لوکس',
                            'align' => 'center',
                            'title_color' => 'var(--g-navy)',
                            'typography_typography' => 'custom',
                            'typography_font_size' => ['unit' => 'px', 'size' => '42'],
                        ],
                    ],
                    [
                        'id' => 'cats_html',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '
                            <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:25px; max-width:1400px; margin:50px auto 0; padding:0 30px; direction:rtl;">
                                <div class="g-cat-card">
                                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=400&q=80">
                                    <div class="g-cat-overlay">
                                        <h3 style="margin:0; font-size:24px;">پوشاک</h3>
                                        <p style="margin:10px 0 0; font-size:14px; opacity:0.8;">کالکشن جدید پاییزه</p>
                                    </div>
                                </div>
                                <div class="g-cat-card">
                                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=400&q=80">
                                    <div class="g-cat-overlay">
                                        <h3 style="margin:0; font-size:24px;">بهداشتی آرایشی</h3>
                                        <p style="margin:10px 0 0; font-size:14px; opacity:0.8;">محصولات مراقبتی پوست</p>
                                    </div>
                                </div>
                                <div class="g-cat-card">
                                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=400&q=80">
                                    <div class="g-cat-overlay">
                                        <h3 style="margin:0; font-size:24px;">خانه و آشپزخانه</h3>
                                        <p style="margin:10px 0 0; font-size:14px; opacity:0.8;">دکوراسیون مدرن</p>
                                    </div>
                                </div>
                                <div class="g-cat-card">
                                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80">
                                    <div class="g-cat-overlay">
                                        <h3 style="margin:0; font-size:24px;">اکسسوری</h3>
                                        <p style="margin:10px 0 0; font-size:14px; opacity:0.8;">ساعت و جواهرات</p>
                                    </div>
                                </div>
                                <div class="g-cat-card">
                                    <img src="https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=400&q=80">
                                    <div class="g-cat-overlay">
                                        <h3 style="margin:0; font-size:24px;">عطر و زیبایی</h3>
                                        <p style="margin:10px 0 0; font-size:14px; opacity:0.8;">رایحه‌های خاص دبی</p>
                                    </div>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 4. PRODUCT GRID
    [
        'id' => 'products_v3',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'prod_col_v3',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'prod_header',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '
                            <div style="display:flex; justify-content:space-between; align-items:center; max-width:1400px; margin:0 auto 40px; padding:0 30px; direction:rtl;">
                                <h2 style="margin:0; font-size:36px; color:var(--g-navy);">✨ محصولات پیشنهادی</h2>
                                <a href="/shop" style="color:var(--g-cyan); font-weight:800; text-decoration:none; font-size:18px;">مشاهده همه محصولات ></a>
                            </div>'
                        ],
                    ],
                    [
                        'id' => 'prod_grid_v3',
                        'elType' => 'widget',
                        'widgetType' => 'shortcode',
                        'settings' => [
                            'shortcode' => '[products limit="8" columns="4" orderby="popularity"]',
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 5. LUXURY FOOTER
    [
        'id' => 'footer_v3',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true],
        ],
        'elements' => [
            [
                'id' => 'footer_col_v3',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'footer_html',
                        'elType' => 'widget',
                        'widgetType' => 'html',
                        'settings' => [
                            'html' => '
                            <footer class="g-footer" style="direction:rtl;">
                                <div class="g-footer-grid">
                                    <div>
                                        <h2 style="color:var(--g-gold); font-weight:900; font-size:42px; margin-bottom:25px;">Gulfino</h2>
                                        <p style="opacity:0.8; line-height:2; font-size:15px;">گلفینو پلی میان شما و بازارهای لوکس حاشیه خلیج فارس است. ما با حذف واسطه‌ها، محصولات اورجینال را مستقیم از نمایندگی‌های دبی و عمان به دست شما می‌رسانیم.</p>
                                        <div style="display:flex; gap:20px; margin-top:35px;">
                                            <span style="font-size:28px; cursor:pointer; color:var(--g-gold);">📸</span>
                                            <span style="font-size:28px; cursor:pointer; color:var(--g-gold);">🐦</span>
                                            <span style="font-size:28px; cursor:pointer; color:var(--g-gold);">📘</span>
                                            <span style="font-size:28px; cursor:pointer; color:var(--g-gold);">📱</span>
                                        </div>
                                    </div>
                                    <div>
                                        <h4>لینک‌های مفید</h4>
                                        <ul>
                                            <li>فروشگاه آنلاین</li>
                                            <li>رهگیری سفارش</li>
                                            <li>قوانین و مقررات</li>
                                            <li>درباره گلفینو</li>
                                            <li>سوالات متداول</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4>دسته‌بندی‌ها</h4>
                                        <ul>
                                            <li>عطر و ادکلن</li>
                                            <li>پوشاک مردانه</li>
                                            <li>آرایشی و بهداشتی</li>
                                            <li>اکسسوری لوکس</li>
                                            <li>لوازم خانگی</li>
                                        </ul>
                                    </div>
                                    <div>
                                        <h4>ارتباط با ما</h4>
                                        <p style="opacity:0.8; font-size:14px; margin-bottom:25px;">تیم پشتیبانی ما ۲۴ ساعته آماده پاسخگویی به شماست.</p>
                                        <div class="g-footer-wa">
                                            <span style="font-size:35px;">💬</span>
                                            <div>
                                                <h4 style="margin:0; font-size:14px; color:#fff;">پشتیبانی واتساپ</h4>
                                                <p style="margin:5px 0 0; font-size:18px; color:var(--g-gold); font-weight:900;">۰۹۱۲ ۳۴۵ ۶۷۸۹</p>
                                            </div>
                                        </div>
                                        <div style="margin-top:30px; display:flex; gap:10px;">
                                            <div style="background:#fff; border-radius:10px; padding:10px; width:60px; height:60px; display:flex; align-items:center; justify-content:center;"><img src="https://trustseal.enamad.ir/Content/Images/Star2/81.png?v=1.1" style="width:100%;"></div>
                                            <div style="background:#fff; border-radius:10px; padding:10px; width:60px; height:60px; display:flex; align-items:center; justify-content:center;"><img src="https://logo.samandehi.ir/logo.aspx?id=12345&p=qwer" style="width:100%;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align:center; margin-top:80px; padding-top:30px; border-top:1px solid rgba(255,255,255,0.1); opacity:0.5; font-size:13px;">
                                    تمامی حقوق این وب‌سایت متعلق به برند Gulfino می‌باشد. ۲۰۲۶ ©
                                </div>
                            </footer>'
                        ],
                    ],
                ],
            ],
        ],
    ],
];

update_post_meta($home_id, '_elementor_data', json_encode($data));
update_post_meta($home_id, '_elementor_edit_mode', 'builder');
update_post_meta($home_id, '_elementor_template_type', 'wp-page');

echo "Gulfino Ultra-Luxury Homepage (v3) built successfully.";
