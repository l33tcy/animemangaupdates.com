<?php
/**
 * Sidebar: trending, search, sections, and a community callout. Falls back to
 * these defaults when no widgets are configured.
 *
 * @package amu
 */
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'amu' ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php else : ?>

		<section class="widget">
			<h3 class="widget-title"><?php esc_html_e( 'Search', 'amu' ); ?></h3>
			<?php get_search_form(); ?>
		</section>

		<?php
		$trending = new WP_Query( array(
			'posts_per_page'      => 5,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'comment_count',
			'no_found_rows'       => true,
		) );
		if ( $trending->have_posts() ) :
			?>
			<section class="widget">
				<h3 class="widget-title"><?php esc_html_e( 'Trending', 'amu' ); ?></h3>
				<ol class="trending">
					<?php
					$i = 0;
					while ( $trending->have_posts() ) :
						$trending->the_post();
						$i++;
						$cats = get_the_category();
						?>
						<li>
							<span class="rank" aria-hidden="true"><?php echo esc_html( $i ); ?></span>
							<span>
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								<span class="t-meta"><?php echo esc_html( ! empty( $cats ) ? $cats[0]->name : get_the_date() ); ?></span>
							</span>
						</li>
					<?php endwhile; ?>
				</ol>
			</section>
			<?php
			wp_reset_postdata();
		endif;
		?>

		<section class="widget">
			<h3 class="widget-title"><?php esc_html_e( 'Sections', 'amu' ); ?></h3>
			<ul>
				<?php wp_list_categories( array( 'title_li' => '', 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'show_count' => true ) ); ?>
			</ul>
		</section>

		<section class="widget callout">
			<h3><?php esc_html_e( 'Never miss a drop', 'amu' ); ?></h3>
			<p><?php esc_html_e( 'Seasonal premieres, chapter releases and industry news — straight to your feed.', 'amu' ); ?></p>
			<a class="btn" href="<?php echo esc_url( home_url( '/feed/' ) ); ?>"><?php esc_html_e( 'Subscribe RSS', 'amu' ); ?></a>
		</section>

	<?php endif; ?>
</aside>
