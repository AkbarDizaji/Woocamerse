<?php
/**
 * Plugin Name:  Gulfino – Bank Transfer (BACS) Booster
 * Description:  Supercharges WooCommerce Direct Bank Transfer for the Iranian market: friendly Persian instructions, payment-receipt upload (فیش واریز) on the Thank-You page & My-Account orders, an "Awaiting Payment Confirmation" status, WhatsApp/Telegram quick-contact buttons with a pre-filled message, and automatic 24h cancellation of unpaid orders.
 * Version:      1.0.0
 * Author:       Gulfino Team
 * Text Domain:  gulfino-bacs
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GBACS_VERSION', '1.0.0' );
define( 'GBACS_FILE', __FILE__ );
define( 'GBACS_DIR', plugin_dir_path( __FILE__ ) );

/* -------------------------------------------------------------------------
 * HPOS (High-Performance Order Storage) compatibility.
 * ---------------------------------------------------------------------- */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', GBACS_FILE, true );
	}
} );

/* =========================================================================
 * 1. CONFIGURATION HELPERS  (edit these — or override with the filters)
 * ====================================================================== */

/**
 * Quick-contact accounts used by the WhatsApp / Telegram buttons.
 *
 * whatsapp / telegram: full international number, DIGITS ONLY (no + or spaces).
 * Temporary placeholder is +98 912 345 6789 → '989123456789'.
 */
function gbacs_contacts() {
	// Reuse the theme's central contacts if present, so there is one source of truth.
	$theme = function_exists( 'gulfino_order_contacts' ) ? gulfino_order_contacts() : array();

	return apply_filters( 'gbacs_contacts', array(
		'whatsapp' => $theme['whatsapp'] ?? '96895699131',
		'telegram' => '96895699131', // Telegram via phone works as https://t.me/+968...
	) );
}

/**
 * Bank account the customer should transfer to.
 * Fill in your real values. Leave a field empty to hide that row.
 */
function gbacs_account() {
	return apply_filters( 'gbacs_account', array(
		'holder' => 'پریناز مهرابی',                // صاحب حساب
		'bank'   => 'بلو',                          // نام بانک
		'card'   => '6219-8619-7426-6222',          // شماره کارت
		'sheba'  => 'IR85 0560 6118 2800 5749 3686 01', // شماره شبا
		'account'=> '',                              // شماره حساب (اختیاری)
	) );
}

/** Hours the customer has to pay before the order is auto-cancelled. */
function gbacs_deadline_hours() {
	return (int) apply_filters( 'gbacs_deadline_hours', 24 );
}

/* -------------------------------------------------------------------------
 * Checkout: short, clear BACS title + description (code = source of truth,
 * so it no longer depends on the WooCommerce DB setting).
 * ---------------------------------------------------------------------- */

/** Convert ASCII digits in a string to Persian digits. */
function gbacs_fa_digits( $str ) {
	return str_replace(
		array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
		array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
		(string) $str
	);
}

add_filter( 'woocommerce_gateway_title', function ( $title, $id ) {
	if ( 'bacs' !== $id ) {
		return $title;
	}
	return 'کارت‌به‌کارت / واریز بانکی (مهلت ' . gbacs_fa_digits( gbacs_deadline_hours() ) . ' ساعت)';
}, 10, 2 );

add_filter( 'woocommerce_gateway_description', function ( $description, $id ) {
	if ( 'bacs' !== $id ) {
		return $description;
	}
	$hours = gbacs_fa_digits( gbacs_deadline_hours() );
	return 'پس از ثبت سفارش، شمارهٔ کارت و شبا نمایش داده می‌شود. مبلغ را واریز کنید و فیش آن را همان‌جا در صفحهٔ تأیید سفارش (یا بعداً از صفحهٔ سفارش در حساب کاربری) بارگذاری نمایید. مهلت پرداخت ' . $hours . ' ساعت است.';
}, 10, 2 );

/* =========================================================================
 * 2. CUSTOM ORDER STATUS — "Awaiting Payment Confirmation"
 *    slug: wc-awaiting-confirm  →  در انتظار تأیید پرداخت
 * ====================================================================== */

add_action( 'init', function () {
	register_post_status( 'wc-awaiting-confirm', array(
		'label'                     => 'در انتظار تأیید پرداخت',
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s: number of orders. */
		'label_count'               => _n_noop(
			'در انتظار تأیید پرداخت <span class="count">(%s)</span>',
			'در انتظار تأیید پرداخت <span class="count">(%s)</span>'
		),
	) );
} );

// Add it to the WooCommerce order-status dropdown, right after "On hold".
add_filter( 'wc_order_statuses', function ( $statuses ) {
	$new = array();
	foreach ( $statuses as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'wc-on-hold' === $key ) {
			$new['wc-awaiting-confirm'] = 'در انتظار تأیید پرداخت';
		}
	}
	if ( ! isset( $new['wc-awaiting-confirm'] ) ) {
		$new['wc-awaiting-confirm'] = 'در انتظار تأیید پرداخت';
	}
	return $new;
} );

// Treat it like "on-hold" for reports / paid-status checks (not yet a paid order).
add_filter( 'woocommerce_order_is_paid_statuses', function ( $statuses ) {
	return $statuses; // intentionally NOT paid until admin confirms → moves to processing/completed
} );

/* =========================================================================
 * 3. THANK-YOU PAGE  — instructions + receipt upload + contact buttons
 * ====================================================================== */

add_action( 'woocommerce_thankyou', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || 'bacs' !== $order->get_payment_method() ) {
		return;
	}
	echo gbacs_render_panel( $order, 'thankyou' ); // phpcs:ignore WordPress.Security.EscapeOutput
}, 5 );

/* =========================================================================
 * 4. MY ACCOUNT → order view  — same panel so customers can upload later
 * ====================================================================== */

add_action( 'woocommerce_view_order', function ( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || 'bacs' !== $order->get_payment_method() ) {
		return;
	}
	// Only while still awaiting money — hide once paid/cancelled.
	if ( in_array( $order->get_status(), array( 'on-hold', 'pending', 'awaiting-confirm' ), true ) ) {
		echo gbacs_render_panel( $order, 'account' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 5 );

/* =========================================================================
 * 5. THE PANEL  (instructions, bank details, upload box, contact buttons)
 * ====================================================================== */

function gbacs_render_panel( $order, $context = 'thankyou' ) {
	$contacts = gbacs_contacts();
	$account  = gbacs_account();
	$hours    = gbacs_deadline_hours();

	$order_no   = $order->get_order_number();
	$total_html = $order->get_formatted_order_total();
	// Decode entities (&nbsp; etc.) and collapse to plain text for the message/links.
	$total_txt  = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( wp_strip_all_tags( $total_html ), ENT_QUOTES, 'UTF-8' ) ) );
	$date_jalali = gbacs_jdate( $order->get_date_created() );

	$has_receipt = (bool) $order->get_meta( '_gbacs_receipt_file' );
	$status      = $order->get_status();

	// Pre-filled Persian message for WhatsApp/Telegram.
	$message = gbacs_build_message( $order_no, $total_txt, $date_jalali );
	$msg_enc = rawurlencode( $message );

	$whatsapp_url = 'https://wa.me/' . preg_replace( '/\D/', '', $contacts['whatsapp'] ) . '?text=' . $msg_enc;
	$telegram_url = 'https://t.me/+' . preg_replace( '/\D/', '', $contacts['telegram'] );

	// Deadline countdown target (created + N hours), in customer-friendly text.
	$deadline_ts = $order->get_date_created() ? ( $order->get_date_created()->getTimestamp() + $hours * HOUR_IN_SECONDS ) : 0;

	ob_start();
	?>
	<div class="gbacs" dir="rtl">

		<?php if ( $has_receipt ) : ?>
			<div class="gbacs-success">
				<span class="gbacs-success-ic">✓</span>
				<div>
					<strong>فیش واریز شما با موفقیت دریافت شد.</strong>
					<p>سفارش شما در وضعیت «<b>در انتظار تأیید پرداخت</b>» قرار گرفت. به‌محض بررسی توسط همکاران ما (معمولاً کمتر از چند ساعت)، تأیید نهایی برای شما ارسال می‌شود. نیازی به اقدام دیگری نیست. 🌿</p>
				</div>
			</div>
		<?php endif; ?>

		<div class="gbacs-card">
			<h2 class="gbacs-h2">💳 پرداخت سفارش شما با کارت‌به‌کارت / واریز بانکی</h2>
			<p class="gbacs-lead">سفارش‌تان ثبت شد ✅ فقط ۲ قدم مانده: مبلغ زیر را واریز کنید و فیش آن را همین‌جا بارگذاری نمایید.</p>

			<div class="gbacs-amount">
				<span>مبلغ قابل پرداخت</span>
				<strong><?php echo wp_kses_post( $total_html ); ?></strong>
			</div>

			<div class="gbacs-deadline" data-deadline="<?php echo esc_attr( $deadline_ts ); ?>">
				⏳ لطفاً پرداخت را حداکثر تا <strong><?php echo esc_html( $hours ); ?> ساعت</strong> آینده انجام دهید؛ در غیر این صورت سفارش به‌صورت خودکار لغو می‌شود.
				<span class="gbacs-timer" id="gbacsTimer"></span>
			</div>

			<!-- BANK DETAILS -->
			<div class="gbacs-bank">
				<h3>اطلاعات حساب برای واریز</h3>
				<ul>
					<?php if ( $account['card'] ) : ?>
						<li>
							<span class="gbacs-bank-k">شماره کارت</span>
							<span class="gbacs-bank-v">
								<bdi class="gbacs-copyable" data-copy="<?php echo esc_attr( preg_replace( '/\D/', '', $account['card'] ) ); ?>"><?php echo esc_html( $account['card'] ); ?></bdi>
								<button type="button" class="gbacs-copybtn" data-copy="<?php echo esc_attr( preg_replace( '/\D/', '', $account['card'] ) ); ?>">کپی</button>
							</span>
						</li>
					<?php endif; ?>
					<?php if ( $account['sheba'] ) : ?>
						<li>
							<span class="gbacs-bank-k">شماره شبا</span>
							<span class="gbacs-bank-v">
								<bdi><?php echo esc_html( $account['sheba'] ); ?></bdi>
								<button type="button" class="gbacs-copybtn" data-copy="<?php echo esc_attr( preg_replace( '/[\s]/', '', $account['sheba'] ) ); ?>">کپی</button>
							</span>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $account['account'] ) ) : ?>
						<li><span class="gbacs-bank-k">شماره حساب</span><span class="gbacs-bank-v"><bdi><?php echo esc_html( $account['account'] ); ?></bdi></span></li>
					<?php endif; ?>
					<?php if ( $account['holder'] ) : ?>
						<li><span class="gbacs-bank-k">به نام</span><span class="gbacs-bank-v"><?php echo esc_html( $account['holder'] ); ?></span></li>
					<?php endif; ?>
					<?php if ( $account['bank'] ) : ?>
						<li><span class="gbacs-bank-k">بانک</span><span class="gbacs-bank-v"><?php echo esc_html( $account['bank'] ); ?></span></li>
					<?php endif; ?>
				</ul>
				<?php if ( $account['sheba'] ) : ?>
					<p class="gbacs-bank-tip">💡 کارت‌به‌کارت سقف روزانه دارد؛ برای مبالغ بالا، واریز با <strong>شماره شبا</strong> مطمئن‌تر و بدون محدودیت است.</p>
				<?php endif; ?>
			</div>

			<!-- STEP-BY-STEP -->
			<div class="gbacs-steps">
				<h3>مراحل تکمیل خرید</h3>
				<ol>
					<li>مبلغ <strong><?php echo esc_html( $total_txt ); ?></strong> را به شماره کارت (یا برای مبالغ بالا، شبا) واریز کنید.</li>
					<li>از رسید پرداخت عکس یا اسکرین‌شات بگیرید.</li>
					<li>فیش را در کادر زیر <strong>بارگذاری</strong> کنید — یا از طریق <strong>واتساپ/تلگرام</strong> بفرستید.</li>
				</ol>
				<p class="gbacs-note">شمارهٔ سفارش شما: <strong>#<?php echo esc_html( $order_no ); ?></strong> — لطفاً هنگام واریز یا ارسال پیام، این شماره را ذکر کنید.</p>
			</div>

			<!-- RECEIPT UPLOAD -->
			<div class="gbacs-upload">
				<h3>📎 بارگذاری فیش واریز</h3>
				<form method="post" enctype="multipart/form-data" class="gbacs-upload-form">
					<?php wp_nonce_field( 'gbacs_upload_' . $order->get_id(), 'gbacs_nonce' ); ?>
					<input type="hidden" name="gbacs_action" value="upload_receipt">
					<input type="hidden" name="gbacs_order" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<input type="hidden" name="gbacs_key" value="<?php echo esc_attr( $order->get_order_key() ); ?>">

					<label class="gbacs-filebox" id="gbacsFilebox">
						<input type="file" name="gbacs_receipt" accept="image/jpeg,image/png,image/webp,application/pdf" required>
						<span class="gbacs-filebox-ic">⬆️</span>
						<span class="gbacs-filebox-txt">عکس یا فایل فیش را اینجا انتخاب کنید<br><small>jpg، png یا pdf — حداکثر ۸ مگابایت</small></span>
						<span class="gbacs-filebox-name"></span>
					</label>

					<button type="submit" class="gbacs-submit"><?php echo $has_receipt ? 'بارگذاری فیش جدید (جایگزین)' : 'ارسال فیش واریز'; ?></button>
				</form>
			</div>

			<!-- QUICK CONTACT -->
			<div class="gbacs-contact">
				<p>سؤالی دارید یا می‌خواهید فیش را مستقیم بفرستید؟ پشتیبانی ما همین حالا پاسخگوست:</p>
				<div class="gbacs-contact-btns">
					<a class="gbacs-btn gbacs-wa" href="<?php echo esc_attr( $whatsapp_url ); ?>" target="_blank" rel="noopener">
						<svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
						ارسال فیش در واتساپ
					</a>
					<a class="gbacs-btn gbacs-tg" href="<?php echo esc_attr( $telegram_url ); ?>" target="_blank" rel="noopener">
						<svg width="22" height="22" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.96 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
						ارسال فیش در تلگرام
					</a>
				</div>
			</div>

			<!-- ALTERNATIVE METHODS -->
			<div class="gbacs-alt">
				راه‌های دیگر پرداخت هم برای شما فعال است: <strong>درگاه پرداخت آنلاین</strong> و <strong>پرداخت در محل (هنگام تحویل)</strong>. در صورت تمایل می‌توانید در خرید بعدی از آن‌ها استفاده کنید.
			</div>
		</div>
	</div>

	<?php gbacs_inline_assets(); ?>
	<?php
	return ob_get_clean();
}

/**
 * Pre-filled Persian message for WhatsApp / Telegram.
 */
function gbacs_build_message( $order_no, $total_txt, $date_jalali ) {
	$lines   = array();
	$lines[] = 'سلام 🙏 فیش واریز سفارش زیر را خدمتتان ارسال می‌کنم؛ لطفاً پرداخت را بررسی و تأیید بفرمایید:';
	$lines[] = '';
	$lines[] = 'شمارهٔ سفارش: #' . $order_no;
	$lines[] = 'مبلغ پرداختی: ' . $total_txt;
	$lines[] = 'تاریخ سفارش: ' . $date_jalali;
	$lines[] = '';
	$lines[] = 'با تشکر از شما.';

	return apply_filters( 'gbacs_message', implode( "\n", $lines ), $order_no, $total_txt, $date_jalali );
}

/* =========================================================================
 * 6. RECEIPT UPLOAD HANDLER
 * ====================================================================== */

add_action( 'wp_loaded', function () {
	if ( empty( $_POST['gbacs_action'] ) || 'upload_receipt' !== $_POST['gbacs_action'] ) {
		return;
	}

	$order_id = isset( $_POST['gbacs_order'] ) ? absint( $_POST['gbacs_order'] ) : 0;
	$order    = $order_id ? wc_get_order( $order_id ) : false;

	if ( ! $order ) {
		gbacs_redirect_with_notice( wc_get_page_permalink( 'shop' ), 'error', 'سفارش پیدا نشد.' );
	}

	// Nonce.
	if ( empty( $_POST['gbacs_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['gbacs_nonce'] ), 'gbacs_upload_' . $order_id ) ) {
		gbacs_redirect_with_notice( $order->get_view_order_url(), 'error', 'درخواست نامعتبر است. لطفاً صفحه را تازه کنید و دوباره تلاش کنید.' );
	}

	// Ownership: logged-in customer OR matching order key (guest from thank-you page).
	$key        = isset( $_POST['gbacs_key'] ) ? wc_clean( wp_unslash( $_POST['gbacs_key'] ) ) : '';
	$owns_login = is_user_logged_in() && (int) $order->get_customer_id() === get_current_user_id();
	$owns_key   = $key && hash_equals( $order->get_order_key(), $key );
	if ( ! $owns_login && ! $owns_key ) {
		gbacs_redirect_with_notice( home_url(), 'error', 'دسترسی شما به این سفارش تأیید نشد.' );
	}

	$back_url = $owns_login ? $order->get_view_order_url() : $order->get_checkout_order_received_url();

	if ( empty( $_FILES['gbacs_receipt']['name'] ) ) {
		gbacs_redirect_with_notice( $back_url, 'error', 'فایلی انتخاب نشده است.' );
	}

	// Validate type & size.
	$allowed = array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf' );
	$check   = wp_check_filetype_and_ext( $_FILES['gbacs_receipt']['tmp_name'], $_FILES['gbacs_receipt']['name'], $allowed );
	if ( empty( $check['ext'] ) || ! in_array( $check['type'], $allowed, true ) ) {
		gbacs_redirect_with_notice( $back_url, 'error', 'فقط فایل‌های jpg، png، webp یا pdf مجاز هستند.' );
	}
	if ( (int) $_FILES['gbacs_receipt']['size'] > 8 * MB_IN_BYTES ) {
		gbacs_redirect_with_notice( $back_url, 'error', 'حجم فایل نباید بیشتر از ۸ مگابایت باشد.' );
	}

	// Store in a protected subfolder of uploads.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	$dir = gbacs_receipts_dir();

	add_filter( 'upload_dir', $gb_upload_dir = function ( $u ) use ( $dir ) {
		$u['path']   = $dir['path'];
		$u['url']    = $dir['url'];
		$u['subdir'] = '/gulfino-receipts';
		return $u;
	} );

	$uploaded = wp_handle_upload(
		$_FILES['gbacs_receipt'],
		array(
			'test_form' => false,
			'unique_filename_callback' => function ( $d, $name, $ext ) use ( $order_id ) {
				return 'receipt-' . $order_id . '-' . substr( md5( $name . microtime() ), 0, 8 ) . $ext;
			},
		)
	);

	remove_filter( 'upload_dir', $gb_upload_dir );

	if ( isset( $uploaded['error'] ) || empty( $uploaded['file'] ) ) {
		gbacs_redirect_with_notice( $back_url, 'error', 'بارگذاری ناموفق بود. لطفاً دوباره تلاش کنید.' );
	}

	// Save on the order (HPOS-safe).
	$order->update_meta_data( '_gbacs_receipt_file', $uploaded['file'] );
	$order->update_meta_data( '_gbacs_receipt_name', sanitize_file_name( $_FILES['gbacs_receipt']['name'] ) );
	$order->update_meta_data( '_gbacs_receipt_time', time() );

	// Move to "Awaiting Payment Confirmation" (only from an unpaid state).
	if ( in_array( $order->get_status(), array( 'on-hold', 'pending' ), true ) ) {
		$order->update_status( 'awaiting-confirm', 'مشتری فیش واریز را بارگذاری کرد.' );
	} else {
		$order->add_order_note( 'مشتری فیش واریز جدیدی بارگذاری کرد.' );
	}
	$order->save();

	gbacs_redirect_with_notice( $back_url, 'success', 'فیش واریز شما با موفقیت ارسال شد. پس از بررسی، تأیید نهایی برای شما ارسال می‌شود. 🌿' );
} );

/** Uploads/gulfino-receipts dir, created with hardening files on first use. */
function gbacs_receipts_dir() {
	$base = wp_upload_dir();
	$dir  = trailingslashit( $base['basedir'] ) . 'gulfino-receipts';
	$url  = trailingslashit( $base['baseurl'] ) . 'gulfino-receipts';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
		// Block direct browsing/hotlinking of customers' financial documents.
		@file_put_contents( $dir . '/.htaccess', "Require all denied\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n" );
		@file_put_contents( $dir . '/index.php', "<?php // Silence is golden.\n" );
	}
	return array( 'path' => $dir, 'url' => $url );
}

/** Redirect helper that surfaces a WooCommerce notice after upload. */
function gbacs_redirect_with_notice( $url, $type, $message ) {
	if ( function_exists( 'wc_add_notice' ) ) {
		wc_add_notice( $message, 'error' === $type ? 'error' : 'success' );
	}
	wp_safe_redirect( $url );
	exit;
}

/* =========================================================================
 * 7. ADMIN — show the receipt on the order screen + secure download
 * ====================================================================== */

add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	$file = $order->get_meta( '_gbacs_receipt_file' );
	if ( ! $file ) {
		echo '<p style="color:#b32d2e;"><strong>فیش واریز:</strong> هنوز بارگذاری نشده.</p>';
		return;
	}
	$name = $order->get_meta( '_gbacs_receipt_name' );
	$time = (int) $order->get_meta( '_gbacs_receipt_time' );
	$dl   = wp_nonce_url(
		admin_url( 'admin-post.php?action=gbacs_download&order=' . $order->get_id() ),
		'gbacs_download_' . $order->get_id()
	);
	echo '<p style="margin-top:10px;"><strong>فیش واریز:</strong> ';
	echo '<a class="button button-primary" href="' . esc_url( $dl ) . '" target="_blank">مشاهده / دانلود فیش</a>';
	if ( $name ) {
		echo ' <span style="color:#666;">(' . esc_html( $name ) . ')</span>';
	}
	if ( $time ) {
		echo '<br><small style="color:#666;">بارگذاری: ' . esc_html( gbacs_jdate( null, $time ) ) . '</small>';
	}
	echo '</p>';
} );

add_action( 'admin_post_gbacs_download', function () {
	$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( 'دسترسی غیرمجاز.' );
	}
	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'gbacs_download_' . $order_id ) ) {
		wp_die( 'درخواست نامعتبر.' );
	}
	$order = wc_get_order( $order_id );
	$file  = $order ? $order->get_meta( '_gbacs_receipt_file' ) : '';
	if ( ! $file || ! file_exists( $file ) ) {
		wp_die( 'فایل پیدا نشد.' );
	}
	$type = wp_check_filetype( $file )['type'] ?: 'application/octet-stream';
	header( 'Content-Type: ' . $type );
	header( 'Content-Disposition: inline; filename="' . basename( $file ) . '"' );
	header( 'Content-Length: ' . filesize( $file ) );
	readfile( $file );
	exit;
} );

/* =========================================================================
 * 8. AUTO-CANCEL unpaid BACS orders after the deadline
 *    Runs hourly; cancels on-hold/pending BACS orders with NO receipt that
 *    are older than the deadline. Orders that uploaded a receipt
 *    (awaiting-confirm) are NEVER auto-cancelled.
 * ====================================================================== */

register_activation_hook( __FILE__, function () {
	if ( ! wp_next_scheduled( 'gbacs_cancel_unpaid' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', 'gbacs_cancel_unpaid' );
	}
} );
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'gbacs_cancel_unpaid' );
} );

add_action( 'gbacs_cancel_unpaid', function () {
	$hours  = gbacs_deadline_hours();
	$cutoff = time() - $hours * HOUR_IN_SECONDS;

	$orders = wc_get_orders( array(
		'status'         => array( 'wc-on-hold', 'wc-pending' ),
		'payment_method' => 'bacs',
		'date_created'   => '<' . $cutoff,
		'limit'          => 50,
		'return'         => 'objects',
	) );

	foreach ( $orders as $order ) {
		if ( $order->get_meta( '_gbacs_receipt_file' ) ) {
			continue; // receipt uploaded → leave for human review
		}
		$order->update_status( 'cancelled', sprintf( 'لغو خودکار: پرداخت ظرف %d ساعت انجام نشد.', $hours ) );
	}
} );

/* =========================================================================
 * 9. CONFIRMATION EMAIL — append deadline + bank details + contact info
 *    to the customer "On hold" email that BACS sends after checkout.
 * ====================================================================== */

add_action( 'woocommerce_email_before_order_table', function ( $order, $sent_to_admin, $plain_text, $email ) {
	if ( $sent_to_admin || 'bacs' !== $order->get_payment_method() ) {
		return;
	}
	if ( ! in_array( $email->id, array( 'customer_on_hold_order', 'customer_invoice' ), true ) ) {
		return;
	}

	$hours    = gbacs_deadline_hours();
	$contacts = gbacs_contacts();
	$total    = trim( wp_strip_all_tags( $order->get_formatted_order_total() ) );
	$msg_enc  = rawurlencode( gbacs_build_message( $order->get_order_number(), $total, gbacs_jdate( $order->get_date_created() ) ) );
	$wa       = 'https://wa.me/' . preg_replace( '/\D/', '', $contacts['whatsapp'] ) . '?text=' . $msg_enc;
	$tg       = 'https://t.me/+' . preg_replace( '/\D/', '', $contacts['telegram'] );
	$upload   = $order->get_checkout_order_received_url();

	if ( $plain_text ) {
		echo "\n----------\n";
		echo "سفارش شما ثبت شد. لطفاً مبلغ {$total} را ظرف {$hours} ساعت واریز کنید و فیش را در این صفحه بارگذاری کنید:\n{$upload}\n";
		echo "واتساپ: {$wa}\nتلگرام: {$tg}\n";
		echo "----------\n\n";
		return;
	}
	?>
	<div style="direction:rtl;text-align:right;background:#f0fbfc;border:1px solid #08B7C8;border-radius:12px;padding:18px 20px;margin:0 0 24px;font-family:Tahoma,Arial,sans-serif;color:#071B3B;line-height:1.9;">
		<p style="margin:0 0 8px;font-size:16px;"><strong>✅ سفارش شما با موفقیت ثبت شد!</strong></p>
		<p style="margin:0 0 8px;">برای نهایی‌شدن خرید، لطفاً مبلغ <strong><?php echo esc_html( $total ); ?></strong> را حداکثر تا <strong><?php echo esc_html( $hours ); ?> ساعت آینده</strong> واریز کنید؛ در غیر این صورت سفارش به‌صورت خودکار لغو می‌شود.</p>
		<p style="margin:0 0 14px;">پس از واریز، فیش را در این صفحه بارگذاری کنید:</p>
		<p style="margin:0 0 14px;">
			<a href="<?php echo esc_url( $upload ); ?>" style="background:#08B7C8;color:#fff;text-decoration:none;padding:10px 18px;border-radius:10px;font-weight:bold;display:inline-block;">📎 بارگذاری فیش واریز</a>
		</p>
		<p style="margin:0;">یا فیش را مستقیم برای ما بفرستید:
			&nbsp;<a href="<?php echo esc_attr( $wa ); ?>" style="color:#25D366;font-weight:bold;">واتساپ</a>
			&nbsp;|&nbsp;<a href="<?php echo esc_attr( $tg ); ?>" style="color:#229ED9;font-weight:bold;">تلگرام</a>
		</p>
	</div>
	<?php
}, 15, 4 );

/* =========================================================================
 * 10. JALALI (Shamsi) DATE  — small self-contained converter
 * ====================================================================== */

/**
 * @param WC_DateTime|null $dt   A WC date object, or null to use $ts.
 * @param int|null         $ts   Unix timestamp (used when $dt is null).
 * @return string e.g. "۱۴۰۳/۰۳/۱۵ - ۱۴:۳۰"
 */
function gbacs_jdate( $dt = null, $ts = null ) {
	if ( $dt instanceof WC_DateTime ) {
		$ts = $dt->getTimestamp();
	} elseif ( null === $ts ) {
		return '';
	}
	$gy = (int) wp_date( 'Y', $ts );
	$gm = (int) wp_date( 'n', $ts );
	$gd = (int) wp_date( 'j', $ts );
	$time = wp_date( 'H:i', $ts );

	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + ( (int) ( ( $gy2 + 3 ) / 4 ) ) - ( (int) ( ( $gy2 + 99 ) / 100 ) )
		+ ( (int) ( ( $gy2 + 399 ) / 400 ) ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * ( (int) ( $days / 12053 ) ) );
	$days  %= 12053;
	$jy    += 4 * ( (int) ( $days / 1461 ) );
	$days  %= 1461;
	if ( $days > 365 ) {
		$jy   += (int) ( ( $days - 1 ) / 365 );
		$days  = ( $days - 1 ) % 365;
	}
	if ( $days < 186 ) {
		$jm = 1 + (int) ( $days / 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + (int) ( ( $days - 186 ) / 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}
	$out = sprintf( '%04d/%02d/%02d - %s', $jy, $jm, $jd, $time );

	// Convert to Persian digits.
	$en = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$fa = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
	return str_replace( $en, $fa, $out );
}

/* =========================================================================
 * 11. INLINE STYLES & SCRIPT  (matches gulfino brand variables)
 * ====================================================================== */

function gbacs_inline_assets() {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	?>
	<style>
	.gbacs{max-width:680px;margin:30px auto;font-family:'Vazirmatn',sans-serif;color:var(--g-navy,#071B3B)}
	.gbacs-card{background:var(--g-white,#fff);border-radius:var(--g-radius,24px);box-shadow:var(--g-shadow,0 15px 45px rgba(7,27,59,.08));padding:30px 28px}
	.gbacs-h2{font-size:21px;font-weight:800;margin:0 0 12px;text-align:center}
	.gbacs-lead{font-size:14.5px;line-height:1.9;color:#3a4a63;margin:0 0 22px;text-align:center}
	.gbacs-amount{display:flex;align-items:center;justify-content:space-between;background:var(--g-navy,#071B3B);color:#fff;border-radius:16px;padding:16px 20px;margin:0 0 16px}
	.gbacs-amount span{font-size:14px;opacity:.85}
	.gbacs-amount strong{font-size:22px;font-weight:900}
	.gbacs-deadline{background:#fff6e8;border:1px solid #f0c97a;border-radius:14px;padding:13px 16px;font-size:13.5px;line-height:1.9;color:#7a5311;margin:0 0 22px}
	.gbacs-deadline strong{font-weight:800;color:#b3760a}
	.gbacs-timer{display:block;font-weight:800;color:#b3760a;margin-top:4px}
	.gbacs-bank{background:var(--g-gray,#F5F7FA);border-radius:16px;padding:18px 20px;margin:0 0 22px}
	.gbacs-bank h3,.gbacs-steps h3,.gbacs-upload h3{font-size:15px;font-weight:700;color:var(--g-cyan,#08B7C8);margin:0 0 14px}
	.gbacs-bank ul{list-style:none;margin:0;padding:0}
	.gbacs-bank li{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px dashed rgba(7,27,59,.1);font-size:14px}
	.gbacs-bank li:last-child{border-bottom:0}
	.gbacs-bank-k{color:#71809a}
	.gbacs-bank-v{font-weight:700;display:flex;align-items:center;gap:8px;direction:ltr}
	.gbacs-bank-v bdi{letter-spacing:.5px}
	.gbacs-bank-tip{font-size:12.5px;line-height:1.9;color:#063e45;background:rgba(8,183,200,.08);border-radius:10px;padding:9px 12px;margin:14px 0 0}
	.gbacs-copybtn{background:var(--g-cyan,#08B7C8);color:#fff;border:0;border-radius:8px;padding:4px 10px;font-size:12px;font-family:inherit;cursor:pointer}
	.gbacs-copybtn:active{transform:scale(.95)}
	.gbacs-steps{margin:0 0 22px}
	.gbacs-steps ol{margin:0;padding-right:20px;font-size:14px;line-height:2.1;color:#3a4a63}
	.gbacs-steps ol strong{color:var(--g-navy,#071B3B)}
	.gbacs-note{font-size:13px;background:rgba(8,183,200,.08);border-radius:10px;padding:10px 12px;margin:12px 0 0;color:#063e45}
	.gbacs-upload{background:rgba(8,183,200,.06);border:1px dashed rgba(8,183,200,.4);border-radius:16px;padding:20px;margin:0 0 22px}
	.gbacs-filebox{display:flex;flex-direction:column;align-items:center;gap:8px;background:#fff;border:2px dashed #c9d6e3;border-radius:14px;padding:24px 16px;cursor:pointer;text-align:center;transition:border-color .2s}
	.gbacs-filebox:hover{border-color:var(--g-cyan,#08B7C8)}
	.gbacs-filebox input[type=file]{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden}
	.gbacs-filebox-ic{font-size:28px}
	.gbacs-filebox-txt{font-size:13.5px;color:#3a4a63;line-height:1.8}
	.gbacs-filebox-txt small{color:#71809a}
	.gbacs-filebox-name{font-size:13px;font-weight:700;color:var(--g-cyan,#08B7C8)}
	.gbacs-submit{display:block;width:100%;margin-top:14px;background:var(--g-cyan,#08B7C8);color:#fff;border:0;border-radius:12px;padding:14px;font-size:15px;font-weight:800;font-family:inherit;cursor:pointer;transition:opacity .2s}
	.gbacs-submit:hover{opacity:.92}
	.gbacs-contact{text-align:center;margin:0 0 18px}
	.gbacs-contact p{font-size:13.5px;color:#3a4a63;margin:0 0 12px}
	.gbacs-contact-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
	.gbacs-btn{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:#fff;font-weight:700;font-size:14px;padding:12px 18px;border-radius:12px;transition:transform .18s}
	.gbacs-btn:hover{transform:translateY(-2px)}
	.gbacs-wa{background:#25D366}
	.gbacs-tg{background:#229ED9}
	.gbacs-alt{font-size:13px;line-height:1.9;color:#3a4a63;background:var(--g-gray,#F5F7FA);border-radius:12px;padding:14px 16px;text-align:center}
	.gbacs-alt strong{color:var(--g-navy,#071B3B)}
	.gbacs-success{display:flex;align-items:flex-start;gap:12px;background:#e9f9ee;border:1px solid #4caf7d;border-radius:16px;padding:16px 18px;margin:0 0 18px}
	.gbacs-success-ic{flex-shrink:0;width:30px;height:30px;border-radius:50%;background:#22a35a;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900}
	.gbacs-success strong{display:block;margin-bottom:4px;color:#13713f}
	.gbacs-success p{margin:0;font-size:13.5px;line-height:1.9;color:#2a6845}
	@media(max-width:520px){.gbacs-card{padding:22px 16px}.gbacs-amount strong{font-size:18px}.gbacs-contact-btns .gbacs-btn{flex:1;justify-content:center}}
	</style>
	<script>
	(function(){
		// File name preview.
		document.querySelectorAll('.gbacs-filebox input[type=file]').forEach(function(inp){
			inp.addEventListener('change',function(){
				var box=inp.closest('.gbacs-filebox');
				var n=box&&box.querySelector('.gbacs-filebox-name');
				if(n)n.textContent=inp.files.length?('✓ '+inp.files[0].name):'';
			});
		});
		// Copy-to-clipboard for card/sheba.
		document.querySelectorAll('.gbacs [data-copy]').forEach(function(el){
			el.addEventListener('click',function(){
				var v=el.getAttribute('data-copy')||'';
				if(navigator.clipboard){navigator.clipboard.writeText(v);}
				var b=el.matches('.gbacs-copybtn')?el:el.parentNode.querySelector('.gbacs-copybtn');
				if(b){var t=b.textContent;b.textContent='کپی شد ✓';setTimeout(function(){b.textContent=t;},1500);}
			});
		});
		// Live countdown.
		var dl=document.querySelector('.gbacs-deadline');
		var timer=document.getElementById('gbacsTimer');
		if(dl&&timer){
			var target=parseInt(dl.getAttribute('data-deadline'),10)*1000;
			function fa(n){return String(n).replace(/[0-9]/g,function(d){return '۰۱۲۳۴۵۶۷۸۹'[d];});}
			function tick(){
				var diff=target-Date.now();
				if(diff<=0){timer.textContent='مهلت پرداخت به پایان رسیده است.';return;}
				var h=Math.floor(diff/3.6e6),m=Math.floor(diff%3.6e6/6e4);
				timer.textContent='زمان باقی‌مانده: '+fa(h)+' ساعت و '+fa(m)+' دقیقه';
				setTimeout(tick,30000);
			}
			tick();
		}
	})();
	</script>
	<?php
}
