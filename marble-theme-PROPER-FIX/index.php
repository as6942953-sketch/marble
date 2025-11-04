<?php
/**
 * The main template file
 *
 * @package Marble_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			
			// Check if content is built with Elementor
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
				the_content();
			} else {
				// Display raw HTML content with all styling preserved
				echo wp_kses_post( get_the_content() );
			}
		}
	} else {
		?>
		<p><?php esc_html_e( 'Nothing found.', 'marble-elementor-theme' ); ?></p>
		<?php
	}
	?>
</div>

<?php
get_footer();
