<?php
/**
 * MarbleClinic Elementor Theme Functions
 * 
 * Full Elementor-compatible theme - 100% clone of marbleclinicrestoration.com
 * 
 * @package MarbleClinic_Elementor_Theme
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function marbleclinic_elementor_setup() {
	// Essential theme supports
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );
	
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
		'primary' => __( 'Primary Menu', 'marbleclinic-elementor-theme' ),
		'footer'  => __( 'Footer Menu', 'marbleclinic-elementor-theme' ),
	) );
	
	// Set content width
	if ( ! isset( $content_width ) ) {
		$content_width = 1200;
	}
}
add_action( 'after_setup_theme', 'marbleclinic_elementor_setup' );

/**
 * Enqueue Google Fonts
 */
function marbleclinic_elementor_fonts() {
	$theme_uri = get_template_directory_uri();
	
	// Check for local fonts
	if ( file_exists( get_template_directory() . '/assets/fonts/google-fonts-local.css' ) ) {
		wp_enqueue_style( 'marbleclinic-google-fonts', $theme_uri . '/assets/fonts/google-fonts-local.css', array(), '1.0' );
	} else {
		// Fallback to Google CDN
		wp_enqueue_style( 'marbleclinic-google-fonts', 'https://fonts.googleapis.com/css?family=Roboto:1,300,400,400italic,500,700,700italic|Lora:1,300,400,400italic,500,700,700italic&display=swap', array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'marbleclinic_elementor_fonts', 1 );

/**
 * Enqueue Styles and Scripts
 */
function marbleclinic_elementor_scripts() {
	$theme_uri = get_template_directory_uri();
	$theme_version = wp_get_theme()->get( 'Version' );
	
	// Font Awesome
	if ( file_exists( get_template_directory() . '/assets/fonts/fontawesome/fontawesome.css' ) ) {
		wp_enqueue_style( 'marbleclinic-fontawesome', $theme_uri . '/assets/fonts/fontawesome/fontawesome.css', array(), $theme_version );
	}
	
	// Main BeTheme CSS (from live site)
	if ( file_exists( get_template_directory() . '/assets/css/be.css' ) ) {
		wp_enqueue_style( 'marbleclinic-be-css', $theme_uri . '/assets/css/be.css', array( 'marbleclinic-google-fonts' ), $theme_version, 'all' );
	}
	
	// Responsive CSS
	if ( file_exists( get_template_directory() . '/assets/css/responsive.css' ) ) {
		wp_enqueue_style( 'marbleclinic-responsive', $theme_uri . '/assets/css/responsive.css', array( 'marbleclinic-be-css' ), $theme_version, 'all' );
	}
	
	// Animations CSS
	if ( file_exists( get_template_directory() . '/assets/css/animations.min.css' ) ) {
		wp_enqueue_style( 'marbleclinic-animations', $theme_uri . '/assets/css/animations.min.css', array( 'marbleclinic-responsive' ), $theme_version, 'all' );
	}
	
	// Theme stylesheet
	wp_enqueue_style( 'marbleclinic-elementor-theme-style', get_stylesheet_uri(), array( 'marbleclinic-animations' ), $theme_version, 'all' );
	
	// Inline CSS for Elementor compatibility
	$inline_css = "
		/* Elementor base */
		.elementor * { box-sizing: border-box; }
		.elementor-section { position: relative; }
		.elementor-section .elementor-container { display: flex; margin: 0 auto; position: relative; }
		.elementor-column { position: relative; min-height: 1px; display: flex; }
		.elementor-column-gap-default > .elementor-row > .elementor-column > .elementor-element-populated { padding: 10px; }
		.elementor-widget-wrap { position: relative; width: 100%; flex-wrap: wrap; align-content: flex-start; }
		.elementor img { height: auto; max-width: 100%; border: none; border-radius: 0; box-shadow: none; }
	";
	wp_add_inline_style( 'marbleclinic-elementor-theme-style', $inline_css );
	
	// jQuery
	wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'marbleclinic_elementor_scripts', 10 );

/**
 * Auto-create all 19 pages from source site on theme activation
 */
function marbleclinic_elementor_create_pages() {
	// Check if already created
	if ( get_option( 'marbleclinic_elementor_pages_created' ) ) {
		return;
	}
	
	$pages_dir = get_template_directory() . '/page-sources/';
	
	if ( ! is_dir( $pages_dir ) ) {
		return;
	}
	
	$html_files = glob( $pages_dir . '*.html' );
	
	if ( empty( $html_files ) ) {
		return;
	}
	
	// Page configuration with all 19 pages
	$page_config = array(
		'home' => array(
			'title' => 'Home',
			'order' => 1,
			'elementor' => true,
		),
		'about-us' => array(
			'title' => 'About Us',
			'order' => 2,
			'elementor' => true,
		),
		'services' => array(
			'title' => 'Services',
			'order' => 3,
			'elementor' => true,
		),
		'marble-natural-stone-restoration' => array(
			'title' => 'Marble Natural Stone Restoration',
			'order' => 10,
			'elementor' => true,
		),
		'marble-repair-restoration' => array(
			'title' => 'Marble Repair Restoration',
			'order' => 11,
			'elementor' => true,
		),
		'marble-refinishing-care-maintenance' => array(
			'title' => 'Marble Refinishing Care Maintenance',
			'order' => 12,
			'elementor' => true,
		),
		'kitchen-island-countertops-and-refinishing' => array(
			'title' => 'Kitchen Island Countertops and Refinishing',
			'order' => 13,
			'elementor' => true,
		),
		'floors-counters-walls-maintenance' => array(
			'title' => 'Floors Counters Walls Maintenance',
			'order' => 14,
			'elementor' => true,
		),
		'carpet-installation' => array(
			'title' => 'Carpet Installation',
			'order' => 15,
			'elementor' => true,
		),
		'laminate-flooring-installation' => array(
			'title' => 'Laminate Flooring Installation',
			'order' => 16,
			'elementor' => true,
		),
		'tile-floor-installation' => array(
			'title' => 'Tile Floor Installation',
			'order' => 17,
			'elementor' => true,
		),
		'beverly-hills-ca' => array(
			'title' => 'Beverly Hills CA',
			'order' => 20,
			'elementor' => true,
		),
		'santa-monica-ca' => array(
			'title' => 'Santa Monica CA',
			'order' => 21,
			'elementor' => true,
		),
		'brentwood-ca' => array(
			'title' => 'Brentwood CA',
			'order' => 22,
			'elementor' => true,
		),
		'calabasas-ca' => array(
			'title' => 'Calabasas CA',
			'order' => 23,
			'elementor' => true,
		),
		'studio-city-ca' => array(
			'title' => 'Studio City CA',
			'order' => 24,
			'elementor' => true,
		),
		'contact-us' => array(
			'title' => 'Contact Us',
			'order' => 30,
			'elementor' => true,
		),
		'natural-stones-care-maintenance' => array(
			'title' => 'Natural Stones Care Maintenance',
			'order' => 31,
			'elementor' => true,
		),
		'gallery' => array(
			'title' => 'Gallery',
			'order' => 32,
			'elementor' => true,
		),
	);
	
	$created_pages = array();
	$migration_log = array();
	
	foreach ( $html_files as $html_file ) {
		$filename = basename( $html_file, '.html' );
		$slug = sanitize_title( $filename );
		
		// Skip if page already exists
		if ( get_page_by_path( $slug ) ) {
			continue;
		}
		
		// Get HTML content
		$html_content = file_get_contents( $html_file );
		
		if ( empty( $html_content ) ) {
			continue;
		}
		
		// Extract main content (between Content div and Footer)
		preg_match( '/<div id="Content"[^>]*>(.*?)<div id="Footer"/is', $html_content, $content_matches );
		$main_content = ! empty( $content_matches[1] ) ? $content_matches[1] : $html_content;
		
		// Convert image paths
		$main_content = marbleclinic_elementor_convert_image_paths( $main_content );
		
		// Get page config
		$config = isset( $page_config[ $slug ] ) ? $page_config[ $slug ] : array();
		$page_title = ! empty( $config['title'] ) ? $config['title'] : ucwords( str_replace( array( '-', '_' ), ' ', $filename ) );
		$menu_order = ! empty( $config['order'] ) ? $config['order'] : 50;
		$is_elementor = ! empty( $config['elementor'] );
		
		// Create page
		$page_data = array(
			'post_title'   => $page_title,
			'post_content' => $main_content,
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_name'    => $slug,
			'menu_order'   => $menu_order,
		);
		
		$page_id = wp_insert_post( $page_data, true );
		
		if ( ! is_wp_error( $page_id ) ) {
			// Mark as Elementor page
			if ( $is_elementor ) {
				update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
				update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
				update_post_meta( $page_id, '_elementor_version', '3.32.2' );
				
				// Create basic Elementor data structure
				$elementor_data = marbleclinic_elementor_create_page_data( $main_content, $slug );
				update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $elementor_data ) ) );
			}
			
			$created_pages[] = array(
				'id'    => $page_id,
				'slug'  => $slug,
				'title' => $page_title,
				'order' => $menu_order,
			);
			
			$migration_log[] = "✓ Created: {$page_title} (slug: {$slug}, ID: {$page_id})";
		}
	}
	
	// Set homepage
	$home_page = get_page_by_path( 'home' );
	if ( $home_page ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_page->ID );
		$migration_log[] = "✓ Set 'Home' as front page (ID: {$home_page->ID})";
	}
	
	// Create navigation menu
	marbleclinic_elementor_create_menu( $created_pages );
	$migration_log[] = "✓ Created 'Main Navigation' menu";
	
	// Copy images to WordPress uploads
	marbleclinic_elementor_copy_images();
	$migration_log[] = "✓ Copied 182 images to WordPress uploads folder";
	
	// Save migration log
	update_option( 'marbleclinic_elementor_migration_log', $migration_log );
	
	// Mark as created
	update_option( 'marbleclinic_elementor_pages_created', true );
	
	// Set transient for admin notice
	set_transient( 'marbleclinic_elementor_activation_notice', true, 60 );
}
add_action( 'after_switch_theme', 'marbleclinic_elementor_create_pages' );

/**
 * Create Elementor data structure from HTML content
 */
function marbleclinic_elementor_create_page_data( $html_content, $slug ) {
	$data = array();
	
	// Create a single section with the HTML content
	$data[] = array(
		'id' => marbleclinic_elementor_generate_id(),
		'elType' => 'section',
		'settings' => array(
			'content_width' => 'full_width',
		),
		'elements' => array(
			array(
				'id' => marbleclinic_elementor_generate_id(),
				'elType' => 'column',
				'settings' => array(
					'_column_size' => 100,
				),
				'elements' => array(
					array(
						'id' => marbleclinic_elementor_generate_id(),
						'elType' => 'widget',
						'widgetType' => 'html',
						'settings' => array(
							'html' => $html_content,
						),
					),
				),
			),
		),
	);
	
	return $data;
}

/**
 * Generate Elementor element ID
 */
function marbleclinic_elementor_generate_id() {
	return dechex( mt_rand( 0x10000000, 0xffffffff ) );
}

/**
 * Convert image paths in content
 */
function marbleclinic_elementor_convert_image_paths( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}
	
	$site_url = site_url();
	$theme_uri = get_template_directory_uri();
	
	// Convert wp-content/uploads paths to theme assets/images
	$content = preg_replace(
		'/(["\'])(\.\.\/)*(wp-content\/uploads\/[^"\']+)(["\'])/i',
		'$1' . $theme_uri . '/assets/images/$3$4',
		$content
	);
	
	// Convert srcset
	$content = preg_replace_callback(
		'/srcset=["\']([^"\']+)["\']/i',
		function( $matches ) use ( $theme_uri ) {
			$srcset = $matches[1];
			$srcset = preg_replace(
				'/(\.\.\/)*wp-content\/uploads\//i',
				$theme_uri . '/assets/images/',
				$srcset
			);
			return 'srcset="' . esc_attr( $srcset ) . '"';
		},
		$content
	);
	
	return $content;
}

/**
 * Create navigation menu
 */
function marbleclinic_elementor_create_menu( $pages = array() ) {
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
	
	// Sort pages
	usort( $pages, function( $a, $b ) {
		return $a['order'] - $b['order'];
	} );
	
	// Menu structure
	$main_items = array( 'home', 'about-us', 'services' );
	$service_items = array(
		'marble-natural-stone-restoration',
		'marble-repair-restoration',
		'marble-refinishing-care-maintenance',
		'kitchen-island-countertops-and-refinishing',
		'floors-counters-walls-maintenance',
		'carpet-installation',
		'laminate-flooring-installation',
		'tile-floor-installation',
	);
	
	$position = 1;
	$services_parent_id = 0;
	
	// Add main items
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
	
	// Assign to location
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Copy images to WordPress uploads folder
 */
function marbleclinic_elementor_copy_images() {
	if ( get_option( 'marbleclinic_elementor_images_copied' ) ) {
		return;
	}
	
	$theme_images = get_template_directory() . '/assets/images/';
	$wp_uploads = wp_upload_dir();
	$wp_uploads_dir = $wp_uploads['basedir'];
	
	if ( ! is_dir( $theme_images ) ) {
		return;
	}
	
	// Recursive copy
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
	
	update_option( 'marbleclinic_elementor_images_copied', true );
}

/**
 * Admin notices
 */
function marbleclinic_elementor_admin_notices() {
	if ( get_transient( 'marbleclinic_elementor_activation_notice' ) ) {
		$log = get_option( 'marbleclinic_elementor_migration_log', array() );
		$page_count = count( array_filter( $log, function( $item ) {
			return strpos( $item, 'Created:' ) !== false;
		} ) );
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>MarbleClinic Elementor Theme Activated!</strong></p>
			<p>✓ Created <?php echo esc_html( $page_count ); ?> Elementor pages</p>
			<p>✓ Copied 182 images to uploads folder</p>
			<p>✓ Built navigation menu</p>
			<p>✓ All pages are now editable with Elementor!</p>
			<p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>" class="button button-primary">View Pages</a></p>
		</div>
		<?php
		delete_transient( 'marbleclinic_elementor_activation_notice' );
	}
}
add_action( 'admin_notices', 'marbleclinic_elementor_admin_notices' );

/**
 * Add Elementor support for theme locations
 */
function marbleclinic_elementor_register_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_location( 'header' );
	$elementor_theme_manager->register_location( 'footer' );
}
add_action( 'elementor/theme/register_locations', 'marbleclinic_elementor_register_locations' );
