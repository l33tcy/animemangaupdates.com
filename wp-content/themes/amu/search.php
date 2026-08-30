<?php
/**
 * Search results.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="layout">
		<div class="content-area">
			<div class="section-head">
				<h2><?php esc_html_e( 'Results', 'amu' ); ?> <em>“<?php echo esc_html( get_search_query() ); ?>”</em></h2>
			</div>

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
				<div class="notice">
					<p><?php esc_html_e( 'No matches. Try another title or character.', 'amu' ); ?></p>
					<div style="max-width:420px;margin:22px auto 0"><?php get_search_form(); ?></div>
				</div>
			<?php endif; ?>
		</div>

		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
