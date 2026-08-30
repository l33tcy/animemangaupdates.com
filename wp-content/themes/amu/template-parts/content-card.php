<?php
/**
 * Post card. Pass array( 'lead' => true ) to render the wide lead variant.
 *
 * @package amu
 */

$amu_lead = ! empty( $args['lead'] );
$amu_size = $amu_lead ? 'amu_hero' : 'amu_card';
?>
<article <?php post_class( 'card reveal' . ( $amu_lead ? ' -lead' : '' ) ); ?>>
	<a class="card-thumb" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail( $amu_size, array( 'alt' => the_title_attribute( array( 'echo' => false ) ) ) );
		} else {
			echo '<span class="ph" aria-hidden="true"></span>';
		}
		?>
	</a>
	<?php amu_cat_tag(); ?>
	<h2 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<div class="card-meta"><?php amu_card_meta(); ?></div>
</article>
