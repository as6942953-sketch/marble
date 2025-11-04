<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="error-404 not-found">
    <h1><?php esc_html_e( 'Page not found', 'marble-replica' ); ?></h1>
    <p><?php esc_html_e( 'The page you are looking for could not be found. Please use the navigation menu.', 'marble-replica' ); ?></p>
</section>

<?php
get_footer();
