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

		<?php // ---- HERO: big lead + two secondary, titles overlaid ---- ?>
		<section class="mhero">
			<?php
			$i = 0;
			while ( have_posts() && $i < 3 ) :
				the_post();
				amu_overlay_card( get_post(), 0 === $i ? 'amu_hero' : 'amu_card', 0 === $i ? 'mh-lead' : 'mh-side' );
				$i++;
			endwhile;
			?>
		</section>

		<?php // ---- LATEST: clean card grid of everything remaining ---- ?>
		<?php if ( have_posts() ) : ?>
			<div class="mhead">
				<h2><?php esc_html_e( 'Latest', 'amu' ); ?></h2>
				<a class="mhead-all" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ); ?>"><?php esc_html_e( 'See all', 'amu' ); ?> &rarr;</a>
			</div>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card', array( 'lead' => false ) );
				endwhile;
				?>
			</div>
		<?php endif; ?>

		<?php // ---- CATEGORY BLOCKS: every category, each links "See all" to its paginated archive ---- ?>
		<section class="home-cats">
			<div class="mhead"><h2><?php esc_html_e( 'Browse by category', 'amu' ); ?></h2></div>
			<?php amu_category_blocks(); ?>
		</section>

		<?php amu_most_read_section(); ?>

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
