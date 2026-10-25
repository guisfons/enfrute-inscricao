<?php
/**
 * Integration with the SciFlow WP plugin and WooCommerce.
 *
 * The theme handles presentation. Business rules and data belong to the
 * plugin; these helpers let templates degrade gracefully when the plugin or
 * WooCommerce is missing, instead of triggering fatal errors.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Whether the SciFlow WP plugin is loaded.
 */
function inscricao_enfrute_sciflow_active()
{
    return defined('SCIFLOW_VERSION') && class_exists('SciFlow_Status_Manager');
}

/**
 * Whether WooCommerce is loaded.
 */
function inscricao_enfrute_woo_active()
{
    return class_exists('WooCommerce') && function_exists('WC');
}

/**
 * URL of a SciFlow page role, with a legacy slug as fallback.
 *
 * @param string $role     Page role (submission, author_dashboard, editor_dashboard, ...).
 * @param string $fallback Path used when the plugin is not active.
 */
function inscricao_enfrute_page_url($role, $fallback = '/')
{
    if (function_exists('sciflow_get_page_url')) {
        return sciflow_get_page_url($role);
    }
    return home_url($fallback);
}

/**
 * Render a full page with a notice and stop the template when a dependency
 * is missing. Returns true when the template must stop.
 *
 * Usage at the top of a template:
 *   if (inscricao_enfrute_require('sciflow')) { return; }
 *
 * @param string $what 'sciflow' | 'woocommerce' | 'both'
 */
function inscricao_enfrute_require($what = 'sciflow')
{
    $missing = array();
    if (in_array($what, array('sciflow', 'both'), true) && !inscricao_enfrute_sciflow_active()) {
        $missing[] = 'SciFlow WP';
    }
    if (in_array($what, array('woocommerce', 'both'), true) && !inscricao_enfrute_woo_active()) {
        $missing[] = 'WooCommerce';
    }
    if (!$missing) {
        return false;
    }

    get_header();
    echo '<main class="site-main"><div class="container my-5"><div class="alert alert-warning" role="alert">';
    echo esc_html__('Esta página está temporariamente indisponível.', 'inscricao-enfrute');
    if (current_user_can('activate_plugins')) {
        echo ' ' . esc_html(sprintf(
            /* translators: %s: plugin names */
            __('Plugin necessário inativo: %s.', 'inscricao-enfrute'),
            implode(', ', $missing)
        ));
    }
    echo '</div></div></main>';
    get_footer();
    return true;
}

/**
 * Admin notice when required/recommended plugins are missing.
 */
function inscricao_enfrute_dependency_notice()
{
    if (!current_user_can('activate_plugins')) {
        return;
    }
    if (!inscricao_enfrute_sciflow_active()) {
        echo '<div class="notice notice-error"><p>' . wp_kses_post(sprintf(
            /* translators: %s: plugins screen URL */
            __('O tema <strong>SciFlow Eventos</strong> depende do plugin <strong>SciFlow WP</strong>. Instale e ative o plugin em <a href="%s">Plugins</a>; sem ele as páginas de submissão, avaliação e painéis ficam indisponíveis.', 'inscricao-enfrute'),
            esc_url(admin_url('plugins.php'))
        )) . '</p></div>';
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!inscricao_enfrute_woo_active() && $screen && in_array($screen->id, array('themes', 'plugins', 'dashboard'), true)) {
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('SciFlow Eventos: o WooCommerce não está ativo. Inscrição paga, checkout com CPF/CNPJ e inscrição de terceiros ficam indisponíveis.', 'inscricao-enfrute') . '</p></div>';
    }
}
add_action('admin_notices', 'inscricao_enfrute_dependency_notice');

/**
 * On theme activation, open the SciFlow setup wizard if setup is not done yet.
 */
function inscricao_enfrute_on_activate()
{
    if (function_exists('sciflow_request_setup_wizard')) {
        sciflow_request_setup_wizard();
    }
}
add_action('after_switch_theme', 'inscricao_enfrute_on_activate');

/**
 * Event short name (configured in SciFlow), with a neutral fallback.
 *
 * @param string $slug 'enfrute' | 'semco'
 */
function inscricao_enfrute_event_label($slug)
{
    if (function_exists('sciflow_event_label')) {
        return sciflow_event_label($slug);
    }
    return ucfirst((string) $slug);
}
