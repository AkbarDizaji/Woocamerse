<?php
/**
 * Checkout Form — Gulfino override.
 *
 * Instead of an online payment form, we ask the customer to place the order via
 * direct message (Instagram / Telegram / WhatsApp). Each button copies the full
 * order details to the clipboard (and WhatsApp also receives them pre-filled),
 * so the customer only has to paste & send.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// Empty cart guard.
if ( ! function_exists( 'WC' ) || ! WC()->cart || WC()->cart->is_empty() ) {
	?>
	<div class="g-pay-panel g-pay-empty">
		<p>سبد خرید شما خالی است.</p>
		<a class="g-pay-shop-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">بازگشت به فروشگاه</a>
	</div>
	<?php
	return;
}

$contacts = gulfino_order_contacts();
$message  = gulfino_order_message();

// URL-encoded message for links that support pre-fill (WhatsApp).
$msg_url = rawurlencode( $message );

$instagram_url = 'https://instagram.com/' . rawurlencode( ltrim( $contacts['instagram'], '@' ) );
$telegram_url  = 'https://t.me/' . rawurlencode( ltrim( $contacts['telegram'], '@' ) );
$whatsapp_url  = 'https://wa.me/' . preg_replace( '/\D/', '', $contacts['whatsapp'] ) . '?text=' . $msg_url;
?>

<div class="g-pay-panel" dir="rtl">

	<h2 class="g-pay-title">برای سفارش و پرداخت لطفا از یکی از طریق زیر اقدام کنید</h2>

	<div class="g-pay-summary">
		<h3>جزئیات سفارش شما</h3>
		<ul>
			<?php foreach ( WC()->cart->get_cart() as $item ) :
				$product = $item['data'];
				if ( ! $product ) {
					continue;
				}
				?>
				<li>
					<span class="g-pay-item-name"><?php echo esc_html( $product->get_name() ); ?></span>
					<span class="g-pay-item-qty">× <?php echo esc_html( $item['quantity'] ); ?></span>
					<span class="g-pay-item-price"><?php echo wp_kses_post( wc_price( $item['line_total'] ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<div class="g-pay-total">
			<span>جمع کل</span>
			<span><?php echo wp_kses_post( WC()->cart->get_total() ); ?></span>
		</div>
	</div>

	<?php
	if ( function_exists( 'gulfino_shipping_policy_html' ) ) {
		echo gulfino_shipping_policy_html(); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>

	<div class="g-pay-icons" data-order-message="<?php echo esc_attr( $message ); ?>">

		<a class="g-pay-icon g-pay-instagram" href="<?php echo esc_url( $instagram_url ); ?>" target="_blank" rel="noopener" data-copy="1">
			<span class="g-pay-icon-svg">
				<svg width="34" height="34" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
			</span>
			<span class="g-pay-icon-label">اینستاگرام</span>
		</a>

		<a class="g-pay-icon g-pay-telegram" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener" data-copy="1">
			<span class="g-pay-icon-svg">
				<svg width="34" height="34" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
			</span>
			<span class="g-pay-icon-label">تلگرام</span>
		</a>

		<a class="g-pay-icon g-pay-whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener" data-copy="1">
			<span class="g-pay-icon-svg">
				<svg width="34" height="34" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
			</span>
			<span class="g-pay-icon-label">واتساپ</span>
		</a>

	</div>

	<p class="g-pay-hint">با کلیک روی هر گزینه، جزئیات سفارش به‌صورت خودکار کپی می‌شود؛ کافی است در گفتگو آن را ارسال کنید.</p>

	<div class="g-pay-toast" id="gPayToast">جزئیات سفارش کپی شد ✓ در گفتگو ارسال کنید</div>

</div>

<style>
.g-pay-panel{max-width:680px;margin:40px auto;padding:36px 30px;background:var(--g-white,#fff);border-radius:var(--g-radius,24px);box-shadow:var(--g-shadow,0 15px 45px rgba(7,27,59,.08));text-align:center;font-family:'Vazirmatn',sans-serif;color:var(--g-navy,#071B3B)}
.g-pay-title{font-size:21px;font-weight:800;line-height:1.7;margin:0 0 28px;color:var(--g-navy,#071B3B)}
.g-pay-summary{background:var(--g-gray,#F5F7FA);border-radius:16px;padding:20px 22px;margin:0 0 30px;text-align:right}
.g-pay-summary h3{font-size:15px;font-weight:700;margin:0 0 14px;color:var(--g-cyan,#08B7C8)}
.g-pay-summary ul{list-style:none;margin:0;padding:0}
.g-pay-summary li{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 0;border-bottom:1px dashed rgba(7,27,59,.1);font-size:14px}
.g-pay-item-name{flex:1;font-weight:500}
.g-pay-item-qty{color:#71809a;font-size:13px}
.g-pay-item-price{font-weight:700;white-space:nowrap}
.g-pay-total{display:flex;align-items:center;justify-content:space-between;padding-top:14px;margin-top:6px;font-size:16px;font-weight:800;color:var(--g-navy,#071B3B)}
.g-pay-icons{display:flex;justify-content:center;gap:26px;margin:6px 0 22px}
.g-pay-icon{display:flex;flex-direction:column;align-items:center;gap:10px;text-decoration:none;color:#fff;cursor:pointer;transition:transform .18s ease}
.g-pay-icon:hover{transform:translateY(-4px)}
.g-pay-icon-svg{width:72px;height:72px;border-radius:20px;display:flex;align-items:center;justify-content:center;box-shadow:0 10px 24px rgba(7,27,59,.18)}
.g-pay-icon-label{font-size:13px;font-weight:600;color:var(--g-navy,#071B3B)}
.g-pay-instagram .g-pay-icon-svg{background:radial-gradient(circle at 30% 107%,#fdf497 0%,#fdf497 5%,#fd5949 45%,#d6249f 60%,#285AEB 90%)}
.g-pay-telegram .g-pay-icon-svg{background:linear-gradient(135deg,#2AABEE,#229ED9)}
.g-pay-whatsapp .g-pay-icon-svg{background:#25D366}
.g-pay-hint{font-size:13px;color:#71809a;line-height:1.8;margin:0}
.g-pay-empty{padding:50px 30px}
.g-pay-shop-link{display:inline-block;margin-top:16px;color:var(--g-cyan,#08B7C8);font-weight:700;text-decoration:none}
.g-pay-toast{position:fixed;left:50%;bottom:40px;transform:translateX(-50%) translateY(20px);background:var(--g-navy,#071B3B);color:#fff;padding:13px 22px;border-radius:14px;font-size:14px;font-weight:600;opacity:0;pointer-events:none;transition:opacity .25s ease,transform .25s ease;z-index:9999}
.g-pay-toast.show{opacity:1;transform:translateX(-50%) translateY(0)}
@media(max-width:520px){.g-pay-icons{gap:16px}.g-pay-icon-svg{width:62px;height:62px}.g-pay-title{font-size:18px}}
</style>

<script>
(function(){
	var wrap = document.querySelector('.g-pay-icons');
	if(!wrap) return;
	var message = wrap.getAttribute('data-order-message') || '';
	var toast = document.getElementById('gPayToast');

	function showToast(){
		if(!toast) return;
		toast.classList.add('show');
		setTimeout(function(){ toast.classList.remove('show'); }, 2600);
	}

	function copyMessage(){
		if(!message) return Promise.resolve();
		if(navigator.clipboard && navigator.clipboard.writeText){
			return navigator.clipboard.writeText(message).catch(function(){ fallbackCopy(); });
		}
		fallbackCopy();
		return Promise.resolve();
	}

	function fallbackCopy(){
		var ta = document.createElement('textarea');
		ta.value = message;
		ta.style.position = 'fixed';
		ta.style.opacity = '0';
		document.body.appendChild(ta);
		ta.select();
		try { document.execCommand('copy'); } catch(e){}
		document.body.removeChild(ta);
	}

	wrap.querySelectorAll('[data-copy]').forEach(function(link){
		link.addEventListener('click', function(){
			// Copy in the same user-gesture tick; the link still opens via target=_blank.
			copyMessage();
			showToast();
		});
	});
})();
</script>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
