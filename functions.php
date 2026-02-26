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


/**
 * Add CPF/CNPJ fields to WooCommerce Checkout for Brazil.
 */
function enfrute_add_brazilian_fields($fields)
{
    $fields['cpf'] = array(
        'label' => __('CPF', 'enfrute'),
        'placeholder' => '000.000.000-00',
        'required' => false,
        'class' => array('form-row-wide', 'sciflow-brazil-field'),
        'clear' => true,
        'priority' => 35,
    );

    $fields['cnpj'] = array(
        'label' => __('CNPJ', 'enfrute'),
        'placeholder' => '00.000.000/0000-00',
        'required' => false,
        'class' => array('form-row-wide', 'sciflow-brazil-field'),
        'clear' => true,
        'priority' => 36,
    );

    return $fields;
}
add_filter('woocommerce_billing_fields', 'enfrute_add_brazilian_fields');

/**
 * Register fields for WooCommerce Blocks (Checkout Block).
 */
add_action('init', function () {
    if (function_exists('woocommerce_blocks_register_checkout_field')) {
        woocommerce_blocks_register_checkout_field(
            array(
                'id' => 'billing_cpf',
                'label' => __('CPF', 'enfrute'),
                'location' => 'address',
                'type' => 'text',
                'required' => false,
                'additional_classes' => array('sciflow-brazil-field'),
            )
        );
        woocommerce_blocks_register_checkout_field(
            array(
                'id' => 'billing_cnpj',
                'label' => __('CNPJ', 'enfrute'),
                'location' => 'address',
                'type' => 'text',
                'required' => false,
                'additional_classes' => array('sciflow-brazil-field'),
            )
        );
    }
});

/**
 * Store API Validation and Meta Saving (for WooCommerce Blocks).
 */
add_action('woocommerce_store_api_checkout_update_order_meta', function ($order) {
    // Get the request data from the input stream
    $json_data = file_get_contents('php://input');
    if (!$json_data)
        return;

    $data = json_decode($json_data, true);
    if (!$data || empty($data['extensions']['woocommerce/checkout-fields']))
        return;

    $checkout_fields = $data['extensions']['woocommerce/checkout-fields'];

    $cpf = $checkout_fields['billing_cpf'] ?? '';
    $cnpj = $checkout_fields['billing_cnpj'] ?? '';

    // Save to order meta
    if (!empty($cpf)) {
        $order->update_meta_data('_billing_cpf', sanitize_text_field($cpf));
    }
    if (!empty($cnpj)) {
        $order->update_meta_data('_billing_cnpj', sanitize_text_field($cnpj));
    }
    // No need to call $order->save() here as it's handled by the Store API after this hook
}, 10, 1);

/**
 * Validate CPF/CNPJ.
 */
function enfrute_validate_brazilian_fields()
{
    $billing_country = $_POST['billing_country'] ?? '';

    if ($billing_country === 'BR') {
        $cpf = $_POST['billing_cpf'] ?? '';
        $cnpj = $_POST['billing_cnpj'] ?? '';

        if (empty($cpf) && empty($cnpj)) {
            wc_add_notice(__('Por favor, preencha o CPF ou CNPJ para faturamento no Brasil.', 'enfrute'), 'error');
        }
    }
}
add_action('woocommerce_checkout_process', 'enfrute_validate_brazilian_fields');

/**
 * Save CPF/CNPJ to order meta.
 */
add_action('woocommerce_checkout_update_order_meta', 'enfrute_save_brazilian_fields');
function enfrute_save_brazilian_fields($order_id)
{
    if (!empty($_POST['billing_cpf'])) {
        update_post_meta($order_id, '_billing_cpf', sanitize_text_field($_POST['billing_cpf']));
    }
    if (!empty($_POST['billing_cnpj'])) {
        update_post_meta($order_id, '_billing_cnpj', sanitize_text_field($_POST['billing_cnpj']));
    }
}

/**
 * JS to toggle Brazil fields and add masks.
 */
function enfrute_checkout_js()
{
    if (!is_checkout())
        return;
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script type="text/javascript">
        jQuery(function ($) {
            function toggleBrazilFields() {
                // Determine country (Classic vs Block)
                var country = $('#billing_country, [name="billing_country"], .wc-block-components-address-form__country select').val();

                // Fallback for Blocks text summary
                if (!country) {
                    var countryText = $('.wc-block-components-address-card__address-country').text().trim().toLowerCase();
                    if (countryText.indexOf('brasil') !== -1 || countryText.indexOf('brazil') !== -1) {
                        country = 'BR';
                    }
                }

                console.log('Enfrute Checkout: Country is ' + (country || 'unknown'));

                var $fields = $('.sciflow-brazil-field');
                if (country === 'BR') {
                    $fields.show().css('display', 'block');
                    // Ensure block wrapper is also visible
                    $fields.closest('.wc-block-components-address-form__field').show();
                } else {
                    $fields.hide().css('display', 'none');
                }
            }

            // Apply masks
            function applyMasks() {
                var $cpf = $('input[name="billing_cpf"], #billing_cpf, [id*="billing_cpf"]');
                var $cnpj = $('input[name="billing_cnpj"], #billing_cnpj, [id*="billing_cnpj"]');

                if ($cpf.length) $cpf.mask('000.000.000-00');
                if ($cnpj.length) $cnpj.mask('00.000.000/0000-00');
            }

            // Listen for any change in the country field (Standard/Blocks)
            $(document.body).on('updated_checkout change country_to_state_changing', '#billing_country, [name="billing_country"], .wc-block-components-address-form__country select', function () {
                toggleBrazilFields();
                applyMasks();
            });

            // Re-apply on general checkout updates (Blocks use a lot of async updates)
            $(document.body).on('updated_checkout', function () {
                toggleBrazilFields();
                applyMasks();
            });

            // Reactive subscription for WooCommerce Blocks
            if (window.wp && wp.data && wp.data.subscribe) {
                var lastState = null;
                wp.data.subscribe(function () {
                    // Primitive throttle/check
                    toggleBrazilFields();
                    applyMasks();
                });
            }

            // Initial runs
            toggleBrazilFields();
            applyMasks();

            // Safety poll for dynamic block loading
            var pollCount = 0;
            var safetyPoll = setInterval(function () {
                toggleBrazilFields();
                applyMasks();
                if (++pollCount > 10) clearInterval(safetyPoll);
            }, 1000);
        });
    </script>
    <style>
        .sciflow-brazil-field {
            display: none;
        }

        /* Ensure Block Checkout doesn't force-hide our injected row if it feels special */
        .wc-block-components-address-form .sciflow-brazil-field {
            margin-bottom: 24px;
        }
    </style>
    <?php
}
add_action('wp_footer', 'enfrute_checkout_js');

/**
 * Display CPF/CNPJ in order emails.
 */
add_filter('woocommerce_email_order_meta_fields', 'enfrute_email_order_meta_fields', 10, 3);
function enfrute_email_order_meta_fields($fields, $sent_to_admin, $order)
{
    if ($order->get_meta('_billing_cpf')) {
        $fields['billing_cpf'] = array(
            'label' => __('CPF', 'enfrute'),
            'value' => $order->get_meta('_billing_cpf'),
        );
    }
    if ($order->get_meta('_billing_cnpj')) {
        $fields['billing_cnpj'] = array(
            'label' => __('CNPJ', 'enfrute'),
            'value' => $order->get_meta('_billing_cnpj'),
        );
    }
    return $fields;
}

/**
 * Ensure fields are saved to order and displayed in admin.
 */
add_filter('woocommerce_admin_billing_fields', 'enfrute_admin_billing_fields');
function enfrute_admin_billing_fields($fields)
{
    $fields['cpf'] = array(
        'label' => __('CPF', 'enfrute'),
        'show' => true,
    );
    $fields['cnpj'] = array(
        'label' => __('CNPJ', 'enfrute'),
        'show' => true,
    );
    return $fields;
}

/**
 * Safely retrieve the registration product.
 */
function enfrute_get_registration_product()
{
    if (!function_exists('wc_get_product')) {
        return false;
    }

    $settings = get_option('sciflow_settings', array());
    $product_ids = explode(',', $settings['woo_product_ids'] ?? '');
    $product_id = !empty($product_ids[0]) ? absint($product_ids[0]) : 0;

    return $product_id ? wc_get_product($product_id) : false;
}
