<?php
/**
 * Template Name: Inscrição de Terceiros
 */

get_header();

$user = wp_get_current_user();
$roles = (array) $user->roles;

// Check permissions
if (!is_user_logged_in() || (!in_array('administrator', $roles) && !in_array('inscritor_terceiros', $roles))) {
    echo '<div class="container my-5"><div class="alert alert-danger">Você não tem permissão para acessar esta página.</div></div>';
    get_footer();
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terceiros_submit'])) {
    
    // Verify nonce for security
    if (!isset($_POST['terceiros_nonce']) || !wp_verify_nonce($_POST['terceiros_nonce'], 'terceiros_action')) {
        $error = 'Requisição inválida (nonce falhou).';
    } else {
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $cpf = sanitize_text_field($_POST['cpf']);
        $phone = sanitize_text_field($_POST['phone']);
        
        $address_1 = sanitize_text_field($_POST['address_1']);
        $number = sanitize_text_field($_POST['number']);
        $neighborhood = sanitize_text_field($_POST['neighborhood']);
        $city = sanitize_text_field($_POST['city']);
        $state = sanitize_text_field($_POST['state']);
        $postcode = sanitize_text_field($_POST['postcode']);
        
        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($cpf) || empty($product_id) || empty($coupon_code)) {
            $error = 'Por favor, preencha os campos obrigatórios (Nome, Sobrenome, E-mail, CPF, Categoria e Cupom).';
        } elseif (email_exists($email)) {
            $error = 'Este e-mail já está cadastrado.';
        } else {
            // Check if CPF exists in user meta
            global $wpdb;
            $cpf_exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_billing_cpf' AND meta_value = %s LIMIT 1", $cpf));
            
            if (!$cpf_exists) {
                // Try also without underscore, depending on how it's stored
                $cpf_exists = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'billing_cpf' AND meta_value = %s LIMIT 1", $cpf));
            }

            if ($cpf_exists) {
                $error = 'Este CPF já está cadastrado em outra conta.';
            } else {
                // Valida o cupom antes de criar o usuário e o pedido
                $coupon = new WC_Coupon($coupon_code);
                if (!$coupon->get_id()) {
                    $error = 'O cupom inserido não existe.';
                } elseif (!$coupon->is_valid()) {
                    $error = 'O cupom inserido é inválido, expirou ou atingiu o limite de uso.';
                } else {
                    $product = wc_get_product($product_id);
                    if (!$product) {
                        $error = 'Produto inválido selecionado.';
                    } else {
                        // 1. Create WordPress User
                        $username = sanitize_user(current(explode('@', $email)), true);
                        $append = 1;
                        $orig_username = $username;
                        while (username_exists($username)) {
                            $username = $orig_username . $append;
                            $append++;
                        }

                        $random_password = wp_generate_password(12, false);
                        $new_user_id = wp_create_user($username, $random_password, $email);

                        if (is_wp_error($new_user_id)) {
                            $error = 'Erro ao criar usuário: ' . $new_user_id->get_error_message();
                        } else {
                            // Update user meta
                            update_user_meta($new_user_id, 'first_name', $first_name);
                            update_user_meta($new_user_id, 'last_name', $last_name);
                            update_user_meta($new_user_id, 'billing_first_name', $first_name);
                            update_user_meta($new_user_id, 'billing_last_name', $last_name);
                            update_user_meta($new_user_id, 'billing_email', $email);
                            update_user_meta($new_user_id, 'billing_cpf', $cpf);
                            update_user_meta($new_user_id, '_billing_cpf', $cpf);
                            update_user_meta($new_user_id, 'billing_phone', $phone);
                            
                            update_user_meta($new_user_id, 'billing_address_1', $address_1);
                            update_user_meta($new_user_id, 'billing_number', $number);
                            update_user_meta($new_user_id, 'billing_neighborhood', $neighborhood);
                            update_user_meta($new_user_id, 'billing_city', $city);
                            update_user_meta($new_user_id, 'billing_state', $state);
                            update_user_meta($new_user_id, 'billing_postcode', $postcode);
                            update_user_meta($new_user_id, 'billing_country', 'BR');

                            // Set standard user role (customer)
                            $new_user = new WP_User($new_user_id);
                            $new_user->set_role('customer');

                            // 2. Create WooCommerce Order
                            try {
                                $order = wc_create_order(array(
                                    'customer_id' => $new_user_id,
                                ));

                                $item_id = $order->add_product($product, 1);
                                
                                // Set billing address on order
                                $address = array(
                                    'first_name' => $first_name,
                                    'last_name'  => $last_name,
                                    'email'      => $email,
                                    'phone'      => $phone,
                                    'address_1'  => $address_1,
                                    'city'       => $city,
                                    'state'      => $state,
                                    'postcode'   => $postcode,
                                    'country'    => 'BR'
                                );
                                $order->set_address($address, 'billing');
                                
                                // Add custom metas to order
                                $order->update_meta_data('_billing_cpf', $cpf);
                                $order->update_meta_data('_billing_number', $number);
                                $order->update_meta_data('_billing_neighborhood', $neighborhood);

                                // Calculate totals BEFORE setting fee so base totals are generated
                                $order->calculate_totals();

                                // 3. APPLY COUPON INSTEAD OF ZEROING AUTOMATICALLY
                                $applied = $order->apply_coupon($coupon_code);
                                if (is_wp_error($applied)) {
                                    throw new Exception($applied->get_error_message());
                                }

                                $order->calculate_totals();

                                // Update status
                                if ($order->get_total() == 0) {
                                    $order->update_status('completed', 'Inscrição realizada por Inscritor de Terceiros com cupom integral.');
                                } else {
                                    $order->update_status('pending', 'Inscrição realizada por Inscritor de Terceiros. Cupom aplicado mas saldo não zerou.');
                                }

                                $receipt_url = $order->get_checkout_order_received_url();
                                $message = 'Inscrição para <strong>' . esc_html($first_name . ' ' . $last_name) . '</strong> criada com sucesso! O pedido e a conta foram gerados.';
                                $message .= '<br><br><a href="' . esc_url($receipt_url) . '" target="_blank" class="btn btn-success"><i class="bi bi-printer"></i> Imprimir Comprovante</a>';
                                
                                // Send new account email
                                wp_new_user_notification($new_user_id, null, 'both');
                                
                                // Reset vars so the form is clean for the next one
                                $_POST = array(); 
                                
                            } catch (Exception $e) {
                                $error = 'Erro ao processar o pedido: ' . $e->getMessage();
                                // Rollback: delete the created user
                                if (!function_exists('wp_delete_user')) {
                                    require_once(ABSPATH . 'wp-admin/includes/user.php');
                                }
                                wp_delete_user($new_user_id);
                            }
                        }
                    }
                }
            }
        }
    }
}

// Ensure old POST values are saved if error
function enfrute_post_val($key) {
    return isset($_POST[$key]) ? esc_attr($_POST[$key]) : '';
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Inscrição de Terceiros</h1>
            <p class="text-muted">Utilize este formulário para registrar participantes. É obrigatório inserir um código de cupom válido para concluir a inscrição.</p>
            
            <?php if (!empty($message)) : ?>
                <div class="alert alert-success"><?php echo wp_kses_post($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?php echo esc_html($error); ?></div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <?php wp_nonce_field('terceiros_action', 'terceiros_nonce'); ?>
                        
                        <h4 class="mb-3 border-bottom pb-2">Dados do Participante</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required value="<?php echo enfrute_post_val('first_name'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Sobrenome *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required value="<?php echo enfrute_post_val('last_name'); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo enfrute_post_val('email'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="cpf" class="form-label">CPF *</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" required placeholder="000.000.000-00" value="<?php echo enfrute_post_val('cpf'); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Telefone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo enfrute_post_val('phone'); ?>">
                            </div>
                        </div>

                        <h4 class="mb-3 border-bottom pb-2 mt-4">Endereço</h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="postcode" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="postcode" name="postcode" value="<?php echo enfrute_post_val('postcode'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="address_1" class="form-label">Rua / Logradouro</label>
                                <input type="text" class="form-control" id="address_1" name="address_1" value="<?php echo enfrute_post_val('address_1'); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="number" class="form-label">Número</label>
                                <input type="text" class="form-control" id="number" name="number" value="<?php echo enfrute_post_val('number'); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="neighborhood" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="neighborhood" name="neighborhood" value="<?php echo enfrute_post_val('neighborhood'); ?>">
                            </div>
                            <div class="col-md-5">
                                <label for="city" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo enfrute_post_val('city'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="state" class="form-label">Estado (UF)</label>
                                <input type="text" class="form-control" id="state" name="state" maxlength="2" placeholder="Ex: SC" value="<?php echo enfrute_post_val('state'); ?>">
                            </div>
                        </div>

                        <h4 class="mb-3 border-bottom pb-2 mt-4">Categoria de Inscrição</h4>
                        <div class="mb-4">
                            <label for="product_id" class="form-label">Selecione a Categoria *</label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">-- Selecione --</option>
                                <?php
                                if (function_exists('enfrute_get_registration_product')) {
                                    $reg_product = enfrute_get_registration_product();
                                    if ($reg_product) {
                                        if ($reg_product->is_type('variable')) {
                                            $variations = $reg_product->get_available_variations('objects');
                                            foreach ($variations as $variation) {
                                                $name = wc_get_formatted_variation($variation, true, false, false);
                                                if (empty($name)) {
                                                    $name = $variation->get_name();
                                                }
                                                $selected = (enfrute_post_val('product_id') == $variation->get_id()) ? 'selected' : '';
                                                echo '<option value="' . esc_attr($variation->get_id()) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                                            }
                                        } else {
                                            $selected = (enfrute_post_val('product_id') == $reg_product->get_id()) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($reg_product->get_id()) . '" ' . $selected . '>' . esc_html($reg_product->get_name()) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="coupon_code" class="form-label">Cupom de Desconto *</label>
                            <input type="text" class="form-control" id="coupon_code" name="coupon_code" required value="<?php echo enfrute_post_val('coupon_code'); ?>" placeholder="Digite o código do cupom">
                            <?php
                            $args = array(
                                'posts_per_page'   => -1,
                                'post_type'        => 'shop_coupon',
                                'post_status'      => 'publish',
                                'orderby'          => 'title',
                                'order'            => 'ASC',
                            );
                            $all_coupons = get_posts( $args );
                            $available_coupons = array();
                            foreach ( $all_coupons as $c_post ) {
                                $taxonomies = get_object_taxonomies('shop_coupon');
                                $is_inscritor = false;
                                foreach ($taxonomies as $tax) {
                                    if (has_term('inscritor', $tax, $c_post->ID) || has_term('Inscritor', $tax, $c_post->ID)) {
                                        $is_inscritor = true;
                                        break;
                                    }
                                }
                                
                                // Fallback: check se está no título ou descrição
                                if (!$is_inscritor && (stripos($c_post->post_excerpt, 'inscritor') !== false || stripos($c_post->post_title, 'inscritor') !== false)) {
                                    $is_inscritor = true;
                                }

                                if ($is_inscritor) {
                                    $coupon_obj = new WC_Coupon($c_post->ID);
                                    
                                    // Check expiração
                                    $expiry_date = $coupon_obj->get_date_expires();
                                    if ($expiry_date && $expiry_date->getTimestamp() < current_time('timestamp')) {
                                        continue;
                                    }
                                    
                                    // Check limite de uso
                                    $usage_limit = $coupon_obj->get_usage_limit();
                                    $usage_count = $coupon_obj->get_usage_count();
                                    if ($usage_limit > 0 && $usage_count >= $usage_limit) {
                                        continue;
                                    }

                                    $discount_type = $coupon_obj->get_discount_type();
                                    $amount = $coupon_obj->get_amount();
                                    $discount_text = ($discount_type == 'percent') ? floatval($amount) . '%' : 'R$ ' . number_format((float)$amount, 2, ',', '.');

                                    $available_coupons[] = '<code style="cursor:pointer;" onclick="document.getElementById(\'coupon_code\').value=\'' . esc_js($coupon_obj->get_code()) . '\'">' . esc_html($coupon_obj->get_code()) . ' (-' . $discount_text . ')</code>';
                                }
                            }

                            if (!empty($available_coupons)) {
                                echo '<div class="form-text mt-2"><i class="bi bi-ticket-perforated"></i> <strong>Cupons disponíveis:</strong> ' . implode(', ', $available_coupons) . ' <br><small>(Clique no cupom para preencher)</small></div>';
                            }
                            ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="terceiros_submit" class="btn btn-primary btn-lg">Realizar Inscrição</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
jQuery(document).ready(function($){
    if($('#cpf').length) { $('#cpf').mask('000.000.000-00'); }
    if($('#phone').length) { $('#phone').mask('(00) 00000-0000'); }
    if($('#postcode').length) { $('#postcode').mask('00000-000'); }
});
</script>

<?php get_footer(); ?>