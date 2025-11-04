<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$manifest   = get_option( 'marble_replica_manifest', [] );
$header_id  = 0;
if ( is_array( $manifest ) && ! empty( $manifest['templates']['header'] ) ) {
    $header_id = (int) $manifest['templates']['header'];
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
if ( $header_id ) {
    echo do_shortcode( '[elementor-template id="' . $header_id . '"]' );
} else {
    ?>
    <header class="marble-replica-header">
        <div class="site-branding">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
        </div>
    </header>
    <?php
}
?>

<main id="site-content" role="main">
