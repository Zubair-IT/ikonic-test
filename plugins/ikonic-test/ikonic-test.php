<?php
/*
Plugin Name: IKONIC TEST
Description: A basic plugin for Ikonic Task.
Version: 1.0
Author: Muhammad Zubair
Author URI: https://www.linkedin.com/in/muhammad-zubair01/
*/

defined('ABSPATH') || exit;


//******************* Redirecting users away having IPs with 77.29 //*******************
add_action('template_redirect', 'hs_block_ip_prefix');
function hs_block_ip_prefix() {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    if (strpos($user_ip, '77.29') === 0) {
        wp_redirect( 'https://www.google.com' );// I could stop(exit) or die the code here too like ("access denied").
        exit;
    }
}


//******************* Registering "Projects" post type and "Project Type" taxonomy *******************//
add_action('init', 'hs_register_projects');
function hs_register_projects() {
    register_post_type('projects', [
        'labels' => [
            'name' => 'Projects',
            'singular_name' => 'Project'
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'projects'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);

    register_taxonomy('project_type', 'projects', [
        'labels' => [
            'name' => 'Project Types',
            'singular_name' => 'Project Type',
        ],
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
    ]);
}


//******************* Limiting archive page projects posts to 6 *******************//
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('projects')) {
        $query->set('posts_per_page', 6);
    }
});


//******************* Creating ajax endpoint for returning projects data in json *******************//
add_action('wp_ajax_hs_get_architecture_projects', 'hs_get_architecture_projects');
add_action('wp_ajax_nopriv_hs_get_architecture_projects', 'hs_get_architecture_projects');

function hs_get_architecture_projects() {
    $count = is_user_logged_in() ? 6 : 3;

    $query = new WP_Query([
        'post_type' => 'projects',
        'posts_per_page' => $count,
        'tax_query' => [[
            'taxonomy' => 'project_type',
            'field' => 'slug',
            'terms' => 'architecture',
        ]]
    ]);

    $projects = [];

    foreach ($query->posts as $post) {
        $projects[] = [
            'id'    => $post->ID,
            'title' => get_the_title($post),
            'link'  => get_permalink($post),
        ];
    }

    wp_send_json(['success' => true, 'data' => $projects]);
}


//******************* Creating function for getting img url from Random Coffee API *******************//
function hs_give_me_coffee() {
    // #note: I got this link from the same website but I think it is not exact same link which I found in the task.
    $res = wp_remote_get('https://coffee.alexflipnote.dev/random.json');
    if (is_wp_error($res)) return 'No link found';
    $img_link = json_decode(wp_remote_retrieve_body($res), true)['file'] ?? '';
    return $img_link ? "<a href='$img_link' target='_blank'>Get your coffee ☕</a>" : 'No coffee link found';
}
add_shortcode('coffee_image', 'hs_give_me_coffee');


//******************* Creating function for outputting 5 quotes *******************//
function hs_kanye_quotes_shortcode() {
    $output = '<div class="kanye-quotes">';
    for ($i = 0; $i < 5; $i++) {
        $res = wp_remote_get('https://api.kanye.rest/');
        $quote = (!is_wp_error($res)) ? json_decode(wp_remote_retrieve_body($res), true)['quote'] ?? 'No quote.' : 'Could not fetch quote.';
        $output .= '<p>🧠 "' . esc_html($quote) . '"</p>';
    }
    return $output . '</div>';
}
add_shortcode('kanye_quotes', 'hs_kanye_quotes_shortcode');

