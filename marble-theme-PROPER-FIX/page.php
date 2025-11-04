<?php
/**
 * The template for displaying all pages
 *
 * @package Marble_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		
		// Check if page is built with Elementor
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
			// Let Elementor render the content
			the_content();
		} else {
			// Display the stored HTML content directly
			// This preserves all Elementor markup, CSS classes, and inline styles
			echo wp_kses_post( get_the_content() );
		}
	}
	?>
</div>

<?php
get_footer();
