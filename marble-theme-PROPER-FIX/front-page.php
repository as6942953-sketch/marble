<?php
/**
 * The template for displaying the front page
 *
 * @package Marble_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	// Get the front page
	$front_page_id = get_option( 'page_on_front' );
	
	if ( $front_page_id ) {
		$front_page = get_post( $front_page_id );
		
		if ( $front_page ) {
			// Setup post data
			setup_postdata( $front_page );
			
			// Check if page is built with Elementor
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $front_page_id ) ) {
				// Let Elementor render the content
				echo apply_filters( 'the_content', $front_page->post_content );
			} else {
				// Display the HTML content directly with all styling
				echo wp_kses_post( $front_page->post_content );
			}
			
			wp_reset_postdata();
		}
	} else {
		// Fallback: show home page content
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				echo wp_kses_post( get_the_content() );
			}
		}
	}
	?>
</div>

<?php
get_footer();
