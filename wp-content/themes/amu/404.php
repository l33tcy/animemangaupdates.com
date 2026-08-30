<?php
/**
 * 404.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="notice">
		<div class="big" aria-hidden="true">404</div>
		<h1><?php esc_html_e( 'This arc doesn’t exist', 'amu' ); ?></h1>
		<p><?php esc_html_e( 'The page wandered off into another dimension.', 'amu' ); ?></p>
		<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">&larr; <?php esc_html_e( 'Back to the front page', 'amu' ); ?></a></p>
		<div style="max-width:420px;margin:24px auto 0"><?php get_search_form(); ?></div>
	</div>
</div>
<?php
get_footer();
