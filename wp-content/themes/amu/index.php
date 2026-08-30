<?php
/**
 * Home / blog index, full-width "Featured" card grid, no sidebar.
 *
 * @package amu
 */

get_header();
$is_front = ( is_home() || is_front_page() ) && ! is_paged();
?>
<div class="wrap">

	<?php if ( $is_front ) : ?>
		<div class="feature-head">
			<div class="intro">
				<h1><?php esc_html_e( 'Featured', 'amu' ); ?></h1>
				<p><?php esc_html_e( 'The hottest anime &amp; manga news right now, releases, reviews and everything worth knowing.', 'amu' ); ?></p>
			</div>
			<nav class="filters" aria-label="<?php esc_attr_e( 'Categories', 'amu' ); ?>">
				<?php foreach ( amu_top_categories( 5 ) as $cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
				<?php endforeach; ?>
				<a class="all" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'See all', 'amu' ); ?> &rarr;</a>
			</nav>
		</div>
	<?php else : ?>
		<div class="section-head"><h2><?php esc_html_e( 'Latest', 'amu' ); ?></h2><span class="bar"></span></div>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php
			$i = 0;
			while ( have_posts() ) :
				the_post();
				$lead = ( $is_front && 0 === $i );   // first card on the front page spans wide
				get_template_part( 'template-parts/content', 'card', array( 'lead' => $lead ) );
				$i++;
			endwhile;
			?>
		</div>

		<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
			<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
		</nav>
	<?php else : ?>
		<div class="notice"><p><?php esc_html_e( 'No posts yet, the first scoop is coming.', 'amu' ); ?></p></div>
	<?php endif; ?>

</div>
<?php
get_footer();
