<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
	<meta name="format-detection" content="telephone=no">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() . '/assets/images/2021/02/the-m.png' ); ?>" type="image/x-icon">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Check if Elementor header template exists
if ( function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( 'header' ) ) {
	return;
}
?>

<div id="Wrapper">
	<div id="Header_wrapper">
		<header id="Header">
			<div class="header_placeholder"></div>
			<div id="Top_bar">
				<div class="container">
					<div class="column one">
						<div class="top_bar_left clearfix">
							<div class="logo">
								<?php
								$logo_url = get_template_directory_uri() . '/assets/images/2021/02/the-m.png';
								?>
								<a id="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-height="60" data-padding="15">
									<img class="logo-main scale-with-grid" src="<?php echo esc_url( $logo_url ); ?>" data-height="73" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-no-retina="">
									<img class="logo-sticky scale-with-grid" src="<?php echo esc_url( $logo_url ); ?>" data-height="73" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-no-retina="">
									<img class="logo-mobile scale-with-grid" src="<?php echo esc_url( $logo_url ); ?>" data-height="73" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-no-retina="">
									<img class="logo-mobile-sticky scale-with-grid" src="<?php echo esc_url( $logo_url ); ?>" data-height="73" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" data-no-retina="">
								</a>
							</div>
							<div class="menu_wrapper">
								<nav id="menu">
									<?php
									wp_nav_menu( array(
										'theme_location' => 'primary',
										'menu_id'        => 'menu-main-menu',
										'menu_class'     => 'menu menu-main',
										'container'      => false,
										'fallback_cb'    => false,
									) );
									?>
									<a class="responsive-menu-toggle" href="#"><i class="icon-menu-fine"></i></a>
								</nav>
							</div>
						</div>
					</div>
				</div>
			</div>
		</header>
	</div>

	<div id="Content">
		<div class="content_wrapper clearfix">
			<div class="sections_group">
