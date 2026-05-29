<?php
require_once('wp-load.php');

$home_id = 50; // The "خانه" page ID

$data = [
    // 1. HERO SECTION
    [
        'id' => 'hero_v2',
        'elType' => 'section',
        'settings' => [
            'layout' => 'full_width',
            'background_background' => 'classic',
            'background_image' => [
                'url' => 'https://images.unsplash.com/photo-1582650625119-3a31f8fa2699?auto=format&fit=crop&w=1920&q=80', // Luxury Dubai Vibe
            ],
            'background_position' => 'center center',
            'background_size' => 'cover',
            'padding' => ['unit' => 'px', 'top' => '100', 'right' => '0', 'bottom' => '100', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'hero_col',
                'elType' => 'column',
                'settings' => ['width' => 100],
                'elements' => [
                    [
                        'id' => 'hero_content_box',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:flex; align-items:center; justify-content:space-between; max-width:1200px; margin:0 auto; padding:0 20px;">
                                <div style="flex:1; text-align:right; color:var(--gulfino-navy);">
                                    <span style="background:var(--gulfino-gray); padding:5px 15px; border-radius:20px; font-size:14px; font-weight:700;">بهترین برندها، بهترین قیمت‌ها</span>
                                    <h1 style="font-size:70px; margin:20px 0; line-height:1.1;">خرید مستقیم<br><span style="color:var(--gulfino-turquoise);">از خلیج فارس</span></h1>
                                    <p style="font-size:20px; margin-bottom:40px; opacity:0.8;">پوشاک، لوازم خانه و محصولات بهداشتی<br>مستقیم از امارات، عمان و کشورهای خلیج فارس</p>
                                    <div style="display:flex; gap:20px;">
                                        <a href="/shop" class="gulfino-btn-primary">🛍️ مشاهده محصولات</a>
                                        <a href="#" class="gulfino-btn-outline">🏷️ تخفیف‌های ویژه</a>
                                    </div>
                                </div>
                                <div style="flex:1; position:relative;">
                                    <img src="https://images.unsplash.com/photo-1541614101331-1a5a3a194e94?auto=format&fit=crop&w=800&q=80" style="width:100%; border-radius:30px; box-shadow:0 30px 60px rgba(0,0,0,0.15);">
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 2. CATEGORY SECTION
    [
        'id' => 'cats_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '60', 'right' => '0', 'bottom' => '60', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'cats_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'cats_grid',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:grid; grid-template-columns:repeat(5, 1fr); gap:20px; max-width:1200px; margin:0 auto; padding:0 20px;">
                                <div style="background:#fff; border-radius:20px; padding:20px; text-align:center; box-shadow:var(--gulfino-shadow); border:1px solid #eee;">
                                    <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=200&q=80" style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
                                    <h4 style="margin:0;">پوشاک</h4>
                                    <a href="#" style="font-size:12px; color:var(--gulfino-turquoise);">مشاهده ></a>
                                </div>
                                <div style="background:#fff; border-radius:20px; padding:20px; text-align:center; box-shadow:var(--gulfino-shadow); border:1px solid #eee;">
                                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=200&q=80" style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
                                    <h4 style="margin:0;">بهداشتی آرایشی</h4>
                                    <a href="#" style="font-size:12px; color:var(--gulfino-turquoise);">مشاهده ></a>
                                </div>
                                <div style="background:#fff; border-radius:20px; padding:20px; text-align:center; box-shadow:var(--gulfino-shadow); border:1px solid #eee;">
                                    <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=200&q=80" style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
                                    <h4 style="margin:0;">خانه و آشپزخانه</h4>
                                    <a href="#" style="font-size:12px; color:var(--gulfino-turquoise);">مشاهده ></a>
                                </div>
                                <div style="background:#fff; border-radius:20px; padding:20px; text-align:center; box-shadow:var(--gulfino-shadow); border:1px solid #eee;">
                                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80" style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
                                    <h4 style="margin:0;">اکسسوری</h4>
                                    <a href="#" style="font-size:12px; color:var(--gulfino-turquoise);">مشاهده ></a>
                                </div>
                                <div style="background:#fff; border-radius:20px; padding:20px; text-align:center; box-shadow:var(--gulfino-shadow); border:1px solid #eee;">
                                    <img src="https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=200&q=80" style="width:100px; height:100px; object-fit:cover; border-radius:50%; margin-bottom:15px;">
                                    <h4 style="margin:0;">عطر و زیبایی</h4>
                                    <a href="#" style="font-size:12px; color:var(--gulfino-turquoise);">مشاهده ></a>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 3. SPECIAL OFFERS SECTION
    [
        'id' => 'offers_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '60', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'offers_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'offers_header',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; max-width:1200px; margin:0 auto 30px;">
                                <h2 style="margin:0; font-size:32px; color:var(--gulfino-navy);">🔥 پیشنهادهای ویژه</h2>
                                <a href="/shop" style="color:var(--gulfino-turquoise); font-weight:700; text-decoration:none;">مشاهده همه ></a>
                            </div>'
                        ],
                    ],
                    [
                        'id' => 'offers_grid',
                        'elType' => 'widget',
                        'widgetType' => 'shortcode',
                        'settings' => [
                            'shortcode' => '[products limit="6" columns="6" orderby="popularity"]',
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 4. FEATURES BAR
    [
        'id' => 'features_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false],
            'background_background' => 'classic',
            'background_color' => 'var(--gulfino-gray)',
        ],
        'elements' => [
            [
                'id' => 'feats_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'feats_content',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:30px; max-width:1200px; margin:0 auto; padding:0 20px;">
                                <div style="display:flex; align-items:center; gap:15px; background:#fff; padding:20px; border-radius:15px;">
                                    <span style="font-size:30px;">✈️</span>
                                    <div><h4 style="margin:0;">ارسال مستقیم</h4><p style="margin:0; font-size:12px; opacity:0.7;">از امارات، عمان و کشورهای خلیج</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:15px; background:#fff; padding:20px; border-radius:15px;">
                                    <span style="font-size:30px;">🛡️</span>
                                    <div><h4 style="margin:0;">ضمانت اصالت کالا</h4><p style="margin:0; font-size:12px; opacity:0.7;">تمامی محصولات اورجینال</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:15px; background:#fff; padding:20px; border-radius:15px;">
                                    <span style="font-size:30px;">💬</span>
                                    <div><h4 style="margin:0;">پشتیبانی واتساپی</h4><p style="margin:0; font-size:12px; opacity:0.7;">۲۴/۷ پاسخگوی شما هستیم</p></div>
                                </div>
                                <div style="display:flex; align-items:center; gap:15px; background:#fff; padding:20px; border-radius:15px;">
                                    <span style="font-size:30px;">💳</span>
                                    <div><h4 style="margin:0;">پرداخت امن</h4><p style="margin:0; font-size:12px; opacity:0.7;">درگاه‌های معتبر بانکی</p></div>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 5. PROMO BANNERS
    [
        'id' => 'promos_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '60', 'right' => '0', 'bottom' => '60', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'promos_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'promos_grid',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; max-width:1200px; margin:0 auto; padding:0 20px;">
                                <div style="background:url(\'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80\'); background-size:cover; height:300px; border-radius:20px; display:flex; align-items:flex-end; padding:30px; color:#fff; position:relative; overflow:hidden;">
                                    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
                                    <div style="position:relative; z-index:1;">
                                        <h3 style="margin:0;">برندهای مطرح دنیا</h3>
                                        <p style="margin:10px 0;">مستقیم از خلیج فارس</p>
                                        <a href="#" style="background:var(--gulfino-turquoise); color:#fff; padding:8px 20px; border-radius:10px; font-size:12px;">مشاهده برندها</a>
                                    </div>
                                </div>
                                <div style="background:url(\'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80\'); background-size:cover; height:300px; border-radius:20px; display:flex; align-items:flex-end; padding:30px; color:#fff; position:relative; overflow:hidden;">
                                    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
                                    <div style="position:relative; z-index:1;">
                                        <h3 style="margin:0;">تخفیف‌های شگفت‌انگیز</h3>
                                        <p style="margin:10px 0;">تا ۵۰٪ تخفیف</p>
                                        <a href="#" style="background:var(--gulfino-gold); color:#fff; padding:8px 20px; border-radius:10px; font-size:12px;">مشاهده تخفیف‌ها</a>
                                    </div>
                                </div>
                                <div style="background:url(\'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80\'); background-size:cover; height:300px; border-radius:20px; display:flex; align-items:flex-end; padding:30px; color:#fff; position:relative; overflow:hidden;">
                                    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7), transparent);"></div>
                                    <div style="position:relative; z-index:1;">
                                        <h3 style="margin:0;">جدیدترین کالکشن‌ها</h3>
                                        <p style="margin:10px 0;">پاییز و زمستان ۲۰۲۴</p>
                                        <a href="#" style="background:#fff; color:var(--gulfino-navy); padding:8px 20px; border-radius:10px; font-size:12px;">مشاهده کالکشن‌ها</a>
                                    </div>
                                </div>
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 6. INSTAGRAM SECTION
    [
        'id' => 'insta_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '80', 'left' => '0', 'isLinked' => false],
        ],
        'elements' => [
            [
                'id' => 'insta_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'insta_header',
                        'elType' => 'widget',
                        'widgetType' => 'heading',
                        'settings' => [
                            'title' => '📸 ما را در اینستاگرام دنبال کنید',
                            'align' => 'center',
                            'title_color' => 'var(--gulfino-navy)',
                        ],
                    ],
                    [
                        'id' => 'insta_grid',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:grid; grid-template-columns:repeat(8, 1fr); gap:10px; max-width:1200px; margin:30px auto 0; padding:0 20px;">
                                <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1512453979798-5eaad0ff3b0d?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1526170315870-ef68973ef812?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                                <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=200&q=80" style="width:100%; aspect-ratio:1; object-fit:cover; border-radius:10px;">
                            </div>'
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 7. FOOTER SECTION (Custom)
    [
        'id' => 'footer_v2',
        'elType' => 'section',
        'settings' => [
            'padding' => ['unit' => 'px', 'top' => '80', 'right' => '0', 'bottom' => '40', 'left' => '0', 'isLinked' => false],
            'background_background' => 'classic',
            'background_color' => 'var(--gulfino-navy)',
        ],
        'elements' => [
            [
                'id' => 'footer_col',
                'elType' => 'column',
                'elements' => [
                    [
                        'id' => 'footer_content',
                        'elType' => 'widget',
                        'widgetType' => 'text-editor',
                        'settings' => [
                            'content' => '
                            <div style="display:grid; grid-template-columns:1.5fr 1fr 1fr 1.5fr; gap:40px; max-width:1200px; margin:0 auto; padding:0 20px; color:#fff;">
                                <div>
                                    <h2 style="color:var(--gulfino-gold); font-weight:900; font-size:32px; margin-bottom:20px;">Gulfino</h2>
                                    <p style="opacity:0.8; line-height:1.8;">Gulfino فروشگاه تخصصی برای خرید مستقیم از کشورهای حاشیه خلیج فارس است. ما بهترین برندهای دنیا را با تضمین اصالت و ارسال سریع به سراسر ایران عرضه می‌کنیم.</p>
                                    <div style="display:flex; gap:15px; margin-top:20px; font-size:24px;">
                                        <span>📱</span><span>📸</span><span>🐦</span><span>📘</span>
                                    </div>
                                </div>
                                <div>
                                    <h4 style="margin-bottom:25px;">لینک‌های سریع</h4>
                                    <ul style="list-style:none; padding:0; opacity:0.8; line-height:2;">
                                        <li>فروشگاه</li>
                                        <li>دسته‌بندی‌ها</li>
                                        <li>برندها</li>
                                        <li>تخفیف‌ها</li>
                                        <li>تماس با ما</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 style="margin-bottom:25px;">دسته‌بندی‌ها</h4>
                                    <ul style="list-style:none; padding:0; opacity:0.8; line-height:2;">
                                        <li>پوشاک</li>
                                        <li>بهداشتی و آرایشی</li>
                                        <li>خانه و آشپزخانه</li>
                                        <li>اکسسوری</li>
                                        <li>عطر و زیبایی</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 style="margin-bottom:25px;">عضویت در خبرنامه</h4>
                                    <p style="font-size:14px; opacity:0.8; margin-bottom:20px;">جدیدترین تخفیف‌ها و محصولات را از دست ندهید.</p>
                                    <div style="display:flex; background:rgba(255,255,255,0.1); border-radius:10px; padding:5px;">
                                        <input type="email" placeholder="ایمیل شما..." style="background:transparent; border:none; color:#fff; padding:10px; width:100%;">
                                        <button style="background:var(--gulfino-turquoise); border:none; color:#fff; padding:10px 20px; border-radius:8px;">عضویت</button>
                                    </div>
                                    <div class="gulfino-footer-whatsapp" style="margin-top:30px;">
                                        <span style="font-size:30px;">📞</span>
                                        <div>
                                            <h4 style="margin:0; font-size:14px;">پشتیبانی واتساپ ۲۴/۷</h4>
                                            <p style="margin:0; font-size:18px; color:var(--gulfino-gold);">۰۹۱۲ ۳۴۵ ۶۷۸۹</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align:center; margin-top:60px; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1); opacity:0.6; font-size:13px;">
                                کلیه حقوق این وبسایت متعلق به Gulfino می‌باشد. ۲۰۲۶ ©
                            </div>'
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

echo "Gulfino Premium Homepage (v2) built successfully.";
