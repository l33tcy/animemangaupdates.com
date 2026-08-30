<?php
/**
 * Page template, full width readable column.
 *
 * @package amu
 */

get_header();
?>
<div class="wrap">
	<div class="single-wrap">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<?php if ( function_exists( 'yoast_breadcrumb' ) ) { yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="Breadcrumb">', '</nav>' ); } ?>
				<h1 class="article-title"><?php the_title(); ?></h1>
				<div class="page-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	</div>
</div>
<?php
get_footer();
