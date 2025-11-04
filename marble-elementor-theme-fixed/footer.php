	<?php
	// Check if Elementor footer template exists
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) {
		// Elementor will handle the footer
	} else {
		// Fallback footer if Elementor footer is not set
		?>
		<footer id="colophon" class="site-footer">
			<div class="site-info">
				<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
			</div>
		</footer>
		<?php
	}
	?>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>

