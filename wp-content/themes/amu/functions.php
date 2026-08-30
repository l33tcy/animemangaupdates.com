<?php
/**
 * AnimeMangaUpdates theme functions.
 *
 * SEO is owned by the real Yoast SEO plugin (titles, descriptions, canonical,
 * Open Graph, sitemaps, JSON-LD schema graph) — no bridge plugin. Custom fields
 * use ACF. Layout is full-width (no sidebars); the main nav is a WordPress menu
 * (Appearance → Menus → "Primary Menu"), fully customizable.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------- setup */
function amu_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'flex-width' => true ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'amu' ),   // main navbar + mobile drawer
		'footer'  => __( 'Footer Menu', 'amu' ),
	) );

	add_image_size( 'amu_hero', 1600, 900, true );
	add_image_size( 'amu_card', 760, 500, true );
}
add_action( 'after_setup_theme', 'amu_setup' );

/* -------------------------------------------------------------- assets */
function amu_assets() {
	$ver = wp_get_theme()->get( 'Version' );

	wp_enqueue_style( 'amu-fonts', 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Chakra+Petch:wght@600;700&family=Zen+Kaku+Gothic+New:wght@400;500;700&display=swap', array(), null );
	wp_enqueue_style( 'amu-style', get_stylesheet_uri(), array( 'amu-fonts' ), $ver );

	wp_enqueue_script( 'amu-main', get_template_directory_uri() . '/assets/js/main.js', array(), $ver, true );
	wp_localize_script( 'amu-main', 'amuData', array(
		'searchRest' => esc_url_raw( rest_url( 'wp/v2/search' ) ),
		'homeUrl'    => esc_url_raw( home_url( '/' ) ),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'amu_assets' );

/* -------------------------------------------------------------- ACF fields */
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
			array( 'key' => 'field_amu_featured', 'label' => 'Feature on homepage', 'name' => 'amu_featured', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Show larger, as the lead card.' ),
			array( 'key' => 'field_amu_series',   'label' => 'Series / Title',       'name' => 'amu_series',   'type' => 'text' ),
			array( 'key' => 'field_amu_status',   'label' => 'Status',               'name' => 'amu_status',   'type' => 'select', 'choices' => array( 'Ongoing' => 'Ongoing', 'Completed' => 'Completed', 'Upcoming' => 'Upcoming', 'Announced' => 'Announced', 'Hiatus' => 'Hiatus' ), 'allow_null' => 1, 'ui' => 1 ),
			array( 'key' => 'field_amu_episodes', 'label' => 'Episodes / Chapters',  'name' => 'amu_episodes', 'type' => 'text' ),
			array( 'key' => 'field_amu_score',    'label' => 'Score (0-10)',         'name' => 'amu_score',    'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.1 ),
			array( 'key' => 'field_amu_keywords', 'label' => 'Meta keywords',        'name' => 'amu_keywords', 'type' => 'text', 'instructions' => 'Comma-separated. Overrides the auto keywords in <meta name="keywords">.' ),
		),
	) );
}
add_action( 'acf/init', 'amu_acf_fields' );

function amu_field( $name, $id = null ) {
	return function_exists( 'get_field' ) ? get_field( $name, $id ) : null;
}

/* -------------------------------------------------------------- meta keywords */
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

/* -------------------------------------------------------------- presentation helpers */

/** Estimated reading time in whole minutes. */
function amu_reading_time( $id = null ) {
	$words = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $id ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/** Deterministic accent colour for a category tag. */
function amu_term_color( $term ) {
	$palette = array( '#ff1fa0', '#2fbf71', '#3d5afe', '#f4d035', '#ff6b3d', '#8b5cf6', '#00c2ff', '#ff4d6d' );
	$id      = is_object( $term ) ? (int) $term->term_id : (int) $term;
	return $palette[ $id % count( $palette ) ];
}

/** Primary category tag (coloured pill) for a card/article. */
function amu_cat_tag() {
	$cats = get_the_category();
	if ( empty( $cats ) ) {
		return;
	}
	printf(
		'<a class="tag-cat" href="%s" style="--tag:%s">%s</a>',
		esc_url( get_category_link( $cats[0]->term_id ) ),
		esc_attr( amu_term_color( $cats[0] ) ),
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

/** Card meta line: date · reading time. */
function amu_card_meta() {
	printf(
		'<span class="date">%s</span><span class="dot">·</span><span class="rt">%d min</span>',
		esc_html( get_the_date() ),
		amu_reading_time()
	);
}

/** Top categories, for the hero filter pills and search overlay. */
function amu_top_categories( $n = 5 ) {
	return get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $n, 'hide_empty' => true ) );
}

/** Trending search terms: most-used tags (fallback: categories). */
function amu_trending_terms( $n = 8 ) {
	$tags = get_tags( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $n, 'hide_empty' => true ) );
	return ! empty( $tags ) ? $tags : amu_top_categories( $n );
}

/** "Subscribe" button target — filterable; defaults to the RSS feed. */
function amu_subscribe_url() {
	return apply_filters( 'amu_subscribe_url', get_feed_link() );
}

/* -------------------------------------------------------------- SEO structure */

/** Tell Yoast to mark single posts as NewsArticle (news intent for SERP). */
add_filter( 'wpseo_schema_article_type', function ( $type ) {
	return is_singular( 'post' ) ? 'NewsArticle' : $type;
} );

/**
 * Ensure single-post breadcrumbs include the (primary) category and its ancestors
 * — e.g. Home › Manga › One Piece › Title. Feeds both the visual Yoast breadcrumb
 * and the BreadcrumbList schema, so the site's hierarchy is consistent for SERP.
 */
add_filter( 'wpseo_breadcrumb_links', function ( $links ) {
	if ( ! is_singular( 'post' ) ) {
		return $links;
	}
	$cats = get_the_category();
	if ( empty( $cats ) ) {
		return $links;
	}
	$primary = (int) get_post_meta( get_the_ID(), '_yoast_wpseo_primary_category', true );
	$term    = $cats[0];
	foreach ( $cats as $c ) {
		if ( $c->term_id === $primary ) { $term = $c; break; }
	}
	// Skip if Yoast already inserted a category crumb.
	foreach ( $links as $l ) {
		if ( ! empty( $l['term_id'] ) ) { return $links; }
	}
	$chain = array();
	$t     = $term;
	$guard = 0;
	while ( $t && $guard < 6 ) {
		array_unshift( $chain, array( 'term_id' => $t->term_id, 'text' => $t->name, 'url' => get_category_link( $t->term_id ) ) );
		$t = $t->parent ? get_term( $t->parent, 'category' ) : null;
		$guard++;
	}
	$last  = array_pop( $links );          // current post (title) crumb
	return array_merge( $links, $chain, array( $last ) );
} );

/**
 * Auto Table of Contents: give every H2 an id and inject a jump-link TOC after
 * the intro paragraph. Improves scannability and earns SERP "jump to" links.
 */
function amu_add_toc( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( ! preg_match_all( '/<h2([^>]*)>(.*?)<\/h2>/is', $content, $m, PREG_SET_ORDER ) || count( $m ) < 2 ) {
		return $content;
	}
	$items = '';
	$used  = array();
	foreach ( $m as $h ) {
		$text = trim( wp_strip_all_tags( $h[2] ) );
		if ( '' === $text ) { continue; }
		$slug = sanitize_title( $text );
		if ( isset( $used[ $slug ] ) ) { $used[ $slug ]++; $slug .= '-' . $used[ $slug ]; } else { $used[ $slug ] = 1; }
		if ( false === strpos( $h[1], 'id=' ) ) {
			$content = str_replace( $h[0], '<h2' . $h[1] . ' id="' . esc_attr( $slug ) . '">' . $h[2] . '</h2>', $content );
		}
		$items .= '<li><a href="#' . esc_attr( $slug ) . '">' . esc_html( $text ) . '</a></li>';
	}
	$toc = '<nav class="toc" aria-label="' . esc_attr__( 'Table of contents', 'amu' ) . '"><p class="toc-title">' . esc_html__( 'Table of contents', 'amu' ) . '</p><ol>' . $items . '</ol></nav>';
	$pos = stripos( $content, '</p>' );
	return false !== $pos ? substr( $content, 0, $pos + 4 ) . $toc . substr( $content, $pos + 4 ) : $toc . $content;
}
add_filter( 'the_content', 'amu_add_toc', 9 );

function amu_excerpt_length() { return 18; }
add_filter( 'excerpt_length', 'amu_excerpt_length' );
function amu_excerpt_more() { return '…'; }
add_filter( 'excerpt_more', 'amu_excerpt_more' );

/* -------------------------------------------------------------- perf / cleanup */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/* -------------------------------------------------------------- origin-hiding hardening */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'wp_headers', function ( $headers ) { unset( $headers['X-Pingback'] ); return $headers; } );
add_filter( 'xmlrpc_methods', function ( $methods ) { unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] ); return $methods; } );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
add_action( 'pre_ping', function ( &$links ) { $links = array(); } );
