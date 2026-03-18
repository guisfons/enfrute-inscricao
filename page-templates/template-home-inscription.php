<?php
/**
 * Template Name: Inscrição - Início
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$product = enfrute_get_registration_product();

// Detect if user was redirected from a restricted page
$inscricao_required = isset($_GET['inscricao_required']) && $_GET['inscricao_required'] === '1';

// Detect on-hold orders (awaiting manual approval) for logged-in users
$user_on_hold = false;
if (is_user_logged_in() && function_exists('wc_get_orders')) {
    $settings = get_option('sciflow_settings', array());
    $raw_ids = $settings['woo_product_ids'] ?? '';
    $product_ids = array_filter(array_map('absint', explode(',', $raw_ids)));

    if (!empty($product_ids)) {
        $on_hold_orders = wc_get_orders(array(
            'customer_id' => get_current_user_id(),
            'status' => array('wc-on-hold'),
            'limit' => -1,
        ));
        foreach ($on_hold_orders as $ord) {
            foreach ($ord->get_items() as $it) {
                if (in_array(absint($it->get_product_id()), $product_ids, true)) {
                    $user_on_hold = true;
                    break 2;
                }
            }
        }
    }
}

?>


<main class="sciflow-home-inscription min-vh-100 py-5 bg-white">
    <div class="container py-lg-5">

        <?php
// Notice for users redirected because they don't have a completed registration
if ($inscricao_required): ?>
        <div class="alert mb-4 px-4 py-3 rounded-3 d-flex align-items-start gap-3"
            style="background:#fff3cd;border:1px solid #ffc107;color:#856404;">
            <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 flex-shrink-0"></i>
            <div>
                <?php if ($user_on_hold): ?>
                <strong>Inscrição em análise</strong><br>
                Sua solicitação de inscrição está sendo analisada pela nossa equipe. Você receberá um e-mail assim que
                for aprovada. Para dúvidas, entre em contato com a organização.
                <?php
    else: ?>
                <strong>Inscrição necessária</strong><br>
                Você precisa ter uma inscrição confirmada para acessar essa página. Realize su inscrição abaixo ou
                aguarde a confirmação do seu pagamento.
                <?php
    endif; ?>
            </div>
        </div>
        <?php
endif; ?>

        <?php
// If user has a pending on-hold order, show status panel instead of purchase CTA
if ($user_on_hold): ?>
        <div class="text-center py-5">
            <div class="mb-4" style="font-size:5rem;">⏳</div>
            <h2 class="fw-900 text-dark mb-3">Inscrição em Análise</h2>
            <p class="text-muted fs-5 mb-4" style="max-width:520px;margin:0 auto;">
                Sua solicitação de inscrição foi recebida e está aguardando <strong>aprovação manual</strong> pela
                equipe organizadora.<br><br>
                Você receberá um e-mail de confirmação assim que sua inscrição for aprovada. Enquanto isso, ainda não é
                possível acessar o painel de submissões.
            </p>
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"
                class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                <i class="bi bi-receipt me-2"></i> Ver Meus Pedidos
            </a>
        </div>
        <?php
elseif ($product): ?>

        <div class="row align-items-center g-5">
            <!-- Product Visual -->
            <div class="col-lg-6 order-lg-2">
                <div class="product-image-container position-relative">
                    <div class="product-image-blob position-absolute translate-middle-x start-50 top-50"
                        style="width: 120%; height: 120%; background: radial-gradient(circle, rgba(13, 110, 67, 0.05) 0%, rgba(255,255,255,0) 70%); z-index: -1;">
                    </div>
                    <?php
    $image_id = $product->get_image_id();
    if ($image_id):
        $image_url = wp_get_attachment_image_url($image_id, 'large');
?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>"
                        class="img-fluid rounded-4 shadow-lg-hover transition-transform">
                    <?php
    else: ?>
                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center border"
                        style="aspect-ratio: 4/5;">
                        <i class="bi bi-journal-text display-1 text-muted opacity-25"></i>
                    </div>
                    <?php
    endif; ?>
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-lg-6 order-lg-1">
                <div class="pe-xl-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span
                            class="badge bg-success text-white px-3 py-2 rounded-pill fw-bold text-uppercase fs-xs">Inscrições
                            Abertas</span>
                        <span class="text-muted small fw-semibold"><i class="bi bi-shield-check text-success me-1"></i>
                            Ambiente Seguro</span>
                    </div>

                    <h1 class="display-4 fw-900 text-dark mb-4">
                        <?php echo esc_html($product->get_name()); ?>
                    </h1>

                    <div class="product-description text-muted fs-5 lh-base mb-5">
                        <?php echo $product->get_short_description() ?: $product->get_description(); ?>
                    </div>

                    <div class="features-list mb-5">
                        <ul class="list-unstyled">
                            <li class="d-flex align-items-start mb-3">
                                <div class="bg-success-subtle rounded-circle p-1 me-3 flex-shrink-0">
                                    <i class="bi bi-check2 text-success"></i>
                                </div>
                                <span class="text-dark">Acesso completo ao painel de submissão de trabalhos.</span>
                            </li>
                            <li class="d-flex align-items-start mb-3">
                                <div class="bg-success-subtle rounded-circle p-1 me-3 flex-shrink-0">
                                    <i class="bi bi-check2 text-success"></i>
                                </div>
                                <span class="text-dark">Feedback técnico de revisores especializados.</span>
                            </li>
                            <li class="d-flex align-items-start">
                                <div class="bg-success-subtle rounded-circle p-1 me-3 flex-shrink-0">
                                    <i class="bi bi-check2 text-success"></i>
                                </div>
                                <span class="text-dark">Participação garantida no evento selecionado.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="price-section d-flex align-items-center gap-4 mb-5">
                        <div>
                            <span class="text-muted small text-uppercase fw-bold d-block mb-1">Investimento</span>
                            <span class="display-5 fw-900 text-dark">
                                <?php
    if ($product->is_type('variable')) {
        $prices = $product->get_variation_prices(true);
        $max_price = !empty($prices['price']) ? max($prices['price']) : $product->get_price();
        echo wc_price($max_price);
    }
    else {
        echo $product->get_price_html();
    }
?>
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons d-grid d-md-flex gap-3">
                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>"
                            class="btn btn-success btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm transition-up">
                            <i class="bi bi-cart-plus me-2"></i> Inscrever-se Agora
                        </a>
                        <?php if (is_user_logged_in()): ?>
                        <a href="<?php echo esc_url(home_url('/meus-artigos')); ?>"
                            class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold">
                            Meus Resumos
                        </a>
                        <?php
    endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
else: ?>
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-1 text-warning mb-4"></i>
            <h2 class="fw-900 text-dark">Serviço Indisponível</h2>
            <p class="text-muted fs-5">Nenhum produto de inscrição foi localizado nas configurações.</p>
            <?php if (current_user_can('manage_options')): ?>
            <a href="<?php echo admin_url('admin.php?page=sciflow-settings'); ?>"
                class="btn btn-primary rounded-pill px-4 mt-3">Configurar SciFlow</a>
            <?php
    endif; ?>
        </div>
        <?php
endif; ?>
    </div>

    <!-- Trust Section -->
    <div class="bg-light mt-5 py-5 border-top border-bottom">
        <div class="container py-4">
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <i class="bi bi-lightning-charge display-6 text-success mb-3"></i>
                    <h3 class="h6 fw-bold text-dark">Submissão Rápida</h3>
                    <p class="text-muted small mb-0">Interface intuitiva para envio de seus trabalhos em poucos minutos.
                    </p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-award display-6 text-success mb-3"></i>
                    <h3 class="h6 fw-bold text-dark">Certificados Oficiais</h3>
                    <p class="text-muted small mb-0">Receba certificados digitais válidos após a aprovação e
                        apresentação.</p>
                </div>
                <div class="col-md-4">
                    <i class="bi bi-people display-6 text-success mb-3"></i>
                    <h3 class="h6 fw-bold text-dark">Networking</h3>
                    <p class="text-muted small mb-0">Conecte-se com pesquisadores e profissionais renomados da área.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .sciflow-home-inscription {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .fw-900 {
        font-weight: 900 !important;
    }

    .transition-up {
        transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .transition-up:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
    }

    .shadow-lg-hover {
        transition: box-shadow 0.3s ease;
    }

    .shadow-lg-hover:hover {
        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, 0.1) !important;
    }

    .transition-transform:hover {
        transform: scale(1.02);
    }

    .fs-xs {
        font-size: 0.75rem;
    }

    .bg-success-subtle {
        background-color: #e8f5e9 !important;
    }
</style>

<?php get_footer(); ?>