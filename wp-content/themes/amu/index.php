<?php
/**
 * Home / blog index. Front page (minimal): overlay hero → Latest grid → Most read.
 * Paged/blog views: a simple grid.
 *
 * @package amu
 */

get_header();
$is_front = ( is_home() || is_front_page() ) && ! is_paged();
?>
<div class="wrap">

	<?php if ( $is_front && have_posts() ) : ?>

		<h1 class="sr-only"><?php esc_html_e( 'Anime &amp; Manga News', 'amu' ); ?></h1>

		<?php // ---- HERO SLIDER: auto-sliding trending posts ---- ?>
		<?php amu_hero_slider(); ?>

		<?php // ---- MAIN (Anime / Manga / Gaming editorial rows) + POPULAR sidebar ---- ?>
		<div class="home-grid">
			<div class="home-main">
				<?php
				amu_editorial_section( 'anime' );
				amu_editorial_section( 'manga' );
				amu_editorial_section( 'gaming' );
				?>
			</div>
			<?php amu_popular_aside(); ?>
		</div>

	<?php else : ?>

		<div class="mhead"><h2><?php esc_html_e( 'Latest', 'amu' ); ?></h2></div>

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
