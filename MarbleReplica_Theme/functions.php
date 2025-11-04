<?php
/**
 * Marble Replica theme bootstrap.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'MARBLE_REPLICA_VERSION', '1.0.0' );
define( 'MARBLE_REPLICA_THEME_DIR', __DIR__ );
define( 'MARBLE_REPLICA_THEME_URI', get_template_directory_uri() );

require_once MARBLE_REPLICA_THEME_DIR . '/inc/navigation.php';
require_once MARBLE_REPLICA_THEME_DIR . '/inc/importer.php';

function marble_replica_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'elementor' );
    add_theme_support( 'align-wide' );

    register_nav_menus(
        [
            'primary'   => __( 'Primary Menu', 'marble-replica' ),
            'secondary' => __( 'Secondary Menu', 'marble-replica' ),
            'mobile'    => __( 'Mobile Menu', 'marble-replica' ),
            'footer'    => __( 'Footer Menu', 'marble-replica' ),
        ]
    );
}
add_action( 'after_setup_theme', 'marble_replica_setup' );

function marble_replica_enqueue_assets() {
    wp_enqueue_style( 'marble-replica-style', get_stylesheet_uri(), [], MARBLE_REPLICA_VERSION );

    $manifest = get_option( 'marble_replica_manifest', [] );
    if ( empty( $manifest['css_files'] ) || ! is_array( $manifest['css_files'] ) ) {
        return;
    }

    $uploads = wp_get_upload_dir();
    if ( empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
        return;
    }

    foreach ( array_unique( $manifest['css_files'] ) as $css_file ) {
        if ( ! $css_file ) {
            continue;
        }

        $relative = 'imported/elementor/css/' . ltrim( $css_file, '/' );
        $path     = trailingslashit( $uploads['basedir'] ) . $relative;

        if ( ! file_exists( $path ) ) {
            continue;
        }

        $handle = 'marble-replica-' . sanitize_title( $css_file );
        $src    = trailingslashit( $uploads['baseurl'] ) . $relative;
        wp_enqueue_style( $handle, $src, [], null );
    }
}
add_action( 'wp_enqueue_scripts', 'marble_replica_enqueue_assets' );

add_action( 'after_switch_theme', [ 'MarbleReplica\\Importer', 'maybe_run' ] );
