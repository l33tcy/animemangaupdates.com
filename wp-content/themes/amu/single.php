<?php
/**
 * Single article: big title + byline, two-column body (article + Trending sidebar),
 * featured stories below. Yoast owns SEO/schema in wp_head().
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'post-single' ); ?>>

			<header class="post-hero">
				<?php if ( function_exists( 'yoast_breadcrumb' ) ) { yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' ); } ?>
				<h1 class="post-title"><?php the_title(); ?></h1>
				<p class="post-byline">
					<?php
					printf(
						/* translators: 1: author, 2: date, 3: reading time */
						wp_kses_post( __( 'Written by <b>%1$s</b> <span class="dot">&middot;</span> %2$s <span class="dot">&middot;</span> %3$s min read', 'amu' ) ),
						esc_html( get_the_author() ),
						esc_html( get_the_date() ),
						esc_html( amu_reading_time() )
					);
					?>
				</p>
			</header>

			<div class="post-layout">
				<main class="post-main">
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="post-figure"><?php the_post_thumbnail( 'amu_hero' ); ?></figure>
					<?php endif; ?>

					<?php amu_gsource_callout(); // Follow-on-Google CTA directly under the hero image ?>

					<div class="article-content">
						<?php
						the_content();
						wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'amu' ), 'after' => '</div>' ) );
						?>
					</div>

					<?php if ( has_tag() ) : ?>
						<div class="tags">
							<?php foreach ( get_the_tags() as $tag ) : ?>
								<a class="tag" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php amu_post_share(); ?>
					<?php amu_gnews_callout(); ?>
					<?php amu_author_box(); ?>
					<?php amu_post_nav(); ?>
				</main>

				<aside class="post-sidebar">
					<?php amu_trending_sidebar(); ?>
				</aside>
			</div>
		</article>

		<?php amu_related_posts( 3 ); ?>

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</div>
<?php
get_footer();
