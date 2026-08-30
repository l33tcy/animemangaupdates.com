<?php
/**
 * Search results — full-width card grid, no sidebar.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="feature-head">
		<div class="intro">
			<h1><?php esc_html_e( 'Results', 'amu' ); ?></h1>
			<p>
				<?php
				/* translators: %s: search query */
				printf( esc_html__( 'for “%s”', 'amu' ), esc_html( get_search_query() ) );
				?>
			</p>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
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
			<div style="max-width:460px;margin:22px auto 0"><?php get_search_form(); ?></div>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
