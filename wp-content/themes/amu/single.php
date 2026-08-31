<?php
/**
 * Single article, full width, no sidebar. Yoast owns SEO/schema in wp_head().
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="single-wrap">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<header class="article-header">
					<?php if ( function_exists( 'yoast_breadcrumb' ) ) { yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' ); } ?>
					<?php amu_cat_tag(); ?>
					<h1 class="article-title"><?php the_title(); ?></h1>
					<div class="article-meta">
						<span><?php echo esc_html( get_the_author() ); ?></span>
						<span><?php echo esc_html( get_the_date() ); ?></span>
						<span><?php echo esc_html( amu_reading_time() ); ?> min read</span>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="article-figure"><?php the_post_thumbnail( 'amu_hero' ); ?></figure>
				<?php endif; ?>

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
			</article>

			<?php amu_post_nav(); ?>
			<?php amu_related_posts( 3 ); ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>
</div>
<?php
get_footer();
