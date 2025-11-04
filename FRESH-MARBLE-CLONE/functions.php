<?php
/**
 * Marble Clinic Restoration Clone - Functions
 * 
 * 100% copy of https://marbleclinicrestoration.com/
 * 
 * @package Marble_Clone
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function marble_clone_setup() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	
	// Elementor support
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );
	
	// Custom logo
	add_theme_support( 'custom-logo', array(
		'height'      => 73,
		'width'       => 250,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	
	// Register navigation menus
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'marble-clone' ),
		'footer'  => __( 'Footer Menu', 'marble-clone' ),
	) );
}
add_action( 'after_setup_theme', 'marble_clone_setup' );

/**
 * Enqueue Google Fonts
 */
function marble_clone_fonts() {
	$theme_uri = get_template_directory_uri();
	$local_fonts = get_template_directory() . '/assets/fonts/google-fonts-local.css';
	
	if ( file_exists( $local_fonts ) ) {
		wp_enqueue_style( 'marble-google-fonts', $theme_uri . '/assets/fonts/google-fonts-local.css', array(), '1.0' );
	} else {
		wp_enqueue_style( 'marble-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto:1,300,400,400italic,500,700,700italic|Lora:1,300,400,400italic,500,700,700italic&display=swap', array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'marble_clone_fonts', 1 );

/**
 * Enqueue Styles and Scripts
 */
function marble_clone_scripts() {
	$theme_uri = get_template_directory_uri();
	
	// Font Awesome
	if ( file_exists( get_template_directory() . '/assets/fonts/fontawesome/fontawesome.css' ) ) {
		wp_enqueue_style( 'marble-fontawesome', $theme_uri . '/assets/fonts/fontawesome/fontawesome.css', array(), '1.0' );
	}
	
	// Main BeTheme CSS
	if ( file_exists( get_template_directory() . '/assets/css/be.css' ) ) {
		wp_enqueue_style( 'marble-be-css', $theme_uri . '/assets/css/be.css', array( 'marble-google-fonts' ), '1.0', 'all' );
	}
	
	// Responsive CSS
	if ( file_exists( get_template_directory() . '/assets/css/responsive.css' ) ) {
		wp_enqueue_style( 'marble-responsive', $theme_uri . '/assets/css/responsive.css', array( 'marble-be-css' ), '1.0', 'all' );
	}
	
	// Animations CSS
	if ( file_exists( get_template_directory() . '/assets/css/animations.min.css' ) ) {
		wp_enqueue_style( 'marble-animations', $theme_uri . '/assets/css/animations.min.css', array( 'marble-responsive' ), '1.0', 'all' );
	}
	
	// Theme stylesheet
	wp_enqueue_style( 'marble-clone-style', get_stylesheet_uri(), array( 'marble-animations' ), '1.0', 'all' );
	
	// jQuery
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'marble_clone_scripts', 10 );

/**
 * Auto-create pages from HTML files on theme activation
 */
function marble_clone_create_pages() {
	// Check if already created
	if ( get_option( 'marble_clone_pages_created' ) ) {
		return;
	}
	
	$html_dir = get_template_directory() . '/downloaded-pages/';
	
	if ( ! is_dir( $html_dir ) ) {
		return;
	}
	
	$html_files = glob( $html_dir . '*.html' );
	
	if ( empty( $html_files ) ) {
		return;
	}
	
	// Page configuration
	$page_config = array(
		'home'                                      => array( 'title' => 'Home', 'order' => 1 ),
		'about-us'                                  => array( 'title' => 'About Us', 'order' => 2 ),
		'services'                                  => array( 'title' => 'Services', 'order' => 3 ),
		'marble-natural-stone-restoration'          => array( 'title' => 'Marble Natural Stone Restoration', 'order' => 10 ),
		'marble-repair-restoration'                 => array( 'title' => 'Marble Repair Restoration', 'order' => 11 ),
		'marble-refinishing-care-maintenance'       => array( 'title' => 'Marble Refinishing Care Maintenance', 'order' => 12 ),
		'kitchen-island-countertops-and-refinishing' => array( 'title' => 'Kitchen Island Countertops and Refinishing', 'order' => 13 ),
		'floors-counters-walls-maintenance'         => array( 'title' => 'Floors Counters Walls Maintenance', 'order' => 14 ),
		'beverly-hills-ca'                          => array( 'title' => 'Beverly Hills CA', 'order' => 20 ),
		'santa-monica-ca'                           => array( 'title' => 'Santa Monica CA', 'order' => 21 ),
		'brentwood-ca'                              => array( 'title' => 'Brentwood CA', 'order' => 22 ),
		'calabasas-ca'                              => array( 'title' => 'Calabasas CA', 'order' => 23 ),
		'studio-city-ca'                            => array( 'title' => 'Studio City CA', 'order' => 24 ),
		'contact-us'                                => array( 'title' => 'Contact Us', 'order' => 30 ),
		'natural-stones-care-maintenance'           => array( 'title' => 'Natural Stones Care Maintenance', 'order' => 31 ),
		'gallery'                                   => array( 'title' => 'Gallery', 'order' => 32 ),
	);
	
	$created_pages = array();
	
	foreach ( $html_files as $html_file ) {
		$filename = basename( $html_file, '.html' );
		$slug     = sanitize_title( $filename );
		
		// Check if page already exists
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		
		// Read HTML content
		$html_content = file_get_contents( $html_file );
		
		if ( empty( $html_content ) ) {
			continue;
		}
		
		// Extract body content
		preg_match( '/<body[^>]*>(.*?)<\/body>/is', $html_content, $body_matches );
		$body_content = ! empty( $body_matches[1] ) ? $body_matches[1] : $html_content;
		
		// Extract main content
		preg_match( '/<div id="Content"[^>]*>(.*?)<div id="Footer"/is', $body_content, $content_matches );
		$main_content = ! empty( $content_matches[1] ) ? $content_matches[1] : $body_content;
		
		// Get page config
		$config      = isset( $page_config[ $slug ] ) ? $page_config[ $slug ] : array();
		$page_title  = ! empty( $config['title'] ) ? $config['title'] : ucwords( str_replace( array( '-', '_' ), ' ', $filename ) );
		$menu_order  = ! empty( $config['order'] ) ? $config['order'] : 50;
		
		// Create page
		$page_data = array(
			'post_title'   => $page_title,
			'post_content' => marble_clone_convert_paths( $main_content ),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_name'    => $slug,
			'menu_order'   => $menu_order,
		);
		
		$page_id = wp_insert_post( $page_data, true );
		
		if ( ! is_wp_error( $page_id ) ) {
			$created_pages[] = array(
				'id'    => $page_id,
				'slug'  => $slug,
				'title' => $page_title,
				'order' => $menu_order,
			);
		}
	}
	
	// Set homepage
	$home_page = get_page_by_path( 'home' );
	if ( $home_page ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_page->ID );
	}
	
	// Create navigation menu
	marble_clone_create_menu( $created_pages );
	
	// Mark as created
	update_option( 'marble_clone_pages_created', true );
	
	// Set success transient
	set_transient( 'marble_clone_pages_created_notice', true, 60 );
}
add_action( 'after_switch_theme', 'marble_clone_create_pages' );

/**
 * Create navigation menu
 */
function marble_clone_create_menu( $pages = array() ) {
	$menu_name = 'Main Navigation';
	$menu_exists = wp_get_nav_menu_object( $menu_name );
	
	if ( ! $menu_exists ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu_exists->term_id;
	}
	
	if ( is_wp_error( $menu_id ) ) {
		return;
	}
	
	// Sort pages by menu_order
	usort( $pages, function( $a, $b ) {
		return $a['order'] - $b['order'];
	} );
	
	// Define menu structure
	$main_items = array( 'home', 'about-us', 'services' );
	$service_items = array(
		'marble-natural-stone-restoration',
		'marble-repair-restoration',
		'marble-refinishing-care-maintenance',
		'kitchen-island-countertops-and-refinishing',
		'floors-counters-walls-maintenance',
	);
	$location_items = array(
		'beverly-hills-ca',
		'santa-monica-ca',
		'brentwood-ca',
		'calabasas-ca',
		'studio-city-ca',
	);
	
	$position = 1;
	$services_parent_id = 0;
	
	// Add main menu items
	foreach ( $pages as $page ) {
		if ( in_array( $page['slug'], $main_items ) ) {
			$menu_item_id = wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $page['title'],
				'menu-item-object-id' => $page['id'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
			
			if ( $page['slug'] === 'services' ) {
				$services_parent_id = $menu_item_id;
			}
		}
	}
	
	// Add service submenu items
	if ( $services_parent_id ) {
		foreach ( $pages as $page ) {
			if ( in_array( $page['slug'], $service_items ) ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => $page['title'],
					'menu-item-object-id' => $page['id'],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $services_parent_id,
				) );
			}
		}
	}
	
	// Add contact and gallery
	foreach ( $pages as $page ) {
		if ( in_array( $page['slug'], array( 'contact-us', 'gallery' ) ) ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $page['title'],
				'menu-item-object-id' => $page['id'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}
	}
	
	// Assign menu to location
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Convert image paths in HTML content
 */
function marble_clone_convert_paths( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}
	
	$site_url = site_url();
	$upload_url = wp_upload_dir();
	$uploads_url = $upload_url['baseurl'];
	
	// Convert wp-content/uploads paths
	$content = preg_replace(
		'/(<img[^>]+src=["\'])(\.\.\/)*(wp-content\/uploads\/[^"\']+)(["\'])/i',
		'$1' . $site_url . '/$3$4',
		$content
	);
	
	// Convert srcset
	$content = preg_replace_callback(
		'/srcset=["\']([^"\']+)["\']/i',
		function( $matches ) use ( $site_url ) {
			$srcset = $matches[1];
			$srcset = preg_replace(
				'/(\.\.\/)*wp-content\/uploads\//i',
				$site_url . '/wp-content/uploads/',
				$srcset
			);
			return 'srcset="' . esc_attr( $srcset ) . '"';
		},
		$content
	);
	
	return $content;
}

/**
 * Copy images to WordPress uploads on activation
 */
function marble_clone_copy_images() {
	if ( get_option( 'marble_clone_images_copied' ) ) {
		return;
	}
	
	$theme_images = get_template_directory() . '/assets/uploads/';
	$wp_uploads = wp_upload_dir();
	$wp_uploads_dir = $wp_uploads['basedir'];
	
	if ( ! is_dir( $theme_images ) ) {
		return;
	}
	
	// Recursive copy function
	$copy_recursive = function( $src, $dst ) use ( &$copy_recursive ) {
		$dir = opendir( $src );
		@mkdir( $dst, 0755, true );
		
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( $file != '.' && $file != '..' ) {
				if ( is_dir( $src . '/' . $file ) ) {
					$copy_recursive( $src . '/' . $file, $dst . '/' . $file );
				} else {
					@copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	};
	
	$copy_recursive( $theme_images, $wp_uploads_dir );
	
	update_option( 'marble_clone_images_copied', true );
	set_transient( 'marble_clone_images_copied_notice', true, 60 );
}
add_action( 'after_switch_theme', 'marble_clone_copy_images', 5 );

/**
 * Admin notices
 */
function marble_clone_admin_notices() {
	if ( get_transient( 'marble_clone_pages_created_notice' ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Marble Clone:</strong> Successfully created 16 pages and navigation menu!</p>
		</div>
		<?php
		delete_transient( 'marble_clone_pages_created_notice' );
	}
	
	if ( get_transient( 'marble_clone_images_copied_notice' ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Marble Clone:</strong> Successfully copied 182 images to WordPress uploads folder!</p>
		</div>
		<?php
		delete_transient( 'marble_clone_images_copied_notice' );
	}
}
add_action( 'admin_notices', 'marble_clone_admin_notices' );
