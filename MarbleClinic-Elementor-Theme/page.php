<?php
/**
 * Page Template
 * 
 * @package MarbleClinic_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		
		// Check if built with Elementor
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
			echo \Elementor\Plugin::$instance->frontend->get_builder_content( get_the_ID(), true );
		} else {
			the_content();
		}
	}
	?>
</div>

<?php
get_footer();
