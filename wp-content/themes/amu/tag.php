<?php
/**
 * Tag archive, dedicated template (separate from categories) for clean SEO/URL
 * structure. Full-width card grid, breadcrumbs, tag description.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<?php if ( function_exists( 'yoast_breadcrumb' ) ) { yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' ); } ?>
	<div class="feature-head">
		<div class="intro">
			<h1>#<?php single_tag_title(); ?></h1>
			<p>
				<?php
				if ( tag_description() ) {
					echo wp_kses_post( tag_description() );
				} else {
					/* translators: %s: tag name */
					printf( esc_html__( 'Everything tagged “%s”, news, spoilers and release updates.', 'amu' ), esc_html( single_tag_title( '', false ) ) );
				}
				?>
			</p>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', 'card' ); endwhile; ?>
		</div>
		<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
			<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
		</nav>
	<?php else : ?>
		<div class="notice"><p><?php esc_html_e( 'Nothing tagged here yet.', 'amu' ); ?></p></div>
	<?php endif; ?>
</div>
<?php
get_footer();
