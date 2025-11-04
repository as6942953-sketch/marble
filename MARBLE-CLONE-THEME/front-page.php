<?php
/**
 * Front Page Template
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	// Get homepage content
	$front_page_id = get_option( 'page_on_front' );
	
	if ( $front_page_id ) {
		$front_page = get_post( $front_page_id );
		
		if ( $front_page ) {
			setup_postdata( $front_page );
			
			// Check if Elementor
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $front_page_id ) ) {
				echo apply_filters( 'the_content', $front_page->post_content );
			} else {
				echo wp_kses_post( $front_page->post_content );
			}
			
			wp_reset_postdata();
		}
	} else {
		// Fallback
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
