<?php
/**
 * Theme footer
 */
?>
	</main><!-- #site-content -->

<?php
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'footer' ) ) :
	// Elementor handled footer rendering.
else :
	?>
	<footer class="marble-replica-fallback-footer">
		<div class="marble-replica-footer-inner">
			<p>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		</div>
	</footer>
	<?php
endif;

wp_footer();
?>
</body>
</html>
