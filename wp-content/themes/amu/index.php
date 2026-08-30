<?php
/**
 * Home / blog index — the news front page.
 *
 * @package amu
 */

get_header();

// Hero only on the first page of the main feed.
$featured_id = 0;
if ( ( is_home() || is_front_page() ) && ! is_paged() ) {
	$hero = amu_featured_query();
	if ( $hero->have_posts() ) {
		$hero->the_post();
		$featured_id = get_the_ID();
		echo '<div class="wrap">';
		get_template_part( 'template-parts/hero' );
		echo '</div>';
		wp_reset_postdata();
	}
}
?>
<div class="wrap">
	<div class="layout">
		<div class="content-area">
			<div class="section-head">
				<h2><?php echo is_paged() ? esc_html__( 'Latest', 'amu' ) : esc_html__( 'Fresh', 'amu' ); ?> <em><?php esc_html_e( 'News', 'amu' ); ?></em></h2>
				<span class="more"><?php echo esc_html( date_i18n( 'D, M j' ) ); ?></span>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="post-grid">
					<?php
					while ( have_posts() ) :
						the_post();
						if ( get_the_ID() === $featured_id ) {
							continue; // already shown in the hero
						}
						get_template_part( 'template-parts/content', 'card' );
					endwhile;
					?>
				</div>

				<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
					<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
				</nav>
			<?php else : ?>
				<div class="notice"><p><?php esc_html_e( 'No posts yet — the first scoop is coming.', 'amu' ); ?></p></div>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
