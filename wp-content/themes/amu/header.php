<?php
/**
 * Header template.
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
				$name = get_bloginfo( 'name' );
				// Highlight the "Updates" part if present, else the whole name.
				echo wp_kses_post( str_ireplace( 'Updates', '<span>Updates</span>', esc_html( $name ) ) );
			}
			?>
		</a>

		<button class="nav-toggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'amu' ); ?>" aria-expanded="false">&#9776;</button>

		<nav class="main-nav" aria-label="<?php esc_attr_e( 'Primary', 'amu' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false ) );
			} else {
				amu_default_menu();
			}
			?>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
	<div class="wrap">
