<?php
/**
 * Single Post Template
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
			</header>
			<div class="entry-content-inner">
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	}
	?>
</div>

<?php
get_footer();
