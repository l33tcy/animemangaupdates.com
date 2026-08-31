<?php
/**
 * Template Name: Anime Calendar
 *
 * @package amu
 */

get_header();
?>
<div class="wrap cal-wrap">
	<div class="mhead">
		<h2><?php echo esc_html( get_the_title() ?: __( 'Anime Calendar', 'amu' ) ); ?></h2>
	</div>
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
				?>
				<div class="cal-intro"><?php the_content(); ?></div>
				<?php
			endif;
		endwhile;
	endif;
	amu_render_calendar();
	?>
</div>
<?php
get_footer();
