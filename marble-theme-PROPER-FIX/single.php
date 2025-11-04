<?php
/**
 * The template for displaying all single posts
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
					
					// Check if post is built with Elementor
					if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
						// Let Elementor render the content
						the_content();
					} else {
						// Standard WordPress post content
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<header class="entry-header">
								<?php
								the_title( '<h1 class="entry-title">', '</h1>' );
								
								if ( 'post' === get_post_type() ) {
									?>
									<div class="entry-meta">
										<span class="posted-on">
											<?php echo get_the_date(); ?>
										</span>
										<span class="byline">
											<?php esc_html_e( 'by', 'marble-elementor-theme' ); ?> <?php the_author(); ?>
										</span>
									</div>
									<?php
								}
								?>
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
							
							<footer class="entry-footer">
								<?php
								$categories_list = get_the_category_list( esc_html__( ', ', 'marble-elementor-theme' ) );
								if ( $categories_list ) {
									printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'marble-elementor-theme' ) . '</span>', $categories_list );
								}
								
								$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'marble-elementor-theme' ) );
								if ( $tags_list ) {
									printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'marble-elementor-theme' ) . '</span>', $tags_list );
								}
								?>
							</footer>
						</article>
						
						<?php
						// Post navigation
						the_post_navigation( array(
							'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'marble-elementor-theme' ) . '</span> <span class="nav-title">%title</span>',
							'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'marble-elementor-theme' ) . '</span> <span class="nav-title">%title</span>',
						) );
						
						// Comments
						if ( comments_open() || get_comments_number() ) {
							comments_template();
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

