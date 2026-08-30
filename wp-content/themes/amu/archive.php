<?php
/**
 * Archive template — categories, tags, author, date.
 *
 * @package amu
 */

get_header();
?>
<div class="layout">
	<div class="content-area">
		<h1 class="section-title"><?php the_archive_title(); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="archive-description"><?php the_archive_description(); ?></div>
		<?php endif; ?>

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
			<div class="notice"><p><?php esc_html_e( 'Nothing here yet.', 'amu' ); ?></p></div>
		<?php endif; ?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
