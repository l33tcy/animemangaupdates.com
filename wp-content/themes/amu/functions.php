<?php
/**
 * AnimeMangaUpdates theme functions.
 *
 * SEO is owned by the real Yoast SEO plugin (titles, descriptions, canonical,
 * Open Graph, sitemaps, JSON-LD schema graph), no bridge plugin. Custom fields
 * use ACF. Layout is full-width (no sidebars); the main nav is a WordPress menu
 * (Appearance → Menus → "Primary Menu"), fully customizable.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/inc/watermark.php'; // logo + domain watermark on uploads

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

/**
 * Render a homepage category section: coloured heading (linked to the archive)
 * + a row of the most recent posts in that category. Skips silently if the
 * category doesn't exist or has no posts.
 *
 * @param string $slug  Category slug (e.g. 'manga', 'anime', 'gaming').
 * @param int    $count Number of cards to show.
 */
function amu_home_section( $slug, $count = 4 ) {
	$cat = get_category_by_slug( $slug );
	if ( ! $cat || 0 === (int) $cat->count ) {
		return;
	}
	$q = new WP_Query( array(
		'category_name'       => $slug,
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return;
	}
	$color = amu_term_color( $cat );
	printf(
		'<section class="home-section" style="--sec:%1$s"><div class="section-head"><span class="sec-dot"></span><h2><a href="%2$s">%3$s</a></h2><span class="bar"></span><a class="sec-more" href="%2$s">%4$s</a></div><div class="card-grid">',
		esc_attr( $color ),
		esc_url( get_category_link( $cat->term_id ) ),
		esc_html( $cat->name ),
		esc_html__( 'View all', 'amu' )
	);
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'template-parts/content', 'card', array( 'lead' => false ) );
	}
	echo '</div></section>';
	wp_reset_postdata();
}

/** Homepage section categories, filterable. Default: Manga, Anime, Gaming. */
function amu_home_sections() {
	return apply_filters( 'amu_home_sections', array( 'manga', 'anime', 'gaming' ) );
}

/** Trending search terms: most-used tags (fallback: categories). */
function amu_trending_terms( $n = 8 ) {
	$tags = get_tags( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => $n, 'hide_empty' => true ) );
	return ! empty( $tags ) ? $tags : amu_top_categories( $n );
}

/**
 * "Follow on Google News" URL. Filterable via 'amu_gnews_url'. Defaults to a
 * Google News search for the site; set your real publication URL once approved.
 */
function amu_gnews_url() {
	$default = 'https://news.google.com/search?q=' . rawurlencode( get_bloginfo( 'name' ) );
	return apply_filters( 'amu_gnews_url', $default );
}

/** Official Google News brand mark for the Follow CTA (nominative use to link to the service). */
function amu_gnews_icon() {
	return sprintf(
		'<img class="gn-logo" src="%s" width="22" height="18" alt="" aria-hidden="true" loading="lazy" decoding="async">',
		esc_url( get_template_directory_uri() . '/assets/google-news.svg' )
	);
}

/** Google News follow callout (shown on single posts only). */
function amu_gnews_callout() {
	?>
	<div class="gnews-callout">
		<span class="gn-mark" aria-hidden="true"><?php echo amu_gnews_icon(); // phpcs:ignore ?></span>
		<span class="gn-txt"><strong><?php esc_html_e( 'Never miss a release.', 'amu' ); ?></strong> <?php esc_html_e( 'Follow us on Google News and add us as a favorite.', 'amu' ); ?></span>
		<a class="btn-gnews" href="<?php echo esc_url( amu_gnews_url() ); ?>" target="_blank" rel="noopener"><?php echo amu_gnews_icon(); // phpcs:ignore ?><span class="label"><?php esc_html_e( 'Follow on Google News', 'amu' ); ?></span></a>
	</div>
	<?php
}

/**
 * Social share buttons for the current post — real brand icons, brand-coloured
 * buttons. Icons live in assets/share/<slug>.svg.
 */
function amu_post_share() {
	$url   = rawurlencode( get_permalink() );
	$title = rawurlencode( get_the_title() );
	$base  = get_template_directory_uri() . '/assets/share/';
	// slug => [ label, brand colour, share URL ]
	$items = array(
		'x'        => array( 'X', '#000000', "https://twitter.com/intent/tweet?url={$url}&text={$title}" ),
		'facebook' => array( 'Facebook', '#1877F2', "https://www.facebook.com/sharer/sharer.php?u={$url}" ),
		'whatsapp' => array( 'WhatsApp', '#25D366', "https://api.whatsapp.com/send?text={$title}%20{$url}" ),
		'reddit'   => array( 'Reddit', '#FF4500', "https://www.reddit.com/submit?url={$url}&title={$title}" ),
		'telegram' => array( 'Telegram', '#26A5E4', "https://t.me/share/url?url={$url}&text={$title}" ),
	);
	echo '<div class="share"><span class="share-label">' . esc_html__( 'Share', 'amu' ) . '</span><ul class="share-list">';
	foreach ( $items as $slug => $p ) {
		printf(
			'<li><a class="share-btn" style="--brand:%1$s" href="%2$s" target="_blank" rel="noopener nofollow" aria-label="%3$s"><img src="%4$s%5$s.svg" width="18" height="18" alt="" aria-hidden="true" loading="lazy" decoding="async"></a></li>',
			esc_attr( $p[1] ),
			esc_url( $p[2] ),
			/* translators: %s: platform name */
			esc_attr( sprintf( __( 'Share on %s', 'amu' ), $p[0] ) ),
			esc_url( $base ),
			esc_attr( $slug )
		);
	}
	echo '</ul></div>';
}

/** [amu_email] renders a "Show email" button that reveals the address via JS (anti-spam). */
add_shortcode( 'amu_email', function () {
	return '<button type="button" class="reveal-email js-reveal-email" data-u="contact" data-d="animemangaupdates.com">' . esc_html__( 'Show email', 'amu' ) . '</button>';
} );

/* -------------------------------------------------------------- SEO structure */

/** Tell Yoast to mark single posts as NewsArticle (news intent for SERP). */
add_filter( 'wpseo_schema_article_type', function ( $type ) {
	return is_singular( 'post' ) ? 'NewsArticle' : $type;
} );

/**
 * Ensure single-post breadcrumbs include the (primary) category and its ancestors
 *, e.g. Home › Manga › One Piece › Title. Feeds both the visual Yoast breadcrumb
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
	$toc = '<details class="toc" open><summary>' . esc_html__( 'Table of contents', 'amu' ) . '</summary><ol>' . $items . '</ol></details>';
	$pos = stripos( $content, '</p>' );
	return false !== $pos ? substr( $content, 0, $pos + 4 ) . $toc . substr( $content, $pos + 4 ) : $toc . $content;
}
add_filter( 'the_content', 'amu_add_toc', 9 );

/** Wrap article tables in a scroll container so wide tables stay responsive. */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || false === stripos( $content, '<table' ) ) {
		return $content;
	}
	return preg_replace( '/<table\b.*?<\/table>/is', '<div class="table-wrap">$0</div>', $content );
}, 11 );

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

/* -------------------------------------------------------------- disable comments everywhere */
add_action( 'init', function () {
	foreach ( get_post_types() as $pt ) {
		if ( post_type_supports( $pt, 'comments' ) ) {
			remove_post_type_support( $pt, 'comments' );
			remove_post_type_support( $pt, 'trackbacks' );
		}
	}
}, 100 );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 20 );
add_filter( 'feed_links_show_comments_feed', '__return_false' );
// Admin: hide the Comments menu, admin-bar node, dashboard widget, and block the page.
add_action( 'admin_menu', function () { remove_menu_page( 'edit-comments.php' ); } );
add_action( 'wp_before_admin_bar_render', function () {
	if ( isset( $GLOBALS['wp_admin_bar'] ) ) { $GLOBALS['wp_admin_bar']->remove_node( 'comments' ); }
} );
add_action( 'admin_init', function () {
	if ( isset( $GLOBALS['pagenow'] ) && 'edit-comments.php' === $GLOBALS['pagenow'] ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
} );
// Drop the comments REST endpoints too.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	foreach ( array( '/wp/v2/comments', '/wp/v2/comments/(?P<id>[\d]+)' ) as $route ) {
		unset( $endpoints[ $route ] );
	}
	return $endpoints;
} );

/* -------------------------------------------------------------- REST + login hardening
 * DDoS and edge rate-limiting are handled by Cloudflare (origin is firewalled to
 * CF IPs). These are the app-level defenses: stop user enumeration and brute force.
 */

// Disable the REST user endpoints (user enumeration) while keeping the rest of the
// REST API working, incl. wp/v2/search used by the live search suggestions.
add_filter( 'rest_endpoints', function ( $endpoints ) {
	foreach ( array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route ) {
		unset( $endpoints[ $route ] );
	}
	return $endpoints;
} );

// Block author enumeration via ?author=N and /author/ archives. Runs at priority
// 0 so it fires BEFORE redirect_canonical, which would otherwise 301 ?author=1 to
// /author/<login>/ and leak the username.
add_action( 'template_redirect', function () {
	if ( is_author() || ( isset( $_GET['author'] ) && ! is_admin() && ! is_user_logged_in() ) ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}, 0 );
// Also strip the author query var early, as a belt-and-suspenders guard.
add_action( 'parse_request', function ( $wp ) {
	if ( ! is_admin() && ! empty( $wp->query_vars['author'] ) && ! is_user_logged_in() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
} );

// Remove REST discovery + oEmbed author leaks from head/headers.
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
add_filter( 'oembed_response_data', function ( $data ) { unset( $data['author_name'], $data['author_url'] ); return $data; } );

// Generic login error (no username/password hints).
add_filter( 'login_errors', function () { return __( 'Invalid login details.', 'amu' ); } );

/** Best-effort client IP (real IP behind Cloudflare/Traefik). */
function amu_client_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			return sanitize_text_field( trim( explode( ',', $_SERVER[ $k ] )[0] ) );
		}
	}
	return '0';
}

// Brute-force throttle: 5 failed logins per IP per 15 minutes, then a temporary block.
add_filter( 'authenticate', function ( $user, $username ) {
	if ( '' === (string) $username ) {
		return $user;
	}
	if ( (int) get_transient( 'amu_lf_' . md5( amu_client_ip() ) ) >= 5 ) {
		return new WP_Error( 'amu_locked', __( 'Too many failed attempts. Please try again in 15 minutes.', 'amu' ) );
	}
	return $user;
}, 30, 2 );
add_action( 'wp_login_failed', function () {
	$k = 'amu_lf_' . md5( amu_client_ip() );
	set_transient( $k, (int) get_transient( $k ) + 1, 15 * MINUTE_IN_SECONDS );
} );
add_action( 'wp_login', function () { delete_transient( 'amu_lf_' . md5( amu_client_ip() ) ); } );

/* -------------------------------------------------------------- search hardening */

// Sanitize the search query: strip tags/entities and cap the length (defensive,
// on top of WordPress's own escaping) so nothing malicious is stored or reflected.
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) {
		return;
	}
	$s = (string) $q->get( 's' );
	$s = trim( wp_strip_all_tags( wp_unslash( $s ) ) );
	$s = preg_replace( '/[<>]/', '', $s );
	if ( function_exists( 'mb_substr' ) ) {
		$s = mb_substr( $s, 0, 120 );
	}
	$q->set( 's', $s );
} );

// Empty search (e.g. /?s=) should not list the whole site; send it home.
add_action( 'template_redirect', function () {
	if ( is_search() && '' === trim( (string) get_search_query() ) ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}
} );

// Search result pages: noindex, follow (avoid search-spam getting indexed).
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_search() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );

/* -------------------------------------------------------------- automatic updates */
add_filter( 'automatic_updater_disabled', '__return_false' );
add_filter( 'auto_update_core', '__return_true' );
add_filter( 'allow_major_auto_core_updates', '__return_true' );
add_filter( 'auto_update_plugin', '__return_true' );
add_filter( 'auto_update_theme', '__return_true' );
add_filter( 'auto_update_translation', '__return_true' );

/* -------------------------------------------------------------- minimal branded login page */
add_filter( 'login_headerurl', function () { return home_url( '/' ); } );
add_filter( 'login_headertext', function () { return get_bloginfo( 'name' ); } );
add_action( 'login_enqueue_scripts', function () {
	$id   = get_theme_mod( 'custom_logo' );
	$logo = $id ? wp_get_attachment_url( $id ) : '';
	?>
	<style>
		body.login { background: #0c0f17; }
		<?php if ( $logo ) : ?>
		.login h1 a {
			background-image: url('<?php echo esc_url( $logo ); ?>') !important;
			background-size: contain !important; background-position: center !important;
			width: 260px !important; height: 68px !important; margin: 0 auto 10px !important;
		}
		<?php endif; ?>
		.login form {
			background: #171b28; border: 1px solid #2b3143; border-radius: 12px;
			box-shadow: 0 24px 60px -24px rgba(0,0,0,.6); padding: 26px 24px;
		}
		.login form label { color: #f2f4f8; font-size: 14px; }
		.login input[type="text"], .login input[type="password"] {
			background: #12151f; border: 1px solid #2b3143; color: #f2f4f8; border-radius: 8px; padding: 10px 12px;
		}
		.login input[type="text"]:focus, .login input[type="password"]:focus {
			border-color: #e01a2b; box-shadow: 0 0 0 2px rgba(224,26,43,.35); outline: 0;
		}
		.wp-core-ui .button-primary {
			background: #e01a2b; border-color: #bf1524; border-radius: 8px; text-shadow: none; box-shadow: none; font-weight: 700;
		}
		.wp-core-ui .button-primary:hover { background: #bf1524; border-color: #bf1524; }
		.login .button.wp-hide-pw { color: #97a1b5; }
		.login #nav a, .login #backtoblog a { color: #97a1b5; }
		.login #nav a:hover, .login #backtoblog a:hover { color: #e01a2b; }
		.login #login_error, .login .message, .login .success {
			border-left-color: #1f5cff; background: #171b28; color: #f2f4f8; border-radius: 8px;
		}
		.login .language-switcher { display: none; }
	</style>
	<?php
} );

/* -------------------------------------------------------------- clean category/tag URLs (no /category/ or /tag/ base) */

// Strip the base from generated term links (theme, menus, breadcrumbs, sitemaps).
add_filter( 'category_link', function ( $link ) {
	return preg_replace( '#/category/#', '/', $link, 1 );
} );
add_filter( 'term_link', function ( $link, $term, $tax ) {
	if ( 'post_tag' === $tax ) {
		$link = preg_replace( '#/tag/#', '/', $link, 1 );
	}
	return $link;
}, 10, 3 );

// Categories resolve at the root (hierarchical), replacing the /category/ rules.
add_filter( 'category_rewrite_rules', function () {
	$rules = array();
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $c ) {
		$path = trim( get_category_parents( $c->term_id, false, '/', true ), '/' );
		if ( '' === $path ) { continue; }
		$rules[ $path . '/feed/(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?category_name=' . $path . '&feed=$matches[1]';
		$rules[ $path . '/page/?([0-9]{1,})/?$' ]             = 'index.php?category_name=' . $path . '&paged=$matches[1]';
		$rules[ $path . '/?$' ]                               = 'index.php?category_name=' . $path;
	}
	return $rules;
} );

// Tags resolve at the root, replacing the /tag/ rules.
add_filter( 'tag_rewrite_rules', function () {
	$rules = array();
	foreach ( get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => false ) ) as $t ) {
		if ( is_wp_error( $t ) || empty( $t->slug ) ) { continue; }
		$s = $t->slug;
		$rules[ $s . '/feed/(feed|rdf|rss|rss2|atom)/?$' ] = 'index.php?tag=' . $s . '&feed=$matches[1]';
		$rules[ $s . '/page/?([0-9]{1,})/?$' ]             = 'index.php?tag=' . $s . '&paged=$matches[1]';
		$rules[ $s . '/?$' ]                               = 'index.php?tag=' . $s;
	}
	return $rules;
} );

// 301 any old /category/... or /tag/... URL to the clean root path.
add_action( 'template_redirect', function () {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
	if ( preg_match( '#^/(?:category|tag)/(.+)$#', $uri, $m ) ) {
		wp_safe_redirect( home_url( '/' . ltrim( $m[1], '/' ) ), 301 );
		exit;
	}
} );
