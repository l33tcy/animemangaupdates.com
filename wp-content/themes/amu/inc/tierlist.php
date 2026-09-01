<?php
/**
 * Tier-list component for tier-list posts.
 *
 *   [tierlist title="Best Units"]
 *     [tier label="S+"]Unit A, Unit B, Unit C[/tier]
 *     [tier label="S" note="Meta"]Unit D, Unit E[/tier]
 *     [tier label="A" color="#12c2e9"]Unit F[/tier]
 *   [/tierlist]
 *
 * Items are split on commas or new lines into chips. Colors default by tier
 * letter; pass color="" to override. Styles live in style.css (.amu-tierlist).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Default accent per tier letter. */
function amu_tier_color( $label ) {
	$map = array(
		'S+' => '#ff3040', 'SS' => '#ff3040',
		'S'  => '#ff6a00',
		'A'  => '#f5b400',
		'B'  => '#3bd16f',
		'C'  => '#2f6bff',
		'D'  => '#8a7dff',
		'E'  => '#c86bff',
		'F'  => '#9aa3b2',
	);
	$key = strtoupper( trim( (string) $label ) );
	return isset( $map[ $key ] ) ? $map[ $key ] : '#2f6bff';
}

/** [tierlist] wrapper. */
add_shortcode( 'tierlist', function ( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'title' => '' ), $atts, 'tierlist' );
	$head  = '' !== $atts['title']
		? '<div class="amu-tier-title">' . esc_html( $atts['title'] ) . '</div>'
		: '';
	// wpautop runs before shortcodes and litters the inner markup with <br>/<p>
	// between the rows; strip that noise before resolving the nested [tier] rows.
	$content = str_replace( array( '<br />', '<br/>', '<br>' ), '', (string) $content );
	$content = preg_replace( '#</?p>#i', '', $content );
	return '<div class="amu-tierlist">' . $head . do_shortcode( $content ) . '</div>';
} );

/** [tier] row. */
add_shortcode( 'tier', function ( $atts, $content = '' ) {
	$atts  = shortcode_atts( array( 'label' => '', 'color' => '', 'note' => '' ), $atts, 'tier' );
	$label = trim( (string) $atts['label'] );
	$color = '' !== $atts['color'] ? $atts['color'] : amu_tier_color( $label );

	// ponytail: comma or newline separates items; a name containing a comma would split.
	$raw   = trim( wp_strip_all_tags( (string) $content ) );
	$items = array_filter( array_map( 'trim', preg_split( '/\s*,\s*|\r?\n/', $raw ) ), 'strlen' );

	$chips = '';
	foreach ( $items as $item ) {
		$chips .= '<span class="amu-tier-chip">' . esc_html( $item ) . '</span>';
	}
	if ( '' === $chips ) {
		$chips = '<span class="amu-tier-empty"></span>';
	}

	$note = '' !== $atts['note']
		? '<span class="amu-tier-note">' . esc_html( $atts['note'] ) . '</span>'
		: '';

	return '<div class="amu-tier-row" style="--tier:' . esc_attr( $color ) . '">'
		. '<div class="amu-tier-label"><span class="amu-tier-letter">' . esc_html( $label ) . '</span>' . $note . '</div>'
		. '<div class="amu-tier-items">' . $chips . '</div>'
		. '</div>';
} );
