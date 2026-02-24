<?php
/**
 * Template Name: Dashboard do Revisor
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('sciflow_review')) {
    wp_redirect(home_url());
    exit;
}

get_header();

$reviewer_id = get_current_user_id();

$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;

// Specific Event Checks
$is_senco_role = in_array('sciflow_senco_editor', $user_roles) || in_array('sciflow_senco_revisor', $user_roles);
$is_enfrute_role = in_array('sciflow_enfrute_editor', $user_roles) || in_array('sciflow_enfrute_revisor', $user_roles);

// Filter Post Types
$post_types = array('enfrute_trabalhos', 'senco_trabalhos');
if ($is_senco_role && !$is_enfrute_role) {
    $post_types = array('senco_trabalhos');
} elseif ($is_enfrute_role && !$is_senco_role) {
    $post_types = array('enfrute_trabalhos');
}

$args = array(
    'post_type' => $post_types,
    'posts_per_page' => -1,
    'post_status' => ($is_senco_role || $is_enfrute_role) ? 'publish' : 'any',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => '_sciflow_reviewer_id',
            'value' => $reviewer_id,
            'compare' => '='
        )
    )
);

// Exclude Drafts for event roles
if ($is_senco_role || $is_enfrute_role) {
    $args['meta_query'][] = array(
        'key' => '_sciflow_status',
        'value' => 'rascunho',
        'compare' => '!=',
    );
}

$query = new WP_Query($args);
?>

<main class="sciflow-submissions-list py-5">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h2 fw-900 text-dark mb-1">Painel do Revisor</h1>
                <p class="text-muted mb-0">Trabalhos atribuídos a você para avaliação técnica.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark border p-2 px-3 rounded-pill fw-bold">
                    <i class="bi bi-clipboard-check me-1"></i>
                    <?php echo esc_html($query->found_posts); ?> Atribuições
                </span>
            </div>
        </div>

        <?php if ($query->have_posts()): ?>
            <div class="sciflow-table-container shadow-sm rounded-4 overflow-hidden bg-white mt-4 border">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 sciflow-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase fs-xs fw-bold text-muted">ID</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Título do Trabalho</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Evento</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Status Atual</th>
                                <th class="pe-4 py-3 text-uppercase fs-xs fw-bold text-muted text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()):
                                $query->the_post();
                                $post_id = get_the_ID();
                                $event_slug = get_post_meta($post_id, '_sciflow_event', true);
                                $event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Senco';

                                // SciFlow Specific Status
                                $sciflow_status = get_post_meta($post_id, '_sciflow_status', true);
                                if (!$sciflow_status)
                                    $sciflow_status = 'rascunho';

                                $status_labels = array(
                                    'em_avaliacao' => 'Em Sua Avaliação',
                                    'aguardando_decisao' => 'Aguardando Decisão do Editor',
                                    'em_correcao' => 'Em Correção pelo Autor',
                                    'aprovado' => 'Trabalho Aprovado',
                                    'reprovado' => 'Trabalho Reprovado',
                                    'aprovado_com_consideracoes' => 'Aprovado com Considerações',
                                    'apto_revisao' => 'Apto para Revisão',
                                    'apto_publicacao' => 'Apto para Publicação',
                                );

                                $badge_classes = array(
                                    'em_avaliacao' => 'bg-info text-white',
                                    'aguardando_decisao' => 'bg-primary text-white',
                                    'em_correcao' => 'bg-secondary text-white',
                                    'aprovado' => 'sciflow-badge--published',
                                    'reprovado' => 'bg-danger text-white',
                                );

                                $status_label = isset($status_labels[$sciflow_status]) ? $status_labels[$sciflow_status] : $sciflow_status;
                                $badge_class = isset($badge_classes[$sciflow_status]) ? $badge_classes[$sciflow_status] : 'bg-light';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-muted small">#
                                            <?php echo esc_html($post_id); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="sciflow-table-title fw-bold text-dark">
                                            <?php the_title(); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i
                                                class="bi <?php echo ($event_slug === 'enfrute') ? 'bi-journal-bookmark text-success' : 'bi-journal-text text-primary'; ?> me-2"></i>
                                            <span class="small fw-semibold">
                                                <?php echo esc_html($event_name); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="sciflow-table-badge <?php echo $badge_class; ?>">
                                            <?php echo esc_html($status_label); ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php
                                        $detail_page = get_pages(array('meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/template-article-detail.php'));
                                        $detail_url = !empty($detail_page) ? get_permalink($detail_page[0]->ID) : home_url('/avaliar-artigo'); // Fallback slug
                                        $view_url = add_query_arg('article_id', $post_id, $detail_url);
                                        ?>
                                        <a href="<?php echo esc_url($view_url); ?>"
                                            class="btn btn-sm btn-light rounded-pill px-3 fw-bold sciflow-table-edit">
                                            <i class="bi bi-pencil-square me-1"></i> Avaliar
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="sciflow-empty-state text-center py-5 shadow-sm rounded-4 bg-white border mt-4">
                <div class="sciflow-empty-icon mb-4">
                    <i class="bi bi-shield-check display-1 text-light"></i>
                </div>
                <h2 class="h3 fw-bold mb-3">Nenhum trabalho atribuído</h2>
                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">
                    Você ainda não possui trabalhos atribuídos para revisão. O editor notificará você assim que houver novos
                    itens.
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>