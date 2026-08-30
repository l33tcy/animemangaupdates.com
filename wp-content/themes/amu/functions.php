<?php
/**
 * AnimeMangaUpdates theme functions.
 *
 * SEO is owned by the real Yoast SEO plugin (titles, descriptions, canonical,
 * Open Graph, sitemaps, and the JSON-LD schema graph) — no bridge plugin. This
 * theme only adds what Yoast doesn't: meta keywords, breadcrumb placement, and
 * ACF-driven article facts. Custom fields use ACF (advanced-custom-fields).
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------- setup */
function amu_setup() {
	add_theme_support( 'title-tag' );          // Yoast filters the title output.
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 44, 'flex-width' => true ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'amu' ),
		'footer'  => __( 'Footer Menu', 'amu' ),
	) );

	add_image_size( 'amu_hero', 1600, 686, true );  // 21:9 hero
	add_image_size( 'amu_card', 720, 450, true );   // 16:10 card
}
add_action( 'after_setup_theme', 'amu_setup' );

/* -------------------------------------------------------------- assets */
function amu_assets() {
	$ver = wp_get_theme()->get( 'Version' );

	// Distinctive type: Bricolage Grotesque (display), Zen Kaku Gothic New (body),
	// Chakra Petch (labels). Loaded from Google Fonts in the browser.
	wp_enqueue_style( 'amu-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Chakra+Petch:wght@600;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap', array(), null );
	wp_enqueue_style( 'amu-style', get_stylesheet_uri(), array( 'amu-fonts' ), $ver );
	wp_enqueue_script( 'amu-main', get_template_directory_uri() . '/assets/js/main.js', array(), $ver, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'amu_assets' );

/* -------------------------------------------------------------- widgets */
function amu_widgets() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'amu' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<section class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'amu_widgets' );

/* -------------------------------------------------------------- ACF fields
 * Registered in code so the field definitions are versioned in git and appear
 * on a fresh install without manual setup. The ACF *plugin* is a persisted
 * plugin (installed once, lives in the wp-content volume).
 */
function amu_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'      => 'group_amu_article',
		'title'    => 'Article Details',
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'position' => 'side',
		'fields'   => array(
			array( 'key' => 'field_amu_featured', 'label' => 'Feature on homepage', 'name' => 'amu_featured', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Show as the lead story in the hero.' ),
			array( 'key' => 'field_amu_series',   'label' => 'Series / Title',       'name' => 'amu_series',   'type' => 'text' ),
			array( 'key' => 'field_amu_status',   'label' => 'Status',               'name' => 'amu_status',   'type' => 'select', 'choices' => array( 'Ongoing' => 'Ongoing', 'Completed' => 'Completed', 'Upcoming' => 'Upcoming', 'Announced' => 'Announced', 'Hiatus' => 'Hiatus' ), 'allow_null' => 1, 'ui' => 1 ),
			array( 'key' => 'field_amu_episodes', 'label' => 'Episodes / Chapters',  'name' => 'amu_episodes', 'type' => 'text' ),
			array( 'key' => 'field_amu_score',    'label' => 'Score (0-10)',         'name' => 'amu_score',    'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1 ),
			array( 'key' => 'field_amu_keywords', 'label' => 'Meta keywords',        'name' => 'amu_keywords', 'type' => 'text', 'instructions' => 'Comma-separated. Overrides the auto keywords (from tags) in the <meta name="keywords"> tag.' ),
		),
	) );
}
add_action( 'acf/init', 'amu_acf_fields' );

/** Safe ACF getter (works whether or not ACF is active). */
function amu_field( $name, $id = null ) {
	return function_exists( 'get_field' ) ? get_field( $name, $id ) : null;
}

/* -------------------------------------------------------------- meta keywords
 * Yoast intentionally drops meta keywords; the site owner wants them, so we emit
 * our own — from the ACF override, else post tags, else the term/site defaults.
 */
function amu_meta_keywords() {
	$words = array();
	if ( is_singular() ) {
		$override = amu_field( 'amu_keywords' );
		if ( $override ) {
			$words = array_map( 'trim', explode( ',', $override ) );
		} else {
			foreach ( wp_get_post_tags( get_the_ID() ) as $t ) { $words[] = $t->name; }
			foreach ( get_the_category() as $c ) { $words[] = $c->name; }
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term    = get_queried_object();
		$words[] = isset( $term->name ) ? $term->name : '';
		$words[] = 'anime news'; $words[] = 'manga news';
	} else {
		$words = array( 'anime news', 'manga news', 'anime updates', 'manga releases', 'new anime', 'seasonal anime', 'manga chapters' );
	}
	$words = array_values( array_filter( array_unique( array_map( 'trim', $words ) ) ) );
	if ( $words ) {
		printf( "<meta name=\"keywords\" content=\"%s\">\n", esc_attr( implode( ', ', array_slice( $words, 0, 15 ) ) ) );
	}
}
add_action( 'wp_head', 'amu_meta_keywords', 1 );

/* -------------------------------------------------------------- helpers */

/** Yoast breadcrumbs when available (also feeds BreadcrumbList schema). */
function amu_breadcrumbs() {
	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' );
	}
}

/** Primary category kicker markup. */
function amu_kicker( $cyan = false ) {
	$cats = get_the_category();
	if ( empty( $cats ) ) {
		return;
	}
	printf(
		'<a class="kicker%s" href="%s"><span>%s</span></a>',
		$cyan ? ' -cyan' : '',
		esc_url( get_category_link( $cats[0]->term_id ) ),
		esc_html( $cats[0]->name )
	);
}

/** Human relative date, e.g. "3 hours ago". */
function amu_relative_date() {
	return sprintf(
		/* translators: %s: human-readable time difference */
		esc_html__( '%s ago', 'amu' ),
		human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
	);
}

/** The featured (hero) post: newest with ACF amu_featured, else newest post. */
function amu_featured_query() {
	$q = new WP_Query( array(
		'posts_per_page'      => 1,
		'ignore_sticky_posts' => 1,
		'meta_key'            => 'amu_featured',
		'meta_value'          => '1',
		'no_found_rows'       => true,
	) );
	if ( ! $q->have_posts() ) {
		$q = new WP_Query( array( 'posts_per_page' => 1, 'ignore_sticky_posts' => 1, 'no_found_rows' => true ) );
	}
	return $q;
}

function amu_excerpt_length() { return 20; }
add_filter( 'excerpt_length', 'amu_excerpt_length' );
function amu_excerpt_more() { return '…'; }
add_filter( 'excerpt_more', 'amu_excerpt_more' );

/* -------------------------------------------------------------- perf / cleanup */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/* -------------------------------------------------------------- origin-hiding hardening
 * Behind Cloudflare with the origin firewalled to CF IPs; shut the WordPress
 * self-unmask vectors (XML-RPC pingback SSRF etc.).
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );
add_filter( 'xmlrpc_methods', function ( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );
	return $methods;
} );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
add_action( 'pre_ping', function ( &$links ) { $links = array(); } );
