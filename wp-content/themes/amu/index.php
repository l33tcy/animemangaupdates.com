<?php
/**
 * Home / blog index. Front page: mosaic hero → category blocks → news columns
 * (Most read / Latest / Trending). Paged/blog views: a simple grid.
 *
 * @package amu
 */

get_header();
$is_front = ( is_home() || is_front_page() ) && ! is_paged();
?>
<div class="wrap">

	<?php if ( $is_front && have_posts() ) : ?>

		<nav class="cat-pills" aria-label="<?php esc_attr_e( 'Browse categories', 'amu' ); ?>">
			<?php foreach ( amu_top_categories( 8 ) as $cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" style="--pill:<?php echo esc_attr( amu_term_color( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php amu_hero_mosaic(); ?>

		<div class="section-head"><span class="sec-dot"></span><h2><?php esc_html_e( 'By category', 'amu' ); ?></h2><span class="bar"></span></div>
		<?php amu_category_blocks( 4 ); ?>

		<div class="section-head"><span class="sec-dot"></span><h2><?php esc_html_e( 'Newsroom', 'amu' ); ?></h2><span class="bar"></span></div>
		<?php amu_news_section(); ?>

	<?php else : ?>

		<div class="section-head"><span class="sec-dot"></span><h2><?php esc_html_e( 'Latest', 'amu' ); ?></h2><span class="bar"></span></div>

		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card', array( 'lead' => false ) );
				endwhile;
				?>
			</div>
			<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
				<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
			</nav>
		<?php else : ?>
			<div class="notice"><p><?php esc_html_e( 'No posts yet, the first scoop is coming.', 'amu' ); ?></p></div>
		<?php endif; ?>

	<?php endif; ?>

</div>
<?php
get_footer();
