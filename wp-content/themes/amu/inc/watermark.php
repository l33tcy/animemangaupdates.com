<?php
/**
 * Brand watermark.
 *
 * Stamps the site logo + domain name into the bottom-right corner of every
 * uploaded image (including images sideloaded from a URL), and ships a
 * `wp amu watermark` CLI command to back-fill images already in the library.
 *
 * Assets (both optional, dropped into the theme's assets/ folder):
 *   - assets/watermark.png       transparent logo mark (composited as-is)
 *   - assets/watermark-font.ttf  font for the domain text (else GD built-in font)
 * The domain text is always drawn, so the watermark works even with no assets.
 *
 * @package amu
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Watermark text (the domain). Filterable. */
function amu_wm_text() {
	return apply_filters( 'amu_watermark_text', 'animemangaupdates.com' );
}

/**
 * Stamp the watermark onto an image file, in place.
 *
 * Runs on the full-size file; WordPress derives the thumbnail sizes from it
 * afterwards, so every size inherits the mark. Returns true on success.
 *
 * @param string $path Absolute path to the image file.
 * @return bool
 */
function amu_watermark_file( $path ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) || ! is_string( $path ) || ! file_exists( $path ) ) {
		return false;
	}
	$info = @getimagesize( $path );
	if ( ! $info ) {
		return false;
	}

	switch ( $info[2] ) {
		case IMAGETYPE_JPEG:
			$img = @imagecreatefromjpeg( $path );
			break;
		case IMAGETYPE_PNG:
			$img = @imagecreatefrompng( $path );
			break;
		case IMAGETYPE_WEBP:
			$img = function_exists( 'imagecreatefromwebp' ) ? @imagecreatefromwebp( $path ) : false;
			break;
		default:
			return false; // gif/other: skip.
	}
	if ( ! $img ) {
		return false;
	}

	$w = imagesx( $img );
	$h = imagesy( $img );
	if ( $w < 200 || $h < 150 ) { // ponytail: skip icons/avatars, not worth stamping.
		imagedestroy( $img );
		return false;
	}

	$margin  = max( 10, (int) round( $w * 0.02 ) );
	$x_right = $w - $margin;
	$y_bot   = $h - $margin;

	imagealphablending( $img, true );

	// --- logo ---
	$logo_path = apply_filters( 'amu_watermark_logo', get_template_directory() . '/assets/watermark.png' );
	$logo      = null;
	$logo_w    = 0;
	$logo_h    = 0;
	if ( file_exists( $logo_path ) && ( $li = @getimagesize( $logo_path ) ) && IMAGETYPE_PNG === $li[2] ) {
		$logo = @imagecreatefrompng( $logo_path );
		if ( $logo ) {
			imagealphablending( $logo, false );
			imagesavealpha( $logo, true );
			$target_w = min( (int) round( $w * 0.16 ), imagesx( $logo ) ); // 16% wide, never upscale.
			$scale    = $target_w / imagesx( $logo );
			$logo_w   = $target_w;
			$logo_h   = (int) round( imagesy( $logo ) * $scale );
		}
	}

	// --- domain text ---
	$text    = (string) amu_wm_text();
	$font    = apply_filters( 'amu_watermark_font', get_template_directory() . '/assets/watermark-font.ttf' );
	$use_ttf = '' !== $text && file_exists( $font ) && function_exists( 'imagettfbbox' );
	$size    = max( 10, (int) round( $w * 0.018 ) );
	$text_w  = 0;
	$text_h  = 0;
	if ( '' !== $text && $use_ttf ) {
		$bbox   = imagettfbbox( $size, 0, $font, $text );
		$text_w = abs( $bbox[2] - $bbox[0] );
		$text_h = abs( $bbox[7] - $bbox[1] );
	} elseif ( '' !== $text ) {
		$text_w = imagefontwidth( 5 ) * strlen( $text );
		$text_h = imagefontheight( 5 );
	}

	// Stack logo above the domain text, right-aligned into the corner.
	$gap     = ( $logo && '' !== $text ) ? max( 4, (int) round( $h * 0.008 ) ) : 0;
	$block_w = max( $logo_w, $text_w );
	$block_h = $logo_h + $gap + $text_h;
	$cur_y   = $y_bot - $block_h;

	// Dark plate behind the block for legibility on any background.
	$pad   = max( 6, (int) round( $w * 0.012 ) );
	$plate = imagecolorallocatealpha( $img, 0, 0, 0, 85 ); // ~66% opaque.
	imagefilledrectangle( $img, $x_right - $block_w - $pad, $cur_y - $pad, $x_right + $pad, $y_bot + $pad, $plate );

	if ( $logo ) {
		imagecopyresampled( $img, $logo, $x_right - $logo_w, $cur_y, 0, 0, $logo_w, $logo_h, imagesx( $logo ), imagesy( $logo ) );
		imagedestroy( $logo );
		$cur_y += $logo_h + $gap;
	}

	if ( '' !== $text ) {
		$white = imagecolorallocatealpha( $img, 255, 255, 255, 20 );
		if ( $use_ttf ) {
			imagettftext( $img, $size, 0, $x_right - $text_w, $cur_y + $text_h, $white, $font, $text );
		} else {
			imagestring( $img, 5, $x_right - $text_w, $cur_y, $text, $white );
		}
	}

	$ok = false;
	switch ( $info[2] ) {
		case IMAGETYPE_JPEG:
			$ok = imagejpeg( $img, $path, 90 );
			break;
		case IMAGETYPE_PNG:
			imagesavealpha( $img, true );
			$ok = imagepng( $img, $path );
			break;
		case IMAGETYPE_WEBP:
			$ok = imagewebp( $img, $path, 90 );
			break;
	}
	imagedestroy( $img );
	return $ok;
}

/**
 * Stamp on upload — fires before thumbnails are generated, so every size
 * inherits the mark. Hooked on both the normal upload path and the sideload
 * path (`wp media import`, `media_sideload_image`, featured-image imports).
 */
function amu_watermark_on_upload( $upload ) {
	if ( isset( $upload['type'], $upload['file'] ) && 0 === strpos( $upload['type'], 'image/' ) ) {
		if ( amu_watermark_file( $upload['file'] ) ) {
			$GLOBALS['amu_wm_stamped_paths'][ $upload['file'] ] = 1; // let add_attachment know it's done.
		}
	}
	return $upload;
}
add_filter( 'wp_handle_upload', 'amu_watermark_on_upload' );
add_filter( 'wp_handle_sideload', 'amu_watermark_on_upload' );

/** Brand assets (logo, footer logo, favicon) must never be watermarked. */
function amu_watermark_excluded( $id ) {
	$id = (int) $id;
	if ( ! $id ) {
		return false;
	}
	$brand = array_filter( array(
		(int) get_theme_mod( 'custom_logo' ),
		(int) get_theme_mod( 'amu_footer_logo' ),
		(int) get_option( 'site_icon' ),
	) );
	return in_array( $id, $brand, true );
}

/**
 * Reliable stamping on attachment insert.
 *
 * Normal uploads are already stamped by the wp_handle_upload/sideload filter
 * above, so we only record the flag. Attachments inserted directly (a script
 * calling wp_insert_attachment, which bypasses those filters) never got a mark,
 * so we stamp them here as a safety net and rebuild their sizes. This is why the
 * old blanket flag was wrong: it marked direct inserts as done without stamping.
 */
add_action( 'add_attachment', function ( $id ) {
	if ( ! wp_attachment_is_image( $id ) || amu_watermark_excluded( $id ) ) {
		return;
	}
	$file = get_attached_file( $id );
	if ( $file && ! empty( $GLOBALS['amu_wm_stamped_paths'][ $file ] ) ) {
		update_post_meta( $id, '_amu_watermarked', 1 ); // stamped by the upload filter already.
		return;
	}
	if ( $file && file_exists( $file ) && amu_watermark_file( $file ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$meta = wp_generate_attachment_metadata( $id, $file ); // rebuild thumbs from the stamped original.
		if ( $meta && ! is_wp_error( $meta ) ) {
			wp_update_attachment_metadata( $id, $meta );
		}
		update_post_meta( $id, '_amu_watermarked', 1 );
	}
} );

/**
 * `wp amu watermark` — back-fill existing library images.
 *   --force  re-stamp even images already marked (may double-stamp).
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'amu watermark', function ( $args, $assoc ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$force = isset( $assoc['force'] );
		$ids   = get_posts( array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/webp' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		$done = 0;
		$skip = 0;
		$fail = 0;
		foreach ( $ids as $id ) {
			if ( amu_watermark_excluded( $id ) ) { // never stamp logo / footer logo / favicon.
				$skip++;
				continue;
			}
			if ( ! $force && get_post_meta( $id, '_amu_watermarked', true ) ) {
				$skip++;
				continue;
			}
			$file = get_attached_file( $id );
			if ( ! $file || ! file_exists( $file ) || ! amu_watermark_file( $file ) ) {
				$fail++;
				continue;
			}
			$meta = wp_generate_attachment_metadata( $id, $file ); // re-crop thumbs from stamped original.
			if ( $meta && ! is_wp_error( $meta ) ) {
				wp_update_attachment_metadata( $id, $meta );
			}
			update_post_meta( $id, '_amu_watermarked', 1 );
			$done++;
			WP_CLI::log( "watermarked #{$id}" );
		}
		WP_CLI::success( "Watermarked {$done}, skipped {$skip}, failed {$fail}." );
	} );
}
