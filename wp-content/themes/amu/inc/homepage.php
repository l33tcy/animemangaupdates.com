<?php
/**
 * Homepage news-portal components: mosaic hero, category blocks, news columns
 * (Latest / Most read / Trending), plus lightweight post-view tracking.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Count a view per single-post load (post meta counter).
 * ponytail: one write per view — fine at this scale; add throttling if traffic grows.
 */
add_action( 'wp_head', function () {
	if ( is_singular( 'post' ) && ! is_user_logged_in() ) {
		$id = get_the_ID();
		update_post_meta( $id, '_amu_views', (int) get_post_meta( $id, '_amu_views', true ) + 1 );
	}
}, 1 );

/** Short timestamp: "14:22" for today, else "Aug 30, 2026". */
function amu_stamp( $post = null ) {
	$ts = (int) get_post_time( 'U', true, $post );
	$today = (bool) ( gmdate( 'Y-m-d', $ts + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) ) === current_time( 'Y-m-d' ) );
	return $today
		? sprintf( /* translators: %s: time */ __( 'Today %s', 'amu' ), get_the_time( 'H:i', $post ) )
		: get_the_date( 'M j, Y', $post );
}

/** Most-read posts by tracked views; falls back to recent when views are thin. */
function amu_most_read( $n = 4 ) {
	$p = get_posts( array(
		'numberposts'  => $n,
		'meta_key'     => '_amu_views', // phpcs:ignore WordPress.DB.SlowDBQuery
		'orderby'      => 'meta_value_num',
		'order'        => 'DESC',
		'no_found_rows' => true,
	) );
	if ( count( $p ) < $n ) {
		$p = get_posts( array( 'numberposts' => $n, 'no_found_rows' => true ) );
	}
	return $p;
}

/** A card with the title overlaid on the (darkened) featured image. */
function amu_overlay_card( $post, $size = 'amu_card', $class = '' ) {
	$img = has_post_thumbnail( $post )
		? get_the_post_thumbnail( $post, $size, array( 'alt' => esc_attr( get_the_title( $post ) ), 'loading' => 'lazy' ) )
		: '<span class="ph"></span>';
	printf(
		'<a class="ov-card %1$s" href="%2$s"><span class="ov-media">%3$s<span class="ov-shade"></span></span><span class="ov-body">%4$s<span class="ov-title">%5$s</span></span></a>',
		esc_attr( $class ),
		esc_url( get_permalink( $post ) ),
		$img, // already escaped markup
		amu_cat_tag_for( $post ),
		esc_html( get_the_title( $post ) )
	);
}

/** Coloured category pill for a given post (string, for use inside other markup). */
function amu_cat_tag_for( $post ) {
	$cats = get_the_category( $post->ID );
	if ( empty( $cats ) ) {
		return '';
	}
	return sprintf( '<span class="ov-cat" style="--tag:%s">%s</span>', esc_attr( amu_term_color( $cats[0] ) ), esc_html( $cats[0]->name ) );
}

/** Mosaic hero: one large overlay card + up to four small ones. */
function amu_hero_mosaic() {
	$posts = get_posts( array( 'numberposts' => 5, 'no_found_rows' => true ) );
	if ( empty( $posts ) ) {
		return;
	}
	echo '<section class="hero-mosaic">';
	$lead = array_shift( $posts );
	amu_overlay_card( $lead, 'amu_hero', 'ov-lead' );
	foreach ( $posts as $p ) {
		amu_overlay_card( $p, 'amu_card', 'ov-sm' );
	}
	echo '</section>';
}

/** One category block: blue header tab, featured lead, headline list, "More". */
function amu_category_block( $cat, $count = 4 ) {
	$posts = get_posts( array( 'category' => $cat->term_id, 'numberposts' => $count, 'no_found_rows' => true ) );
	if ( empty( $posts ) ) {
		return;
	}
	$link = get_category_link( $cat->term_id );
	$lead = array_shift( $posts );
	echo '<section class="cat-block">';
	printf(
		'<a class="cat-block-head" href="%s"><span class="cbh-name">%s</span><span class="cbh-arrows" aria-hidden="true">›››</span></a>',
		esc_url( $link ),
		esc_html( $cat->name )
	);
	printf(
		'<a class="cat-block-lead" href="%s">%s</a>',
		esc_url( get_permalink( $lead ) ),
		has_post_thumbnail( $lead ) ? get_the_post_thumbnail( $lead, 'amu_card', array( 'loading' => 'lazy', 'alt' => esc_attr( get_the_title( $lead ) ) ) ) : '<span class="ph"></span>'
	);
	printf(
		'<h3 class="cat-block-title"><a href="%s">%s</a></h3><div class="cb-stamp">%s</div>',
		esc_url( get_permalink( $lead ) ),
		esc_html( get_the_title( $lead ) ),
		esc_html( amu_stamp( $lead ) )
	);
	if ( $posts ) {
		echo '<ul class="cat-block-list">';
		foreach ( $posts as $p ) {
			printf(
				'<li><a href="%s">%s</a><span class="cb-stamp">%s</span></li>',
				esc_url( get_permalink( $p ) ),
				esc_html( get_the_title( $p ) ),
				esc_html( amu_stamp( $p ) )
			);
		}
		echo '</ul>';
	}
	printf( '<a class="cat-block-more" href="%s">%s &rarr;</a>', esc_url( $link ), esc_html__( 'See all', 'amu' ) );
	echo '</section>';
}

/** Row of category blocks. $max = 0 renders EVERY category that has posts (ordered by count); >0 caps it. */
function amu_category_blocks( $max = 0 ) {
	$args = array( 'orderby' => 'count', 'order' => 'DESC', 'hide_empty' => true, 'exclude' => array( 1 ) );
	if ( $max > 0 ) {
		$args['number'] = $max;
	}
	$cats = get_categories( $args );
	if ( empty( $cats ) ) {
		return;
	}
	echo '<div class="cat-blocks">';
	foreach ( $cats as $cat ) {
		amu_category_block( $cat );
	}
	echo '</div>';
}

/** A plain headline list (title + timestamp), optional leading number/thumb. */
function amu_headline_list( $posts, $numbered = false ) {
	echo '<ul class="hl-list' . ( $numbered ? ' -num' : '' ) . '">';
	$i = 0;
	foreach ( $posts as $p ) {
		$i++;
		echo '<li>';
		if ( $numbered ) {
			printf( '<span class="hl-num">%d</span>', $i );
		}
		printf(
			'<span class="hl-body"><a class="hl-title" href="%s">%s</a><span class="hl-stamp">%s</span></span>',
			esc_url( get_permalink( $p ) ),
			esc_html( get_the_title( $p ) ),
			esc_html( amu_stamp( $p ) )
		);
		if ( $numbered && has_post_thumbnail( $p ) ) {
			printf( '<a class="hl-thumb" href="%s">%s</a>', esc_url( get_permalink( $p ) ), get_the_post_thumbnail( $p, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ) );
		}
		echo '</li>';
	}
	echo '</ul>';
}

/** Trending Posts sidebar (single-post): red numbered list with thumbnails + dates. */
function amu_trending_sidebar( $n = 6 ) {
	$posts = amu_most_read( $n );
	$posts = array_values( array_filter( $posts, function ( $p ) { return $p->ID !== get_the_ID(); } ) );
	if ( empty( $posts ) ) {
		return;
	}
	echo '<div class="trending-card"><h2 class="trending-title">' . esc_html__( 'Trending Posts', 'amu' ) . '</h2><ol class="trending-list">';
	$i = 0;
	foreach ( $posts as $p ) {
		$i++;
		$thumb = has_post_thumbnail( $p )
			? '<span class="tr-thumb">' . get_the_post_thumbnail( $p, 'thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ) . '</span>'
			: '<span class="tr-thumb tr-ph"></span>';
		printf(
			'<li><a href="%1$s"><span class="tr-num">%2$d</span>%3$s<span class="tr-body"><span class="tr-t">%4$s</span><span class="tr-date">%5$s</span></span></a></li>',
			esc_url( get_permalink( $p ) ),
			$i,
			$thumb,
			esc_html( get_the_title( $p ) ),
			esc_html( get_the_date( 'M j, Y', $p ) )
		);
	}
	echo '</ol></div>';
}

/** Featured/related posts grid shown at the end of a single post. */
function amu_related_posts( $count = 3 ) {
	$args = array(
		'post__not_in'        => array( get_the_ID() ),
		'posts_per_page'      => $count,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);
	$cats = wp_get_post_categories( get_the_ID() );
	if ( $cats ) {
		$args['category__in'] = $cats;
	}
	$q = new WP_Query( $args );
	if ( ! $q->have_posts() && $cats ) { // fall back to recent when the category is thin
		$q = new WP_Query( array( 'post__not_in' => array( get_the_ID() ), 'posts_per_page' => $count, 'no_found_rows' => true ) );
	}
	if ( ! $q->have_posts() ) {
		wp_reset_postdata();
		return;
	}
	echo '<section class="related"><div class="mhead"><h2>' . esc_html__( 'Featured stories', 'amu' ) . '</h2></div><div class="card-grid">';
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'template-parts/content', 'card', array( 'lead' => false ) );
	}
	echo '</div></section>';
	wp_reset_postdata();
}

/** Minimal "Most read" list — big faint numbers + bold titles, two columns. */
function amu_most_read_section() {
	$posts = amu_most_read( 6 );
	if ( empty( $posts ) ) {
		return;
	}
	echo '<div class="mhead"><h2>' . esc_html__( 'Most read', 'amu' ) . '</h2></div>';
	echo '<ol class="read-list">';
	$i = 0;
	foreach ( $posts as $p ) {
		$i++;
		printf(
			'<li><a href="%s"><span class="rl-num">%02d</span><span class="rl-body"><span class="rl-title">%s</span><span class="rl-stamp">%s</span></span></a></li>',
			esc_url( get_permalink( $p ) ),
			$i,
			esc_html( get_the_title( $p ) ),
			esc_html( amu_stamp( $p ) )
		);
	}
	echo '</ol>';
}

/** Three-column news section: Latest + Most read + Trending (blue card). */
function amu_news_section() {
	$latest = get_posts( array( 'numberposts' => 6, 'no_found_rows' => true ) );
	if ( empty( $latest ) ) {
		return;
	}
	$most = amu_most_read( 4 );
	$trend = amu_most_read( 5 );
	?>
	<div class="news-cols">
		<div class="news-col">
			<h2 class="news-head"><?php esc_html_e( 'Most read', 'amu' ); ?></h2>
			<?php amu_headline_list( $most ); ?>
		</div>
		<div class="news-col">
			<h2 class="news-head"><?php esc_html_e( 'Latest news', 'amu' ); ?></h2>
			<?php amu_headline_list( $latest ); ?>
		</div>
		<div class="news-col trending">
			<h2 class="news-head"><?php esc_html_e( 'Trending', 'amu' ); ?></h2>
			<?php amu_headline_list( $trend, true ); ?>
		</div>
	</div>
	<?php
}
