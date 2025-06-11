<?php
/**
 * Template Name: Affiliate Registration
 */
get_header();

if (is_user_logged_in()) {
    echo do_shortcode('[affiliate_dashboard]');
} else {
    echo do_shortcode('[affiliate_register]');
}

get_footer();