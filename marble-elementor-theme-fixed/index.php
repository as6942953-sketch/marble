<?php
/**
 * The main template file
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
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						
						// Check if page is built with Elementor
						if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
							// Let Elementor render the content
							the_content();
						} else {
							// Standard WordPress content
							?>
							<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
								<header class="entry-header">
									<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
								</header>
								
								<div class="entry-content">
									<?php the_content(); ?>
								</div>
							</article>
							<?php
						}
					}
				} else {
					?>
					<p><?php esc_html_e( 'Nothing found.', 'marble-elementor-theme' ); ?></p>
					<?php
				}
				?>
				
			</div>
		</div>
	</div>
</div>

<?php
get_footer();

