<?php
/**
 * Footer: Google News follow callout, brand + sections + company + legal columns,
 * contact email, and a back-to-top button.
 *
 * @package amu
 */

/** Output <li> links for the given page slug => fallback-label map (existing pages only). */
if ( ! function_exists( 'amu_page_links' ) ) {
	function amu_page_links( $map ) {
		foreach ( $map as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ?: $label ) );
			}
		}
	}
}
$amu_email = 'contact@animemangaupdates.com';
?>
</main><!-- #main -->

<footer class="site-footer">
	<div class="wrap">

		<div class="gnews-callout">
			<span class="gn-txt"><?php esc_html_e( 'Stay ahead of every release, follow us on Google News and add us as a favorite.', 'amu' ); ?></span>
			<a class="btn-gnews" href="<?php echo esc_url( amu_gnews_url() ); ?>" target="_blank" rel="noopener">
				<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 8h7M6 11h9M6 14h6M6 17h9" stroke="currentColor" stroke-width="1.6" fill="none"/></svg>
				<span class="label"><?php esc_html_e( 'Follow on Google News', 'amu' ); ?></span>
			</a>
		</div>

		<div class="footer-top">
			<div class="footer-brand">
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				if ( $logo_id ) {
					echo wp_get_attachment_image( $logo_id, 'medium', false, array( 'class' => 'footer-brand-img', 'alt' => get_bloginfo( 'name' ) ) );
				} else {
					echo '<div class="name">' . wp_kses_post( str_ireplace( 'Manga', '<b>Manga</b>', esc_html( get_bloginfo( 'name' ) ) ) ) . '</div>';
				}
				?>
				<p><?php bloginfo( 'description' ); ?></p>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Sections', 'amu' ); ?></h4>
				<ul><?php wp_list_categories( array( 'title_li' => '', 'number' => 6, 'orderby' => 'count', 'order' => 'DESC' ) ); ?></ul>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Company', 'amu' ); ?></h4>
				<ul>
					<?php amu_page_links( array( 'about-us' => 'About Us', 'contact-us' => 'Contact Us' ) ); ?>
					<li><a href="<?php echo esc_url( home_url( '/sitemap_index.xml' ) ); ?>"><?php esc_html_e( 'Sitemap', 'amu' ); ?></a></li>
				</ul>
			</div>

			<div class="footer-col">
				<h4><?php esc_html_e( 'Legal', 'amu' ); ?></h4>
				<ul>
					<?php amu_page_links( array(
						'privacy-policy'    => 'Privacy Policy',
						'terms-of-service'  => 'Terms of Service',
						'dmca'              => 'DMCA',
						'cookie-policy'     => 'Cookie Policy',
					) ); ?>
				</ul>
			</div>
		</div>

		<div class="footer-bottom">
			<span>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'amu' ); ?></span>
			<span><a href="mailto:<?php echo esc_attr( $amu_email ); ?>"><?php echo esc_html( $amu_email ); ?></a></span>
		</div>
	</div>
</footer>

<button class="back-to-top js-back-to-top" aria-label="<?php esc_attr_e( 'Back to top', 'amu' ); ?>">
	<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V6M5 12l7-7 7 7"/></svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
