<?php
/**
 * Footer.
 *
 * @package amu
 */
?>
</main><!-- #main -->

<hr class="speedlines" aria-hidden="true">
<footer class="site-footer">
	<div class="wrap">
		<div class="footer-top">
			<div class="footer-brand">
				<?php echo wp_kses_post( str_ireplace( 'Manga', '<b>Manga</b>', esc_html( get_bloginfo( 'name' ) ) ) ); ?>
				<p><?php bloginfo( 'description' ); ?></p>
			</div>
			<div class="footer-cols">
				<div>
					<h4><?php esc_html_e( 'Sections', 'amu' ); ?></h4>
					<ul>
						<?php wp_list_categories( array( 'title_li' => '', 'number' => 6, 'orderby' => 'count', 'order' => 'DESC' ) ); ?>
					</ul>
				</div>
				<div>
					<h4><?php esc_html_e( 'More', 'amu' ); ?></h4>
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) );
					} else {
						echo '<ul><li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'amu' ) . '</a></li></ul>';
					}
					?>
				</div>
			</div>
		</div>
		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></span>
			<span><?php esc_html_e( 'Anime &amp; manga news, updates and releases', 'amu' ); ?></span>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
