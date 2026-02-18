<?php
/**
 * Theme functions and definitions
 */

if (!function_exists('inscricao_enfrute_setup')):
    function inscricao_enfrute_setup()
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
        add_theme_support('customize-selective-refresh-widgets');

        register_nav_menus(array(
            'primary' => __('Primary Menu', 'inscricao-enfrute'),
        ));
    }
endif;
add_action('after_setup_theme', 'inscricao_enfrute_setup');

function inscricao_enfrute_scripts()
{
    wp_enqueue_style('inscricao-enfrute-style', get_stylesheet_uri(), array(), '1.0.0');
    // Enqueue compiled assets
    wp_enqueue_style('inscricao-enfrute-main', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.0');
    wp_enqueue_script('inscricao-enfrute-js', get_template_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'inscricao_enfrute_scripts');
