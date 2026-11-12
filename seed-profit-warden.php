<?php
/**
 * Seeder script for WooCommerce & Profit Warden Playground environment
 *
 * Populates WooCommerce with demo catalog items, realistic completed orders
 * distributed across the last 30 days, COGS metadata (_purchase_cost),
 * profit analysis records, variable & fixed costs, and dashboard intelligence mock.
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
	error_log( '[Profit Warden Seed] WooCommerce not detected or not loaded. Halting seed.' );
	return;
}

// -----------------------------------------------------------------------------
// 1. Configure Profit Warden Settings & Cost Structures in wp_options
// -----------------------------------------------------------------------------
$profwabr_settings = array(
	'enable_api_sync'         => '1',
	'api_url'                 => 'https://api.beralabs.it/calculate-profit',
	'api_key'                 => 'demo_playground_key_profitwarden_998877',
	'packaging_cost'          => '0.80',
	'default_shipping'        => '5.00',
	'paypal_fixed'            => '0.35',
	'stripe_fixed'            => '0.25',
	'cod_fixed'               => '3.00',
	'report_email'            => get_option( 'admin_email', 'admin@example.com' ),
	'report_frequency'        => 'weekly',
	'enable_loss_alerts'      => '1',
	'enable_breakeven_alerts' => '1',
	// Monthly operational fixed expenses (Total = 2,500.00 €/mo => ~83.33 €/day burn rate)
	'fixed_hosting'           => '80.00',
	'fixed_saas'              => '170.00',
	'fixed_marketing'         => '350.00',
	'fixed_salaries'          => '1200.00',
	'fixed_rent'              => '450.00',
	'fixed_insurance'         => '50.00',
	'fixed_accounting'        => '120.00',
	'fixed_other'             => '80.00',
);

update_option( 'profwabr_settings', $profwabr_settings );

// Suppress setup wizard redirect transient
delete_transient( 'profwabr_activation_redirect' );
update_option( 'profwabr_setup_completed', '1' );

// -----------------------------------------------------------------------------
// 2. Configure WooCommerce Shipping Method Actual Cost
// -----------------------------------------------------------------------------
// Ensure default Flat Rate zone method has profwabr_real_cost set for health checks
$flat_rate_options = get_option( 'woocommerce_flat_rate_settings', array() );
$flat_rate_options['enabled']            = 'yes';
$flat_rate_options['title']              = 'Spedizione Espressa 24/48h';
$flat_rate_options['cost']               = '5.00';
$flat_rate_options['profwabr_real_cost'] = '4.50';
update_option( 'woocommerce_flat_rate_settings', $flat_rate_options );

// -----------------------------------------------------------------------------
// 3. Register Playground Mock Provider (mu-plugin) for Offline Wasm Simulation
// -----------------------------------------------------------------------------
if ( defined( 'WP_CONTENT_DIR' ) ) {
	$mu_dir = WP_CONTENT_DIR . '/mu-plugins';
	if ( ! is_dir( $mu_dir ) ) {
		wp_mkdir_p( $mu_dir );
	}
	$mock_file = $mu_dir . '/profwabr-playground-provider.php';
	$mock_code = <<<'PHP'
<?php
/**
 * Plugin Name: Profit Warden Playground Provider & Mock Engine
 * Description: Intercepts external API calls and serves real-time computed financial data locally in Wasm Playground.
 */

add_action( 'admin_init', function() {
	delete_transient( 'profwabr_activation_redirect' );
} );

// Intercept wp_remote HTTP requests targeting the Profit Warden API
add_filter( 'pre_http_request', function( $preempt, $parsed_args, $url ) {
	if ( false === strpos( $url, 'beralabs.it' ) ) {
		return $preempt;
	}

	// 1. Health check endpoint ping (/docs)
	if ( false !== strpos( $url, '/docs' ) ) {
		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body'     => json_encode( array( 'status' => 'online', 'service' => 'Profit Warden Engine' ) ),
		);
	}

	// 2. Ads Summary endpoint (/ad-summary)
	if ( false !== strpos( $url, 'ad-summary' ) ) {
		$ad_data = array(
			'total_ad_spend' => 480.00,
			'total_revenue'  => 2350.00,
			'global_roas'    => 4.89,
			'campaigns'      => array(
				array(
					'platform'           => 'meta',
					'campaign_name'      => 'BOFU - Dynamic Retargeting IT',
					'spend'              => 210.00,
					'attributed_orders'  => 11,
					'attributed_revenue' => 1240.00,
					'roas'               => 5.90,
				),
				array(
					'platform'           => 'meta',
					'campaign_name'      => 'MOFU - Lookalike Top Spenders 1%',
					'spend'              => 160.00,
					'attributed_orders'  => 5,
					'attributed_revenue' => 720.00,
					'roas'               => 4.50,
				),
				array(
					'platform'           => 'meta',
					'campaign_name'      => 'TOF - Brand Awareness Video Reels',
					'spend'              => 110.00,
					'attributed_orders'  => 2,
					'attributed_revenue' => 390.00,
					'roas'               => 3.54,
				),
			),
		);
		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body'     => json_encode( $ad_data ),
		);
	}

	// 3. Dashboard Data endpoint (/dashboard-data)
	if ( false !== strpos( $url, 'dashboard-data' ) ) {
		global $wpdb;

		// Query completed demo orders
		$orders = wc_get_orders( array(
			'limit'  => 30,
			'status' => array( 'wc-completed', 'completed' ),
			'order'  => 'ASC',
		) );

		$total_revenue = 0.0;
		$total_profit  = 0.0;
		$total_cogs    = 0.0;
		$total_vat     = 0.0;
		$order_count   = count( $orders );

		$daily_map     = array();
		$recent_orders = array();
		$prod_rank_map = array();
		$cust_rank_map = array();

		foreach ( $orders as $order ) {
			$order_id   = $order->get_id();
			$order_rev  = (float) $order->get_total();
			$order_date = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d' ) : current_time( 'Y-m-d' );
			$analysis   = $order->get_meta( '_profwabr_profit_analysis_data', true );

			$order_profit = 0.0;
			$order_cogs   = 0.0;
			$order_vat    = (float) $order->get_total_tax();

			if ( is_array( $analysis ) && isset( $analysis['summary']['net_profit'] ) ) {
				$order_profit = (float) $analysis['summary']['net_profit'];
				if ( isset( $analysis['breakdown']['cogs'] ) ) {
					$order_cogs = (float) $analysis['breakdown']['cogs'];
				}
			} else {
				$order_profit = $order_rev * 0.45;
				$order_cogs   = $order_rev * 0.35;
			}

			$total_revenue += $order_rev;
			$total_profit  += $order_profit;
			$total_cogs    += $order_cogs;
			$total_vat     += $order_vat;

			// Aggregate daily stats for Chart.js
			if ( ! isset( $daily_map[ $order_date ] ) ) {
				$daily_map[ $order_date ] = array(
					'day'         => $order_date,
					'revenue'     => 0.0,
					'profit'      => 0.0,
					'real_profit' => 0.0,
					'ad_spend'    => 16.0,
				);
			}
			$daily_map[ $order_date ]['revenue']     += $order_rev;
			$daily_map[ $order_date ]['profit']      += $order_profit;
			$daily_map[ $order_date ]['real_profit'] += ( $order_profit - 16.0 );

			// Recent order feed item
			$margin_val = $order_rev > 0 ? ( ( $order_profit / $order_rev ) * 100 ) : 0.0;
			$recent_orders[] = array(
				'order_id' => $order_id,
				'revenue'  => $order_rev,
				'vat'      => $order_vat,
				'cogs'     => $order_cogs,
				'profit'   => $order_profit,
				'margin'   => round( $margin_val, 1 ),
			);

			// Aggregate customer ranking
			$cust_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
			if ( empty( $cust_name ) ) {
				$cust_name = 'Cliente Demo #' . $order_id;
			}
			if ( ! isset( $cust_rank_map[ $cust_name ] ) ) {
				$cust_rank_map[ $cust_name ] = array(
					'name'    => $cust_name,
					'orders'  => 0,
					'revenue' => 0.0,
					'profit'  => 0.0,
				);
			}
			$cust_rank_map[ $cust_name ]['orders']  += 1;
			$cust_rank_map[ $cust_name ]['revenue'] += $order_rev;
			$cust_rank_map[ $cust_name ]['profit']  += $order_profit;

			// Aggregate product ranking
			foreach ( $order->get_items() as $item ) {
				$p_name = $item->get_name();
				$qty    = (int) $item->get_quantity();
				$p_rev  = (float) $item->get_total();
				$p_vat  = (float) $item->get_total_tax();
				$p_obj  = $item->get_product();
				$p_cost = $p_obj ? (float) get_post_meta( $p_obj->get_id(), '_purchase_cost', true ) : 0.0;
				$p_cogs = $p_cost * $qty;

				if ( ! isset( $prod_rank_map[ $p_name ] ) ) {
					$prod_rank_map[ $p_name ] = array(
						'name'    => $p_name,
						'qty'     => 0,
						'revenue' => 0.0,
						'vat'     => 0.0,
						'cogs'    => 0.0,
					);
				}
				$prod_rank_map[ $p_name ]['qty']     += $qty;
				$prod_rank_map[ $p_name ]['revenue'] += $p_rev;
				$prod_rank_map[ $p_name ]['vat']     += $p_vat;
				$prod_rank_map[ $p_name ]['cogs']    += $p_cogs;
			}
		}

		// Fill missing days in the 30-day range for smooth chart rendering
		$chart_data = array();
		for ( $i = 29; $i >= 0; $i-- ) {
			$date_key = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			if ( isset( $daily_map[ $date_key ] ) ) {
				$chart_data[] = array(
					'day'         => $date_key,
					'revenue'     => round( $daily_map[ $date_key ]['revenue'], 2 ),
					'profit'      => round( $daily_map[ $date_key ]['profit'], 2 ),
					'real_profit' => round( $daily_map[ $date_key ]['real_profit'], 2 ),
					'ad_spend'    => round( $daily_map[ $date_key ]['ad_spend'], 2 ),
				);
			} else {
				$chart_data[] = array(
					'day'         => $date_key,
					'revenue'     => 0.0,
					'profit'      => 0.0,
					'real_profit' => -16.0,
					'ad_spend'    => 16.0,
				);
			}
		}

		$total_ad_spend = 480.0;
		$avg_margin     = $total_revenue > 0 ? ( ( $total_profit / $total_revenue ) * 100 ) : 0.0;
		$real_profit    = $total_profit - $total_ad_spend;
		$roas           = $total_ad_spend > 0 ? ( $total_revenue / $total_ad_spend ) : 0.0;

		$recent_orders = array_reverse( $recent_orders );
		$recent_orders = array_slice( $recent_orders, 0, 8 );

		usort( $prod_rank_map, function( $a, $b ) {
			return $b['revenue'] <=> $a['revenue'];
		} );
		usort( $cust_rank_map, function( $a, $b ) {
			return $b['revenue'] <=> $a['revenue'];
		} );

		$response_payload = array(
			'metrics' => array(
				'revenue'          => number_format( $total_revenue, 2, '.', '' ),
				'profit'           => number_format( $total_profit, 2, '.', '' ),
				'margin'           => round( $avg_margin, 1 ),
				'orders'           => $order_count,
				'ad_spend'         => $total_ad_spend,
				'real_net_profit'  => $real_profit,
				'roas'             => round( $roas, 2 ),
			),
			'chart_data'    => $chart_data,
			'recent_orders' => $recent_orders,
			'rankings'      => array(
				'products'  => array_values( array_slice( $prod_rank_map, 0, 5 ) ),
				'customers' => array_values( array_slice( $cust_rank_map, 0, 5 ) ),
			),
		);

		return array(
			'response' => array( 'code' => 200, 'message' => 'OK' ),
			'body'     => json_encode( $response_payload ),
		);
	}

	return $preempt;
}, 10, 3 );
PHP;

	if ( ! file_exists( $mock_file ) ) {
		file_put_contents( $mock_file, $mock_code );
	}
}

// -----------------------------------------------------------------------------
// 4. Create 5 Realistic Demo Products with COGS Metadata (_purchase_cost)
// -----------------------------------------------------------------------------
$catalog_products = array(
	array(
		'title'       => 'Cuffie Wireless Active Noise-Cancelling Pro',
		'slug'        => 'cuffie-wireless-anc-pro',
		'price'       => '120.00',
		'cogs'        => '48.00',
		'sku'         => 'AUDIO-ANC-PRO-120',
		'description' => 'Cuffie wireless over-ear di alta fedeltà con cancellazione attiva del rumore e 35h di autonomia.',
		'category'    => 'Elettronica',
	),
	array(
		'title'       => 'Smartwatch Fitness Tracker GPS Waterproof',
		'slug'        => 'smartwatch-fitness-gps-waterproof',
		'price'       => '89.00',
		'cogs'        => '36.00',
		'sku'         => 'WATCH-GPS-FIT-89',
		'description' => 'Smartwatch sportivo con sensore cardio ottico, GPS integrato e monitoraggio SpO2.',
		'category'    => 'Elettronica',
	),
	array(
		'title'       => 'Zaino Tecnico da Viaggio Impermeabile 30L',
		'slug'        => 'zaino-tecnico-viaggio-impermeabile',
		'price'       => '65.00',
		'cogs'        => '24.00',
		'sku'         => 'BAG-TECH-TRIP-65',
		'description' => 'Zaino multitasca ultraresistente con scomparto imbottito per laptop 16" e porta USB.',
		'category'    => 'Accessori',
	),
	array(
		'title'       => 'Felpa Hoodie Premium Cotone Organico Unisex',
		'slug'        => 'felpa-hoodie-cotone-organico',
		'price'       => '49.90',
		'cogs'        => '18.50',
		'sku'         => 'WEAR-HOODIE-ORG-49',
		'description' => 'Felpa con cappuccio 350 GSM in 100% cotone pettinato biologico certificato GOTS.',
		'category'    => 'Abbigliamento',
	),
	array(
		'title'       => 'T-shirt Minimalist Streetwear Heavyweight',
		'slug'        => 'tshirt-minimalist-streetwear',
		'price'       => '29.90',
		'cogs'        => '9.50',
		'sku'         => 'WEAR-TEE-MIN-29',
		'description' => 'T-shirt taglio relaxed fit girocollo ad alta grammatura, morbida e traspirante.',
		'category'    => 'Abbigliamento',
	),
);

$product_objects = array();

foreach ( $catalog_products as $item_data ) {
	$existing = get_posts( array(
		'post_type'      => 'product',
		'title'          => $item_data['title'],
		'post_status'    => 'all',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( ! empty( $existing ) ) {
		$p_id = $existing[0];
		$prod = wc_get_product( $p_id );
	} else {
		$prod = new WC_Product_Simple();
		$prod->set_name( $item_data['title'] );
		$prod->set_slug( $item_data['slug'] );
		$prod->set_regular_price( $item_data['price'] );
		$prod->set_price( $item_data['price'] );
		$prod->set_sku( $item_data['sku'] );
		$prod->set_status( 'publish' );
		$prod->set_catalog_visibility( 'visible' );
		$prod->set_short_description( $item_data['description'] );
		$prod->set_description( $item_data['description'] . ' Testato per la gestione COGS e margini operativi con Profit Warden.' );
		$p_id = $prod->save();
	}

	// Set official Profit Warden COGS meta: _purchase_cost
	update_post_meta( $p_id, '_purchase_cost', $item_data['cogs'] );
	// Additional cross-compatibility meta keys
	update_post_meta( $p_id, '_cogs', $item_data['cogs'] );
	update_post_meta( $p_id, '_profit_warden_cost', $item_data['cogs'] );
	update_post_meta( $p_id, '_profit_warden_cogs', $item_data['cogs'] );

	$product_objects[] = wc_get_product( $p_id );
}

// -----------------------------------------------------------------------------
// 5. Generate 18 Realistic Completed Orders Across the Last 30 Days
// -----------------------------------------------------------------------------
$mock_customers = array(
	array( 'first' => 'Marco', 'last' => 'Ferrari', 'email' => 'marco.ferrari@techmail.it', 'city' => 'Milano', 'postcode' => '20121' ),
	array( 'first' => 'Giulia', 'last' => 'Romano', 'email' => 'giulia.romano@designstudio.it', 'city' => 'Roma', 'postcode' => '00187' ),
	array( 'first' => 'Alessandro', 'last' => 'Moretti', 'email' => 'a.moretti@innovate.com', 'city' => 'Torino', 'postcode' => '10121' ),
	array( 'first' => 'Elena', 'last' => 'Ricci', 'email' => 'elena.ricci@fashiongroup.it', 'city' => 'Firenze', 'postcode' => '50123' ),
	array( 'first' => 'Davide', 'last' => 'Conti', 'email' => 'davide.conti@agrotech.it', 'city' => 'Bologna', 'postcode' => '40121' ),
	array( 'first' => 'Chiara', 'last' => 'Greco', 'email' => 'chiara.greco@medialab.it', 'city' => 'Napoli', 'postcode' => '80138' ),
	array( 'first' => 'Federico', 'last' => 'Galli', 'email' => 'federico.galli@startupvibe.it', 'city' => 'Verona', 'postcode' => '37121' ),
	array( 'first' => 'Silvia', 'last' => 'Marino', 'email' => 'silvia.marino@consulting.it', 'city' => 'Genova', 'postcode' => '16121' ),
);

$payment_gateways = array(
	array( 'method' => 'stripe', 'title' => 'Carta di Credito (Stripe)', 'pct' => 0.014, 'fix' => 0.25 ),
	array( 'method' => 'paypal', 'title' => 'PayPal Express', 'pct' => 0.034, 'fix' => 0.35 ),
	array( 'method' => 'cod',    'title' => 'Contrassegno (COD)', 'pct' => 0.0,   'fix' => 3.00 ),
);

$campaign_attribution = array(
	array( 'utm_source' => 'facebook', 'utm_medium' => 'cpc', 'utm_campaign' => 'retargeting_cart_abandoners' ),
	array( 'utm_source' => 'instagram', 'utm_medium' => 'cpc', 'utm_campaign' => 'lookalike_purchase_1pct' ),
	array( 'utm_source' => 'google', 'utm_medium' => 'cpc', 'utm_campaign' => 'brand_search_protection' ),
	array( 'utm_source' => 'newsletter', 'utm_medium' => 'email', 'utm_campaign' => 'vip_early_access_spring' ),
);

$total_orders_to_create = 18;

for ( $i = 0; $i < $total_orders_to_create; $i++ ) {
	$identifier_key = 'pw_demo_order_slot_' . $i;

	$existing_order_ids = get_posts( array(
		'post_type'   => array( 'shop_order', 'shop_order_placehold' ),
		'meta_key'    => '_profwabr_demo_slot',
		'meta_value'  => $identifier_key,
		'post_status' => 'any',
		'numberposts' => 1,
		'fields'      => 'ids',
	) );

	if ( ! empty( $existing_order_ids ) ) {
		continue;
	}

	// Calculate staggered date: evenly spread across past 28 days
	$days_ago    = (int) floor( ( ( $total_orders_to_create - 1 - $i ) * 28 ) / $total_orders_to_create );
	$order_time  = strtotime( "-{$days_ago} days " . ( 9 + ( $i % 12 ) ) . ":15:00" );
	$date_mysql  = gmdate( 'Y-m-d H:i:s', $order_time );

	// Select customer and gateway
	$cust = $mock_customers[ $i % count( $mock_customers ) ];
	$gate = $payment_gateways[ $i % count( $payment_gateways ) ];
	$attr = $campaign_attribution[ $i % count( $campaign_attribution ) ];

	$order = wc_create_order();

	// Add 1 or 2 items to the order
	$p1_idx = $i % count( $product_objects );
	$p1     = $product_objects[ $p1_idx ];
	$qty1   = ( $i % 4 === 0 ) ? 2 : 1;
	$order->add_product( $p1, $qty1 );

	$total_items_cogs = ( (float) get_post_meta( $p1->get_id(), '_purchase_cost', true ) ) * $qty1;
	$order_items_dump = array(
		array(
			'sku'           => $p1->get_sku(),
			'product_name'  => $p1->get_name(),
			'qty'           => $qty1,
			'purchase_cost' => (float) get_post_meta( $p1->get_id(), '_purchase_cost', true ),
			'price_net'     => (float) $p1->get_price(),
		),
	);

	if ( $i % 3 === 0 ) {
		$p2_idx = ( $i + 2 ) % count( $product_objects );
		$p2     = $product_objects[ $p2_idx ];
		$qty2   = 1;
		$order->add_product( $p2, $qty2 );
		$p2_cogs           = (float) get_post_meta( $p2->get_id(), '_purchase_cost', true );
		$total_items_cogs += $p2_cogs * $qty2;
		$order_items_dump[] = array(
			'sku'           => $p2->get_sku(),
			'product_name'  => $p2->get_name(),
			'qty'           => $qty2,
			'purchase_cost' => $p2_cogs,
			'price_net'     => (float) $p2->get_price(),
		);
	}

	// Set Addresses
	$address = array(
		'first_name' => $cust['first'],
		'last_name'  => $cust['last'],
		'company'    => '',
		'email'      => $cust['email'],
		'phone'      => '+39338' . str_pad( (string) ( 1000000 + $i * 777 ), 7, '0', STR_PAD_LEFT ),
		'address_1'  => 'Via Roma ' . ( 12 + $i * 3 ),
		'city'       => $cust['city'],
		'state'      => '',
		'postcode'   => $cust['postcode'],
		'country'    => 'IT',
	);
	$order->set_address( $address, 'billing' );
	$order->set_address( $address, 'shipping' );

	// Shipping
	$shipping_item = new WC_Order_Item_Shipping();
	$shipping_item->set_method_title( 'Spedizione Espressa 24/48h' );
	$shipping_item->set_method_id( 'flat_rate' );
	$shipping_item->set_total( 5.00 );
	$order->add_item( $shipping_item );

	// Payment Gateway
	$order->set_payment_method( $gate['method'] );
	$order->set_payment_method_title( $gate['title'] );

	$order->calculate_totals();

	// Financial Breakdown Calculation
	$order_total       = (float) $order->get_total();
	$order_vat         = (float) $order->get_total_tax();
	$actual_shipping   = 4.50; // Real carrier fee
	$packaging_cost    = 0.80; // Packaging expense
	$gateway_fee       = round( ( $order_total * $gate['pct'] ) + $gate['fix'], 2 );

	// Net Profit formula: (Total - VAT) - COGS - Actual Shipping - Packaging - Gateway Fee
	$net_revenue       = $order_total - $order_vat;
	$net_profit        = $net_revenue - $total_items_cogs - $actual_shipping - $packaging_cost - $gateway_fee;
	$margin_percentage = $net_revenue > 0 ? ( ( $net_profit / $net_revenue ) * 100 ) : 0.0;

	$status_label = $net_profit > 20.0 ? 'PROFITTO OTTIMO' : ( $net_profit > 5.0 ? 'UTILE NORMALE' : 'ATTENZIONE MARGINE' );

	// Set Status and Historic Timestamps
	$order->set_status( 'completed', 'Ordine evaso e completato con successo.' );
	$order->set_date_created( $date_mysql );
	$order->set_date_completed( $date_mysql );

	// Assign Demo Slot Identifier
	$order->update_meta_data( '_profwabr_demo_slot', $identifier_key );

	// Store Official Profit Warden Order Analysis Metadata
	$analysis_payload = array(
		'summary'   => array(
			'status'            => $status_label,
			'net_profit'        => number_format( $net_profit, 2, '.', '' ),
			'margin_percentage' => number_format( $margin_percentage, 1, '.', '' ) . '%',
		),
		'breakdown' => array(
			'revenue'          => $order_total,
			'cogs'             => $total_items_cogs,
			'vat'              => $order_vat,
			'shipping_cost'    => $actual_shipping,
			'packaging_cost'   => $packaging_cost,
			'gateway_fee'      => $gateway_fee,
			'logistics_impact' => number_format( $actual_shipping + $packaging_cost, 2, '.', '' ),
		),
		'items'     => $order_items_dump,
	);
	$order->update_meta_data( '_profwabr_profit_analysis_data', $analysis_payload );

	// Marketing attribution tracking metadata
	$order->update_meta_data( '_profwabr_track_utm_source', $attr['utm_source'] );
	$order->update_meta_data( '_profwabr_track_utm_medium', $attr['utm_medium'] );
	$order->update_meta_data( '_profwabr_track_utm_campaign', $attr['utm_campaign'] );
	$order->update_meta_data( '_profwabr_track_fbclid', 'fb.1.' . $order_time . '.demoAttributionKey' . $i );

	$order->save();

	// Add detailed audit note
	$note_msg = sprintf(
		'[Profit Warden] Analisi Finanziaria Ordine #%d: Ricavo Netto: €%.2f | COGS: €%.2f | Spedizione Reale: €%.2f | Packaging: €%.2f | Fee Gateway: €%.2f => UTILE NETTO: €%.2f (Margine: %.1f%%)',
		$order->get_id(),
		$net_revenue,
		$total_items_cogs,
		$actual_shipping,
		$packaging_cost,
		$gateway_fee,
		$net_profit,
		$margin_percentage
	);
	$order->add_order_note( $note_msg );
}
