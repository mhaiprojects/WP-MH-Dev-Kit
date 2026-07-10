#!/usr/bin/env bash
# Seed 10 in-demand Shepparton (Goulburn Valley) produce products with vendor details.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

run_seed() {
  if ! ddev wp plugin is-active woocommerce --user=1 2>/dev/null; then
    echo "ERROR: WooCommerce is not active. Run ./scripts/setup-woocommerce.sh first."
    exit 1
  fi

  echo "==> Setting store region to Shepparton, Victoria (AUD)..."
  ddev wp option update woocommerce_default_country "AU:VIC"
  ddev wp option update woocommerce_currency "AUD"
  ddev wp option update woocommerce_store_address "Maude Street Mall"
  ddev wp option update woocommerce_store_city "Shepparton"
  ddev wp option update woocommerce_store_postcode "3630"

  EXISTING=$(ddev wp post list --post_type=product --meta_key=_shepparton_seed --meta_value=1 --format=count --user=1 2>/dev/null || echo "0")
  if [ "${EXISTING}" -ge 20 ]; then
    echo "Shepparton seed products already exist (${EXISTING} found). Skipping."
    ddev wp post list --post_type=product --meta_key=_shepparton_seed --meta_value=1 --fields=ID,post_title,post_name --user=1
    exit 0
  fi

  echo "==> Creating product category..."
  CAT_ID=$(ddev wp wc product_cat create --user=1 --name="Shepparton Local Produce" --slug=shepparton-local-produce --porcelain 2>/dev/null || \
    ddev wp term list product_cat --slug=shepparton-local-produce --field=term_id --user=1 2>/dev/null | head -1)

  create_product() {
    local slug="$1"
    local name="$2"
    local price="$3"
    local short="$4"
    local description="$5"

    if ddev wp post list --post_type=product --name="$slug" --format=count --user=1 2>/dev/null | grep -q '^1$'; then
      echo "  skip (exists): $name"
      return 0
    fi

    local id
    id=$(ddev wp wc product create --user=1 \
      --name="$name" \
      --slug="$slug" \
      --type=simple \
      --regular_price="$price" \
      --description="$description" \
      --short_description="$short" \
      --categories="[{\"id\":${CAT_ID}}]" \
      --status=publish \
      --porcelain)

    ddev wp post meta update "$id" _shepparton_seed 1 --user=1 >/dev/null
    echo "  created (#${id}): $name — \$${price} AUD"
  }

  echo "==> Adding 10 Shepparton in-demand products..."

  create_product "goulburn-valley-pink-lady-apples-1kg" \
    "Goulburn Valley Pink Lady Apples (1 kg)" \
    "5.50" \
    "Crisp Pink Lady apples — peak demand item at Shepparton markets." \
    "<p>Hand-picked Pink Lady apples from the Goulburn Valley orchard belt. One of the most requested lines at Shepparton farmers markets and local grocers.</p>
<h3>Vendor details</h3>
<p><strong>Rodney's Orchard</strong><br>
1429 Midland Highway, Kialla VIC 3631 (near Shepparton)<br>
Phone: (03) 5823 4410<br>
Email: orders@rodneysorchard.com.au<br>
Pickup: Shepparton Farmers Market (Maude Street Mall), Saturdays 8am–12:30pm</p>"

  create_product "packham-pears-1kg" \
    "Packham Pears (1 kg)" \
    "4.80" \
    "Sweet Packham pears — a Goulburn Valley staple." \
    "<p>Classic Packham pears grown in irrigated Goulburn Valley orchards. Reliable seller through autumn and winter in the Shepparton region.</p>
<h3>Vendor details</h3>
<p><strong>Goulburn Valley Fruit Co-op</strong><br>
15 Packham Avenue, Shepparton VIC 3630<br>
Phone: (03) 5821 7788<br>
Email: sales@gvfruitcoop.com.au<br>
Wholesale &amp; retail; market stall every Saturday</p>"

  create_product "free-range-eggs-dozen" \
    "Free Range Eggs (dozen)" \
    "7.50" \
    "Pasture-raised eggs — consistently high demand locally." \
    "<p>Free-range eggs from hens on open pasture. Always sells out early at Shepparton markets.</p>
<h3>Vendor details</h3>
<p><strong>Abbey Road Farm Eggs</strong><br>
88 Abbey Road, Shepparton VIC 3630<br>
Phone: (03) 5822 1903<br>
Email: hello@abbeyroadfarmeggs.com.au<br>
Farm gate sales Wed–Sat; Shepparton Farmers Market Saturdays</p>"

  create_product "raw-honey-500g" \
    "Raw Goulburn Valley Honey (500 g)" \
    "14.00" \
    "Cold-filtered raw honey from local apiaries." \
    "<p>Pure raw honey sourced from Goulburn Valley eucalyptus and clover. Strong repeat demand from Shepparton households.</p>
<h3>Vendor details</h3>
<p><strong>Shepparton Honey Collective</strong><br>
Unit 4, 210 Wyndham Street, Shepparton VIC 3630<br>
Phone: (03) 5821 5560<br>
Email: orders@sheppartonhoney.com.au<br>
Also stocked at local providores; market stall 2nd &amp; 4th Saturday</p>"

  create_product "cherry-tomatoes-500g" \
    "Cherry Tomatoes (500 g punnet)" \
    "6.20" \
    "Sweet greenhouse cherry tomatoes — year-round local favourite." \
    "<p>Glasshouse-grown cherry tomatoes from the Shepparton irrigation district. Popular for salads and school lunchboxes.</p>
<h3>Vendor details</h3>
<p><strong>Turner's Garden Produce</strong><br>
7260 Goulburn Valley Highway, Kialla VIC 3631<br>
Phone: (03) 5823 9921<br>
Email: turners@gvproduce.com.au<br>
Shepparton Farmers Market; farm gate Fri 2–5pm</p>"

  create_product "navel-oranges-1kg" \
    "Navel Oranges (1 kg)" \
    "4.20" \
    "Juicy navel oranges — winter citrus in steady demand." \
    "<p>Sweet navel oranges from Goulburn Valley citrus groves. High demand through the cooler months in Shepparton.</p>
<h3>Vendor details</h3>
<p><strong>Sunset Grove Citrus</strong><br>
Lot 12 Lemnos Road, Shepparton VIC 3630<br>
Phone: (03) 5821 3344<br>
Email: citrus@sunsetgrove.com.au<br>
Market Saturdays; local delivery Wed &amp; Fri</p>"

  create_product "white-peaches-1kg" \
    "White Peaches (1 kg)" \
    "6.90" \
    "Fragrant white peaches — summer stone-fruit bestseller." \
    "<p>Tree-ripened white peaches from Shepparton-area orchards. Peak summer demand; limited seasonal window.</p>
<h3>Vendor details</h3>
<p><strong>Peach Tree Lane Orchard</strong><br>
245 Peach Tree Lane, Tatura VIC 3616 (Goulburn Valley)<br>
Phone: (03) 5824 1102<br>
Email: pick@peachtreelane.com.au<br>
Shepparton &amp; Tatura markets; u-pick Dec–Feb by appointment</p>"

  create_product "full-cream-milk-2l" \
    "Full Cream Milk (2 L)" \
    "3.80" \
    "Fresh Goulburn Valley dairy milk — everyday essential." \
    "<p>Full cream milk from Goulburn Valley dairy farms. Consistent daily demand across Shepparton supermarkets and independents.</p>
<h3>Vendor details</h3>
<p><strong>Goulburn Valley Fresh Dairy</strong><br>
Industrial Estate, 90 Numurkah Road, Shepparton VIC 3630<br>
Phone: (03) 5820 8800<br>
Email: wholesale@gvfreshdairy.com.au<br>
Distribution hub; retail partners across Greater Shepparton</p>"

  create_product "strawberries-250g" \
    "Strawberries (250 g punnet)" \
    "5.50" \
    "Sweet local strawberries — top seller in spring and summer." \
    "<p>Locally grown strawberries from protected cropping in the Goulburn Valley. Among the fastest-selling lines at Shepparton markets.</p>
<h3>Vendor details</h3>
<p><strong>Red Bank Berries</strong><br>
411 Red Bank Road, Mooroopna VIC 3629<br>
Phone: (03) 5825 6670<br>
Email: berries@redbankberries.com.au<br>
Shepparton Farmers Market; Mooroopna community stall monthly</p>"

  create_product "seasonal-vegetable-box" \
    "Seasonal Vegetable Box (medium)" \
    "28.00" \
    "Mixed seasonal veg box — popular weekly subscription item." \
    "<p>Curated medium box of seasonal vegetables from Goulburn Valley growers. Strong demand for weekly pickup and COD home delivery in Shepparton.</p>
<h3>Vendor details</h3>
<p><strong>GV Growers Box Collective</strong><br>
C/O Shepparton Farmers Market, Maude Street Mall, Shepparton VIC 3630<br>
Phone: (03) 5821 9900<br>
Email: boxes@gvgrowers.com.au<br>
Order by Thursday for Saturday market pickup; COD delivery Shepparton &amp; Mooroopna</p>"

  create_product "hass-avocados-each" \
    "Hass Avocados (each)" \
    "2.20" \
    "Creamy Hass avocados — strong café and household demand." \
    "<p>Ripening Hass avocados from irrigated Goulburn Valley groves.</p>
<h3>Vendor details</h3>
<p><strong>Murray Valley Avocados</strong><br>
56 Poplar Avenue, Shepparton VIC 3630<br>
Phone: (03) 5821 4412<br>
Email: sales@murrayvalleyavo.com.au<br>
Shepparton Farmers Market Saturdays</p>"

  create_product "bananas-1kg" \
    "Bananas (1 kg)" \
    "3.90" \
    "Everyday fruit staple with steady wholesale demand." \
    "<p>Sweet bananas supplied through regional produce distributors serving Shepparton retailers.</p>
<h3>Vendor details</h3>
<p><strong>Goulburn Valley Banana Traders</strong><br>
22 Welsford Street, Shepparton VIC 3630<br>
Phone: (03) 5821 2290<br>
Email: orders@gvbananas.com.au<br>
Wholesale Mon–Fri; market stall alternate Saturdays</p>"

  create_product "brown-onions-1kg" \
    "Brown Onions (1 kg)" \
    "2.80" \
    "Pantry essential — high volume seller." \
    "<p>Brushed brown onions from Goulburn Valley paddocks.</p>
<h3>Vendor details</h3>
<p><strong>Kialla Onion Growers</strong><br>
1800 Goulburn Valley Highway, Kialla VIC 3631<br>
Phone: (03) 5823 5501<br>
Email: onions@kiallagrowers.com.au<br>
Farm gate Wed–Sat; Shepparton market Saturdays</p>"

  create_product "brushed-potatoes-2kg" \
    "Brushed Potatoes (2 kg)" \
    "4.50" \
    "Versatile brushed potatoes — reliable bulk seller." \
    "<p>Washed brushed potatoes ideal for roasting, mashing, and chips.</p>
<h3>Vendor details</h3>
<p><strong>Tatura Potato Farm</strong><br>
77 Hogan Road, Tatura VIC 3616<br>
Phone: (03) 5824 3310<br>
Email: spuds@taturapotato.com.au<br>
Tatura &amp; Shepparton markets</p>"

  create_product "carrots-1kg" \
    "Carrots (1 kg bunch)" \
    "3.20" \
    "Sweet carrots — popular with families and schools." \
    "<p>Fresh carrot bunches from Shepparton irrigation district growers.</p>
<h3>Vendor details</h3>
<p><strong>GV Root Crop Co.</strong><br>
14 Industrial Avenue, Shepparton VIC 3630<br>
Phone: (03) 5820 7711<br>
Email: carrots@gvrootcrop.com.au<br>
Market Saturdays; local delivery Tue &amp; Fri</p>"

  create_product "broccoli-each" \
    "Broccoli (each)" \
    "3.60" \
    "Green broccoli heads — winter brassica in demand." \
    "<p>Firm broccoli heads harvested within 24 hours of market.</p>
<h3>Vendor details</h3>
<p><strong>Shepparton Greens</strong><br>
9 Broccoli Drive, Mooroopna VIC 3629<br>
Phone: (03) 5825 1188<br>
Email: greens@sheppartongreens.com.au<br>
Mooroopna &amp; Shepparton Farmers Market</p>"

  create_product "lemons-1kg" \
    "Lemons (1 kg)" \
    "4.00" \
    "Juicy lemons for home and café use." \
    "<p>Sun-ripened lemons from Goulburn Valley citrus blocks.</p>
<h3>Vendor details</h3>
<p><strong>Citrus Lane Orchard</strong><br>
300 Lemnos Road, Shepparton VIC 3630<br>
Phone: (03) 5821 6602<br>
Email: lemons@citruslane.com.au<br>
Market stall Saturdays 8am–12:30pm</p>"

  create_product "greek-yoghurt-1kg" \
    "Greek Yoghurt (1 kg tub)" \
    "6.50" \
    "Thick Greek yoghurt — dairy category bestseller." \
    "<p>Creamy Greek-style yoghurt from Goulburn Valley milk.</p>
<h3>Vendor details</h3>
<p><strong>Valley Culture Dairy</strong><br>
45 Numurkah Road, Shepparton VIC 3630<br>
Phone: (03) 5820 9933<br>
Email: culture@valleydairy.com.au<br>
Stockists across Shepparton; market fridge Saturdays</p>"

  create_product "sourdough-loaf" \
    "Artisan Sourdough Loaf" \
    "7.00" \
    "Crusty sourdough — sells out at weekend markets." \
    "<p>Slow-fermented sourdough baked fresh for Saturday markets.</p>
<h3>Vendor details</h3>
<p><strong>Maude Street Bakehouse</strong><br>
88 Maude Street, Shepparton VIC 3630<br>
Phone: (03) 5821 8801<br>
Email: hello@maudestreetbake.com.au<br>
Bakery 6am–3pm daily; market pop-up Saturdays</p>"

  create_product "extra-virgin-olive-oil-750ml" \
    "Extra Virgin Olive Oil (750 ml)" \
    "18.00" \
    "Cold-pressed olive oil — gift and pantry item." \
    "<p>Extra virgin olive oil from Murray-Goulburn grove plantings.</p>
<h3>Vendor details</h3>
<p><strong>GV Olive Grove</strong><br>
2100 Midland Highway, Kialla VIC 3631<br>
Phone: (03) 5823 7700<br>
Email: oil@gvolive.com.au<br>
Farm shop Fri–Sun; Shepparton market monthly</p>"

  create_product "free-range-chicken-whole" \
    "Free Range Whole Chicken (approx. 1.8 kg)" \
    "16.50" \
    "Whole free-range chicken — popular Sunday roast item." \
    "<p>Pasture-raised whole chickens processed locally for Shepparton customers.</p>
<h3>Vendor details</h3>
<p><strong>Shepparton Poultry Farm</strong><br>
600 River Road, Shepparton VIC 3630<br>
Phone: (03) 5822 4400<br>
Email: orders@sheppartonpoultry.com.au<br>
Pre-order Thu for Sat pickup; COD at market</p>"

  echo ""
  echo "==> Product catalog"
  ddev wp post list --post_type=product --meta_key=_shepparton_seed --meta_value=1 \
    --fields=ID,post_title --format=table --user=1
  echo ""
  echo "Shop: https://cursor-wpdev2.ddev.site/shop/"
}

if docker info >/dev/null 2>&1; then
  run_seed
elif getent group docker | grep -qE "[[:space:]]${USER}(,|$)"; then
  echo "Docker group membership not active in this shell; re-running via sg docker..."
  exec sg docker -c "cd '$ROOT' && ./scripts/seed-shepparton-products.sh"
else
  echo "ERROR: Cannot access Docker."
  exit 1
fi
