			</div><!-- .sections_group -->
		</div><!-- .content_wrapper -->
	</div><!-- #Content -->
	
	<?php
	// Check if Elementor footer template exists
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		// Elementor will handle the footer
	} else {
		// Fallback footer
		?>
		<footer id="Footer" class="site-footer">
			<div class="footer_copy">
				<div class="container">
					<div class="column one">
						<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. All rights reserved.</p>
					</div>
				</div>
			</div>
		</footer>
		<?php
	}
	?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
