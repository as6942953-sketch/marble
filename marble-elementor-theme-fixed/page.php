<?php
/**
 * The template for displaying all pages
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
				while ( have_posts() ) {
					the_post();
					
					// Check if page is built with Elementor
					if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
						// Let Elementor render the content
						the_content();
					} else {
						// Try to load extracted HTML content based on page slug
						$page_slug = get_post_field( 'post_name', get_the_ID() );
						$html_filename = marble_elementor_theme_get_page_html_file( $page_slug );
						$elementor_html_path = get_template_directory() . '/elementor-pages/' . $html_filename . '.html';
						
						if ( file_exists( $elementor_html_path ) ) {
							// Load and display the extracted Elementor HTML content
							$elementor_content = file_get_contents( $elementor_html_path );
							
							if ( false !== $elementor_content ) {
								// Convert paths in HTML content
								$elementor_content = marble_elementor_theme_convert_html_paths( $elementor_content );
								// Output the content (already sanitized when page was created)
								echo $elementor_content;
							} else {
								// Fallback if file read fails
								the_content();
							}
						} else {
							// Standard WordPress page content as fallback
							?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<header class="entry-header">
									<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
								</header>
								
								<div class="entry-content">
									<?php
									the_content();
									
									wp_link_pages( array(
										'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'marble-elementor-theme' ),
										'after'  => '</div>',
									) );
									?>
								</div>
							</article>
							<?php
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

