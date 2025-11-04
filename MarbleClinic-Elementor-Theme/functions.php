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
 * Enqueue Google Fonts
 */
function marble_elementor_theme_fonts() {
	// Enqueue Google Fonts (Roboto and Lora)
	wp_enqueue_style(
		'marble-google-fonts',
		'https://fonts.googleapis.com/css?family=Roboto:1,300,400,400italic,500,700,700italic|Lora:1,300,400,400italic,500,700,700italic&display=swap',
		array(),
		null
	);
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
	
	// Page mapping for titles (optional - can override auto-generated titles)
	$page_titles = array(
		'home' => 'Home',
		'about-us' => 'About Us',
		'services' => 'Services',
		'gallery' => 'Gallery',
		'contact-us' => 'Contact Us',
		'marble-natural-stone-restoration' => 'Marble Natural Stone Restoration',
		'marble-repair-restoration' => 'Marble Repair Restoration',
		'marble-refinishing-care-maintenance' => 'Marble Refinishing Care Maintenance',
		'natural-stones-care-maintenance' => 'Natural Stones Care Maintenance',
		'kitchen-island-countertops-and-refinishing' => 'Kitchen Island Countertops and Refinishing',
		'floors-counters-walls-maintenance' => 'Floors Counters Walls Maintenance',
		'beverly-hills-ca' => 'Beverly Hills, CA',
		'santa-monica-ca' => 'Santa Monica, CA',
		'brentwood-ca' => 'Brentwood, CA',
		'calabasas-ca' => 'Calabasas, CA',
		'studio-city-ca' => 'Studio City, CA',
	);
	
	$created_pages = array();
	
	// Process each HTML file
	foreach ( $html_files as $html_file ) {
		// Get filename without extension
		$filename = basename( $html_file, '.html' );
		$slug = $filename;
		
		// Skip if slug is empty
		if ( empty( $slug ) ) {
			continue;
		}
		
		// Check if page already exists
		$existing_page = get_page_by_path( $slug, OBJECT, 'page' );
		
		if ( $existing_page ) {
			// Page already exists, skip
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
		
		// Get page title
		$page_title = isset( $page_titles[ $slug ] ) ? $page_titles[ $slug ] : marble_elementor_theme_slug_to_title( $slug );
		
		// Special handling for home page
		if ( 'home' === $slug ) {
			$page_title = 'Home';
		}
		
		// Prepare page data
		$page_data = array(
			'post_type'    => 'page',
			'post_title'  => $page_title,
			'post_name'   => $slug,
			'post_content' => $sanitized_content,
			'post_status' => 'publish',
			'post_author' => 1, // Default to admin user
			'meta_input'  => array(
				// Add meta to indicate this was auto-created
				'_marble_auto_created' => '1',
				'_marble_source_file' => basename( $html_file ),
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
		);
		
		// Set home page if this is the home page
		if ( 'home' === $slug ) {
			update_option( 'page_on_front', $page_id );
			update_option( 'show_on_front', 'page' );
		}
	}
	
	// Store created pages info in transient (for admin notice if needed)
	if ( ! empty( $created_pages ) ) {
		set_transient( 'marble_theme_pages_created', $created_pages, 60 );
	}
	
	// Flush rewrite rules to ensure new pages are accessible
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'marble_elementor_theme_create_pages_on_activation' );

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

