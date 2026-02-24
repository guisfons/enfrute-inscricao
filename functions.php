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
        add_theme_support('woocommerce');

        register_nav_menus(array(
            'primary' => __('Primary Menu', 'inscricao-enfrute'),
            'inscritos' => __('Menu Inscritos', 'inscricao-enfrute'),
        ));

        // Ensure page attributes are supported
        add_post_type_support('page', 'page-attributes');
    }

endif;
add_action('after_setup_theme', 'inscricao_enfrute_setup');

function inscricao_enfrute_scripts()
{
    // Bootstrap 5 CSS
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');

    // Bootstrap Icons
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css', array(), '1.11.2');

    wp_enqueue_style('inscricao-enfrute-style', get_stylesheet_uri(), array(), '1.0.0');

    // Enqueue compiled assets (ensure it loads after bootstrap)
    wp_enqueue_style('inscricao-enfrute-main', get_template_directory_uri() . '/assets/css/main.css', array('bootstrap-css'), '1.0.0');

    // Bootstrap 5 JS Bundle
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true);

    wp_enqueue_script('inscricao-enfrute-js', get_template_directory_uri() . '/assets/js/main.js', array('jquery', 'bootstrap-js'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'inscricao_enfrute_scripts');

/**
 * Register page templates manually if they are not detected automatically.
 */
function inscricao_enfrute_register_templates($post_templates, $theme, $post, $post_type)
{
    if ('page' === $post_type) {
        $post_templates['page-templates/template-submission.php'] = 'Submissão de Artigo';
        $post_templates['page-templates/template-submissions-list.php'] = 'Listagem de Submissões';
        $post_templates['page-templates/template-editor-dashboard.php'] = 'Dashboard do Editor';
        $post_templates['page-templates/template-reviewer-dashboard.php'] = 'Dashboard do Revisor';
        $post_templates['page-templates/template-article-detail.php'] = 'Detalhes do Artigo (Review)';
        $post_templates['page-templates/template-home-inscription.php'] = 'Inscrição - Início';

    }

    return $post_templates;
}
add_filter('theme_templates', 'inscricao_enfrute_register_templates', 10, 4);

/**
 * Switch menu location based on user role.
 */
function inscricao_enfrute_nav_menu_args($args)
{
    if ($args['theme_location'] === 'primary' && is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;

        // Editor and Revisor roles use 'primary' (default)
        $is_editor_revisor = false;
        $special_roles = array(
            'sciflow_enfrute_editor',
            'sciflow_senco_editor',
            'sciflow_enfrute_revisor',
            'sciflow_senco_revisor',
            'administrator'
        );

        foreach ($special_roles as $role) {
            if (in_array($role, $roles)) {
                $is_editor_revisor = true;
                break;
            }
        }

        if (!$is_editor_revisor && in_array('sciflow_inscrito', $roles)) {
            $args['theme_location'] = 'inscritos';
        }
    }
    return $args;
}
add_filter('wp_nav_menu_args', 'inscricao_enfrute_nav_menu_args');


