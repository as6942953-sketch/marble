<?php
/**
 * Main Template File
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			echo wp_kses_post( get_the_content() );
		}
	} else {
		echo '<p>No content found.</p>';
	}
	?>
</div>

<?php
get_footer();
