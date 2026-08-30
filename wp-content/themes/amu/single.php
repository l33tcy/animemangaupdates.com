<?php
/**
 * Single article. SEO title/description/canonical/OG and the JSON-LD schema
 * graph are emitted by Yoast in wp_head(); this template handles presentation
 * plus ACF article facts and breadcrumbs.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="layout">
		<div class="content-area">
			<?php
			while ( have_posts() ) :
				the_post();
				$series   = amu_field( 'amu_series' );
				$status   = amu_field( 'amu_status' );
				$episodes = amu_field( 'amu_episodes' );
				$score    = amu_field( 'amu_score' );
				$has_spec = $series || $status || $episodes || $score;
				?>
				<article <?php post_class(); ?>>
					<header class="article-hero">
						<?php amu_breadcrumbs(); ?>
						<?php amu_kicker(); ?>
						<h1 class="article-title"><?php the_title(); ?></h1>
						<div class="article-meta">
							<span><?php echo esc_html( get_the_author() ); ?></span>
							<span><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo esc_html( amu_relative_date() ); ?></span>
							<?php if ( $score ) : ?><span>Score <b>★ <?php echo esc_html( number_format( (float) $score, 1 ) ); ?></b></span><?php endif; ?>
						</div>
					</header>

					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="article-figure"><?php the_post_thumbnail( 'amu_hero' ); ?></figure>
					<?php endif; ?>

					<?php if ( $has_spec ) : ?>
						<div class="spec-box">
							<dl>
								<?php if ( $series ) : ?><div><dt><?php esc_html_e( 'Series', 'amu' ); ?></dt><dd><?php echo esc_html( $series ); ?></dd></div><?php endif; ?>
								<?php if ( $status ) : ?><div><dt><?php esc_html_e( 'Status', 'amu' ); ?></dt><dd><?php echo esc_html( $status ); ?></dd></div><?php endif; ?>
								<?php if ( $episodes ) : ?><div><dt><?php esc_html_e( 'Episodes / Ch.', 'amu' ); ?></dt><dd><?php echo esc_html( $episodes ); ?></dd></div><?php endif; ?>
								<?php if ( $score ) : ?><div><dt><?php esc_html_e( 'Score', 'amu' ); ?></dt><dd class="score">★ <?php echo esc_html( number_format( (float) $score, 1 ) ); ?></dd></div><?php endif; ?>
							</dl>
						</div>
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
								<a class="tag" href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>"><span>#<?php echo esc_html( $tag->name ); ?></span></a>
							<?php endforeach; ?>
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
</div>
<?php
get_footer();
