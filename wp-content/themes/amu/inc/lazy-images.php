<?php
/**
 * Progressive image loading.
 *
 * WordPress already emits native loading="lazy" + decoding="async" and keeps the
 * LCP image eager. This adds a blur-up fade-in on top for content images: they
 * reserve space (no layout shift), then reveal smoothly once decoded. The reveal
 * is gated on a `.amu-js` class (added by main.js), so with JS off the images are
 * fully visible, never stuck hidden.
 *
 * Styles: style.css (.amu-lazy). Reveal logic: assets/js/main.js.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tag content images for the fade and guarantee lazy + async decoding. The
 * featured image (wp-post-image) is the LCP candidate, so it is left eager and
 * un-faded on purpose. Runs per <img> in post content (WP 6.0+).
 */
add_filter( 'wp_content_img_tag', function ( $tag ) {
	if ( false !== strpos( $tag, 'wp-post-image' ) ) {
		return $tag; // LCP: keep it eager and instant.
	}
	if ( false === strpos( $tag, ' loading=' ) ) {
		$tag = str_replace( '<img ', '<img loading="lazy" ', $tag );
	}
	if ( false === strpos( $tag, ' decoding=' ) ) {
		$tag = str_replace( '<img ', '<img decoding="async" ', $tag );
	}
	if ( false !== strpos( $tag, 'class="' ) ) {
		$tag = preg_replace( '/class="/', 'class="amu-lazy ', $tag, 1 );
	} else {
		$tag = str_replace( '<img ', '<img class="amu-lazy" ', $tag );
	}
	return $tag;
}, 20 );
