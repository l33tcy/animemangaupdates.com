<?php
/**
 * Header.
 *
 * @package amu
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'amu' ); ?></a>

<header class="site-header">
	<div class="wrap header-inner">
		<a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php
			if ( has_custom_logo() ) {
				the_custom_logo();
			} else {
				// Wordmark: highlight "Manga" in ink-red, add a NEWS chip.
				echo wp_kses_post( str_ireplace( 'Manga', '<b>Manga</b>', esc_html( get_bloginfo( 'name' ) ) ) );
				echo '<span class="tag">News</span>';
			}
			?>
		</a>

		<button class="nav-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'amu' ); ?>" aria-expanded="false">&#9776;</button>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'amu' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 1 ) );
			} else {
				echo '<ul>';
				wp_list_categories( array( 'title_li' => '', 'number' => 6, 'orderby' => 'count', 'order' => 'DESC' ) );
				echo '</ul>';
			}
			?>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
