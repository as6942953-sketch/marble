<?php
/**
 * The template for displaying the front page
 *
 * @package Marble_Elementor_Theme
 * @since 1.0
 */

get_header();
?>

<div id="Content" role="main">
	<div class="content_wrapper clearfix">
		<div class="sections_group">
			<div class="entry-content" itemprop="mainContentOfPage">
				
				<?php
				// Get the front page ID
				$front_page_id = get_option( 'page_on_front' );
				
				if ( $front_page_id ) {
					// Get the front page content
					$front_page = get_post( $front_page_id );
					
					if ( $front_page ) {
						// Check if page is built with Elementor
						if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( $front_page_id ) ) {
							// Let Elementor render the content
							echo apply_filters( 'the_content', $front_page->post_content );
						} else {
							// Try to load home.html content
							$home_html_path = get_template_directory() . '/elementor-pages/home.html';
							if ( file_exists( $home_html_path ) ) {
								// Load and display the extracted home page HTML content
								$home_content = file_get_contents( $home_html_path );
								
								if ( false !== $home_content ) {
									// Convert paths in HTML content
									$home_content = marble_elementor_theme_convert_html_paths( $home_content );
									// Output the content
									echo $home_content;
								} else {
									// Fallback if file read fails
									echo apply_filters( 'the_content', $front_page->post_content );
								}
							} else {
								// Standard WordPress content as fallback
								?>
								<article id="post-<?php echo esc_attr( $front_page_id ); ?>" <?php post_class(); ?>>
									<div class="entry-content">
										<?php echo apply_filters( 'the_content', $front_page->post_content ); ?>
									</div>
								</article>
								<?php
							}
						}
					}
				} else {
					// Fallback: try to load home.html
					$home_html_path = get_template_directory() . '/elementor-pages/home.html';
					if ( file_exists( $home_html_path ) ) {
						$home_content = file_get_contents( $home_html_path );
						
						if ( false !== $home_content ) {
							// Convert paths in HTML content
							$home_content = marble_elementor_theme_convert_html_paths( $home_content );
							echo $home_content;
						} else {
							// Show recent posts if file read fails
							if ( have_posts() ) {
								while ( have_posts() ) {
									the_post();
									the_content();
								}
							}
						}
					} else {
						// Show recent posts
						if ( have_posts() ) {
							while ( have_posts() ) {
								the_post();
								the_content();
							}
						}
					}
				}
				?>
				
			</div>
		</div>
	</div>
</div>

<?php
get_footer();

