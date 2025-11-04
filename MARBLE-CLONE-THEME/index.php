<?php
/**
 * Main template file
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div id="Content">
	<div class="content_wrapper clearfix">
		<div class="sections_group">
			<div class="entry-content" itemprop="mainContentOfPage">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						
						// Display content with all HTML/CSS preserved
						echo wp_kses_post( get_the_content() );
					}
				} else {
					echo '<p>No content found.</p>';
				}
				?>
			</div>
		</div>
	</div>
</div>

<?php
get_footer();
