<?php
/**
 * Hero / lead story. Expects the current post to be set up in the loop.
 *
 * @package amu
 */

$score = amu_field( 'amu_score' );
?>
<article class="hero reveal">
	<div class="hero-media">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( 'amu_hero', array( 'fetchpriority' => 'high', 'alt' => the_title_attribute( array( 'echo' => false ) ) ) );
		}
		?>
		<span class="screentone" aria-hidden="true"></span>
	</div>
	<div class="hero-body">
		<?php amu_kicker(); ?>
		<h2 class="hero-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="hero-meta">
			<span><?php echo esc_html( get_the_date() ); ?></span>
			<span><?php echo esc_html( amu_relative_date() ); ?></span>
			<?php if ( $score ) : ?><span>Score <b>★ <?php echo esc_html( number_format( (float) $score, 1 ) ); ?></b></span><?php endif; ?>
		</div>
	</div>
</article>
