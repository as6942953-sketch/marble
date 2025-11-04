<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        the_content();
    }
} else {
    echo '<p>' . esc_html__( 'No content found.', 'marble-replica' ) . '</p>';
}

get_footer();
