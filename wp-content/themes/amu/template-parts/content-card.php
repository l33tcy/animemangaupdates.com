<?php
/**
 * Post card for feeds and archives.
 *
 * @package amu
 */

$score = amu_field( 'amu_score' );
?>
<article <?php post_class( 'card reveal' ); ?>>
	<div class="card-thumb">
		<?php amu_kicker(); ?>
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail() ) {
				the_post_thumbnail( 'amu_card', array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) );
			} else {
				echo '<span class="ph" aria-hidden="true"></span>';
			}
			?>
		</a>
	</div>
	<div class="card-body">
		<h2 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="card-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
		<div class="card-meta">
			<span><?php echo esc_html( amu_relative_date() ); ?></span>
			<?php if ( $score ) : ?><span class="score">★ <?php echo esc_html( number_format( (float) $score, 1 ) ); ?></span><?php endif; ?>
		</div>
	</div>
</article>
