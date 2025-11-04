<?php
/**
 * Single Post Template
 * 
 * @package MarbleClinic_Elementor_Theme
 */

get_header();
?>

<div class="entry-content" itemprop="mainContentOfPage">
	<?php
	while ( have_posts() ) {
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header">
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-meta">
					<span class="posted-on"><?php echo get_the_date(); ?></span>
				</div>
			</header>
			<div class="entry-content-inner">
				<?php
				// Check if built with Elementor
				if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->db->is_built_with_elementor( get_the_ID() ) ) {
					echo \Elementor\Plugin::$instance->frontend->get_builder_content( get_the_ID(), true );
				} else {
					the_content();
				}
				?>
			</div>
		</article>
		<?php
	}
	?>
</div>

<?php
get_footer();
