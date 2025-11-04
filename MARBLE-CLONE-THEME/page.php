<?php
/**
 * Template for displaying single pages
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		
		// Check if page is built with Elementor
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
			// Let Elementor render
			the_content();
		} else {
			// Display with all HTML/CSS preserved
			echo wp_kses_post( get_the_content() );
		}
	}
	?>
</div>

<?php
get_footer();
