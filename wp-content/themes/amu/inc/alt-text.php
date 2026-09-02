<?php
/**
 * Dynamic alt-text fallbacks for accessibility and image SEO.
 *
 * WordPress only emits an alt attribute when the attachment actually has alt
 * meta saved. Featured images and pasted-in content images often ship with an
 * empty alt, which hurts screen-reader users and image search. This fills a
 * sensible alt whenever one is missing, and never overrides a real alt the
 * editor set.
 *
 * Featured / attachment images: wp_get_attachment_image_attributes covers
 * the_post_thumbnail (single hero), the card grid, homepage rows, and the
 * footer + drawer logos. In-content images: wp_content_img_tag covers both the
 * classic editor and block-editor images.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Best available alt text for an attachment, in priority order: saved alt meta,
 * caption, image title, parent post title, then a caller-supplied fallback and
 * finally the site name. Always returns a non-empty string.
 *
 * @param int    $attachment_id  Attachment ID.
 * @param string $fallback_title Context title to use before the site name.
 * @return string
 */
function amu_attachment_alt( $attachment_id, $fallback_title = '' ) {
	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( '' !== $alt ) {
		return $alt;
	}
	$att = get_post( $attachment_id );
	if ( $att ) {
		if ( '' !== trim( (string) $att->post_excerpt ) ) { return trim( $att->post_excerpt ); } // caption
		if ( '' !== trim( (string) $att->post_title ) )   { return trim( $att->post_title ); }  // image title
		if ( $att->post_parent ) {
			$parent = get_the_title( $att->post_parent );
			if ( '' !== trim( (string) $parent ) ) { return $parent; }
		}
	}
	if ( '' !== trim( (string) $fallback_title ) ) { return trim( $fallback_title ); }
	return get_bloginfo( 'name' );
}

/**
 * Fill an empty alt on featured / attachment images rendered through WordPress.
 */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) ) {
		$fallback    = is_singular() ? get_the_title() : get_bloginfo( 'name' );
		$attr['alt'] = amu_attachment_alt( $attachment->ID, $fallback );
	}
	return $attr;
}, 20, 2 );

/**
 * Guarantee an alt on in-content images. Runs before the lazy-image filter
 * (priority 20) and leaves any real alt untouched.
 */
add_filter( 'wp_content_img_tag', function ( $tag, $context = '', $attachment_id = 0 ) {
	if ( preg_match( '/\salt="[^"]+"/', $tag ) ) {
		return $tag; // Editor already wrote a real alt.
	}
	$alt = $attachment_id ? amu_attachment_alt( (int) $attachment_id, get_the_title() ) : '';
	if ( '' === $alt && preg_match( '/wp-image-(\d+)/', $tag, $m ) ) {
		$alt = amu_attachment_alt( (int) $m[1], get_the_title() );
	}
	if ( '' === $alt ) {
		$alt = get_the_title();
	}
	$alt = esc_attr( $alt );
	if ( preg_match( '/\salt=""/', $tag ) ) {
		return preg_replace( '/\salt=""/', ' alt="' . $alt . '"', $tag, 1 );
	}
	return str_replace( '<img ', '<img alt="' . $alt . '" ', $tag );
}, 15, 3 );
