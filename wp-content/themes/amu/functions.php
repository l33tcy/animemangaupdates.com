<?php
/**
 * AnimeMangaUpdates theme functions.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme setup: features and menus.
 */
function amu_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array( 'height' => 40, 'flex-width' => true ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'amu' ),
	) );

	add_image_size( 'amu_card', 640, 360, true );
}
add_action( 'after_setup_theme', 'amu_setup' );

/**
 * Enqueue the single theme stylesheet + nav script.
 */
function amu_assets() {
	$ver = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'amu-style', get_stylesheet_uri(), array(), $ver );
	wp_enqueue_script( 'amu-main', get_template_directory_uri() . '/assets/js/main.js', array(), $ver, true );
}
add_action( 'wp_enqueue_scripts', 'amu_assets' );

/**
 * Sidebar widget area.
 */
function amu_widgets() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'amu' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<div class="widget">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'amu_widgets' );

/**
 * Fallback menu when no primary menu is assigned: list top categories.
 */
function amu_default_menu() {
	echo '<ul>';
	wp_list_categories( array(
		'title_li'   => '',
		'number'     => 6,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'show_count' => false,
	) );
	echo '</ul>';
}

/**
 * Human-friendly relative post date, e.g. "3 hours ago".
 */
function amu_relative_date() {
	return sprintf(
		/* translators: %s: human-readable time difference */
		esc_html__( '%s ago', 'amu' ),
		human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
	);
}

/**
 * Trim excerpts to a tighter length for cards.
 */
function amu_excerpt_length() {
	return 22;
}
add_filter( 'excerpt_length', 'amu_excerpt_length' );
