<?php
/**
 * Theme header
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
}

if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) :
	// Elementor handled the header output.
else :
	?>
	<header class="marble-replica-fallback-header">
		<div class="marble-replica-header-inner">
			<div class="marble-replica-branding">
				<a class="marble-replica-site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
				<p class="marble-replica-tagline"><?php bloginfo( 'description' ); ?></p>
			</div>
			<nav class="marble-replica-nav" aria-label="<?php esc_attr_e( 'Primary Navigation', 'marble-replica' ); ?>">
				<?php
				wp_nav_menu(
					[
						'theme_location' => 'primary',
						'fallback_cb'    => 'wp_page_menu',
						'menu_class'     => 'marble-replica-menu',
					]
				);
				?>
			</nav>
		</div>
	</header>
	<?php
endif;
?>

<main id="site-content" class="site-content">
