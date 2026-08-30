<?php
/**
 * Home / blog index. Front page: hero block (lead + top stories) → Latest grid →
 * per-category rails. Paged/blog views: a simple grid.
 *
 * @package amu
 */

get_header();
$is_front = ( is_home() || is_front_page() ) && ! is_paged();
?>
<div class="wrap">

	<?php if ( $is_front && have_posts() ) : ?>

		<nav class="cat-pills" aria-label="<?php esc_attr_e( 'Browse categories', 'amu' ); ?>">
			<?php foreach ( amu_top_categories( 6 ) as $cat ) : ?>
				<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>" style="--pill:<?php echo esc_attr( amu_term_color( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php // ---- HERO: first post as lead, next four as the top-stories rail ---- ?>
		<section class="hero">
			<?php the_post(); ?>
			<article class="hero-lead">
				<a class="hero-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
					<?php the_post_thumbnail( 'amu_hero', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); ?>
					<span class="hero-shade"></span>
				</a>
				<div class="hero-body">
					<?php amu_cat_tag(); ?>
					<h1 class="hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<p class="hero-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
					<div class="card-meta"><?php amu_card_meta(); ?></div>
				</div>
			</article>

			<?php if ( have_posts() ) : ?>
				<div class="hero-side">
					<h2 class="hero-side-head"><?php esc_html_e( 'Top stories', 'amu' ); ?></h2>
					<?php
					$hs = 0;
					while ( have_posts() && $hs < 4 ) :
						the_post();
						?>
						<article class="hero-item">
							<a class="hero-item-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
								<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'amu_card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) ); } else { echo '<span class="ph"></span>'; } ?>
							</a>
							<div class="hero-item-body">
								<?php amu_cat_tag(); ?>
								<h3 class="hero-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<div class="card-meta"><?php amu_card_meta(); ?></div>
							</div>
						</article>
						<?php
						$hs++;
					endwhile;
					?>
				</div>
			<?php endif; ?>
		</section>

		<?php // ---- LATEST: everything remaining in the main query ---- ?>
		<?php if ( have_posts() ) : ?>
			<div class="section-head"><span class="sec-dot"></span><h2><?php esc_html_e( 'Latest', 'amu' ); ?></h2><span class="bar"></span></div>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content', 'card', array( 'lead' => false ) );
				endwhile;
				?>
			</div>
		<?php endif; ?>

		<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
			<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
		</nav>

		<?php // ---- CATEGORY RAILS ---- ?>
		<?php foreach ( amu_home_sections() as $amu_slug ) { amu_home_section( $amu_slug ); } ?>

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
