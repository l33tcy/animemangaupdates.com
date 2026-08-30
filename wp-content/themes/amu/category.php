<?php
/**
 * Category archive, dedicated template (separate from tags) for clean SEO/URL
 * structure. Full-width card grid, breadcrumbs, category description.
 *
 * @package amu
 */

get_header();
$term = get_queried_object();
?>
<div class="wrap">
	<?php if ( function_exists( 'yoast_breadcrumb' ) ) { yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' ); } ?>
	<div class="feature-head">
		<div class="intro">
			<h1><?php single_cat_title(); ?></h1>
			<p>
				<?php
				if ( category_description() ) {
					echo wp_kses_post( category_description() );
				} else {
					/* translators: %s: category name */
					printf( esc_html__( 'All the latest %s news, releases and updates.', 'amu' ), esc_html( single_cat_title( '', false ) ) );
				}
				?>
			</p>
		</div>
		<?php
		// Sibling / child categories as quick filters.
		$children = get_categories( array( 'parent' => $term->term_id, 'hide_empty' => true, 'number' => 6 ) );
		if ( ! empty( $children ) ) : ?>
			<nav class="filters" aria-label="<?php esc_attr_e( 'Subcategories', 'amu' ); ?>">
				<?php foreach ( $children as $c ) : ?>
					<a href="<?php echo esc_url( get_category_link( $c->term_id ) ); ?>"><?php echo esc_html( $c->name ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content', 'card' ); endwhile; ?>
		</div>
		<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
			<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
		</nav>
	<?php else : ?>
		<div class="notice"><p><?php esc_html_e( 'No posts in this category yet.', 'amu' ); ?></p></div>
	<?php endif; ?>
</div>
<?php
get_footer();
