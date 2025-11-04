<?php
/**
 * Marble Clone Theme Functions
 * 100% copy of marbleclinicrestoration.com
 * 
 * @package Marble_Clone
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function marble_clone_theme_setup() {
	// Theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'custom-logo' );
	
	// Elementor support
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );
	
	// Register menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'marble-clone' ),
		'footer'  => esc_html__( 'Footer Menu', 'marble-clone' ),
	) );
	
	// Set content width
	if ( ! isset( $content_width ) ) {
		$content_width = 1170;
	}
}
add_action( 'after_setup_theme', 'marble_clone_theme_setup' );

/**
 * Enqueue Google Fonts
 */
function marble_clone_fonts() {
	$theme_uri = get_template_directory_uri();
	$local_fonts = get_template_directory() . '/assets/fonts/google-fonts-local.css';
	
	if ( file_exists( $local_fonts ) ) {
		wp_enqueue_style( 'marble-clone-fonts', $theme_uri . '/assets/fonts/google-fonts-local.css', array(), '1.0' );
	} else {
		wp_enqueue_style( 'marble-clone-fonts', 'https://fonts.googleapis.com/css?family=Roboto:1,300,400,400italic,500,700,700italic|Lora:1,300,400,400italic,500,700,700italic&display=swap', array(), null );
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
		wp_enqueue_style( 'fontawesome', $theme_uri . '/assets/fonts/fontawesome/fontawesome.css', array(), '5.15.1' );
	}
	
	// Main BeTheme CSS
	if ( file_exists( get_template_directory() . '/assets/css/be.css' ) ) {
		wp_enqueue_style( 'marble-clone-be', $theme_uri . '/assets/css/be.css', array( 'marble-clone-fonts' ), '1.0', 'all' );
	}
	
	// Responsive CSS
	if ( file_exists( get_template_directory() . '/assets/css/responsive.css' ) ) {
		wp_enqueue_style( 'marble-clone-responsive', $theme_uri . '/assets/css/responsive.css', array( 'marble-clone-be' ), '1.0', 'all' );
	}
	
	// Animations CSS
	if ( file_exists( get_template_directory() . '/assets/css/animations.min.css' ) ) {
		wp_enqueue_style( 'marble-clone-animations', $theme_uri . '/assets/css/animations.min.css', array( 'marble-clone-responsive' ), '1.0', 'all' );
	}
	
	// Theme stylesheet
	wp_enqueue_style( 'marble-clone-style', get_stylesheet_uri(), array( 'marble-clone-animations' ), '1.0', 'all' );
	
	// Inline CSS for Elementor
	$inline_css = "
		body { margin: 0; padding: 0; }
		#Content { width: 100%; }
		.content_wrapper { width: 100%; max-width: 100%; }
		.sections_group { width: 100%; }
		.entry-content { width: 100%; }
		.elementor { visibility: visible !important; }
		.elementor-section { position: relative; width: 100%; }
		.elementor-container { max-width: 1170px; margin: 0 auto; }
		img { max-width: 100%; height: auto; }
	";
	wp_add_inline_style( 'marble-clone-style', $inline_css );
	
	// jQuery
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'marble_clone_scripts', 10 );

/**
 * Convert image paths from theme to WordPress uploads
 */
function marble_clone_convert_paths( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}
	
	$theme_uri = get_template_directory_uri();
	$site_url = site_url();
	
	// Convert wp-content/uploads paths
	$content = preg_replace(
		'/src=["\'](\.\.\/)*(wp-content\/uploads\/[^"\']+)["\']/i',
		'src="' . $site_url . '/$2"',
		$content
	);
	
	// Also handle without ../
	$content = preg_replace(
		'/src=["\'](?!http)wp-content\/uploads\/([^"\']+)["\']/i',
		'src="' . $site_url . '/wp-content/uploads/$1"',
		$content
	);
	
	// Handle srcset
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
	
	// Background images in inline styles
	$content = preg_replace_callback(
		'/style=["\']([^"\']*background-image:\s*url\(["\']?)([^"\')\s]+)(["\']?\)[^"\']*)["\']/',
		function( $matches ) use ( $site_url ) {
			$style = $matches[1];
			$url = $matches[2];
			$closing = $matches[3];
			
			if ( strpos( $url, 'wp-content/uploads/' ) !== false ) {
				$url = preg_replace(
					'/(\.\.\/)*wp-content\/uploads\//i',
					$site_url . '/wp-content/uploads/',
					$url
				);
			}
			
			return 'style="' . $style . $url . $closing . '"';
		},
		$content
	);
	
	return $content;
}

/**
 * Auto-copy images from theme to WordPress uploads folder
 */
function marble_clone_copy_images() {
	$theme_images_dir = get_template_directory() . '/assets/uploads/';
	$wp_uploads = wp_upload_dir();
	$wp_uploads_dir = $wp_uploads['basedir'];
	
	if ( ! is_dir( $theme_images_dir ) ) {
		return;
	}
	
	// Check if already copied
	if ( get_option( 'marble_clone_images_copied', false ) ) {
		return;
	}
	
	// Recursive copy function
	function marble_clone_recursive_copy( $src, $dst ) {
		$dir = @opendir( $src );
		if ( ! $dir ) {
			return;
		}
		
		@mkdir( $dst, 0755, true );
		
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( $file !== '.' && $file !== '..' ) {
				if ( is_dir( $src . '/' . $file ) ) {
					marble_clone_recursive_copy( $src . '/' . $file, $dst . '/' . $file );
				} else {
					@copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}
	
	// Copy images
	marble_clone_recursive_copy( $theme_images_dir, $wp_uploads_dir );
	
	// Mark as copied
	update_option( 'marble_clone_images_copied', true );
	set_transient( 'marble_clone_images_notice', true, 60 );
}
add_action( 'after_switch_theme', 'marble_clone_copy_images', 5 );

/**
 * Create pages from HTML files
 */
function marble_clone_create_pages() {
	$pages_dir = get_template_directory() . '/elementor-pages/';
	
	if ( ! is_dir( $pages_dir ) ) {
		return;
	}
	
	// Check if already created
	if ( get_option( 'marble_clone_pages_created', false ) ) {
		return;
	}
	
	// Page configuration
	$page_config = array(
		'home' => array( 'title' => 'Home', 'menu_order' => 1 ),
		'about-us' => array( 'title' => 'About Us', 'menu_order' => 2 ),
		'services' => array( 'title' => 'Services', 'menu_order' => 3 ),
		'marble-natural-stone-restoration' => array( 'title' => 'Marble Natural Stone Restoration', 'menu_order' => 10 ),
		'marble-repair-restoration' => array( 'title' => 'Marble Repair Restoration', 'menu_order' => 11 ),
		'marble-refinishing-care-maintenance' => array( 'title' => 'Marble Refinishing Care Maintenance', 'menu_order' => 12 ),
		'kitchen-island-countertops-and-refinishing' => array( 'title' => 'Kitchen Island Countertops and Refinishing', 'menu_order' => 13 ),
		'floors-counters-walls-maintenance' => array( 'title' => 'Floors Counters Walls Maintenance', 'menu_order' => 14 ),
		'natural-stones-care-maintenance' => array( 'title' => 'Natural Stones Care Maintenance', 'menu_order' => 15 ),
		'beverly-hills-ca' => array( 'title' => 'Beverly Hills CA', 'menu_order' => 20 ),
		'santa-monica-ca' => array( 'title' => 'Santa Monica CA', 'menu_order' => 21 ),
		'brentwood-ca' => array( 'title' => 'Brentwood CA', 'menu_order' => 22 ),
		'calabasas-ca' => array( 'title' => 'Calabasas CA', 'menu_order' => 23 ),
		'studio-city-ca' => array( 'title' => 'Studio City CA', 'menu_order' => 24 ),
		'contact-us' => array( 'title' => 'Contact Us', 'menu_order' => 30 ),
		'gallery' => array( 'title' => 'Gallery', 'menu_order' => 31 ),
	);
	
	$html_files = glob( $pages_dir . '*.html' );
	$created_pages = array();
	
	foreach ( $html_files as $html_file ) {
		$filename = basename( $html_file, '.html' );
		$slug = sanitize_title( $filename );
		
		// Check if page exists
		$existing_page = get_page_by_path( $slug );
		if ( $existing_page ) {
			$created_pages[] = array(
				'id' => $existing_page->ID,
				'slug' => $slug,
				'menu_order' => isset( $page_config[ $slug ]['menu_order'] ) ? $page_config[ $slug ]['menu_order'] : 50,
			);
			continue;
		}
		
		// Read HTML content
		$html_content = file_get_contents( $html_file );
		
		// Extract body content
		if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $html_content, $matches ) ) {
			$content = $matches[1];
			
			// Extract sections_group content if exists
			if ( preg_match( '/<div class="sections_group">(.*?)<\/div><!-- \.sections_group -->/is', $content, $section_matches ) ) {
				$content = $section_matches[1];
			}
			
			// Convert paths
			$content = marble_clone_convert_paths( $content );
			
			// Create page
			$page_data = array(
				'post_title'   => isset( $page_config[ $slug ]['title'] ) ? $page_config[ $slug ]['title'] : ucwords( str_replace( '-', ' ', $filename ) ),
				'post_name'    => $slug,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => 1,
				'menu_order'   => isset( $page_config[ $slug ]['menu_order'] ) ? $page_config[ $slug ]['menu_order'] : 50,
			);
			
			$page_id = wp_insert_post( $page_data, true );
			
			if ( ! is_wp_error( $page_id ) ) {
				$created_pages[] = array(
					'id' => $page_id,
					'slug' => $slug,
					'menu_order' => $page_data['menu_order'],
				);
				
				// Set as homepage if it's the home page
				if ( $slug === 'home' ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $page_id );
				}
			}
		}
	}
	
	// Store created pages
	set_transient( 'marble_clone_created_pages', $created_pages, 600 );
	update_option( 'marble_clone_pages_created', true );
	
	// Create menu
	marble_clone_create_menu( $created_pages );
	
	// Flush rewrite rules
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'marble_clone_create_pages', 10 );

/**
 * Create navigation menu
 */
function marble_clone_create_menu( $pages = array() ) {
	if ( empty( $pages ) ) {
		$pages = get_transient( 'marble_clone_created_pages' );
	}
	
	if ( empty( $pages ) ) {
		return;
	}
	
	// Sort by menu_order
	usort( $pages, function( $a, $b ) {
		return $a['menu_order'] - $b['menu_order'];
	} );
	
	$menu_name = 'Main Navigation';
	$menu_exists = wp_get_nav_menu_object( $menu_name );
	
	if ( ! $menu_exists ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu_exists->term_id;
		// Clear existing items
		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( $menu_items ) {
			foreach ( $menu_items as $item ) {
				wp_delete_post( $item->ID, true );
			}
		}
	}
	
	// Define menu structure
	$main_items = array( 'home', 'about-us', 'services' );
	$service_items = array(
		'marble-natural-stone-restoration',
		'marble-repair-restoration',
		'marble-refinishing-care-maintenance',
		'kitchen-island-countertops-and-refinishing',
		'floors-counters-walls-maintenance',
		'natural-stones-care-maintenance',
	);
	$location_items = array(
		'beverly-hills-ca',
		'santa-monica-ca',
		'brentwood-ca',
		'calabasas-ca',
		'studio-city-ca',
	);
	
	$position = 1;
	$services_menu_item_id = 0;
	
	// Add main items
	foreach ( $pages as $page ) {
		if ( in_array( $page['slug'], $main_items ) ) {
			$item_id = wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => get_the_title( $page['id'] ),
				'menu-item-object-id' => $page['id'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
			
			if ( $page['slug'] === 'services' ) {
				$services_menu_item_id = $item_id;
			}
		}
	}
	
	// Add service sub-items
	if ( $services_menu_item_id > 0 ) {
		foreach ( $pages as $page ) {
			if ( in_array( $page['slug'], $service_items ) ) {
				wp_update_nav_menu_item( $menu_id, 0, array(
					'menu-item-title'     => get_the_title( $page['id'] ),
					'menu-item-object-id' => $page['id'],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $services_menu_item_id,
					'menu-item-position'  => $position++,
				) );
			}
		}
	}
	
	// Add other pages
	foreach ( $pages as $page ) {
		if ( ! in_array( $page['slug'], array_merge( $main_items, $service_items ) ) ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => get_the_title( $page['id'] ),
				'menu-item-object-id' => $page['id'],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position++,
			) );
		}
	}
	
	// Assign to theme location
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Admin notices
 */
function marble_clone_admin_notices() {
	if ( get_transient( 'marble_clone_images_notice' ) ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>Marble Clone:</strong> All images have been copied to WordPress uploads folder!</p>
		</div>
		<?php
		delete_transient( 'marble_clone_images_notice' );
	}
}
add_action( 'admin_notices', 'marble_clone_admin_notices' );

/**
 * Extended allowed tags for wp_kses to preserve Elementor markup
 */
function marble_clone_allowed_tags() {
	global $allowedposttags;
	
	$elementor_attrs = array(
		'data-elementor-type' => true,
		'data-elementor-id' => true,
		'data-element_type' => true,
		'data-widget_type' => true,
		'data-settings' => true,
		'data-id' => true,
		'data-post-id' => true,
		'data-obj-id' => true,
		'data-parent' => true,
	);
	
	// Add to all tags
	foreach ( $allowedposttags as $tag => $attrs ) {
		$allowedposttags[ $tag ] = array_merge( (array) $attrs, $elementor_attrs );
	}
	
	// Add section/div tags
	$allowedposttags['section'] = array_merge( $allowedposttags['div'], $elementor_attrs );
	
	return $allowedposttags;
}
add_filter( 'wp_kses_allowed_html', 'marble_clone_allowed_tags' );
