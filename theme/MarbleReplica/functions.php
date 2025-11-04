<?php
/**
 * Marble Replica Theme functions
 */

if ( ! defined( 'MARBLE_REPLICA_VERSION' ) ) {
    define( 'MARBLE_REPLICA_VERSION', '1.0.0' );
}

if ( ! defined( 'MARBLE_REPLICA_THEME_DIR' ) ) {
    define( 'MARBLE_REPLICA_THEME_DIR', get_template_directory() );
}

if ( ! defined( 'MARBLE_REPLICA_DATA_PATH' ) ) {
    define( 'MARBLE_REPLICA_DATA_PATH', MARBLE_REPLICA_THEME_DIR . '/data/import_payload.json' );
}

if ( ! defined( 'MARBLE_REPLICA_ASSETS_DIR' ) ) {
    define( 'MARBLE_REPLICA_ASSETS_DIR', MARBLE_REPLICA_THEME_DIR . '/assets' );
}

/**
 * Theme supports and setup.
 */
function marble_replica_setup(): void {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'html5', [ 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'align-wide' );
    add_theme_support( 'responsive-embeds' );

    register_nav_menus(
        [
            'primary'   => __( 'Primary Navigation', 'marble-replica' ),
            'secondary' => __( 'Secondary Navigation', 'marble-replica' ),
        ]
    );
}
add_action( 'after_setup_theme', 'marble_replica_setup' );

/**
 * Enqueue base assets.
 */
function marble_replica_enqueue_assets(): void {
    wp_enqueue_style( 'marble-replica-style', get_stylesheet_uri(), [], MARBLE_REPLICA_VERSION );
}
add_action( 'wp_enqueue_scripts', 'marble_replica_enqueue_assets' );

/**
 * Handle Elementor data import on activation.
 */
function marble_replica_run_import(): void {
    if ( get_option( 'marble_replica_import_completed' ) ) {
        return;
    }

    if ( ! file_exists( MARBLE_REPLICA_DATA_PATH ) ) {
        return;
    }

    $payload = json_decode( file_get_contents( MARBLE_REPLICA_DATA_PATH ), true );
    if ( empty( $payload ) || empty( $payload['pages'] ) ) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/post.php';
    require_once ABSPATH . 'wp-admin/includes/taxonomy.php';

    marble_replica_copy_theme_uploads();

    $imported_pages     = marble_replica_import_pages( $payload['pages'] );
    $imported_templates = [];

    if ( ! empty( $payload['templates'] ) && is_array( $payload['templates'] ) ) {
        $imported_templates = marble_replica_import_templates( $payload['templates'] );
    }

    if ( isset( $imported_pages['11'] ) ) {
        update_option( 'page_on_front', $imported_pages['11'] );
        update_option( 'show_on_front', 'page' );
    }

    update_option(
        'marble_replica_import_completed',
        [
            'timestamp' => time(),
            'pages'     => count( $imported_pages ),
            'templates' => count( $imported_templates ),
        ]
    );

    if ( class_exists( '\\Elementor\\Plugin' ) ) {
        \Elementor\Plugin::$instance->files_manager->clear_cache();
    }

    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'marble_replica_run_import' );

/**
 * Copy bundled uploads assets into the WordPress uploads directory.
 */
function marble_replica_copy_theme_uploads(): void {
    $source = trailingslashit( MARBLE_REPLICA_ASSETS_DIR ) . 'uploads';

    if ( ! is_dir( $source ) ) {
        return;
    }

    $uploads = wp_get_upload_dir();
    if ( empty( $uploads['basedir'] ) ) {
        return;
    }

    marble_replica_recursive_copy( $source, trailingslashit( $uploads['basedir'] ) );
}

/**
 * Import Elementor pages from payload.
 *
 * @param array $pages
 * @return array<string,int> Map of original IDs to created post IDs.
 */
function marble_replica_import_pages( array $pages ): array {
    $imported = [];

    foreach ( $pages as $original_id => $page ) {
        $post_id = marble_replica_upsert_post( (int) $original_id, $page, 'page', 'wp-page' );
        if ( $post_id ) {
            $imported[ (string) $original_id ] = $post_id;
        }
    }

    return $imported;
}

/**
 * Import Elementor templates (header/footer).
 *
 * @param array $templates
 * @return array<string,int>
 */
function marble_replica_import_templates( array $templates ): array {
    $imported = [];

    foreach ( $templates as $original_id => $template ) {
        $template_type = $template['document_type'] ?? 'section';
        $post_id       = marble_replica_upsert_post( (int) $original_id, $template, 'elementor_library', $template_type, ucfirst( $template_type ) . ' Template ' . $original_id );
        if ( $post_id ) {
            $imported[ (string) $original_id ] = $post_id;
        }
    }

    return $imported;
}

/**
 * Create or update a post and attach Elementor data.
 *
 * @param int         $original_id Original ID captured from source site.
 * @param array       $data        Payload data for post.
 * @param string      $post_type   Target post type.
 * @param string      $template    Elementor template type (wp-page, header, footer, etc.).
 * @param string|null $fallback_title Optional fallback post title.
 *
 * @return int|false
 */
function marble_replica_upsert_post( int $original_id, array $data, string $post_type, string $template, ?string $fallback_title = null ) {
    $desired_slug = $data['slug'] ?? '';
    $desired_slug = $desired_slug ?: ( 'page' === $post_type ? 'home' : sanitize_title( $fallback_title ?? 'template-' . $original_id ) );

    $existing = get_post( $original_id );
    if ( ! $existing ) {
        $existing = get_page_by_path( $desired_slug, OBJECT, $post_type );
    }

    $postarr = [
        'post_type'   => $post_type,
        'post_status' => 'publish',
        'post_title'  => wp_strip_all_tags( $data['title'] ?? ( $fallback_title ?? $desired_slug ) ),
        'post_name'   => $desired_slug,
        'post_content'=> '',
    ];

    if ( $existing ) {
        $postarr['ID'] = $existing->ID;
    } elseif ( $original_id > 0 ) {
        $postarr['import_id'] = $original_id;
    }

    $post_id = wp_insert_post( $postarr, true );
    if ( is_wp_error( $post_id ) || ! $post_id ) {
        return false;
    }

    marble_replica_store_elementor_meta( $post_id, $data['elements'] ?? [], $template );

    return $post_id;
}

/**
 * Persist Elementor meta for a post.
 *
 * @param int    $post_id
 * @param array  $elements
 * @param string $template_type
 */
function marble_replica_store_elementor_meta( int $post_id, array $elements, string $template_type ): void {
    $json = wp_json_encode( $elements );
    if ( false === $json ) {
        return;
    }

    update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
    update_post_meta( $post_id, '_elementor_version', '3.32.2' );
    update_post_meta( $post_id, '_elementor_template_type', $template_type );
    update_post_meta( $post_id, '_elementor_data', wp_slash( $json ) );
    delete_post_meta( $post_id, '_elementor_css' );
}

/**
 * Simple admin notice summarising import results.
 */
function marble_replica_admin_notice(): void {
    if ( ! current_user_can( 'switch_themes' ) ) {
        return;
    }

    $status = get_option( 'marble_replica_import_completed' );
    if ( empty( $status ) ) {
        return;
    }

    printf(
        '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
        sprintf(
            /* translators: 1: number of pages, 2: number of templates. */
            esc_html__( 'Marble Replica Theme imported %1$d pages and %2$d templates. Review Elementor templates to assign global header and footer if needed.', 'marble-replica' ),
            (int) $status['pages'],
            (int) $status['templates']
        )
    );
}
add_action( 'admin_notices', 'marble_replica_admin_notice' );

/**
 * Recursively copy files from source to destination if they do not already exist.
 *
 * @param string $source
 * @param string $destination
 */
function marble_replica_recursive_copy( string $source, string $destination ): void {
    if ( ! class_exists( 'RecursiveDirectoryIterator' ) || ! class_exists( 'FilesystemIterator' ) ) {
        return;
    }

    $source      = trailingslashit( wp_normalize_path( $source ) );
    $destination = trailingslashit( wp_normalize_path( $destination ) );

    if ( ! is_dir( $source ) ) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $source, FilesystemIterator::SKIP_DOTS ),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ( $iterator as $item ) {
        $target = str_replace( $source, $destination, wp_normalize_path( $item->getPathname() ) );

        if ( $item->isDir() ) {
            wp_mkdir_p( $target );
            continue;
        }

        if ( file_exists( $target ) ) {
            continue;
        }

        wp_mkdir_p( dirname( $target ) );
        copy( $item->getPathname(), $target );
    }
}

