<?php
/**
 * Seeder script for BeraLab GEO Engine Playground environment
 *
 * Populates WordPress with:
 * 1. Comprehensive GEO Engine settings (Organization / LocalBusiness entity, knowledge topics, verified sameAs).
 * 2. Playground Mock & Enhancer mu-plugin:
 *    - Schema.org LocalBusiness enriched metadata (address, geo-coordinates, opening hours, services).
 *    - Native AI crawler directives for robots.txt (GPTBot, ClaudeBot, PerplexityBot, etc.).
 *    - Custom WP-Admin GEO Health & AI Telemetry Dashboard Widget (Score 94/100, live AI scans log).
 *    - Admin menu alias redirect for /wp-admin/admin.php?page=geo-schema-wp.
 * 3. Fictitious but realistic GEO Audit & AI Scans database records in wp_options.
 * 4. High-quality demonstration articles and pages showcasing JSON-LD schema injection with semantic 'about' & 'mentions'.
 *
 * @package BeraLabGeoEngine
 */

// Context and security check
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_CLI' ) ) {
	exit( 'Direct access denied.' );
}

// -----------------------------------------------------------------------------
// 1. Ensure BeraLab GEO Engine is Loaded and Active
// -----------------------------------------------------------------------------
if ( ! class_exists( '\BeraLabGeoEngine\Core' ) ) {
	$plugin_candidates = array(
		WP_PLUGIN_DIR . '/geo-schema-forge-generative-engine-optimization-structured-data/geo-schema-forge-generative-engine-optimization-structured-data.php',
		WP_PLUGIN_DIR . '/geo-schema-forge-generative-engine-optimization-structured-data/beralab-geo-engine.php',
		WP_PLUGIN_DIR . '/beralab-geo-engine/beralab-geo-engine.php',
		WP_PLUGIN_DIR . '/geo-schema-wp/beralab-geo-engine.php',
	);

	foreach ( $plugin_candidates as $candidate ) {
		if ( file_exists( $candidate ) ) {
			require_once $candidate;
			if ( function_exists( 'activate_plugin' ) ) {
				activate_plugin( plugin_basename( $candidate ) );
			}
			break;
		}
	}
}

// -----------------------------------------------------------------------------
// 2. Configure Official GEO Engine Settings in wp_options
// -----------------------------------------------------------------------------
$geo_settings = array(
	'entity_type'         => 'Organization',
	'entity_name'         => 'TechBera Solutions',
	'entity_alternate'    => 'TechBera AI Lab',
	'entity_description' => 'Laboratorio tecnologico europeo specializzato in Generative Engine Optimization (GEO), Knowledge Graphs semantici e infrastrutture di retrieval per motori di intelligenza artificiale (ChatGPT, Perplexity, Claude, Google Gemini).',
	'entity_logo'         => 'https://beralabs.it/assets/img/techbera-logo.png',
	'entity_knows_about'  => 'Generative Engine Optimization, Schema.org Knowledge Graphs, AI Crawlers, Perplexity AI, ChatGPT Search, Semantic Web, Structured Data, llms.txt Standard, High Performance WordPress Architecture',
	'same_as_urls'        => "https://twitter.com/beralabs\nhttps://linkedin.com/company/beralabs\nhttps://github.com/beralabs\nhttps://wikidata.org/wiki/Q115862875\nhttps://en.wikipedia.org/wiki/Generative_engine_optimization",
	'default_post_schema' => 'TechArticle',
	'enable_semantic_geo' => '1',
	'enable_breadcrumbs'  => '1',
	'enable_llms_txt'     => '1',
	'enable_llms_full'    => '1',
	'llms_site_summary'   => 'TechBera Solutions sviluppa protocolli di interoperabilità semantica e moduli di Generative Engine Optimization (GEO) che garantiscono la corretta indicizzazione, citazione e comprensione dei contenuti digitali da parte dei modelli linguistici di grandi dimensioni (LLM).',
	'llms_post_types'     => array( 'post', 'page' ),
	'llms_max_posts'      => 30,
	'llms_cache_hours'    => 24,
);

update_option( 'beralab_geo_engine_settings', $geo_settings );

// -----------------------------------------------------------------------------
// 3. Register Realistic GEO Health Audit & Telemetry Data
// -----------------------------------------------------------------------------
$audit_checks = array(
	array(
		'id'          => 'schema_syntax',
		'name'        => 'Schema.org JSON-LD W3C Validation',
		'status'      => 'PASSED',
		'score'       => 100,
		'description' => 'Grafo semantico @graph valido al 100% su Schema.org Validator e Google Rich Results Test.',
	),
	array(
		'id'          => 'entity_linking',
		'name'        => 'Deterministic @id Entity Anchors',
		'status'      => 'PASSED',
		'score'       => 98,
		'description' => 'Tutti i nodi (Organization, WebSite, TechArticle, Person) sono collegati senza frammentazione di nodi orfani.',
	),
	array(
		'id'          => 'llms_txt_standard',
		'name'        => 'Dynamic /llms.txt Endpoint Conformance',
		'status'      => 'PASSED',
		'score'       => 100,
		'description' => 'Endpoint markdown conforme RFC servito con HTTP 200, Content-Type text/plain e cache transient attiva.',
	),
	array(
		'id'          => 'llms_full_standard',
		'name'        => 'Deep Knowledge Corpus (/llms-full.txt)',
		'status'      => 'PASSED',
		'score'       => 95,
		'description' => 'Corpus completo per RAG vector search indicizzato con tassonomie e metadati cronologici.',
	),
	array(
		'id'          => 'ai_bot_directives',
		'name'        => 'AI Crawler Directives (robots.txt)',
		'status'      => 'PASSED',
		'score'       => 92,
		'description' => 'Accesso prioritario accordato a GPTBot, ClaudeBot, PerplexityBot e ChatGPT-User con rate limiting ottimizzato.',
	),
	array(
		'id'          => 'semantic_enrichment',
		'name'        => 'Semantic Mentions & About Taxonomies',
		'status'      => 'PASSED',
		'score'       => 90,
		'description' => 'Iniezione automatica di nodi Thing (about e mentions) collegati alle categorie e tag tematici.',
	),
);

$ai_scans_log = array(
	array(
		'crawler'     => 'PerplexityBot',
		'endpoint'    => '/llms.txt',
		'ip'          => '151.101.65.140',
		'http_status' => 200,
		'latency_ms'  => 38,
		'payload_kb'  => 1.6,
		'timestamp'   => gmdate( 'Y-m-d H:i:s', strtotime( '-12 minutes' ) ),
		'action'      => 'RAG Knowledge Indexing',
	),
	array(
		'crawler'     => 'GPTBot (OpenAI)',
		'endpoint'    => '/',
		'ip'          => '20.171.206.42',
		'http_status' => 200,
		'latency_ms'  => 52,
		'payload_kb'  => 19.4,
		'timestamp'   => gmdate( 'Y-m-d H:i:s', strtotime( '-45 minutes' ) ),
		'action'      => 'Knowledge Graph @graph Parse',
	),
	array(
		'crawler'     => 'ClaudeBot (Anthropic)',
		'endpoint'    => '/llms-full.txt',
		'ip'          => '54.210.12.89',
		'http_status' => 200,
		'latency_ms'  => 64,
		'payload_kb'  => 14.2,
		'timestamp'   => gmdate( 'Y-m-d H:i:s', strtotime( '-2 hours' ) ),
		'action'      => 'Full Corpus Vector Ingestion',
	),
	array(
		'crawler'     => 'OAI-SearchBot',
		'endpoint'    => '/guida-generative-engine-optimization-wordpress-2026',
		'ip'          => '40.83.2.11',
		'http_status' => 200,
		'latency_ms'  => 44,
		'payload_kb'  => 22.8,
		'timestamp'   => gmdate( 'Y-m-d H:i:s', strtotime( '-4 hours' ) ),
		'action'      => 'Semantic Article & Author Extraction',
	),
	array(
		'crawler'     => 'Google-Extended',
		'endpoint'    => '/robots.txt',
		'ip'          => '66.249.66.1',
		'http_status' => 200,
		'latency_ms'  => 21,
		'payload_kb'  => 0.9,
		'timestamp'   => gmdate( 'Y-m-d H:i:s', strtotime( '-7 hours' ) ),
		'action'      => 'Crawl Permissions Verification',
	),
);

$indexed_entities = array(
	array(
		'type'        => 'Organization / LocalBusiness',
		'name'        => 'TechBera Solutions',
		'schema_id'   => home_url( '/#entity' ),
		'status'      => 'INDEXED',
		'attributes'  => 'Name, AlternateName, Logo, Address, Coordinates, knowsAbout (9 topics), sameAs (5 profiles)',
	),
	array(
		'type'        => 'WebSite',
		'name'        => get_bloginfo( 'name' ),
		'schema_id'   => home_url( '/#website' ),
		'status'      => 'INDEXED',
		'attributes'  => 'SearchAction EntryPoint, Publisher link, Language (it-IT)',
	),
	array(
		'type'        => 'TechArticle',
		'name'        => 'Guida Definitiva a Generative Engine Optimization (GEO) per WordPress nel 2026',
		'schema_id'   => home_url( '/guida-generative-engine-optimization-wordpress-2026/#content' ),
		'status'      => 'INDEXED',
		'attributes'  => 'Author Person @id, BreadcrumbList, About (2 Things), Mentions (4 Things), wordCount',
	),
	array(
		'type'        => 'ServiceCatalog',
		'name'        => 'GEO Architecture & Semantic Services',
		'schema_id'   => home_url( '/servizi-geo/#catalog' ),
		'status'      => 'INDEXED',
		'attributes'  => 'Generative Search Audit, Schema Graph Automation, llms.txt Deployment',
	),
);

update_option( 'beralab_geo_health_score', 94 );
update_option( 'beralab_geo_health_status', 'Excellent' );
update_option( 'beralab_geo_audit_checks', $audit_checks );
update_option( 'beralab_geo_ai_scans_log', $ai_scans_log );
update_option( 'beralab_geo_indexed_entities', $indexed_entities );
update_option( 'beralab_geo_last_audit_time', gmdate( 'Y-m-d H:i:s' ) );

// Purge any stale transients to ensure immediate dynamic rendering
delete_transient( 'beralab_geo_engine_llms_txt_cache' );
delete_transient( 'beralab_geo_engine_llms_full_txt_cache' );

// -----------------------------------------------------------------------------
// 4. Register Playground Enhancer (mu-plugin)
// -----------------------------------------------------------------------------
if ( defined( 'WP_CONTENT_DIR' ) ) {
	$mu_dir = WP_CONTENT_DIR . '/mu-plugins';
	if ( ! is_dir( $mu_dir ) ) {
		wp_mkdir_p( $mu_dir );
	}

	$mu_file = $mu_dir . '/geo-engine-playground-enhancer.php';
	$mu_code = <<<'PHP'
<?php
/**
 * Plugin Name: BeraLab GEO Engine Playground Enhancer & Mock Provider
 * Description: Injects LocalBusiness schema, native robots.txt AI bot directives, admin telemetry widgets, and convenience route aliases.
 * Author: BeraLab
 */

// 1. Enrich Schema.org Organization into a LocalBusiness with address, coords, and services
add_filter( 'beralab_geo_engine_organization_schema', function( $node ) {
	$node['@type'] = array( 'Organization', 'LocalBusiness' );

	$node['address'] = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'Via Monte Napoleone 18',
		'addressLocality' => 'Milano',
		'addressRegion'   => 'MI',
		'postalCode'      => '20121',
		'addressCountry'  => 'IT',
	);

	$node['geo'] = array(
		'@type'     => 'GeoCoordinates',
		'latitude'  => 45.4697,
		'longitude' => 9.1970,
	);

	$node['telephone'] = '+39-02-8900-1234';
	$node['email']     = 'contact@techbera.com';
	$node['priceRange'] = '€€€';

	$node['openingHoursSpecification'] = array(
		array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
			'opens'     => '09:00',
			'closes'    => '18:30',
		),
	);

	$node['hasOfferCatalog'] = array(
		'@type' => 'OfferCatalog',
		'name'  => 'Generative Engine Optimization & Structured Data Solutions',
		'itemListElement' => array(
			array(
				'@type' => 'Offer',
				'itemOffered' => array(
					'@type'       => 'Service',
					'name'        => 'Generative Search Audit & Citation Gap Analysis',
					'description' => 'Analisi approfondita della visibilità del brand su Perplexity, ChatGPT Search e Google AI Overviews.',
				),
			),
			array(
				'@type' => 'Offer',
				'itemOffered' => array(
					'@type'       => 'Service',
					'name'        => 'Semantic Knowledge Graph & JSON-LD Automation',
					'description' => 'Architettura del grafo semantico unificato multi-entità conforme alle direttive Schema.org e W3C.',
				),
			),
			array(
				'@type' => 'Offer',
				'itemOffered' => array(
					'@type'       => 'Service',
					'name'        => 'Dynamic /llms.txt Endpoint Integration',
					'description' => 'Erogazione autonoma di manifest markdown per crawler generativi a zero impatto sulle prestazioni.',
				),
			),
		),
	);

	return $node;
} );

// 2. Inject Native AI Crawler Directives into virtual robots.txt
add_filter( 'robots_txt', function( $output, $public ) {
	$ai_directives = "\n# ------------------------------------------------------------\n";
	$ai_directives .= "# BeraLab GEO Engine: Generative AI Crawler Policies\n";
	$ai_directives .= "# ------------------------------------------------------------\n";
	$ai_directives .= "User-agent: GPTBot\nAllow: /\nCrawl-delay: 1\n\n";
	$ai_directives .= "User-agent: ClaudeBot\nAllow: /\n\n";
	$ai_directives .= "User-agent: PerplexityBot\nAllow: /\n\n";
	$ai_directives .= "User-agent: ChatGPT-User\nAllow: /\n\n";
	$ai_directives .= "User-agent: Google-Extended\nAllow: /\n\n";
	$ai_directives .= "User-agent: OAI-SearchBot\nAllow: /\n\n";
	$ai_directives .= "# Canonical LLMs index endpoint\n";
	$ai_directives .= "Disallow: /wp-admin/\n";
	$ai_directives .= "Allow: /wp-admin/admin-ajax.php\n";
	$ai_directives .= "Allow: /llms.txt\n";
	$ai_directives .= "Allow: /llms-full.txt\n\n";
	$ai_directives .= "Sitemap: " . esc_url( home_url( '/sitemap.xml' ) ) . "\n";

	return $output . $ai_directives;
}, 20, 2 );

// 3. Register Admin Top-Level Menu Alias for Playground Quick Access
add_action( 'admin_menu', function() {
	add_menu_page(
		'GEO Engine',
		'GEO Engine',
		'manage_options',
		'geo-schema-wp',
		function() {
			if ( wp_safe_redirect( admin_url( 'options-general.php?page=beralab-geo-engine' ) ) ) {
				exit;
			}
		},
		'dashicons-networking',
		58
	);
}, 20 );

// 4. Register Custom Dashboard Widget: "GEO Health & AI Crawler Intelligence"
add_action( 'wp_dashboard_setup', function() {
	wp_add_dashboard_widget(
		'beralab_geo_health_dashboard_widget',
		'⚡ BeraLab GEO Engine - AI Readiness & Health Score',
		function() {
			$score   = get_option( 'beralab_geo_health_score', 94 );
			$status  = get_option( 'beralab_geo_health_status', 'Excellent' );
			$checks  = get_option( 'beralab_geo_audit_checks', array() );
			$scans   = get_option( 'beralab_geo_ai_scans_log', array() );
			$entities= get_option( 'beralab_geo_indexed_entities', array() );
			?>
			<div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif; margin:-6px -12px -12px;">
				<div style="background:linear-gradient(135deg, #0d1117 0%, #161b22 100%); color:#fff; padding:18px 20px; border-bottom:1px solid #30363d;">
					<div style="display:flex; justify-content:space-between; align-items:center;">
						<div>
							<span style="font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#58a6ff; font-weight:700;">AI Ingestion Readiness</span>
							<h2 style="color:#fff; margin:4px 0 0; font-size:20px; font-weight:700;">TechBera Solutions</h2>
						</div>
						<div style="text-align:right;">
							<span style="display:inline-block; background:#238636; color:#fff; font-size:18px; font-weight:800; padding:6px 14px; border-radius:8px; box-shadow:0 0 12px rgba(35,134,54,0.4);">
								<?php echo esc_html( $score ); ?>/100
							</span>
							<div style="font-size:11px; color:#7ee787; margin-top:4px; font-weight:600;"><?php echo esc_html( $status ); ?></div>
						</div>
					</div>
				</div>

				<div style="padding:16px 20px;">
					<h4 style="margin:0 0 10px; font-size:13px; text-transform:uppercase; color:#57606a;">Verifiche Semantiche & Schema.org</h4>
					<div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px;">
						<?php foreach ( array_slice( $checks, 0, 4 ) as $chk ) : ?>
							<div style="background:#f6f8fa; border:1px solid #d0d7de; border-radius:6px; padding:8px 12px;">
								<span style="color:#1a7f37; font-weight:bold; font-size:12px;">✓ <?php echo esc_html( $chk['name'] ); ?></span>
								<div style="font-size:11px; color:#57606a; margin-top:2px;"><?php echo esc_html( $chk['status'] ); ?> (<?php echo esc_html( $chk['score'] ); ?>%)</div>
							</div>
						<?php endforeach; ?>
					</div>

					<h4 style="margin:0 0 10px; font-size:13px; text-transform:uppercase; color:#57606a;">Ultimi Scan Crawler Generativi</h4>
					<table style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom:14px;">
						<thead>
							<tr style="text-align:left; color:#57606a; border-bottom:1px solid #d0d7de;">
								<th style="padding:6px 4px;">Bot AI</th>
								<th style="padding:6px 4px;">Risorsa</th>
								<th style="padding:6px 4px;">Esito</th>
								<th style="padding:6px 4px;">Latenza</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $scans, 0, 3 ) as $scn ) : ?>
								<tr style="border-bottom:1px solid #eaeef2;">
									<td style="padding:6px 4px; font-weight:600; color:#0969da;"><?php echo esc_html( $scn['crawler'] ); ?></td>
									<td style="padding:6px 4px; font-family:monospace;"><?php echo esc_html( $scn['endpoint'] ); ?></td>
									<td style="padding:6px 4px;"><span style="background:#dafbe1; color:#1a7f37; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:700;">HTTP <?php echo esc_html( $scn['http_status'] ); ?></span></td>
									<td style="padding:6px 4px; color:#57606a;"><?php echo esc_html( $scn['latency_ms'] ); ?>ms</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div style="display:flex; gap:10px; margin-top:10px;">
						<a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank" class="button button-primary" style="font-size:12px;">
							Apri /llms.txt &rarr;
						</a>
						<a href="<?php echo esc_url( home_url( '/llms-full.txt' ) ); ?>" target="_blank" class="button" style="font-size:12px;">
							Apri /llms-full.txt
						</a>
						<a href="<?php echo esc_url( admin_url( 'options-general.php?page=beralab-geo-engine' ) ); ?>" class="button button-secondary" style="font-size:12px; margin-left:auto;">
							Configura GEO Engine
						</a>
					</div>
				</div>
			</div>
			<?php
		}
	);
} );
PHP;

	if ( ! file_exists( $mu_file ) ) {
		file_put_contents( $mu_file, $mu_code );
	}
}

// -----------------------------------------------------------------------------
// 5. Create Demonstration Taxonomies and Categories
// -----------------------------------------------------------------------------
$cat_geo = wp_create_category( 'Generative Engine Optimization' );
$cat_ai  = wp_create_category( 'Intelligenza Artificiale & Semantic Web' );

$tags_list = array( 'GEO', 'Schema.org', 'LLM Retrieval', 'Perplexity AI', 'Knowledge Graph', 'JSON-LD' );
wp_create_terms( $tags_list, 'post_tag' );

// -----------------------------------------------------------------------------
// 6. Create Cornerstone Technical Post & Test Pages
// -----------------------------------------------------------------------------
$post_title = 'Guida Definitiva a Generative Engine Optimization (GEO) per WordPress nel 2026';
$post_slug  = 'guida-generative-engine-optimization-wordpress-2026';

$existing_post = get_posts( array(
	'name'        => $post_slug,
	'post_type'   => 'post',
	'post_status' => 'any',
	'numberposts' => 1,
	'fields'      => 'ids',
) );

$post_content = <<<HTML
<p class="lead">Nel 2026 i motori di risposta ad intelligenza artificiale come <strong>ChatGPT Search</strong>, <strong>Perplexity AI</strong>, <strong>Claude</strong> e <strong>Google AI Overviews</strong> elaborano miliardi di query informative e transazionali. Per mantenere la massima rilevanza e citabilità, i siti web devono evolvere dalla SEO tradizionale alla <em>Generative Engine Optimization (GEO)</em>.</p>

<h2>1. Che cos'è la Generative Engine Optimization (GEO)?</h2>
<p>La Generative Engine Optimization è l'insieme di pratiche architetturali, semantiche e di contenuto mirate ad ottimizzare la comprensione del contesto e l'autorevolezza del brand da parte dei modelli linguistici di grandi dimensioni (LLM). A differenza del tradizionale crawling HTML, gli agenti generativi prediligono:</p>
<ul>
	<li><strong>Knowledge Graphs connessi:</strong> Grafi JSON-LD unificati senza nodi isolati, con identificatori univoci (URI deterministiche).</li>
	<li><strong>Manifest Standardizzati:</strong> Endpoint <code>/llms.txt</code> e <code>/llms-full.txt</code> ad alta efficienza di token.</li>
	<li><strong>Arricchimento Semantico:</strong> Marcatura esplicita delle entità tramite i predicati <code>about</code> e <code>mentions</code>.</li>
</ul>

<h2>2. L'Architettura Semantica di TechBera Solutions</h2>
<p>Grazie all'integrazione con BeraLab GEO Engine, questo portale genera in tempo reale un grafo JSON-LD certificato Schema.org che definisce chiaramente l'entità primaria (Organization e LocalBusiness), i canali verificati (sameAs) e la matrice di competenze (knowsAbout). I crawler AI possono navigare e ingerire l'intero patrimonio informativo a zero overhead di rendering del DOM.</p>

<h2>3. Test e Validazione</h2>
<p>Puoi ispezionare l'output di questa pagina aprendo il sorgente HTML nella sezione <code>&lt;head&gt;</code> per verificare il blocco <code>application/ld+json</code> contenente la dichiarazione gerarchica dell'articolo, dell'autore e delle entità collegate.</p>
HTML;

if ( empty( $existing_post ) ) {
	$new_post_id = wp_insert_post( array(
		'post_title'    => $post_title,
		'post_name'     => $post_slug,
		'post_content'  => $post_content,
		'post_excerpt'  => 'Guida tecnica approfondita sui principi di Generative Engine Optimization (GEO), Knowledge Graphs semantici e integrazione dello standard llms.txt in WordPress.',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type'     => 'post',
		'post_category' => array( $cat_geo, $cat_ai ),
		'tags_input'    => $tags_list,
	) );
}

// Create Test Demonstration Page: "Chi Siamo - Laboratorio di Ingegneria Semantica TechBera"
$page_title = 'Chi Siamo - Laboratorio di Ingegneria Semantica TechBera';
$page_slug  = 'chi-siamo';

$existing_page = get_posts( array(
	'name'        => $page_slug,
	'post_type'   => 'page',
	'post_status' => 'any',
	'numberposts' => 1,
	'fields'      => 'ids',
) );

$page_content = <<<HTML
<p><strong>TechBera Solutions</strong> è una software company fondata per guidare brand, merchant ed editori nella transizione verso la ricerca generativa e la comprensione computazionale dei contenuti.</p>

<h2>La Nostra Missione</h2>
<p>Fornire infrastrutture algoritmiche robuste, leggere e conformi agli standard internazionali del W3C e di Schema.org per garantire che le conoscenze umane siano indicizzate e citate con la massima fedeltà dagli assistenti AI globali.</p>

<h2>Sede e Recapiti Operativi</h2>
<p>Sede: Via Monte Napoleone 18, 20121 Milano (MI) - Italia<br>
Telefono: +39 02 8900 1234 | Email: contact@techbera.com<br>
Orari: Lunedì - Venerdì: 09:00 - 18:30</p>
HTML;

if ( empty( $existing_page ) ) {
	wp_insert_post( array(
		'post_title'   => $page_title,
		'post_name'    => $page_slug,
		'post_content' => $page_content,
		'post_excerpt' => 'Profilo istituzionale di TechBera Solutions, laboratorio di ingegneria semantica e Generative Engine Optimization.',
		'post_status'  => 'publish',
		'post_author'  => 1,
		'post_type'    => 'page',
	) );
}

// -----------------------------------------------------------------------------
// 7. Flush Rewrite Rules for /llms.txt and /llms-full.txt
// -----------------------------------------------------------------------------
flush_rewrite_rules( false );
