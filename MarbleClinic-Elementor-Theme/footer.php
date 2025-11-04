			</div><!-- .sections_group -->
		</div><!-- .content_wrapper -->
	</div><!-- #Content -->
	
	<?php
	// Check if Elementor footer template exists
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		wp_footer();
		return;
	}
	?>
	
	<footer id="Footer" class="clearfix">
		<div class="footer_copy">
			<div class="container">
				<div class="column one">
					<div class="copyright">
						&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.
					</div>
					<?php
					if ( has_nav_menu( 'footer' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'footer',
							'menu_class'     => 'footer-menu',
							'container'      => 'nav',
							'container_class' => 'footer-navigation',
							'depth'          => 1,
						) );
					}
					?>
				</div>
			</div>
		</div>
	</footer>
	
</div><!-- #Wrapper -->

<?php wp_footer(); ?>

</body>
</html>
