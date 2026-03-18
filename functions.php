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
            'palestrantes' => __('Menu Palestrantes', 'inscricao-enfrute'),
            'editor_dashboard' => __('Menu do Editor', 'inscricao-enfrute'),
            'reviewer_dashboard' => __('Menu do Revisor', 'inscricao-enfrute'),
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
        $is_editor = false;
        $editor_roles = array(
            'sciflow_enfrute_editor',
            'sciflow_semco_editor',
            'administrator'
        );

        foreach ($editor_roles as $role) {
            if (in_array($role, $roles)) {
                $is_editor = true;
                break;
            }
        }

        $is_reviewer = false;
        $reviewer_roles = array(
            'sciflow_enfrute_revisor',
            'sciflow_semco_revisor',
        );

        foreach ($reviewer_roles as $role) {
            if (in_array($role, $roles)) {
                $is_reviewer = true;
                break;
            }
        }

        if ($is_editor && has_nav_menu('editor_dashboard')) {
            $args['theme_location'] = 'editor_dashboard';
        } elseif ($is_reviewer && has_nav_menu('reviewer_dashboard')) {
            $args['theme_location'] = 'reviewer_dashboard';
        } elseif (!$is_editor && !$is_reviewer && in_array('sciflow_speaker', $roles) && has_nav_menu('palestrantes')) {
            $args['theme_location'] = 'palestrantes';
        } elseif (!$is_editor && !$is_reviewer && in_array('sciflow_inscrito', $roles) && has_nav_menu('inscritos')) {
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

            // International payment notice
            function checkInternationalPayment() {
                var country = $('#billing_country, [name="billing_country"], .wc-block-components-address-form__country select').val();
                if (country && country !== 'BR') {
                    var noticeText = 'For international registrants, the international payment process will be implemented shortly. Please contact the organizing team for further instructions.';
                    
                    // Main notice at top
                    if (!$('#enfrute-international-notice').length) {
                        var mainNoticeHtml = '<div id="enfrute-international-notice" class="woocommerce-info enfrute-dynamic-notice">' + noticeText + '</div>';
                        $('.woocommerce-before-checkout-form, .wc-block-checkout__before').first().prepend(mainNoticeHtml);
                    }
                    
                    // Payment area notice
                    if (!$('#payment-loc-enfrute-international-notice').length) {
                        var paymentNoticeHtml = '<div id="payment-loc-enfrute-international-notice" class="woocommerce-info enfrute-dynamic-notice">' + noticeText + '</div>';
                        var $paymentArea = $('.woocommerce-checkout-payment, .wc-block-checkout__payment-method').first();
                        if ($paymentArea.length) {
                             $paymentArea.before(paymentNoticeHtml);
                        }
                    }
                } else {
                    $('.enfrute-dynamic-notice').remove();
                }
            }

            $(document.body).on('change updated_checkout', '#billing_country, [name="billing_country"], .wc-block-components-address-form__country select', function() {
                checkInternationalPayment();
            });
            checkInternationalPayment();

            // Safety poll for dynamic block loading
            var pollCount = 0;
            var safetyPoll = setInterval(function () {
                toggleBrazilFields();
                applyMasks();
                checkInternationalPayment();
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

        #enfrute-international-notice {
            margin-bottom: 25px;
            border-left: 4px solid #3CAC34;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
    <?php
}
add_action('wp_footer', 'enfrute_checkout_js');

/**
 * PHP fallback notice for international payments.
 */
add_action('woocommerce_before_checkout_form', 'enfrute_international_payment_notice_php', 5);
function enfrute_international_payment_notice_php() {
    $customer_country = WC()->customer->get_billing_country();
    if ($customer_country && $customer_country !== 'BR') {
       wc_print_notice(
           __('For international registrants, the international payment process will be implemented shortly. Please contact the organizing team for further instructions.', 'enfrute'),
           'notice'
       );
    }
}

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

/**
 * Check if the current (or given) user has a completed/processing registration order.
 * Administrators and editors always pass.
 *
 * @param int|null $user_id  Defaults to current user.
 * @return bool
 */
function enfrute_user_has_paid_registration($user_id = null)
{
    if (!function_exists('wc_get_orders')) {
        return false;
    }

    if (null === $user_id) {
        $user_id = get_current_user_id();
    }

    if (!$user_id) {
        return false;
    }

    // Admins and editors always have access
    $user = get_userdata($user_id);
    if ($user) {
        $bypass_roles = array('administrator', 'sciflow_editor', 'sciflow_enfrute_editor', 'sciflow_semco_editor', 'sciflow_review', 'sciflow_revisor', 'sciflow_enfrute_revisor', 'sciflow_semco_revisor');
        foreach ($bypass_roles as $role) {
            if (in_array($role, (array) $user->roles, true)) {
                return true;
            }
        }
    }

    // Get configured product IDs
    $settings    = get_option('sciflow_settings', array());
    $raw_ids     = $settings['woo_product_ids'] ?? '';
    $product_ids = array_filter(array_map('absint', explode(',', $raw_ids)));

    if (empty($product_ids)) {
        // If no product configured, allow access
        return true;
    }

    // Look for a completed or processing order for this user
    $orders = wc_get_orders(array(
        'customer_id' => $user_id,
        'status'      => array('wc-completed', 'wc-processing'),
        'limit'       => -1,
    ));

    foreach ($orders as $order) {
        foreach ($order->get_items() as $item) {
            $pid = absint($item->get_product_id());
            if (in_array($pid, $product_ids, true)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Get the URL of the registration home page (template-home-inscription).
 */
function enfrute_get_inscription_home_url()
{
    $pages = get_pages(array(
        'meta_key'   => '_wp_page_template',
        'meta_value' => 'page-templates/template-home-inscription.php',
    ));
    if (!empty($pages)) {
        return get_permalink($pages[0]->ID);
    }
    return home_url('/inscricao');
}


/**
 * Filter available payment gateways based on backorder status.
 * If any item is "sob encomenda", only allow manual payment (BACS).
 */
add_filter('woocommerce_available_payment_gateways', 'enfrute_restrict_gateways_for_backorder');
function enfrute_restrict_gateways_for_backorder($available_gateways)
{
    if (is_admin()) {
        return $available_gateways;
    }

    $customer_country = WC()->customer ? WC()->customer->get_billing_country() : '';
    $has_backorder = false;
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product->is_on_backorder($cart_item['quantity'])) {
                $has_backorder = true;
                break;
            }
        }
    }

    // 1. Handling for international vs domestic
    if (!empty($customer_country) && strtoupper($customer_country) !== 'BR') {
        // International: Only allow PayPal
        foreach ($available_gateways as $gateway_id => $gateway) {
            if ($gateway_id !== 'paypal') {
                unset($available_gateways[$gateway_id]);
            }
        }
    } else {
        // Brazil: Hide PayPal
        unset($available_gateways['paypal']);
    }

    // 2. If backorder and Brazil, only keep "bacs" (Solicitar Reserva)
    if ($has_backorder && (empty($customer_country) || strtoupper($customer_country) === 'BR')) {
        $new_gateways = array();
        if (isset($available_gateways['bacs'])) {
            $new_gateways['bacs'] = $available_gateways['bacs'];
        }
        $available_gateways = $new_gateways;
    }

    return $available_gateways;
}

/**
 * Rename the BACS gateway title and description when backorder is active.
 */
add_filter('woocommerce_gateway_title', 'enfrute_rename_bacs_gateway_title', 10, 2);
function enfrute_rename_bacs_gateway_title($title, $gateway_id)
{
    if ($gateway_id === 'bacs') {
        $has_backorder = false;
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                if ($product->is_on_backorder($cart_item['quantity'])) {
                    $has_backorder = true;
                    break;
                }
            }
        }
        if ($has_backorder) {
            return __('Solicitar Inscrição (Análise Manual/Reserva)', 'enfrute');
        }
    }
    return $title;
}

add_filter('woocommerce_gateway_description', 'enfrute_rename_bacs_gateway_desc', 10, 2);
function enfrute_rename_bacs_gateway_desc($description, $gateway_id)
{
    if ($gateway_id === 'bacs') {
        $has_backorder = false;
        if (WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item) {
                $product = $cart_item['data'];
                if ($product->is_on_backorder($cart_item['quantity'])) {
                    $has_backorder = true;
                    break;
                }
            }
        }
        if ($has_backorder) {
            return __('Seu pedido será enviado para análise da equipe. Você receberá um e-mail com a confirmação após a aprovação manual.', 'enfrute');
        }
    }
    return $description;
}

/**
 * Change Checkout button text for backorders.
 */
add_filter('woocommerce_order_button_text', 'enfrute_backorder_button_text');
function enfrute_backorder_button_text($button_text)
{
    $has_backorder = false;
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product->is_on_backorder($cart_item['quantity'])) {
                $has_backorder = true;
                break;
            }
        }
    }

    if ($has_backorder) {
        return __('Enviar para Aprovação Manual', 'enfrute');
    }

    return $button_text;
}

/**
 * Show notice on checkout when backorder restriction is active.
 */
add_action('woocommerce_before_checkout_form', 'enfrute_backorder_checkout_notice');
function enfrute_backorder_checkout_notice()
{
    $has_backorder = false;
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            $product = $cart_item['data'];
            if ($product->is_on_backorder($cart_item['quantity'])) {
                $has_backorder = true;
                break;
            }
        }
    }

    if ($has_backorder) {
        wc_print_notice(
            __('Este item está disponível apenas mediante solicitação e aprovação externa. Por favor, finalize o pedido para que nossa equipe possa analisar sua inscrição.', 'enfrute'),
            'notice'
        );
    }
}

/**
 * Helper: check if an order contains a backordered product.
 */
function enfrute_order_has_backorder($order)
{
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        if ($product && $product->is_on_backorder($item->get_quantity())) {
            return true;
        }
    }
    return false;
}

/**
 * Force order status to "on-hold" for orders with backordered items.
 * Fires on classic checkout.
 */
add_action('woocommerce_checkout_order_processed', 'enfrute_force_on_hold_for_backorders', 99, 3);
function enfrute_force_on_hold_for_backorders($order_id, $posted_data, $order)
{
    if (enfrute_order_has_backorder($order)) {
        $order->update_status('on-hold', __('Pedido com item sob encomenda aguardando aprovação manual.', 'enfrute'));
        $order->save();
    }
}

/**
 * Force order status to "on-hold" for backorders via Store API (Block Checkout).
 */
add_action('woocommerce_store_api_checkout_order_processed', 'enfrute_force_on_hold_for_backorders_store_api', 99, 1);
function enfrute_force_on_hold_for_backorders_store_api($order)
{
    if (enfrute_order_has_backorder($order)) {
        $order->update_status('on-hold', __('Pedido com item sob encomenda aguardando aprovação manual.', 'enfrute'));
        $order->save();
    }
}

/**
 * Prevent payment_complete from moving a backorder order to processing/completed.
 * This is the most critical hook — fires regardless of gateway.
 */
add_filter('woocommerce_payment_complete_order_status', 'enfrute_prevent_completion_for_backorders', 99, 3);
function enfrute_prevent_completion_for_backorders($status, $order_id, $order)
{
    if (enfrute_order_has_backorder($order)) {
        return 'on-hold';
    }
    return $status;
}

/**
 * Specifically for BACS (Bank Transfer), ensure it stays on-hold for backorders.
 */
add_filter('woocommerce_bacs_process_payment_order_status', 'enfrute_bacs_status_for_backorders', 99, 2);
function enfrute_bacs_status_for_backorders($status, $order)
{
    if (enfrute_order_has_backorder($order)) {
        return 'on-hold';
    }
    return $status;
}

/**
 * Also intercept Sicredi/PayGo gateways to keep on-hold if somehow they are used.
 * Bypasses for admins doing manual status changes in the backend.
 */
add_action('woocommerce_order_status_changed', 'enfrute_revert_backorder_status_if_completed', 99, 4);
function enfrute_revert_backorder_status_if_completed($order_id, $old_status, $new_status, $order)
{
    // Allow manual status changes from the WP Admin panel by Shop Managers/Admins
    if (is_admin() && current_user_can('edit_shop_orders')) {
        return;
    }

    // If an order with backorder items tries to go to processing or completed via frontend/gateway, revert it.
    if (in_array($new_status, array('processing', 'completed'), true) && enfrute_order_has_backorder($order)) {
        // Remove action temporarily to avoid infinite loop
        remove_action('woocommerce_order_status_changed', 'enfrute_revert_backorder_status_if_completed', 99);
        $order->update_status('on-hold', __('Status revertido: pedido com item sob encomenda aguarda aprovação manual.', 'enfrute'));
        $order->save();
        add_action('woocommerce_order_status_changed', 'enfrute_revert_backorder_status_if_completed', 99, 4);
    }
}

/**
 * Show a clear "awaiting approval" message on the thank-you page for backorder orders.
 */
add_action('woocommerce_thankyou', 'enfrute_backorder_thankyou_notice', 5);
function enfrute_backorder_thankyou_notice($order_id)
{
    $order = wc_get_order($order_id);
    if (!$order || !enfrute_order_has_backorder($order)) {
        return;
    }
    echo '<div class="woocommerce-info" style="margin-bottom:20px;border-left:4px solid #f0ad4e;background:#fff8e6;padding:16px;border-radius:6px;">';
    echo '<strong>' . esc_html__('Solicitação de inscrição enviada!', 'enfrute') . '</strong><br>';
    echo esc_html__('Seu pedido foi enviado para análise manual pela nossa equipe. Você receberá um e-mail assim que sua inscrição for aprovada. Não é necessário realizar nenhum pagamento agora.', 'enfrute');
    echo '</div>';
}

/**
 * Remove "Informação Adicional" tab from single product page.
 */
add_filter('woocommerce_product_tabs', 'enfrute_remove_product_tabs', 98);
function enfrute_remove_product_tabs($tabs)
{
    unset($tabs['additional_information']);
    return $tabs;
}

/**
 * Remove product meta (SKU, categories, tags) from single product page.
 */
add_action('init', 'enfrute_remove_product_meta');
function enfrute_remove_product_meta()
{
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
}

/**
 * Remove PayPal Smart Buttons from Single Product Page.
 */
add_action('init', 'enfrute_remove_paypal_buttons_from_product_page');
function enfrute_remove_paypal_buttons_from_product_page() {
    if (class_exists('WC_Gateway_Paypal')) {
        remove_action('woocommerce_after_add_to_cart_form', array(WC_Gateway_Paypal::get_instance(), 'render_buttons_container'));
    }
}

