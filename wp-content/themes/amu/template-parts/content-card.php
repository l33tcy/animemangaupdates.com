<?php
/**
 * Post card used in feeds and archives.
 *
 * @package amu
 */

$cats = get_the_category();
?>
<article <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card-thumb" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
			<?php the_post_thumbnail( 'amu_card' ); ?>
		</a>
	<?php endif; ?>
	<div class="card-body">
		<?php if ( ! empty( $cats ) ) : ?>
			<a class="card-cat" href="<?php echo esc_url( get_category_link( $cats[0]->term_id ) ); ?>"><?php echo esc_html( $cats[0]->name ); ?></a>
		<?php endif; ?>
		<h2 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="card-meta"><?php echo esc_html( amu_relative_date() ); ?></div>
		<p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
	</div>
</article>
