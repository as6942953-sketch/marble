<?php
/**
 * Marble Elementor Theme Functions
 * 
 * @package Marble_Elementor_Theme
 * @since 1.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 */
function marble_elementor_theme_setup() {
	// Add theme support for various WordPress features
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );
	
	// Add theme support for Elementor
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );
	
	// Register navigation menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'marble-elementor-theme' ),
		'footer' => esc_html__( 'Footer Menu', 'marble-elementor-theme' ),
	) );
}
add_action( 'after_setup_theme', 'marble_elementor_theme_setup' );

/**
 * Enqueue Google Fonts (Local or Remote)
 */
function marble_elementor_theme_fonts() {
	$theme_uri = get_template_directory_uri();
	$local_fonts_file = get_template_directory() . '/assets/fonts/google-fonts-local.css';
	
	// Check if local fonts CSS exists
	if ( file_exists( $local_fonts_file ) ) {
		// Use local fonts
		wp_enqueue_style(
			'marble-google-fonts',
			$theme_uri . '/assets/fonts/google-fonts-local.css',
			array(),
			'1.0'
		);
	} else {
		// Fallback to Google Fonts CDN
		wp_enqueue_style(
			'marble-google-fonts',
			'https://fonts.googleapis.com/css?family=Roboto:1,300,400,400italic,500,700,700italic|Lora:1,300,400,400italic,500,700,700italic&display=swap',
			array(),
			null
		);
	}
}
add_action( 'wp_enqueue_scripts', 'marble_elementor_theme_fonts', 1 );

/**
 * Enqueue Styles and Scripts
 */
function marble_elementor_theme_scripts() {
	// Get theme directory URI
	$theme_uri = get_template_directory_uri();
	
	// Enqueue main theme CSS (be.css) - must be first
	if ( file_exists( get_template_directory() . '/assets/css/be.css' ) ) {
		wp_enqueue_style( 
			'marble-be-css', 
			$theme_uri . '/assets/css/be.css', 
			array( 'marble-google-fonts' ), 
			'1.0' 
		);
	}
	
	// Enqueue responsive CSS
	if ( file_exists( get_template_directory() . '/assets/css/responsive.css' ) ) {
		wp_enqueue_style( 
			'marble-responsive-css', 
			$theme_uri . '/assets/css/responsive.css', 
			array( 'marble-be-css' ), 
			'1.0' 
		);
	}
	
	// Enqueue animations CSS
	if ( file_exists( get_template_directory() . '/assets/css/animations.min.css' ) ) {
		wp_enqueue_style( 
			'marble-animations-css', 
			$theme_uri . '/assets/css/animations.min.css', 
			array( 'marble-responsive-css' ), 
			'1.0' 
		);
	}
	
	// Enqueue dynamic inline CSS (theme styles, colors, fonts)
	if ( file_exists( get_template_directory() . '/assets/css/dynamic-inline.css' ) ) {
		wp_enqueue_style( 
			'marble-dynamic-inline-css', 
			$theme_uri . '/assets/css/dynamic-inline.css', 
			array( 'marble-animations-css' ), 
			'1.0' 
		);
	}
	
	// Enqueue custom inline CSS (Elementor customizations)
	if ( file_exists( get_template_directory() . '/assets/css/custom-inline.css' ) ) {
		wp_enqueue_style( 
			'marble-custom-inline-css', 
			$theme_uri . '/assets/css/custom-inline.css', 
			array( 'marble-dynamic-inline-css' ), 
			'1.0' 
		);
	}
	
	// Enqueue theme stylesheet (style.css) - last
	wp_enqueue_style( 
		'marble-elementor-theme-style', 
		get_stylesheet_uri(), 
		array( 'marble-custom-inline-css' ), 
		'1.0' 
	);
	
	// Enqueue Font Awesome if it exists
	$fontawesome_path = get_template_directory() . '/assets/fonts/fontawesome/fontawesome.css';
	if ( file_exists( $fontawesome_path ) ) {
		wp_enqueue_style( 
			'marble-fontawesome', 
			$theme_uri . '/assets/fonts/fontawesome/fontawesome.css', 
			array(), 
			'1.0' 
		);
	}
	
	// Enqueue custom JavaScript if needed
	if ( file_exists( get_template_directory() . '/assets/js/custom.js' ) ) {
		wp_enqueue_script( 
			'marble-custom-js', 
			$theme_uri . '/assets/js/custom.js', 
			array( 'jquery' ), 
			'1.0', 
			true 
		);
	}
}
add_action( 'wp_enqueue_scripts', 'marble_elementor_theme_scripts' );

/**
 * Register Widget Areas
 */
function marble_elementor_theme_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'marble-elementor-theme' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here.', 'marble-elementor-theme' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}
add_action( 'widgets_init', 'marble_elementor_theme_widgets_init' );

/**
 * Add body classes for Elementor compatibility
 */
function marble_elementor_theme_body_classes( $classes ) {
	// Add classes that match the original theme
	$classes[] = 'wp-singular';
	$classes[] = 'is-elementor';
	$classes[] = 'elementor-default';
	
	// Add page-specific classes if needed
	if ( is_page() ) {
		$classes[] = 'page';
		$classes[] = 'page-id-' . get_the_ID();
	}
	
	return $classes;
}
add_filter( 'body_class', 'marble_elementor_theme_body_classes' );

/**
 * Get page slug mapping for extracted HTML files
 * Maps WordPress page slugs to extracted HTML filenames
 */
function marble_elementor_theme_get_page_html_file( $page_slug ) {
	// Map of page slugs to HTML filenames
	$page_mapping = array(
		'home' => 'home',
		'about-us' => 'about-us',
		'services' => 'services',
		'gallery' => 'gallery',
		'contact-us' => 'contact-us',
		'marble-natural-stone-restoration' => 'marble-natural-stone-restoration',
		'marble-repair-restoration' => 'marble-repair-restoration',
		'marble-refinishing-care-maintenance' => 'marble-refinishing-care-maintenance',
		'natural-stones-care-maintenance' => 'natural-stones-care-maintenance',
		'kitchen-island-countertops-and-refinishing' => 'kitchen-island-countertops-and-refinishing',
		'floors-counters-walls-maintenance' => 'floors-counters-walls-maintenance',
		'beverly-hills-ca' => 'beverly-hills-ca',
		'santa-monica-ca' => 'santa-monica-ca',
		'brentwood-ca' => 'brentwood-ca',
		'calabasas-ca' => 'calabasas-ca',
		'studio-city-ca' => 'studio-city-ca',
	);
	
	// Check if we have a mapping for this slug
	if ( isset( $page_mapping[ $page_slug ] ) ) {
		return $page_mapping[ $page_slug ];
	}
	
	// Default: return the slug as-is (it might already match)
	return $page_slug;
}

/**
 * Convert slug to formatted title
 * Example: "about-us" -> "About Us"
 */
function marble_elementor_theme_slug_to_title( $slug ) {
	// Replace hyphens with spaces
	$title = str_replace( '-', ' ', $slug );
	// Capitalize first letter of each word
	$title = ucwords( $title );
	return $title;
}

/**
 * Convert HTML content paths to WordPress-compatible paths
 * Converts relative paths to WordPress URLs
 */
function marble_elementor_theme_convert_html_paths( $content ) {
	if ( empty( $content ) ) {
		return $content;
	}
	
	$theme_uri = get_template_directory_uri();
	$site_url = site_url();
	
	// Convert wp-content/uploads paths to WordPress media URLs (src attribute)
	$content = preg_replace(
		'/src=["\'](\.\.\/)+(wp-content\/uploads\/[^"\']+)["\']/i',
		'src="' . $site_url . '/$2"',
		$content
	);
	
	// Also handle paths without ../ prefix
	$content = preg_replace(
		'/src=["\']wp-content\/uploads\/([^"\']+)["\']/i',
		'src="' . $site_url . '/wp-content/uploads/$1"',
		$content
	);
	
	// Convert srcset with relative paths
	$content = preg_replace_callback(
		'/srcset=["\']([^"\']+)["\']/i',
		function( $matches ) use ( $site_url ) {
			$srcset = $matches[1];
			// Replace relative wp-content paths (with ../ prefix)
			$srcset = preg_replace(
				'/(\.\.\/)+wp-content\/uploads\//i',
				$site_url . '/wp-content/uploads/',
				$srcset
			);
			// Replace wp-content paths without prefix
			$srcset = preg_replace(
				'/wp-content\/uploads\//i',
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
 * Automatically create pages from HTML files on theme activation
 */
function marble_elementor_theme_create_pages_on_activation() {
	// Get the elementor-pages directory
	$elementor_pages_dir = get_template_directory() . '/elementor-pages/';
	
	// Check if directory exists
	if ( ! is_dir( $elementor_pages_dir ) ) {
		return;
	}
	
	// Get all HTML files
	$html_files = glob( $elementor_pages_dir . '*.html' );
	
	if ( empty( $html_files ) ) {
		return;
	}
	
	// Page mapping for titles and menu order
	$page_config = array(
		'home' => array( 'title' => 'Home', 'menu_order' => 1 ),
		'about-us' => array( 'title' => 'About Us', 'menu_order' => 2 ),
		'services' => array( 'title' => 'Services', 'menu_order' => 3 ),
		'marble-natural-stone-restoration' => array( 'title' => 'Marble Natural Stone Restoration', 'menu_order' => 10 ),
		'marble-repair-restoration' => array( 'title' => 'Marble Repair Restoration', 'menu_order' => 11 ),
		'marble-refinishing-care-maintenance' => array( 'title' => 'Marble Refinishing Care & Maintenance', 'menu_order' => 12 ),
		'natural-stones-care-maintenance' => array( 'title' => 'Natural Stones Care & Maintenance', 'menu_order' => 13 ),
		'kitchen-island-countertops-and-refinishing' => array( 'title' => 'Kitchen Island Countertops & Refinishing', 'menu_order' => 14 ),
		'floors-counters-walls-maintenance' => array( 'title' => 'Floors Counters Walls Maintenance', 'menu_order' => 15 ),
		'gallery' => array( 'title' => 'Gallery', 'menu_order' => 4 ),
		'beverly-hills-ca' => array( 'title' => 'Beverly Hills, CA', 'menu_order' => 20 ),
		'santa-monica-ca' => array( 'title' => 'Santa Monica, CA', 'menu_order' => 21 ),
		'brentwood-ca' => array( 'title' => 'Brentwood, CA', 'menu_order' => 22 ),
		'calabasas-ca' => array( 'title' => 'Calabasas, CA', 'menu_order' => 23 ),
		'studio-city-ca' => array( 'title' => 'Studio City, CA', 'menu_order' => 24 ),
		'contact-us' => array( 'title' => 'Contact Us', 'menu_order' => 5 ),
	);
	
	$created_pages = array();
	
	// Process each HTML file
	foreach ( $html_files as $html_file ) {
		// Get filename without extension
		$filename = basename( $html_file, '.html' );
		$slug = sanitize_title( $filename );
		
		// Skip if slug is empty
		if ( empty( $slug ) ) {
			continue;
		}
		
		// Check if page already exists
		$existing_page = get_page_by_path( $slug, OBJECT, 'page' );
		
		if ( $existing_page ) {
			// Page already exists, add to created_pages array for menu building
			$created_pages[] = array(
				'id' => $existing_page->ID,
				'title' => $existing_page->post_title,
				'slug' => $slug,
				'menu_order' => isset( $page_config[ $slug ]['menu_order'] ) ? $page_config[ $slug ]['menu_order'] : 99,
			);
			continue;
		}
		
		// Read HTML content
		$html_content = file_get_contents( $html_file );
		
		if ( false === $html_content ) {
			// Failed to read file, skip
			continue;
		}
		
		// Convert paths in HTML content
		$html_content = marble_elementor_theme_convert_html_paths( $html_content );
		
		// Sanitize HTML content while preserving Elementor attributes
		// Use wp_kses with extended allowed tags for Elementor
		$allowed_html = wp_kses_allowed_html( 'post' );
		
		// Add Elementor-specific attributes to allowed tags
		foreach ( $allowed_html as $tag => $attributes ) {
			$allowed_html[ $tag ]['data-elementor-type'] = true;
			$allowed_html[ $tag ]['data-elementor-id'] = true;
			$allowed_html[ $tag ]['data-elementor-post-type'] = true;
			$allowed_html[ $tag ]['data-element_type'] = true;
			$allowed_html[ $tag ]['data-id'] = true;
			$allowed_html[ $tag ]['data-widget_type'] = true;
			$allowed_html[ $tag ]['data-settings'] = true;
			$allowed_html[ $tag ]['class'] = true;
			$allowed_html[ $tag ]['style'] = true;
		}
		
		// Sanitize with extended allowed HTML
		$sanitized_content = wp_kses( $html_content, $allowed_html );
		
		// Get page title and menu order
		$page_title = isset( $page_config[ $slug ]['title'] ) ? $page_config[ $slug ]['title'] : marble_elementor_theme_slug_to_title( $slug );
		$menu_order = isset( $page_config[ $slug ]['menu_order'] ) ? $page_config[ $slug ]['menu_order'] : 99;
		
		// Prepare page data
		$page_data = array(
			'post_type'    => 'page',
			'post_title'   => $page_title,
			'post_name'    => $slug,
			'post_content' => $sanitized_content,
			'post_status'  => 'publish',
			'post_author'  => 1, // Default to admin user
			'menu_order'   => $menu_order,
			'meta_input'   => array(
				// Add meta to indicate this was auto-created
				'_marble_auto_created' => '1',
				'_marble_source_file'  => basename( $html_file ),
			),
		);
		
		// Insert the page
		$page_id = wp_insert_post( $page_data, true );
		
		if ( is_wp_error( $page_id ) ) {
			// Log error but continue with other pages
			// Optionally log: error_log( 'Marble Theme: Failed to create page ' . $slug . ' - ' . $page_id->get_error_message() );
			continue;
		}
		
		// Ensure post is properly saved
		if ( ! $page_id || $page_id === 0 ) {
			continue;
		}
		
		$created_pages[] = array(
			'id' => $page_id,
			'title' => $page_title,
			'slug' => $slug,
			'menu_order' => $menu_order,
		);
		
		// Set home page if this is the home page
		if ( 'home' === $slug ) {
			update_option( 'page_on_front', $page_id );
			update_option( 'show_on_front', 'page' );
		}
	}
	
	// Store created pages info in transient (for admin notice and menu creation)
	if ( ! empty( $created_pages ) ) {
		set_transient( 'marble_theme_pages_created', $created_pages, 300 );
	}
	
	// Create navigation menu with all pages
	marble_elementor_theme_create_navigation_menu( $created_pages );
	
	// Flush rewrite rules to ensure new pages are accessible
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'marble_elementor_theme_create_pages_on_activation' );

/**
 * Automatically create navigation menu with all pages
 */
function marble_elementor_theme_create_navigation_menu( $pages = array() ) {
	// If no pages provided, try to get from transient or query all pages
	if ( empty( $pages ) ) {
		$pages = get_transient( 'marble_theme_pages_created' );
		
		if ( empty( $pages ) ) {
			// Query all auto-created pages
			$all_pages = get_pages( array(
				'meta_key'   => '_marble_auto_created',
				'meta_value' => '1',
				'post_type'  => 'page',
				'post_status' => 'publish',
			) );
			
			if ( empty( $all_pages ) ) {
				return;
			}
			
			// Convert to our format
			foreach ( $all_pages as $page ) {
				$pages[] = array(
					'id' => $page->ID,
					'title' => $page->post_title,
					'slug' => $page->post_name,
					'menu_order' => $page->menu_order,
				);
			}
		}
	}
	
	if ( empty( $pages ) ) {
		return;
	}
	
	// Sort pages by menu_order
	usort( $pages, function( $a, $b ) {
		$order_a = isset( $a['menu_order'] ) ? $a['menu_order'] : 99;
		$order_b = isset( $b['menu_order'] ) ? $b['menu_order'] : 99;
		return $order_a - $order_b;
	} );
	
	// Menu name
	$menu_name = 'Main Navigation';
	
	// Check if menu already exists
	$menu_exists = wp_get_nav_menu_object( $menu_name );
	
	if ( ! $menu_exists ) {
		// Create the menu
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu_exists->term_id;
	}
	
	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		return;
	}
	
	// Define main menu items (top level) and service submenu items
	$main_items = array( 'home', 'about-us', 'services', 'gallery', 'contact-us' );
	$service_items = array(
		'marble-natural-stone-restoration',
		'marble-repair-restoration',
		'marble-refinishing-care-maintenance',
		'natural-stones-care-maintenance',
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
	
	// Track created menu items and their IDs
	$menu_item_ids = array();
	
	// Add pages to menu
	foreach ( $pages as $page ) {
		$slug = $page['slug'];
		
		// Determine if this is a top-level menu item
		if ( in_array( $slug, $main_items, true ) ) {
			// Add top-level menu item
			$menu_item_data = array(
				'menu-item-object-id'   => $page['id'],
				'menu-item-object'      => 'page',
				'menu-item-type'        => 'post_type',
				'menu-item-status'      => 'publish',
				'menu-item-title'       => $page['title'],
				'menu-item-parent-id'   => 0,
			);
			
			$menu_item_id = wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );
			
			if ( ! is_wp_error( $menu_item_id ) ) {
				$menu_item_ids[ $slug ] = $menu_item_id;
			}
		}
	}
	
	// Add service items as submenu items under Services (if Services exists)
	if ( isset( $menu_item_ids['services'] ) ) {
		$services_parent_id = $menu_item_ids['services'];
		
		foreach ( $pages as $page ) {
			$slug = $page['slug'];
			
			if ( in_array( $slug, $service_items, true ) ) {
				$menu_item_data = array(
					'menu-item-object-id'   => $page['id'],
					'menu-item-object'      => 'page',
					'menu-item-type'        => 'post_type',
					'menu-item-status'      => 'publish',
					'menu-item-title'       => $page['title'],
					'menu-item-parent-id'   => $services_parent_id,
				);
				
				wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );
			}
		}
	}
	
	// Optionally add location pages (not in menu by default to avoid clutter)
	// You can uncomment below to add them
	/*
	foreach ( $pages as $page ) {
		$slug = $page['slug'];
		
		if ( in_array( $slug, $location_items, true ) ) {
			$menu_item_data = array(
				'menu-item-object-id'   => $page['id'],
				'menu-item-object'      => 'page',
				'menu-item-type'        => 'post_type',
				'menu-item-status'      => 'publish',
				'menu-item-title'       => $page['title'],
				'menu-item-parent-id'   => 0,
			);
			
			wp_update_nav_menu_item( $menu_id, 0, $menu_item_data );
		}
	}
	*/
	
	// Assign menu to theme location
	$locations = get_theme_mod( 'nav_menu_locations' );
	if ( ! is_array( $locations ) ) {
		$locations = array();
	}
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Manual menu rebuild function (can be called from admin or hooks)
 */
function marble_elementor_theme_rebuild_menu() {
	marble_elementor_theme_create_navigation_menu();
}

/**
 * Admin notice to show created pages
 */
function marble_elementor_theme_activation_notice() {
	$created_pages = get_transient( 'marble_theme_pages_created' );
	
	if ( $created_pages && ! empty( $created_pages ) ) {
		$count = count( $created_pages );
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong>Marble Elementor Theme:</strong> 
				<?php 
				printf( 
					esc_html( _n( 
						'%d page has been automatically created from your HTML files.', 
						'%d pages have been automatically created from your HTML files.', 
						$count,
						'marble-elementor-theme' 
					) ),
					$count
				);
				?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">
					<?php esc_html_e( 'View Pages', 'marble-elementor-theme' ); ?>
				</a>
			</p>
		</div>
		<?php
		// Delete transient after showing
		delete_transient( 'marble_theme_pages_created' );
	}
}
add_action( 'admin_notices', 'marble_elementor_theme_activation_notice' );

/**
 * Ensure Elementor can edit pages properly
 */
function marble_elementor_theme_content_support() {
	// This ensures Elementor can work with the theme
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}
	
	// Elementor will handle the content rendering
}
add_action( 'wp', 'marble_elementor_theme_content_support' );

