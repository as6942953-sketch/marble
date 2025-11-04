<?php
/**
 * Page Template
 * 
 * @package Marble_Clone
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		
		if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
			the_content();
		} else {
			echo wp_kses_post( get_the_content() );
		}
	}
	?>
</div>

<?php
get_footer();
