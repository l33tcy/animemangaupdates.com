<?php
/**
 * Home / blog index — the news feed.
 *
 * @package amu
 */

get_header();
?>
<div class="layout">
	<div class="content-area">
		<h1 class="section-title"><?php esc_html_e( 'Latest News', 'amu' ); ?></h1>

		<?php if ( have_posts() ) : ?>
			<div class="post-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card' );
				endwhile;
				?>
			</div>

			<div class="pagination">
				<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '&larr;', 'next_text' => '&rarr;' ) ) ); ?>
			</div>
		<?php else : ?>
			<div class="notice"><p><?php esc_html_e( 'No posts yet. Check back soon.', 'amu' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
