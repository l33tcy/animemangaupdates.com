<?php
/**
 * Archive, full-width card grid, no sidebar.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="feature-head">
		<div class="intro">
			<h1><?php the_archive_title( '', '' ); ?></h1>
			<?php if ( get_the_archive_description() ) : ?><p><?php echo wp_kses_post( get_the_archive_description() ); ?></p><?php endif; ?>
		</div>
	</div>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>
		<nav class="pagination" aria-label="<?php esc_attr_e( 'Posts', 'amu' ); ?>">
			<?php echo wp_kses_post( paginate_links( array( 'mid_size' => 1, 'prev_text' => '←', 'next_text' => '→' ) ) ); ?>
		</nav>
	<?php else : ?>
		<div class="notice"><p><?php esc_html_e( 'Nothing filed here yet.', 'amu' ); ?></p></div>
	<?php endif; ?>
</div>
<?php
get_footer();
