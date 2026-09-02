<?php
/**
 * Header: magenta navbar (WP-customizable Primary menu), controls, mobile drawer,
 * and the full-screen search overlay.
 *
 * @package amu
 */

$amu_menu_args = array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1, 'fallback_cb' => 'amu_menu_fallback', 'items_wrap' => '<ul>%3$s</ul>' );

if ( ! function_exists( 'amu_menu_fallback' ) ) {
	/** Default menu when none is assigned: top categories + Home. */
	function amu_menu_fallback() {
		echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'amu' ) . '</a></li>';
		wp_list_categories( array( 'title_li' => '', 'number' => 8, 'orderby' => 'count', 'order' => 'DESC' ) );
		echo '</ul>';
	}
}
?>
<!doctype html>
<html <?php language_attributes(); ?> data-theme="light">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>try{var t=localStorage.getItem('amu-theme');if(t)document.documentElement.setAttribute('data-theme',t);}catch(e){}</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'amu' ); ?></a>

<header class="site-header">
	<div class="header-top">
		<div class="wrap header-top-inner">
			<span class="ht-left" aria-hidden="true"></span>

			<?php
			// Two logo versions swap by theme (CSS): dark-letter wordmark for the light
			// header, light-letter wordmark for the dark header.
			$amu_light_logo = has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : '';
			if ( $amu_light_logo ) {
				printf(
					'<a class="custom-logo-link" href="%1$s" rel="home" aria-label="%2$s">' .
						'<img class="custom-logo amu-logo-light" src="%3$s" alt="%2$s" decoding="async">' .
						'<img class="custom-logo amu-logo-dark" src="%4$s" alt="" aria-hidden="true" decoding="async">' .
					'</a>',
					esc_url( home_url( '/' ) ),
					esc_attr( get_bloginfo( 'name' ) ),
					esc_url( $amu_light_logo ),
					esc_url( get_template_directory_uri() . '/assets/logo-dark.png' )
				);
			} else {
				printf(
					'<a class="brand" href="%s" rel="home">%s</a>',
					esc_url( home_url( '/' ) ),
					wp_kses_post( str_ireplace( 'Manga', '<b>Manga</b>', esc_html( get_bloginfo( 'name' ) ) ) )
				);
			}
			?>

			<div class="ht-right nav-actions">
				<button class="icon-btn js-search-open" aria-label="<?php esc_attr_e( 'Search', 'amu' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
				</button>
				<?php amu_theme_switch(); ?>
				<button class="icon-btn nav-burger js-drawer-open" aria-label="<?php esc_attr_e( 'Menu', 'amu' ); ?>">
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
				</button>
			</div>
		</div>
	</div>

	<div class="header-bottom">
		<nav class="wrap primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'amu' ); ?>">
			<?php wp_nav_menu( $amu_menu_args ); ?>
		</nav>
	</div>
</header>

<!-- Mobile drawer -->
<div class="mobile-drawer" id="amuDrawer" aria-hidden="true">
	<div class="drawer-inner">
		<div class="drawer-head">
			<a class="brand drawer-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php
				if ( has_custom_logo() ) {
					echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'medium', false, array( 'class' => 'drawer-logo', 'alt' => get_bloginfo( 'name' ) ) );
				} else {
					echo wp_kses_post( str_ireplace( 'Manga', '<b>Manga</b>', esc_html( get_bloginfo( 'name' ) ) ) );
				}
				?>
			</a>
			<button class="drawer-close js-drawer-close" aria-label="<?php esc_attr_e( 'Close menu', 'amu' ); ?>">&times;</button>
		</div>
		<nav class="drawer-nav" aria-label="<?php esc_attr_e( 'Mobile', 'amu' ); ?>"><?php wp_nav_menu( $amu_menu_args ); ?></nav>
	</div>
</div>

<!-- Search overlay -->
<div class="search-overlay" id="amuSearch" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Search', 'amu' ); ?>">
	<div class="search-inner">
		<form class="search-box" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="field">
				<label class="screen-reader-text" for="amuSearchInput"><?php esc_html_e( 'Search', 'amu' ); ?></label>
				<input type="search" id="amuSearchInput" name="s" placeholder="<?php esc_attr_e( 'Search…', 'amu' ); ?>" autocomplete="off" value="<?php echo esc_attr( get_search_query() ); ?>">
			</span>
			<button type="submit" class="go" aria-label="<?php esc_attr_e( 'Go', 'amu' ); ?>"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg></button>
			<button type="button" class="close js-search-close" aria-label="<?php esc_attr_e( 'Close', 'amu' ); ?>">&times;</button>
		</form>

		<div class="search-suggest" id="amuSuggest" role="listbox" aria-label="<?php esc_attr_e( 'Suggestions', 'amu' ); ?>"></div>

		<div class="search-cols">
			<div>
				<p class="col-h"><?php esc_html_e( 'Trending searches', 'amu' ); ?></p>
				<ul class="trend">
					<?php foreach ( amu_trending_terms( 8 ) as $term ) : ?>
						<li><a href="<?php echo esc_url( home_url( '/?s=' . rawurlencode( $term->name ) ) ); ?>"><span class="sq" style="--sq:<?php echo esc_attr( amu_term_color( $term ) ); ?>"></span><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div>
				<p class="col-h"><?php esc_html_e( 'History', 'amu' ); ?></p>
				<ul class="history" id="amuHistory"><li class="muted"><?php esc_html_e( 'No search history', 'amu' ); ?></li></ul>
			</div>
		</div>
	</div>
</div>

<main id="main" class="site-main">
