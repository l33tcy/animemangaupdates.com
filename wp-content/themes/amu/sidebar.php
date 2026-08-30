<?php
/**
 * Sidebar. Falls back to a search box + recent posts when no widgets are set.
 *
 * @package amu
 */
?>
<aside class="sidebar" aria-label="<?php esc_attr_e( 'Sidebar', 'amu' ); ?>">
	<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
		<?php dynamic_sidebar( 'sidebar-1' ); ?>
	<?php else : ?>
		<div class="widget">
			<h3 class="widget-title"><?php esc_html_e( 'Search', 'amu' ); ?></h3>
			<?php get_search_form(); ?>
		</div>
		<div class="widget">
			<h3 class="widget-title"><?php esc_html_e( 'Recent Updates', 'amu' ); ?></h3>
			<ul>
				<?php
				foreach ( get_posts( array( 'numberposts' => 6 ) ) as $post ) :
					printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $post ) ), esc_html( get_the_title( $post ) ) );
				endforeach;
				wp_reset_postdata();
				?>
			</ul>
		</div>
	<?php endif; ?>
</aside>
