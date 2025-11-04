<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$manifest  = get_option( 'marble_replica_manifest', [] );
$footer_id = 0;
if ( is_array( $manifest ) && ! empty( $manifest['templates']['footer'] ) ) {
    $footer_id = (int) $manifest['templates']['footer'];
}

?>
</main>

<?php
if ( $footer_id ) {
    echo do_shortcode( '[elementor-template id="' . $footer_id . '"]' );
} else {
    ?>
    <footer class="marble-replica-footer">
        <p>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
    </footer>
    <?php
}
?>

<?php wp_footer(); ?>
</body>
</html>
