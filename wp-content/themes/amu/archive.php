<?php
/**
 * Archive — category, tag, author, date.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="layout">
		<div class="content-area">
			<div class="section-head">
				<h2><?php the_archive_title( '', '' ); ?></h2>
			</div>
			<?php amu_breadcrumbs(); ?>
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
				<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
					<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
				</nav>
			<?php else : ?>
				<div class="notice"><p><?php esc_html_e( 'Nothing filed here yet.', 'amu' ); ?></p></div>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
