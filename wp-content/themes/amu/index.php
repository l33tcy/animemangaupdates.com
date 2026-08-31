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

		<?php // ---- MASTHEAD: single H1 stating the page topic for crawlers ---- ?>
		<header class="home-masthead">
			<h1 class="home-h1"><span class="hm-mark" aria-hidden="true"></span><?php echo esc_html( get_bloginfo( 'name' ) ); ?>: <?php esc_html_e( 'Anime & Manga News, Releases & Guides', 'amu' ); ?></h1>
		</header>

		<?php // ---- HERO: big lead + two secondary; these stories are registered so the cards below never repeat them ---- ?>
		<section class="mhero" aria-label="<?php esc_attr_e( 'Top stories', 'amu' ); ?>">
			<?php
			$i = 0;
			while ( have_posts() && $i < 3 ) :
				the_post();
				amu_mark_seen( get_the_ID() );
				amu_overlay_card( get_post(), 0 === $i ? 'amu_hero' : 'amu_card', 0 === $i ? 'mh-lead' : 'mh-side' );
				$i++;
			endwhile;
			?>
		</section>

		<?php // ---- CATEGORY CARDS: one <section> per category (deduped); each "See all" -> its paginated archive ---- ?>
		<?php amu_category_blocks(); ?>

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
