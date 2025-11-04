<?php
/**
 * Front Page Template
 * 
 * @package MarbleClinic_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	$front_page_id = get_option( 'page_on_front' );
	
	if ( $front_page_id ) {
		$front_page = get_post( $front_page_id );
		
		if ( $front_page ) {
			setup_postdata( $front_page );
			
			// Check if built with Elementor
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $front_page_id ) ) {
				echo \Elementor\Plugin::$instance->frontend->get_builder_content( $front_page_id, true );
			} else {
				echo apply_filters( 'the_content', $front_page->post_content );
			}
			
			wp_reset_postdata();
		}
	} else {
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				the_content();
			}
		}
	}
	?>
</div>

<?php
get_footer();
