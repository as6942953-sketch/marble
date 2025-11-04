<?php
namespace MarbleReplica;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function register_shortcodes() {
    add_shortcode( 'marble_menu', __NAMESPACE__ . '\\render_menu_shortcode' );
}
add_action( 'init', __NAMESPACE__ . '\\register_shortcodes' );

function render_menu_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'slug'   => '',
            'layout' => 'primary',
        ],
        $atts,
        'marble_menu'
    );

    if ( empty( $atts['slug'] ) ) {
        return '';
    }

    $menu = wp_get_nav_menu_object( $atts['slug'] );
    if ( ! $menu ) {
        $menu = wp_get_nav_menu_object( sanitize_title( $atts['slug'] ) );
    }

    if ( ! $menu ) {
        return '';
    }

    $items = wp_get_nav_menu_items( $menu->term_id );
    if ( empty( $items ) ) {
        return '';
    }

    $tree = build_menu_tree( $items );
    ob_start();
    ?>
    <nav class="ekit-wid-con ekit_menu_responsive_tablet" data-hamburger-icon="" data-hamburger-icon-type="icon" data-responsive-breakpoint="1024">
        <button class="elementskit-menu-hamburger elementskit-menu-toggler" type="button" aria-label="hamburger-icon">
            <span class="elementskit-menu-hamburger-icon"></span>
            <span class="elementskit-menu-hamburger-icon"></span>
            <span class="elementskit-menu-hamburger-icon"></span>
        </button>
        <?php render_menu_list( $tree, $atts['layout'] ); ?>
        <div class="elementskit-nav-identity-panel"><button class="elementskit-menu-close elementskit-menu-toggler" type="button">X</button></div>
        <div class="elementskit-menu-overlay elementskit-menu-offcanvas-elements elementskit-menu-toggler ekit-nav-menu--overlay"></div>
    </nav>
    <?php
    return ob_get_clean();
}

function build_menu_tree( $items ) {
    $tree = [];
    $references = [];

    foreach ( $items as $item ) {
        $item->children = [];
        $references[ $item->ID ] = $item;
    }

    foreach ( $references as $item ) {
        if ( $item->menu_item_parent ) {
            if ( isset( $references[ $item->menu_item_parent ] ) ) {
                $references[ $item->menu_item_parent ]->children[] = $item;
            }
        } else {
            $tree[] = $item;
        }
    }

    return $tree;
}

function render_menu_list( $items, $layout = 'primary', $depth = 0 ) {
    if ( empty( $items ) ) {
        return;
    }

    $list_classes = [ 'elementskit-navbar-nav', 'elementskit-menu-po-left', 'submenu-click-on-icon' ];
    if ( $depth > 0 ) {
        $list_classes = [ 'elementskit-dropdown', 'elementskit-submenu-panel' ];
    }

    printf( '<ul class="%s">', esc_attr( implode( ' ', $list_classes ) ) );

    foreach ( $items as $item ) {
        $url   = ! empty( $item->url ) ? $item->url : '#';
        $title = esc_html( $item->title );
        $has_children = ! empty( $item->children );

        $classes = [ 'menu-item', 'nav-item', 'elementskit-mobile-builder-content' ];
        if ( $has_children ) {
            $classes[] = 'menu-item-has-children';
            $classes[] = 'elementskit-dropdown-has';
            $classes[] = 'relative_position';
            $classes[] = 'elementskit-dropdown-menu-default_width';
        }

        printf( '<li class="%s">', esc_attr( implode( ' ', $classes ) ) );

        $link_classes = $has_children ? 'ekit-menu-nav-link ekit-menu-dropdown-toggle' : 'ekit-menu-nav-link';
        printf( '<a class="%s" href="%s">%s', esc_attr( $link_classes ), esc_url( $url ), $title );
        if ( $has_children ) {
            echo '<i aria-hidden="true" class="icon icon-down-arrow1 elementskit-submenu-indicator"></i>';
        }
        echo '</a>';

        if ( $has_children ) {
            render_menu_list( $item->children, $layout, $depth + 1 );
        }

        echo '</li>';
    }

    echo '</ul>';
}
