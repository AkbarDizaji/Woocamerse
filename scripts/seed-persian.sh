#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP=(php -d memory_limit=512M -d error_reporting=22527 /opt/homebrew/bin/wp --path="$ROOT" --user=1)

run_wp() { "${WP[@]}" "$@"; }

echo "==> Updating store identity (Persian)..."
run_wp option update blogname "ووکامرز"
run_wp option update blogdescription "فروشگاه آنلاین عطر، پوشاک، کفش و لوازم خانگی"
run_wp option update woocommerce_store_address "تهران، خیابان ولیعصر"
run_wp option update woocommerce_store_city "تهران"
run_wp option update woocommerce_default_country "IR:TE"
run_wp option update woocommerce_currency "IRR"
run_wp option update woocommerce_price_thousand_sep ","
run_wp option update woocommerce_price_decimal_sep "."
run_wp option update woocommerce_price_num_decimals "0"

echo "==> Creating product categories..."
CAT_PERF=$(run_wp wc product_cat create --name="عطر و ادکلن" --slug="atr-o-adkolon" --description="عطر، ادکلن و اسپری‌های مردانه و زنانه" --porcelain)
CAT_CLOTH=$(run_wp wc product_cat create --name="پوشاک" --slug="pooshak" --description="لباس مردانه، زنانه و اسپرت" --porcelain)
CAT_SHOES=$(run_wp wc product_cat create --name="کفش" --slug="kafsh" --description="کفش رسمی، ورزشی و فصلی" --porcelain)
CAT_HOME=$(run_wp wc product_cat create --name="خانه و آشپزخانه" --slug="khane-ashpazkhane" --description="دکور، آشپزخانه و لوازم خانگی" --porcelain)

create_product() {
  local name="$1"
  local short="$2"
  local desc="$3"
  local price="$4"
  local cat_id="$5"
  local sku="$6"
  local featured="${7:-false}"

  run_wp wc product create \
    --name="$name" \
    --type=simple \
    --status=publish \
    --featured="$featured" \
    --regular_price="$price" \
    --short_description="$short" \
    --description="$desc" \
    --sku="$sku" \
    --manage_stock=true \
    --stock_quantity=25 \
    --categories="[{\"id\":${cat_id}}]" \
    --porcelain >/dev/null
  echo "  + $name"
}

echo "==> Seeding perfumes..."
create_product "عطر مردانه آرمان" \
  "رایحه چوبی و ادویه‌ای، ماندگاری بالا" \
  "عطر مردانه آرمان با نت‌های ابتدایی مرکبات، قلب چوب صندل و پای کهربایی گرم. مناسب استفاده روزانه و مهمانی." \
  "28900000" "$CAT_PERF" "WOO-PERF-001" true

create_product "ادکلن زنانه یاس" \
  "گلی ملایم با حس تازگی بهاری" \
  "ادکلن زنانه یاس ترکیبی از یاس، زنبق و وانیل است. بسته‌بندی شیک و مناسب هدیه." \
  "24500000" "$CAT_PERF" "WOO-PERF-002" true

create_product "اسپری بدن لانگ‌لاست" \
  "مناسب ورزش و استفاده روزمره" \
  "اسپری بدن با خشکی سریع و رایحه مرکباتی. حجم ۲۰۰ میلی‌لیتر." \
  "8900000" "$CAT_PERF" "WOO-PERF-003" false

create_product "عطر زوجین وودی" \
  "یونیسکس با نت‌های چوب و عنبر" \
  "عطر مشترک با طراحی مینیمال؛ مناسب زوج‌هایی که رایحه مشابه دوست دارند." \
  "31500000" "$CAT_PERF" "WOO-PERF-004" false

echo "==> Seeding clothing..."
create_product "پیراهن مردانه کتان" \
  "سبک، خنک و مناسب تابستان" \
  "پیراهن آستین کوتاه از پارچه کتان طبیعی. رنگ‌های سفید، آبی روشن و کرم." \
  "4200000" "$CAT_CLOTH" "WOO-CLOTH-001" true

create_product "مانتو زنانه مجلسی" \
  "دوخت تمیز با آستر نرم" \
  "مانتو مجلسی با برش مدرن و دکمه‌های پنهان. سایزبندی استاندارد ۳۶ تا ۴۴." \
  "7800000" "$CAT_CLOTH" "WOO-CLOTH-002" true

create_product "هودی ورزشی یونیسکس" \
  "پارچه ضخیم و مناسب پاییز" \
  "هودی کلاه‌دار با جیب کانگورویی. مناسب استایل اسپرت و روزمره." \
  "5600000" "$CAT_CLOTH" "WOO-CLOTH-003" false

create_product "شلوار جین اسلیم فیت" \
  "کشسانی ملایم و دوخت مقاوم" \
  "شلوار جین مردانه با فیت اسلیم. رنگ دودی و سرمه‌ای موجود است." \
  "4900000" "$CAT_CLOTH" "WOO-CLOTH-004" false

echo "==> Seeding shoes..."
create_product "کفش چرم مردانه کلاسیک" \
  "چرم طبیعی با کفی راحت" \
  "کفش رسمی مردانه مناسب محل کار و مراسم. نگهداری آسان با واکس مخصوص." \
  "12500000" "$CAT_SHOES" "WOO-SHOE-001" true

create_product "کتونی زنانه رانینگ" \
  "سبک با زیره ضربه‌گیر" \
  "کتونی مخصوص دویدگی و پیاده‌روی روزانه. تهویه مناسب و آستر آنتی‌باکتریال." \
  "9800000" "$CAT_SHOES" "WOO-SHOE-002" true

create_product "صندل تابستانی" \
  "راحت برای استفاده روزانه" \
  "صندل سبک با بند قابل تنظیم. مناسب ساحل و استفاده خانگی." \
  "3200000" "$CAT_SHOES" "WOO-SHOE-003" false

create_product "بوت زمستانی ضدآب" \
  "گرم و مقاوم در برابر باران" \
  "بوت پاشنه کوتاه با آستر پشمی. مناسب هوای سرد و برفی." \
  "14200000" "$CAT_SHOES" "WOO-SHOE-004" false

echo "==> Seeding home products..."
create_product "شمع معطر لاوندر" \
  "آرامش‌بخش برای اتاق خواب" \
  "شمع سویا با زمان سوخت ۴۵ ساعته. رایحه اسطوخودوس برای فضای آرام." \
  "1850000" "$CAT_HOME" "WOO-HOME-001" false

create_product "ست قهوه‌خوری سرامیک ۶ نفره" \
  "مناسب پذیرایی و هدیه" \
  "ست شامل ۶ فنجان و نعلبکی با طراحی مینیمال. قابل شستشو در ماشین ظرفشویی." \
  "6700000" "$CAT_HOME" "WOO-HOME-002" true

create_product "پتو مبل طرح مدرن" \
  "نرم و سبک برای سالن" \
  "پتو پشمی با ابعاد ۱۵۰×۲۰۰ سانتی‌متر. رنگ‌های خنثی و گرم." \
  "5400000" "$CAT_HOME" "WOO-HOME-003" false

create_product "ست چاقو آشپزخانه استیل" \
  "تیز و ضدزنگ با دسته ارگونومیک" \
  "شامل سرآشپز، میوه‌خوری و پوست‌کن. همراه با بلوک نگهداری چوبی." \
  "8900000" "$CAT_HOME" "WOO-HOME-004" false

echo "==> Creating Persian pages..."
PAGE_ABOUT=$(run_wp post create --post_type=page --post_title="درباره ما" --post_status=publish --post_content="ووکامرز فروشگاه آنلاین شما برای خرید عطر، پوشاک، کفش و لوازم خانگی با ارسال سریع و پشتیبانی فارسی است." --porcelain)
PAGE_CONTACT=$(run_wp post create --post_type=page --post_title="تماس با ما" --post_status=publish --post_content="آدرس: تهران، خیابان ولیعصر\nتلفن: ۰۲۱-۱۲۳۴۵۶۷۸\nایمیل: info@woocamerse.local" --porcelain)

run_wp option update woocommerce_shop_page_id "$(run_wp post list --post_type=page --name=shop --field=ID --format=ids 2>/dev/null || true)"
SHOP_ID=$(run_wp post list --post_type=page --pagename=shop --field=ID --format=ids 2>/dev/null || echo "")
if [[ -z "$SHOP_ID" ]]; then
  SHOP_ID=$(run_wp wc tool run install_pages --user=1 2>/dev/null | true)
fi

echo "==> Creating navigation menu..."
MENU_ID=$(run_wp menu create "منوی اصلی" --porcelain)
run_wp menu item add-custom "$MENU_ID" "خانه" "http://localhost:8080/" 2>/dev/null || true
run_wp menu location assign "$MENU_ID" primary 2>/dev/null || run_wp menu location assign "$MENU_ID" primary-menu 2>/dev/null || true

echo "==> Importing sample product images..."
IMG1=$(run_wp media import "https://picsum.photos/seed/woo-perfume/800/800" --title="نمونه عطر" --porcelain 2>/dev/null || echo "")
IMG2=$(run_wp media import "https://picsum.photos/seed/woo-cloth/800/800" --title="نمونه پوشاک" --porcelain 2>/dev/null || echo "")
IMG3=$(run_wp media import "https://picsum.photos/seed/woo-shoes/800/800" --title="نمونه کفش" --porcelain 2>/dev/null || echo "")

if [[ -n "$IMG1" ]]; then
  PID=$(run_wp post list --post_type=product --name="عطر-مردانه-آرمان" --field=ID --format=ids 2>/dev/null | awk '{print $1}')
  [[ -z "$PID" ]] && PID=$(run_wp post list --post_type=product --s="عطر مردانه آرمان" --field=ID --format=ids | head -1)
  [[ -n "$PID" && "$PID" != "0" ]] && run_wp post meta update "$PID" _thumbnail_id "$IMG1" 2>/dev/null || true
fi
if [[ -n "$IMG2" ]]; then
  PID=$(run_wp post list --post_type=product --s="پیراهن مردانه کتان" --field=ID --format=ids | head -1)
  [[ -n "$PID" ]] && run_wp post meta update "$PID" _thumbnail_id "$IMG2" 2>/dev/null || true
fi
if [[ -n "$IMG3" ]]; then
  PID=$(run_wp post list --post_type=product --s="کفش چرم مردانه کلاسیک" --field=ID --format=ids | head -1)
  [[ -n "$PID" ]] && run_wp post meta update "$PID" _thumbnail_id "$IMG3" 2>/dev/null || true
fi

run_wp rewrite flush --hard
run_wp cache flush 2>/dev/null || true

echo ""
echo "Done! Seeded Persian categories, products, and pages."
run_wp post list --post_type=product --format=table --fields=ID,post_title,post_status --path="$ROOT" | head -20
