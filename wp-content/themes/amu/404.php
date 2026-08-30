<?php
/**
 * 404 template.
 *
 * @package amu
 */

get_header();
?>
<div class="notice">
	<h1>404</h1>
	<p><?php esc_html_e( 'This page wandered off into another dimension.', 'amu' ); ?></p>
	<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( '&larr; Back to the homepage', 'amu' ); ?></a></p>
	<div style="max-width:420px;margin:20px auto 0"><?php get_search_form(); ?></div>
</div>
<?php
get_footer();
