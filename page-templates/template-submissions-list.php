<?php
/**
 * Template Name: Listagem de Submissões
 */

get_header();

$user_id = get_current_user_id();

if (!$user_id) {
    echo '<div class="container py-5"><p>Por favor, faça login para ver suas submissões.</p></div>';
    get_footer();
    return;
}

$args = array(
    'post_type' => array('enfrute_trabalhos', 'senco_trabalhos'),
    'author' => $user_id,
    'posts_per_page' => -1,
    'post_status' => array('publish', 'draft', 'pending', 'future'),
    'meta_query' => array(
        array(
            'key' => '_sciflow_status',
            'value' => array('submetido', 'em_avaliacao', 'aguardando_decisao'),
            'compare' => 'NOT IN',
        ),
    ),
);

$query = new WP_Query($args);
?>

<main class="sciflow-submissions-list py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2 mb-0">
                <?php esc_html_e('Meus Trabalhos', 'enfrute'); ?>
            </h1>
            <a href="<?php echo esc_url(home_url('/submissao')); ?>" class="sciflow-btn sciflow-btn--primary">
                <?php esc_html_e('Nova Submissão', 'enfrute'); ?>
            </a>
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
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted text-center">Data</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Status</th>
                                <th class="pe-4 py-3 text-uppercase fs-xs fw-bold text-muted text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()):
                                $query->the_post();
                                $post_id = get_the_ID();
                                $event_slug = get_post_meta($post_id, '_sciflow_event', true);
                                $event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Senco';
                                $post_status = get_post_status();
                                $date = get_the_date('d/m/Y');

                                $sciflow_status = get_post_meta($post_id, '_sciflow_status', true) ?: 'rascunho';

                                $status_labels = array(
                                    'rascunho' => 'Rascunho',
                                    'aguardando_pagamento' => 'Aguardando Pagamento',
                                    'submetido' => 'Submetido',
                                    'em_avaliacao' => 'Em Avaliação',
                                    'aguardando_decisao' => 'Aguardando Decisão',
                                    'em_correcao' => 'Correções Solicitadas',
                                    'aprovado' => 'Aprovado',
                                    'reprovado' => 'Reprovado',
                                    'aprovado_com_consideracoes' => 'Aprovado com Considerações',
                                    'poster_enviado' => 'Pôster Enviado',
                                    'confirmado' => 'Confirmado',
                                );

                                $badge_classes = array(
                                    'rascunho' => 'sciflow-badge--draft',
                                    'aguardando_pagamento' => 'bg-warning text-dark',
                                    'submetido' => 'bg-info text-white',
                                    'em_avaliacao' => 'bg-primary text-white',
                                    'aguardando_decisao' => 'bg-primary text-white',
                                    'em_correcao' => 'bg-secondary text-white',
                                    'aprovado' => 'sciflow-badge--published',
                                    'reprovado' => 'bg-danger text-white',
                                    'aprovado_com_consideracoes' => 'bg-info text-white',
                                );

                                $status_label = isset($status_labels[$sciflow_status]) ? $status_labels[$sciflow_status] : $sciflow_status;
                                $badge_class = isset($badge_classes[$sciflow_status]) ? $badge_classes[$sciflow_status] : 'bg-light';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-muted small">#<?php echo esc_html($post_id); ?></span>
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
                                            <span class="small fw-semibold"><?php echo esc_html($event_name); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="small text-muted"><?php echo esc_html($date); ?></span>
                                    </td>
                                    <td>
                                        <span class="sciflow-table-badge <?php echo $badge_class; ?>">
                                            <?php echo esc_html($status_label); ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php if ($sciflow_status === 'rascunho' || $sciflow_status === 'em_correcao' || $sciflow_status === 'aguardando_pagamento'): ?>
                                            <a href="<?php echo esc_url(add_query_arg('edit_id', $post_id, home_url('/submissao'))); ?>"
                                                class="btn btn-sm btn-light rounded-pill px-3 fw-bold sciflow-table-edit">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                <?php echo ($sciflow_status === 'em_correcao') ? 'Corrigir' : 'Ver/Editar'; ?>
                                            </a>
                                        <?php else: ?>
                                            <?php
                                            $detail_page = get_pages(array('meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/template-article-detail.php'));
                                            $detail_url = !empty($detail_page) ? get_permalink($detail_page[0]->ID) : home_url('/avaliar-artigo');
                                            $view_url = add_query_arg('article_id', $post_id, $detail_url);
                                            ?>
                                            <a href="<?php echo esc_url($view_url); ?>"
                                                class="btn btn-sm btn-light rounded-pill px-3 fw-bold">
                                                <i class="bi bi-eye me-1"></i> Detalhes
                                            </a>
                                        <?php endif; ?>
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
                    <i class="bi bi-file-earmark-spreadsheet display-1 text-light"></i>
                </div>
                <h2 class="h3 fw-bold mb-3">Sua lista está vazia</h2>
                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">
                    Você ainda não iniciou a submissão de nenhum trabalho científico.
                </p>
                <a href="<?php echo esc_url(home_url('/submissao')); ?>"
                    class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    Nova Submissão
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>