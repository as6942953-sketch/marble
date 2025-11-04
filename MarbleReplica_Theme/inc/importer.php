<?php
namespace MarbleReplica;

use WP_Post;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Importer {

    private const OPTION_FLAG    = 'marble_replica_import_complete';
    private const MANIFEST_OPTION = 'marble_replica_manifest';
    private const BLUEPRINT_PATH = MARBLE_REPLICA_THEME_DIR . '/assets/blueprint.json';

    private array $blueprint = [];
    private array $menus     = [];

    public static function maybe_run(): void {
        if ( get_option( self::OPTION_FLAG ) ) {
            return;
        }

        $importer = new self();
        $importer->run();
    }

    private function run(): void {
        $blueprint = $this->load_blueprint();
        if ( ! $blueprint ) {
            return;
        }

        $this->blueprint = $blueprint;

        $uploads = wp_get_upload_dir();
        if ( ! empty( $uploads['basedir'] ) ) {
            wp_mkdir_p( trailingslashit( $uploads['basedir'] ) . 'imported' );
        }

        $this->transform_navigation_widgets();

        $css_files = $this->collect_css_files();

        $imported_menus = $this->import_menus();
        $template_ids   = $this->import_templates();
        $page_ids       = $this->import_pages();

        $this->assign_front_page( $page_ids );

        if ( did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' ) ) {
            try {
                \Elementor\Plugin::$instance->files_manager->clear_cache();
            } catch ( \Throwable $e ) {
                // Ignore cache flush errors.
            }
        }

        update_option(
            self::MANIFEST_OPTION,
            [
                'css_files'   => $css_files,
                'templates'   => $template_ids,
                'menus'       => $imported_menus,
                'imported_at' => time(),
            ]
        );

        update_option( self::OPTION_FLAG, time() );
    }

    private function load_blueprint(): array {
        if ( ! file_exists( self::BLUEPRINT_PATH ) ) {
            return [];
        }

        $contents = file_get_contents( self::BLUEPRINT_PATH );
        if ( ! $contents ) {
            return [];
        }

        $data = json_decode( $contents, true );
        return is_array( $data ) ? $data : [];
    }

    private function collect_css_files(): array {
        $files = [ 'post-8.css' ]; // Global kit CSS

        $pages = $this->blueprint['pages'] ?? [];
        foreach ( $pages as $page ) {
            if ( ! empty( $page['elementor_id'] ) ) {
                $files[] = 'post-' . $page['elementor_id'] . '.css';
            }
        }

        $templates = $this->blueprint['templates'] ?? [];
        foreach ( $templates as $template ) {
            if ( ! empty( $template['elementor_id'] ) ) {
                $files[] = 'post-' . $template['elementor_id'] . '.css';
            }
        }

        $files = array_unique( array_filter( $files ) );
        sort( $files );

        return $files;
    }

    private function transform_navigation_widgets(): void {
        if ( empty( $this->blueprint['templates']['header']['elements'] ) ) {
            return;
        }

        $elements = &$this->blueprint['templates']['header']['elements'];
        $this->walk_elements( $elements, function ( array &$node ) {
            if ( 'widget' !== ( $node['elType'] ?? '' ) ) {
                return;
            }

            if ( 'html' !== ( $node['widgetType'] ?? '' ) ) {
                return;
            }

            $settings = $node['settings'] ?? [];
            $classes  = $settings['_css_classes'] ?? '';

            if ( false === strpos( $classes, 'elementor-widget-ekit-nav-menu' ) ) {
                return;
            }

            $html = $settings['content'] ?? '';
            if ( ! $html ) {
                return;
            }

            [ $menu_slug, $layout ] = $this->register_menu_blueprint( $html, $node['id'] ?? uniqid( 'menu_' ) );

            $node['widgetType'] = 'shortcode';
            $node['settings']['shortcode'] = sprintf( '[marble_menu slug="%s" layout="%s"]', $menu_slug, $layout ?: 'primary' );
            unset( $node['settings']['content'] );
        } );
    }

    private function import_menus(): array {
        $locations = get_theme_mod( 'nav_menu_locations', [] );
        $registered = [];

        foreach ( $this->menus as $menu ) {
            $slug = sanitize_title( $menu['slug'] );
            $name = $menu['name'];

            $menu_obj = wp_get_nav_menu_object( $slug );
            if ( $menu_obj ) {
                $menu_id = $menu_obj->term_id;
                wp_update_term( $menu_id, 'nav_menu', [ 'name' => $name, 'slug' => $slug ] );
                $existing_items = wp_get_nav_menu_items( $menu_id );
                if ( $existing_items ) {
                    foreach ( $existing_items as $item ) {
                        wp_delete_post( $item->ID, true );
                    }
                }
            } else {
                $menu_id = wp_create_nav_menu( $name );
                if ( is_wp_error( $menu_id ) ) {
                    continue;
                }
                wp_update_term( $menu_id, 'nav_menu', [ 'slug' => $slug ] );
            }

            $this->create_menu_items( (int) $menu_id, $menu['items'] );

            $registered[ $slug ] = (int) $menu_id;

            if ( isset( $menu['location'] ) ) {
                $locations[ $menu['location'] ] = (int) $menu_id;
            }
        }

        set_theme_mod( 'nav_menu_locations', $locations );

        return $registered;
    }

    private function import_templates(): array {
        $result = [];
        $templates = $this->blueprint['templates'] ?? [];
        foreach ( $templates as $key => $template ) {
            $slug = 'marble-replica-' . sanitize_title( $key );
            $title = ucwords( str_replace( '-', ' ', $key ) ) . ' Template';
            $post_id = $this->upsert_elementor_post(
                [
                    'post_type'   => 'elementor_library',
                    'post_status' => 'publish',
                    'post_title'  => $title,
                    'post_name'   => $slug,
                ],
                $template['elements'] ?? [],
                $template['elementor_id'] ?? null,
                $key
            );
            if ( $post_id ) {
                $result[ $key ] = $post_id;
            }
        }

        return $result;
    }

    private function import_pages(): array {
        $page_ids = [];
        $pages = $this->blueprint['pages'] ?? [];

        foreach ( $pages as $page ) {
            $slug  = sanitize_title( $page['slug'] ?? $page['source'] ?? uniqid( 'page_' ) );
            $title = $page['title'] ?? ucfirst( $slug );

            $args = [
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_title'  => $title,
                'post_name'   => $slug,
            ];

            $post_id = $this->upsert_elementor_post(
                $args,
                $page['elements'] ?? [],
                $page['elementor_id'] ?? null,
                'page'
            );

            if ( $post_id ) {
                $page_ids[ $slug ] = $post_id;
            }
        }

        return $page_ids;
    }

    private function upsert_elementor_post( array $post_args, array $elements, $elementor_id = null, string $template_type = 'page' ) {
        $existing = null;
        if ( ! empty( $post_args['post_name'] ) ) {
            $existing = get_page_by_path( $post_args['post_name'], OBJECT, $post_args['post_type'] );
        }

        if ( $existing instanceof WP_Post ) {
            $post_args['ID'] = $existing->ID;
            $post_id = wp_update_post( wp_slash( $post_args ), true );
        } else {
            $post_id = wp_insert_post( wp_slash( $post_args ), true );
        }

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return 0;
        }

        $data = $this->prepare_elementor_data( $elements );

        update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $data ) ) );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $post_id, '_elementor_version', '3.32.2' );

        if ( 'page' === $template_type ) {
            update_post_meta( $post_id, '_wp_page_template', 'default' );
        } else {
            update_post_meta( $post_id, '_elementor_template_type', $template_type );
        }

        update_post_meta( $post_id, '_elementor_css', '' );

        return $post_id;
    }

    private function prepare_elementor_data( array $elements ): array {
        $resolved = [];
        foreach ( $elements as $element ) {
            $element['settings'] = $this->resolve_value( $element['settings'] ?? [] );
            if ( ! empty( $element['elements'] ) ) {
                $element['elements'] = $this->prepare_elementor_data( $element['elements'] );
            }
            $resolved[] = $element;
        }
        return $resolved;
    }

    private function resolve_value( $value ) {
        if ( is_string( $value ) ) {
            return $this->replace_placeholders( $value );
        }

        if ( is_array( $value ) ) {
            $resolved = [];
            foreach ( $value as $key => $inner ) {
                $resolved[ $key ] = $this->resolve_value( $inner );
            }
            return $resolved;
        }

        return $value;
    }

    private function replace_placeholders( string $value ): string {
        $uploads = wp_get_upload_dir();

        return preg_replace_callback(
            '/\{\{(page|media):([^}]+)\}\}/',
            function ( $matches ) use ( $uploads ) {
                $type = $matches[1];
                $target = trim( $matches[2] );

                if ( 'page' === $type ) {
                    $page = get_page_by_path( $target );
                    if ( $page instanceof WP_Post ) {
                        return get_permalink( $page );
                    }
                    return home_url( '/' . $target . '/' );
                }

                if ( 'media' === $type ) {
                    return trailingslashit( $uploads['baseurl'] ) . 'imported/' . ltrim( $target, '/' );
                }

                return $matches[0];
            },
            $value
        );
    }

    private function assign_front_page( array $page_ids ): void {
        if ( empty( $page_ids['home'] ) ) {
            return;
        }

        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $page_ids['home'] );
        update_option( 'page_for_posts', 0 );
    }

    private function walk_elements( array &$elements, callable $callback ): void {
        foreach ( $elements as &$element ) {
            $callback( $element );
            if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
                $this->walk_elements( $element['elements'], $callback );
            }
        }
    }

    private function register_menu_blueprint( string $html, string $widget_id ): array {
        $document = new \DOMDocument();
        libxml_use_internal_errors( true );
        $document->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
        libxml_clear_errors();

        $uls = $document->getElementsByTagName( 'ul' );
        if ( ! $uls->length ) {
            return [ $widget_id, 'primary' ];
        }

        /** @var \DOMElement $root */
        $root = $uls->item( 0 );
        $items = $this->parse_dom_list( $root );

        $slug  = $root->getAttribute( 'id' );
        $slug  = $slug ? $slug : 'menu-' . $widget_id;
        $location = $this->infer_menu_location( $slug );

        $this->menus[] = [
            'slug'     => $slug,
            'name'     => ucwords( str_replace( '-', ' ', $slug ) ),
            'items'    => $items,
            'location' => $location,
        ];

        return [ $slug, $location ?: 'primary' ];
    }

    private function parse_dom_list( \DOMElement $ul ): array {
        $items = [];
        foreach ( $ul->childNodes as $child ) {
            if ( ! $child instanceof \DOMElement || 'li' !== $child->tagName ) {
                continue;
            }

            $link = null;
            foreach ( $child->childNodes as $node ) {
                if ( $node instanceof \DOMElement && 'a' === $node->tagName ) {
                    $link = $node;
                    break;
                }
            }

            $title = $link ? trim( $link->textContent ) : '';
            $href  = $link ? $link->getAttribute( 'href' ) : '';

            $item = [
                'title'    => $title,
                'href'     => $href,
                'children' => [],
            ];

            foreach ( $child->childNodes as $node ) {
                if ( $node instanceof \DOMElement && 'ul' === $node->tagName ) {
                    $item['children'] = $this->parse_dom_list( $node );
                    break;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    private function infer_menu_location( string $slug ): ?string {
        if ( false !== strpos( $slug, 'new-menu' ) ) {
            return 'mobile';
        }

        if ( false !== strpos( $slug, 'main-menu-1' ) ) {
            return 'primary';
        }

        if ( false !== strpos( $slug, 'main-menu' ) ) {
            return 'secondary';
        }

        if ( false !== strpos( $slug, 'services' ) ) {
            return 'footer';
        }

        return null;
    }

    private function create_menu_items( int $menu_id, array $items, int $parent = 0 ): void {
        foreach ( $items as $item ) {
            $menu_item_id = $this->add_menu_item( $menu_id, $item, $parent );
            if ( $menu_item_id && ! empty( $item['children'] ) ) {
                $this->create_menu_items( $menu_id, $item['children'], $menu_item_id );
            }
        }
    }

    private function add_menu_item( int $menu_id, array $item, int $parent = 0 ): int {
        $title = $item['title'] ?: 'Menu Item';
        $href  = $item['href'] ?? '';

        $page_id = null;
        if ( preg_match( '/\{\{page:([^}]+)\}\}/', $href, $matches ) ) {
            $page = get_page_by_path( $matches[1] );
            if ( $page instanceof WP_Post ) {
                $page_id = $page->ID;
            }
        }

        if ( $page_id ) {
            $args = [
                'menu-item-title'     => $title,
                'menu-item-object-id' => $page_id,
                'menu-item-object'    => 'page',
            'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $parent,
            ];
        } else {
            $resolved = $this->replace_placeholders( $href );
            $args     = [
                'menu-item-title'     => $title,
                'menu-item-url'       => $resolved,
                'menu-item-type'      => 'custom',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $parent,
            ];
        }

        $item_id = wp_update_nav_menu_item( $menu_id, 0, $args );

        return is_wp_error( $item_id ) ? 0 : (int) $item_id;
    }
}
