<?php
/**
 * Search form.
 *
 * @package amu
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="amu-s"><?php esc_html_e( 'Search for:', 'amu' ); ?></label>
	<input type="search" id="amu-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search news…', 'amu' ); ?>" />
	<button type="submit"><?php esc_html_e( 'Search', 'amu' ); ?></button>
</form>
