#!/usr/bin/env bash
set -euo pipefail

ROOT="/Users/akbardizaji/Woocamerse"
WP=(php -d memory_limit=512M -d error_reporting=22527 /opt/homebrew/bin/wp --path="$ROOT" --user=1)

run_wp() { "${WP[@]}" "$@"; }

create_product() {
  local name="$1"
  local price="$2"
  local cat_id="$3"
  local sku="$4"
  local sale_price="${5:-}"

  ARGS=(wc product create --name="$name" --type=simple --status=publish --regular_price="$price" --sku="$sku" --categories="[{\"id\":${cat_id}}]")
  if [[ -n "$sale_price" ]]; then
    ARGS+=(--sale_price="$sale_price")
  fi

  run_wp "${ARGS[@]}" >/dev/null
  echo "  + $name"
}

# Get Category IDs
CAT_CLOTH=$(run_wp term list product_cat --slug=pooshak --field=term_id)
CAT_BEAUTY=$(run_wp term list product_cat --slug=behasht-araishi --field=term_id || run_wp term list product_cat --slug=behasht-araishi --field=term_id || run_wp wc product_cat create --name="بهداشتی آرایشی" --slug="behasht-araishi" --porcelain)
CAT_PERFUME=$(run_wp term list product_cat --slug=atr-o-adkolon --field=term_id)

echo "==> Seeding Premium Products..."
create_product "کت زنانه Zara" "2890000" "$CAT_CLOTH" "GULF-ZARA-001" "2490000"
create_product "Adidas Originals" "4200000" "$CAT_CLOTH" "GULF-ADI-001" "3890000"
create_product "سرم هیالورونیک لاروش پوزای" "2100000" "$CAT_BEAUTY" "GULF-LRP-001" "1890000"
create_product "سشوار Dyson Airwrap" "25000000" "$CAT_BEAUTY" "GULF-DYSON-001" "22500000"
create_product "پیراهن مردانه Tommy Hilfiger" "2600000" "$CAT_CLOTH" "GULF-TOMMY-001" "2190000"
create_product "Versace Dylan Blue" "5200000" "$CAT_PERFUME" "GULF-VERS-001" "4390000"

echo "Done seeding premium products."
