<?php
/**
 * Seeder script for WooCommerce & COD Anti-Fraud Playground environment
 *
 * Populates WooCommerce with demo catalog items, simulated COD orders with varying
 * risk profiles (Low, Medium, High risk/blocked), and optimal COD Anti-Fraud plugin settings.
 */

// Context and security check
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
	exit( 'Direct access denied.' );
}

// Ensure WooCommerce is loaded
if ( ! class_exists( 'WooCommerce' ) ) {
	if ( defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
		require_once WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
	}
}

if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_create_order' ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	error_log( '[WCCAF Seed] WooCommerce not detected or not loaded. Halting seed.' );
	return;
}

// 1. Configure and enable COD payment gateway in WooCommerce
$cod_settings = array(
	'enabled'            => 'yes',
	'title'              => 'Contrassegno (COD)',
	'description'        => 'Paga comodamente in contanti alla consegna del pacco.',
	'instructions'       => 'Paga in contanti al corriere al momento della consegna.',
	'enable_for_methods' => array(),
	'enable_for_virtual' => 'no',
);
update_option( 'woocommerce_cod_settings', $cod_settings );

// 2. Configure Woo COD Anti-Fraud plugin settings in database
$wccaf_settings = array(
	'enabled'                         => 'yes',
	'verification_timing'             => 'pre_checkout',
	'require_otp_mode'                => 'always',
	'risk_threshold'                  => 40,
	'min_cart_amount_otp'             => 0.0,
	'max_cart_amount_cod'             => 500.0,
	'max_cart_action'                 => 'require_otp',
	'blacklist_phones'                => "+393339999999
+393330000000",
	'whitelist_phones'                => "+393401234567",
	'rate_limit_max_requests'         => 3,
	'rate_limit_window_minutes'       => 10,
	'whatsapp_provider'               => 'beralabs',
	'beralabs_endpoint'               => 'https://api.beralabs.it/wa/api/send-otp',
	'beralabs_api_key'                => 'demo_playground_api_key_wccaf',
	'instance_id'                     => '',
	'api_token'                       => '',
	'otp_expiry_minutes'              => 5,
	'max_attempts'                    => 3,
	'custom_message_template'         => "Il tuo codice di verifica per l'ordine in contrassegno è: {{otp}}. Valido 5 minuti.",
	'enable_trust_badge'              => 'yes',
	'trust_badge_position'            => 'woocommerce_review_order_after_submit',
	'wccaf_whatsapp_branding_enabled' => 'yes',
);

update_option( 'wc_cod_antifraud_settings', $wccaf_settings );
update_option( 'wccaf_verified_orders_count', 12 );
update_option( 'wccaf_whatsapp_branding_enabled', 'yes' );
update_option( 'wccaf_review_dismissed', 'no' );
update_option( 'wccaf_review_snooze_until', 0 );

// 3. Register admin menu redirect helper for landingPage "/wp-admin/admin.php?page=woo-cod-antifraud"
if ( defined( 'WP_CONTENT_DIR' ) ) {
	$mu_dir = WP_CONTENT_DIR . '/mu-plugins';
	if ( ! is_dir( $mu_dir ) ) {
		wp_mkdir_p( $mu_dir );
	}
	$helper_file = $mu_dir . '/wccaf-landing-helper.php';
	$helper_code = <<<'PHP'
<?php
// Auto-generated helper for Playground landingPage redirect
add_action( 'admin_menu', function() {
	add_menu_page(
		'COD Anti-Fraud',
		'COD Anti-Fraud',
		'manage_woocommerce',
		'woo-cod-antifraud',
		function() {
			if ( wp_safe_redirect( admin_url( 'admin.php?page=wc-settings&tab=wc_cod_antifraud' ) ) ) {
				exit;
			}
		},
		'dashicons-shield',
		56
	);
}, 20 );
PHP;
	if ( ! file_exists( $helper_file ) ) {
		file_put_contents( $helper_file, $helper_code );
	}
}

// 4. Create or retrieve fictitious test product: "Smartphone X Protection Test" (price 150)
$product_title = 'Smartphone X Protection Test';
$product_price = '150.00';
$product_sku   = 'SMART-X-PROT-150';

$existing_products = get_posts( array(
	'post_type'      => 'product',
	'title'          => $product_title,
	'post_status'    => 'all',
	'posts_per_page' => 1,
	'fields'         => 'ids',
) );

if ( ! empty( $existing_products ) ) {
	$product_id = $existing_products[0];
} else {
	$product = new WC_Product_Simple();
	$product->set_name( $product_title );
	$product->set_slug( 'smartphone-x-protection-test' );
	$product->set_regular_price( $product_price );
	$product->set_price( $product_price );
	$product->set_sku( $product_sku );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( 'Prodotto fittizio di test per la simulazione e il collaudo del motore anti-frode contrassegno.' );
	$product->set_description( 'Smartphone demo ad alte prestazioni per testare il calcolo dinamico del Risk Score e la verifica OTP WhatsApp con Woo COD Anti-Fraud.' );
	$product_id = $product->save();
}

$product_obj = wc_get_product( $product_id );

// 5. Create 3 test COD orders with distinct risk profiles
$now_str = current_time( 'mysql' );

// Order 1: Basso Rischio, Verificato/Approvato
$existing_order_1 = get_posts( array(
	'post_type'   => array( 'shop_order', 'shop_order_placehold' ),
	'meta_key'    => '_wccaf_demo_order',
	'meta_value'  => 'order_low_risk',
	'post_status' => 'any',
	'numberposts' => 1,
	'fields'      => 'ids',
) );

if ( empty( $existing_order_1 ) ) {
	$order1 = wc_create_order();
	$order1->add_product( $product_obj, 1 );
	$order1->set_address( array(
		'first_name' => 'Mario',
		'last_name'  => 'Rossi',
		'company'    => 'Rossi Design',
		'email'      => 'mario.rossi@example.com',
		'phone'      => '+393401234567',
		'address_1'  => 'Via Montenapoleone 8',
		'city'       => 'Milano',
		'state'      => 'MI',
		'postcode'   => '20121',
		'country'    => 'IT',
	), 'billing' );
	$order1->set_address( array(
		'first_name' => 'Mario',
		'last_name'  => 'Rossi',
		'address_1'  => 'Via Montenapoleone 8',
		'city'       => 'Milano',
		'state'      => 'MI',
		'postcode'   => '20121',
		'country'    => 'IT',
	), 'shipping' );
	$order1->set_payment_method( 'cod' );
	$order1->set_payment_method_title( 'Contrassegno (COD)' );
	$order1->set_status( 'processing', 'Ordine confermato e pronto alla spedizione.' );

	// Metadata specificati
	$order1->update_meta_data( '_wccaf_risk_score', 15 );
	$order1->update_meta_data( '_wccaf_verification_status', 'verified' );
	$order1->update_meta_data( '_wccaf_demo_order', 'order_low_risk' );

	// Metadata compatibilità pannello ordine admin
	$order1->update_meta_data( '_cod_risk_score', 15 );
	$order1->update_meta_data( '_cod_verified', 'yes' );
	$order1->update_meta_data( '_cod_otp_required', 'yes' );
	$order1->update_meta_data( '_cod_verified_phone', '+393401234567' );
	$order1->update_meta_data( '_cod_verification_timestamp', $now_str );
	$order1->update_meta_data( '_wccaf_verified_counted', 'yes' );

	$order1->calculate_totals();
	$order1->save();

	$order1->add_order_note( "[COD Anti-Fraud] Risk Score: 15/100 (Basso rischio). Verifica OTP WhatsApp completata con successo al numero +393401234567. Ordine approvato per l'evasione." );
}

// Order 2: Medio Rischio, In Attesa di Verifica OTP WhatsApp
$existing_order_2 = get_posts( array(
	'post_type'   => array( 'shop_order', 'shop_order_placehold' ),
	'meta_key'    => '_wccaf_demo_order',
	'meta_value'  => 'order_medium_risk',
	'post_status' => 'any',
	'numberposts' => 1,
	'fields'      => 'ids',
) );

if ( empty( $existing_order_2 ) ) {
	$order2 = wc_create_order();
	$order2->add_product( $product_obj, 1 );
	$order2->set_address( array(
		'first_name' => 'Luca',
		'last_name'  => 'Bianchi',
		'company'    => '',
		'email'      => 'luca.bianchi@example.com',
		'phone'      => '+393498877665',
		'address_1'  => 'Corso Umberto I 120',
		'city'       => 'Napoli',
		'state'      => 'NA',
		'postcode'   => '80138',
		'country'    => 'IT',
	), 'billing' );
	$order2->set_address( array(
		'first_name' => 'Luca',
		'last_name'  => 'Bianchi',
		'address_1'  => 'Corso Umberto I 120',
		'city'       => 'Napoli',
		'state'      => 'NA',
		'postcode'   => '80138',
		'country'    => 'IT',
	), 'shipping' );
	$order2->set_payment_method( 'cod' );
	$order2->set_payment_method_title( 'Contrassegno (COD)' );
	$order2->set_status( 'on-hold', 'In attesa di verifica OTP WhatsApp da parte del cliente.' );

	// Metadata specificati
	$order2->update_meta_data( '_wccaf_risk_score', 55 );
	$order2->update_meta_data( '_wccaf_verification_status', 'pending_otp' );
	$order2->update_meta_data( '_wccaf_demo_order', 'order_medium_risk' );

	// Metadata compatibilità pannello ordine admin
	$order2->update_meta_data( '_cod_risk_score', 55 );
	$order2->update_meta_data( '_cod_verified', 'no' );
	$order2->update_meta_data( '_cod_otp_required', 'yes' );
	$order2->update_meta_data( '_cod_verified_phone', '+393498877665' );

	$order2->calculate_totals();
	$order2->save();

	$order2->add_order_note( '[COD Anti-Fraud] Risk Score: 55/100 (Medio rischio - Nuovo acquirente, CAP ad alta percentuale di giacenze COD). Codice OTP WhatsApp inviato al numero +393498877665. In attesa di validazione.' );
}

// Order 3: Alto Rischio, Bloccato/Annullato
$existing_order_3 = get_posts( array(
	'post_type'   => array( 'shop_order', 'shop_order_placehold' ),
	'meta_key'    => '_wccaf_demo_order',
	'meta_value'  => 'order_high_risk',
	'post_status' => 'any',
	'numberposts' => 1,
	'fields'      => 'ids',
) );

if ( empty( $existing_order_3 ) ) {
	$order3 = wc_create_order();
	$order3->add_product( $product_obj, 1 );
	$order3->set_address( array(
		'first_name' => 'Falso',
		'last_name'  => 'Tentativo',
		'company'    => '',
		'email'      => 'fraud.tester99@disposablemail.org',
		'phone'      => '+393339999999',
		'address_1'  => 'Via Sospetta 999',
		'city'       => 'Palermo',
		'state'      => 'PA',
		'postcode'   => '90100',
		'country'    => 'IT',
	), 'billing' );
	$order3->set_address( array(
		'first_name' => 'Falso',
		'last_name'  => 'Tentativo',
		'address_1'  => 'Via Sospetta 999',
		'city'       => 'Palermo',
		'state'      => 'PA',
		'postcode'   => '90100',
		'country'    => 'IT',
	), 'shipping' );
	$order3->set_payment_method( 'cod' );
	$order3->set_payment_method_title( 'Contrassegno (COD)' );
	$order3->set_status( 'cancelled', 'Ordine bloccato e annullato dal sistema anti-frode.' );

	// Metadata specificati
	$order3->update_meta_data( '_wccaf_risk_score', 90 );
	$order3->update_meta_data( '_wccaf_verification_status', 'blocked' );
	$order3->update_meta_data( '_wccaf_demo_order', 'order_high_risk' );

	// Metadata compatibilità pannello ordine admin
	$order3->update_meta_data( '_cod_risk_score', 90 );
	$order3->update_meta_data( '_cod_verified', 'no' );
	$order3->update_meta_data( '_cod_otp_required', 'yes' );
	$order3->update_meta_data( '_cod_verified_phone', '+393339999999' );

	$order3->calculate_totals();
	$order3->save();

	$order3->add_order_note( '[COD Anti-Fraud] BLOCCATO / ANNULLATO: Risk Score critico: 90/100. Motivo: Numero telefonico (+393339999999) presente nella Blacklist o ripetuti tentativi fraudolenti rilevati dal Rate Limiter.' );
}
