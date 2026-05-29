<?php
/**
 * Gulfino – Custom Login / Register page
 * Overrides: woocommerce/myaccount/form-login.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

do_action( 'woocommerce_before_customer_login_form' );
?>

<style>
/* ============================================================
   GULFINO AUTH PAGE
============================================================ */
.g-auth-page {
  min-height: 80vh;
  background: linear-gradient(135deg, #f0fafb 0%, #f5f7fa 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 20px;
}
.g-auth-box {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 960px;
  width: 100%;
  background: #fff;
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 30px 80px rgba(7, 27, 59, .12);
}

/* ---- Brand Panel ---- */
.g-auth-brand {
  background: linear-gradient(145deg, #071B3B 0%, #0a2a55 100%);
  padding: 60px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.g-auth-brand::before {
  content: '';
  position: absolute;
  width: 280px; height: 280px;
  border-radius: 50%;
  background: rgba(8, 183, 200, .08);
  top: -60px; left: -80px;
}
.g-auth-brand::after {
  content: '';
  position: absolute;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: rgba(213, 165, 78, .07);
  bottom: -40px; right: -50px;
}
.g-auth-logo {
  font-size: 52px;
  font-weight: 900;
  color: #fff;
  letter-spacing: -2px;
  margin-bottom: 8px;
  position: relative;
  z-index: 1;
}
.g-auth-logo span { color: #08B7C8; }
.g-auth-tagline {
  color: rgba(255,255,255,.65);
  font-size: 14px;
  margin-bottom: 48px;
  position: relative;
  z-index: 1;
}
.g-auth-perks { list-style: none; padding: 0; margin: 0; position: relative; z-index: 1; }
.g-auth-perks li {
  display: flex;
  align-items: center;
  gap: 14px;
  color: rgba(255,255,255,.85);
  font-size: 14px;
  font-weight: 600;
  padding: 12px 0;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.g-auth-perks li:last-child { border-bottom: none; }
.g-perk-icon {
  width: 38px; height: 38px;
  background: rgba(255,255,255,.08);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  font-size: 18px;
}

/* ---- Form Panel ---- */
.g-auth-form-panel {
  padding: 52px 44px;
  overflow-y: auto;
}

/* Tab switcher */
.g-auth-tabs {
  display: flex;
  background: #f5f7fa;
  border-radius: 14px;
  padding: 4px;
  margin-bottom: 36px;
  gap: 4px;
}
.g-auth-tab {
  flex: 1;
  padding: 11px;
  border-radius: 11px;
  border: none;
  font-family: 'Vazirmatn', sans-serif;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all .25s;
  background: transparent;
  color: #888;
}
.g-auth-tab.active {
  background: #fff;
  color: #071B3B;
  box-shadow: 0 4px 14px rgba(7,27,59,.08);
}

/* Forms */
.g-auth-section { display: none; }
.g-auth-section.active { display: block; }
.g-auth-section h2 {
  font-size: 24px; font-weight: 900;
  color: #071B3B; margin-bottom: 6px;
}
.g-auth-section p.sub {
  color: #888; font-size: 13px; margin-bottom: 28px;
}
.g-field { margin-bottom: 20px; }
.g-field label {
  display: block;
  font-size: 13px; font-weight: 700;
  color: #444; margin-bottom: 8px;
}
.g-field input {
  width: 100%;
  padding: 13px 18px;
  border: 1.5px solid #e8ecf0;
  border-radius: 13px;
  font-family: 'Vazirmatn', sans-serif;
  font-size: 14px;
  color: #1a1a2e;
  background: #fafbfc;
  transition: border-color .2s, box-shadow .2s;
  outline: none;
}
.g-field input:focus {
  border-color: #08B7C8;
  box-shadow: 0 0 0 3px rgba(8,183,200,.12);
  background: #fff;
}
.g-field-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}
.g-field-row label {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; color: #666; cursor: pointer; font-weight: 500;
}
.g-field-row label input[type=checkbox] { width: auto; }
.g-field-row a { font-size: 13px; color: #08B7C8; font-weight: 700; }
.g-field-row a:hover { text-decoration: underline; }
.g-btn-submit {
  width: 100%;
  padding: 15px;
  background: #08B7C8;
  color: #fff;
  border: none;
  border-radius: 14px;
  font-family: 'Vazirmatn', sans-serif;
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  transition: background .25s, transform .2s, box-shadow .25s;
  box-shadow: 0 8px 24px rgba(8,183,200,.3);
}
.g-btn-submit:hover {
  background: #07a0b0;
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(8,183,200,.35);
}
.g-divider {
  display: flex; align-items: center; gap: 12px;
  color: #ccc; font-size: 12px; margin: 22px 0;
}
.g-divider::before, .g-divider::after {
  content: ''; flex: 1; height: 1px; background: #eee;
}
.g-privacy-note {
  text-align: center; font-size: 12px; color: #aaa; margin-top: 18px; line-height: 1.7;
}
.g-privacy-note a { color: #08B7C8; }
.woocommerce-error {
  background: #fff5f5;
  border: 1.5px solid #ffcdd2;
  border-radius: 12px;
  padding: 14px 18px;
  color: #c62828;
  font-size: 13px;
  margin-bottom: 20px;
  list-style: none;
}
/* Hide WooCommerce's raw privacy text — we use our own */
.woocommerce-privacy-policy-text { display: none !important; }
.g-consent-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 13px;
  color: #666;
  margin-bottom: 20px;
  cursor: pointer;
  line-height: 1.6;
}
.g-consent-row input[type=checkbox] {
  width: 17px; height: 17px;
  margin-top: 2px; flex-shrink: 0;
  accent-color: #08B7C8;
  cursor: pointer;
}
.g-consent-row a { color: #08B7C8; font-weight: 700; }
.g-consent-row a:hover { text-decoration: underline; }

@media (max-width: 700px) {
  .g-auth-box { grid-template-columns: 1fr; }
  .g-auth-brand { padding: 40px 28px; }
  .g-auth-form-panel { padding: 36px 24px; }
}
</style>

<div class="g-auth-page">
  <div class="g-auth-box">

    <!-- Brand panel -->
    <div class="g-auth-brand">
      <div class="g-auth-logo">Gulf<span>ino</span></div>
      <div class="g-auth-tagline">خرید مستقیم از خلیج فارس</div>
      <ul class="g-auth-perks">
        <li>
          <span class="g-perk-icon">✈️</span>
          ارسال مستقیم از امارات و عمان
        </li>
        <li>
          <span class="g-perk-icon">🛡️</span>
          ضمانت ۱۰۰٪ اصالت کالا
        </li>
        <li>
          <span class="g-perk-icon">🎁</span>
          تخفیف ویژه اعضا
        </li>
        <li>
          <span class="g-perk-icon">💬</span>
          پشتیبانی واتساپی ۲۴/۷
        </li>
        <li>
          <span class="g-perk-icon">🔒</span>
          پرداخت کاملاً امن
        </li>
      </ul>
    </div>

    <!-- Form panel -->
    <div class="g-auth-form-panel">

      <!-- Tabs -->
      <div class="g-auth-tabs">
        <button class="g-auth-tab active" onclick="gAuth('login',this)">ورود به حساب</button>
        <button class="g-auth-tab" onclick="gAuth('register',this)">ثبت‌نام</button>
      </div>

      <!-- WooCommerce errors -->
      <?php wc_print_notices(); ?>

      <!-- LOGIN -->
      <div class="g-auth-section active" id="g-auth-login">
        <h2>خوش آمدید 👋</h2>
        <p class="sub">با حساب کاربری خود وارد شوید</p>

        <form class="woocommerce-form woocommerce-form-login" method="post">
          <?php do_action( 'woocommerce_login_form_start' ); ?>

          <div class="g-field">
            <label for="username">ایمیل یا نام کاربری</label>
            <input type="text" id="username" name="username"
                   value="<?php echo esc_attr( isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : '' ); ?>"
                   placeholder="example@email.com" autocomplete="username">
          </div>

          <div class="g-field">
            <label for="password">رمز عبور</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••" autocomplete="current-password">
          </div>

          <div class="g-field-row">
            <label>
              <input type="checkbox" name="rememberme" value="forever">
              مرا به خاطر بسپار
            </label>
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">فراموشی رمز عبور؟</a>
          </div>

          <?php do_action( 'woocommerce_login_form' ); ?>

          <input type="hidden" name="redirect" value="<?php echo esc_attr( wc_get_page_permalink( 'myaccount' ) ); ?>">
          <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

          <button type="submit" class="g-btn-submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
            ورود به حساب کاربری
          </button>

          <?php do_action( 'woocommerce_login_form_end' ); ?>
        </form>

        <div class="g-divider">یا</div>
        <p class="g-privacy-note">
          هنوز حساب ندارید؟
          <a href="#" onclick="gAuth('register',document.querySelectorAll('.g-auth-tab')[1]);return false;">همین الان ثبت‌نام کنید</a>
        </p>
      </div>

      <!-- REGISTER -->
      <?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>
      <div class="g-auth-section" id="g-auth-register">
        <h2>ساخت حساب جدید ✨</h2>
        <p class="sub">عضویت رایگان است و کمتر از یک دقیقه طول می‌کشد</p>

        <form class="woocommerce-form woocommerce-form-register" method="post">
          <?php do_action( 'woocommerce_register_form_start' ); ?>

          <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
          <div class="g-field">
            <label for="reg_username">نام کاربری</label>
            <input type="text" id="reg_username" name="username"
                   value="<?php echo esc_attr( isset( $_POST['username'] ) ? wp_unslash( $_POST['username'] ) : '' ); ?>"
                   placeholder="نام کاربری دلخواه">
          </div>
          <?php endif; ?>

          <div class="g-field">
            <label for="reg_email">آدرس ایمیل</label>
            <input type="email" id="reg_email" name="email"
                   value="<?php echo esc_attr( isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '' ); ?>"
                   placeholder="example@email.com" autocomplete="email">
          </div>

          <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
          <div class="g-field">
            <label for="reg_password">رمز عبور</label>
            <input type="password" id="reg_password" name="password"
                   placeholder="حداقل ۸ کاراکتر" autocomplete="new-password">
          </div>
          <?php endif; ?>

          <?php do_action( 'woocommerce_register_form' ); ?>

          <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

          <!-- Privacy consent -->
          <label class="g-consent-row">
            <input type="checkbox" name="gtorob_privacy" required>
            <span>
              با
              <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" target="_blank">سیاست حریم خصوصی</a>
              و
              <a href="#" target="_blank">شرایط استفاده</a>
              موافقم
            </span>
          </label>

          <button type="submit" class="g-btn-submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
            ساخت حساب کاربری رایگان
          </button>

          <?php do_action( 'woocommerce_register_form_end' ); ?>
        </form>

        <p class="g-privacy-note">
          قبلاً حساب دارید؟
          <a href="#" onclick="gAuth('login',document.querySelectorAll('.g-auth-tab')[0]);return false;">وارد شوید</a>
        </p>
      </div>
      <?php endif; ?>

    </div><!-- /.g-auth-form-panel -->
  </div><!-- /.g-auth-box -->
</div>

<script>
function gAuth(section, btn) {
  document.querySelectorAll('.g-auth-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.g-auth-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('g-auth-' + section).classList.add('active');
  btn.classList.add('active');
}
// Auto-switch to register tab if URL has ?action=register
if (window.location.search.includes('action=register')) {
  gAuth('register', document.querySelectorAll('.g-auth-tab')[1]);
}
</script>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
