<?php
/**
 * Codes component with copy-to-clipboard buttons for codes posts.
 *
 *   [codes title="Working Codes"]
 *     [code reward="50 Rerolls"]SINGALONG[/code]
 *     [code reward="30 Rerolls"]CQC[/code]
 *   [/codes]
 *
 * The button copies the code via the clipboard handler in assets/js/main.js.
 * Styles live in style.css (.amu-codes / .amu-code).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** [codes] wrapper. */
add_shortcode( 'codes', function ( $atts, $content = '' ) {
	$atts = shortcode_atts( array( 'title' => 'Working Codes' ), $atts, 'codes' );
	$head = '' !== $atts['title']
		? '<div class="amu-codes-title">' . esc_html( $atts['title'] ) . '</div>'
		: '';
	// wpautop litters the inner markup with <br>/<p> before shortcodes resolve.
	$content = str_replace( array( '<br />', '<br/>', '<br>' ), '', (string) $content );
	$content = preg_replace( '#</?p>#i', '', $content );
	return '<div class="amu-codes">' . $head . do_shortcode( $content ) . '</div>';
} );

/** [code reward="..."]THECODE[/code] */
add_shortcode( 'code', function ( $atts, $content = '' ) {
	$atts   = shortcode_atts( array( 'reward' => '' ), $atts, 'code' );
	$code   = trim( wp_strip_all_tags( (string) $content ) );
	if ( '' === $code ) {
		return '';
	}
	$reward = '' !== $atts['reward']
		? '<span class="amu-code-reward">' . esc_html( $atts['reward'] ) . '</span>'
		: '';

	return '<div class="amu-code">'
		. '<code class="amu-code-val">' . esc_html( $code ) . '</code>'
		. $reward
		. '<button type="button" class="amu-code-copy" data-code="' . esc_attr( $code ) . '" aria-label="' . esc_attr( 'Copy code ' . $code ) . '">Copy</button>'
		. '</div>';
} );
