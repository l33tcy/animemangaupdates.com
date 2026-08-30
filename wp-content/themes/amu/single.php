<?php
/**
 * Single article template.
 *
 * @package amu
 */

get_header();
?>
<div class="layout">
	<div class="content-area">
		<?php
		while ( have_posts() ) :
			the_post();
			$cats = get_the_category();
			?>
			<article <?php post_class( 'single-article' ); ?>>
				<header class="article-header">
					<?php if ( ! empty( $cats ) ) : ?>
						<a class="article-cat" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
					<?php endif; ?>
					<h1 class="article-title"><?php the_title(); ?></h1>
					<div class="article-meta">
						<span><?php echo esc_html( get_the_author() ); ?></span>
						<span><?php echo esc_html( get_the_date() ); ?></span>
						<span><?php echo esc_html( amu_relative_date() ); ?></span>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="article-hero"><?php the_post_thumbnail( 'large' ); ?></figure>
				<?php endif; ?>

				<div class="article-content">
					<?php
					the_content();
					wp_link_pages( array( 'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'amu' ), 'after' => '</div>' ) );
					?>
				</div>

				<?php if ( has_tag() ) : ?>
					<div class="article-tags">
						<?php
						foreach ( get_the_tags() as $tag ) :
							printf( '<a class="tag-pill" href="%s">#%s</a>', esc_url( get_tag_link( $tag->term_id ) ), esc_html( $tag->name ) );
						endforeach;
						?>
					</div>
				<?php endif; ?>
			</article>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>
	</div>

	<?php get_sidebar(); ?>
</div>
<?php
get_footer();
