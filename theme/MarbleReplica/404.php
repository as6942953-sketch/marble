<?php
/**
 * 404 template
 */

get_header();
?>

<section class="marble-replica-404">
	<div class="marble-replica-404-inner">
		<h1><?php esc_html_e( 'Page not found', 'marble-replica' ); ?></h1>
		<p><?php esc_html_e( "We couldn't locate the page you requested. Please check the navigation menu for available pages.", 'marble-replica' ); ?></p>
		<p><a class="marble-replica-back-home" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'marble-replica' ); ?></a></p>
	</div>
</section>

<?php
get_footer();
