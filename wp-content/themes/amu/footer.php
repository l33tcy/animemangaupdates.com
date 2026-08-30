<?php
/**
 * Footer template.
 *
 * @package amu
 */
?>
	</div><!-- .wrap -->
</main>

<footer class="site-footer">
	<div class="wrap footer-inner">
		<div>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></div>
		<div><?php esc_html_e( 'Anime &amp; manga news, updates and releases.', 'amu' ); ?></div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
