<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<?php
	// Check if Elementor header template exists
	if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
		// Elementor will handle the header
	} else {
		// Fallback header
		?>
		<header id="Top_bar" class="site-header">
			<div class="container">
				<div class="column one">
					<div class="top_bar_left clearfix">
						<div class="logo">
							<?php
							if ( has_custom_logo() ) {
								the_custom_logo();
							} else {
								?>
								<a id="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
									<h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
								</a>
								<?php
							}
							?>
						</div>
						<div class="menu_wrapper">
							<nav id="menu" class="menu-main-container">
								<?php
								wp_nav_menu( array(
									'theme_location' => 'primary',
									'menu_id'        => 'menu-main-menu',
									'menu_class'     => 'menu menu-main',
									'container'      => false,
									'fallback_cb'    => false,
								) );
								?>
							</nav>
						</div>
					</div>
				</div>
			</div>
		</header>
		<?php
	}
	?>
	
	<div id="Content">
		<div class="content_wrapper clearfix">
			<div class="sections_group">
