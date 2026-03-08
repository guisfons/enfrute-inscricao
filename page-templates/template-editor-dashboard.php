<?php
/**
 * Template Name: Dashboard do Editor
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_sciflow')) {
    wp_redirect(home_url());
    exit;
}

get_header();

$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;

// Specific Event Checks
$is_semco_role = in_array('sciflow_semco_editor', $user_roles) || in_array('sciflow_semco_revisor', $user_roles);
$is_enfrute_role = in_array('sciflow_enfrute_editor', $user_roles) || in_array('sciflow_enfrute_revisor', $user_roles);

// Filter Post Types
$post_types = array('enfrute_trabalhos', 'semco_trabalhos');
if ($is_semco_role && !$is_enfrute_role) {
    $post_types = array('semco_trabalhos');
} elseif ($is_enfrute_role && !$is_semco_role) {
    $post_types = array('enfrute_trabalhos');
}

$args = array(
    'post_type' => $post_types,
    'posts_per_page' => -1,
    'post_status' => ($is_semco_role || $is_enfrute_role) ? 'publish' : 'any',
);

// Exclude Drafts and Evaluation for event roles
if ($is_semco_role || $is_enfrute_role) {
    $args['meta_query'] = array(
        array(
            'key' => '_sciflow_status',
            'value' => array('rascunho', 'em_avaliacao'),
            'compare' => 'NOT IN',
        ),
    );
} else {
    // For global admin, just exclude drafts
    $args['meta_query'] = array(
        array(
            'key' => '_sciflow_status',
            'value' => 'rascunho',
            'compare' => '!=',
        ),
    );
}

$query = new WP_Query($args);

// Calculate global order of submission for IDs
$all_enfrute = get_posts(array(
    'post_type' => 'enfrute_trabalhos',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'orderby' => 'date',
    'order' => 'ASC',
    'fields' => 'ids',
));
$enfrute_numbers = array_flip($all_enfrute);

$all_semco = get_posts(array(
    'post_type' => 'semco_trabalhos',
    'posts_per_page' => -1,
    'post_status' => 'any',
    'orderby' => 'date',
    'order' => 'ASC',
    'fields' => 'ids',
));
$semco_numbers = array_flip($all_semco);
?>

<main class="sciflow-submissions-list py-5">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h2 fw-900 text-dark mb-1">Painel do Editor</h1>
                <p class="text-muted mb-0">Gerenciamento global de todos os trabalhos submetidos.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-light text-dark border p-2 px-3 rounded-pill fw-bold">
                    <i class="bi bi-people me-1"></i>
                    <?php echo esc_html($query->found_posts); ?> Trabalhos
                </span>
            </div>
        </div>

        <?php if ($query->have_posts()): ?>
            <!-- Filters -->
            <div class="row g-3 mb-4 sciflow-filters" id="sciflow-dashboard-filters">
                <div class="col-12 col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                            class="form-control border-start-0 ps-0 fw-medium shadow-none sciflow-filter-text"
                            placeholder="Buscar (Título, Autor, Cultura, Área)...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm fw-medium text-secondary shadow-none sciflow-filter-status">
                        <option value="">Todos os Status</option>
                        <option value="rascunho">Rascunho</option>
                        <option value="aguardando_pagamento">Pgto. Pendente</option>
                        <option value="submetido">Submetido</option>
                        <option value="em_avaliacao">Em Avaliação</option>
                        <option value="aguardando_decisao">Decisão Pendente</option>
                        <option value="em_correcao">Necessita Alterações</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="reprovado">Reprovado</option>
                        <option value="aprovado_com_consideracoes">Necessita Alterações (Aprovado)</option>
                        <option value="submetido_com_revisao">Submetido com Alterações</option>
                        <option value="apto_revisao">Apto para Revisão</option>
                        <option value="apto_publicacao">Apto para Publicação</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm fw-medium text-secondary shadow-none sciflow-filter-cultura">
                        <option value="">Todas as Culturas</option>
                        <optgroup label="Frutas de clima temperado">
                            <option value="Figo">Figo</option>
                            <option value="Frutas de caroço">Frutas de caroço</option>
                            <option value="Goiaba/Caqui">Goiaba/Caqui</option>
                            <option value="Maçã/Pera">Maçã/Pera</option>
                            <option value="Pequenas frutas">Pequenas frutas</option>
                            <option value="Frutas nativas">Frutas nativas</option>
                            <option value="Uva">Uva</option>
                            <option value="Outras (Frutas)">Outras</option>
                        </optgroup>
                        <optgroup label="Olerícolas">
                            <option value="Alho">Alho</option>
                            <option value="Cebola">Cebola</option>
                            <option value="Tomate">Tomate</option>
                            <option value="Morango">Morango</option>
                            <option value="Aipim/mandioca">Aipim/mandioca</option>
                            <option value="Cenoura">Cenoura</option>
                            <option value="Pimentão">Pimentão</option>
                            <option value="Folhosas">Folhosas</option>
                            <option value="Outras (Olerícolas)">Outras</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm fw-medium text-secondary shadow-none sciflow-filter-area">
                        <option value="">Todas as Áreas</option>
                        <option value="Biotecnologia/Genética e Melhoramento">Biotecnologia/Genética e Melhoramento</option>
                        <option value="Botânica e Fisiologia">Botânica e Fisiologia</option>
                        <option value="Colheita e Pós-Colheita">Colheita e Pós-Colheita</option>
                        <option value="Fitossanidade">Fitossanidade</option>
                        <option value="Economia/Estatística">Economia/Estatística</option>
                        <option value="Fitotecnia">Fitotecnia</option>
                        <option value="Irrigação">Irrigação</option>
                        <option value="Processamento (Química e Bioquímica)">Processamento (Química e Bioquímica)</option>
                        <option value="Propagação">Propagação</option>
                        <option value="Sementes">Sementes</option>
                        <option value="Solos e Nutrição de Plantas">Solos e Nutrição de Plantas</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>
            </div>

            <div class="sciflow-table-container shadow-sm rounded-4 overflow-hidden bg-white mt-4 border">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 sciflow-table">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 text-uppercase fs-xs fw-bold text-muted">ID</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Autor</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Título do Trabalho</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Evento</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Status SciFlow</th>
                                <th class="pe-4 py-3 text-uppercase fs-xs fw-bold text-muted text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()):
                                $query->the_post();
                                $post_id = get_the_ID();
                                $author_id = get_the_author_meta('ID');
                                $author_name = get_the_author();
                                $event_slug = get_post_meta($post_id, '_sciflow_event', true);
                                $event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Semco';

                                // SciFlow Specific Status
                                $sciflow_status = get_post_meta($post_id, '_sciflow_status', true);
                                if (!$sciflow_status)
                                    $sciflow_status = 'rascunho';

                                $status_labels = array(
                                    'rascunho' => 'Rascunho',
                                    'aguardando_pagamento' => 'Pgto. Pendente',
                                    'submetido' => 'Submetido',
                                    'em_avaliacao' => 'Em Avaliação',
                                    'aguardando_decisao' => 'Decisão Pendente',
                                    'em_correcao' => 'Necessita Alterações',
                                    'aprovado' => 'Aprovado',
                                    'reprovado' => 'Reprovado',
                                    'aprovado_com_consideracoes' => 'Necessita Alterações',
                                    'submetido_com_revisao' => 'SUBMETIDO COM ALTERAÇÕES',
                                    'apto_revisao' => 'Apto para Revisão',
                                    'apto_publicacao' => 'Apto para Publicação',
                                );

                                $badge_classes = array(
                                    'rascunho' => 'sciflow-badge--draft',
                                    'aguardando_pagamento' => 'bg-warning text-dark',
                                    'submetido' => 'bg-info text-white',
                                    'em_avaliacao' => 'sciflow-badge--pending',
                                    'aguardando_decisao' => 'bg-primary text-white',
                                    'em_correcao' => 'bg-secondary text-white',
                                    'aprovado' => 'sciflow-badge--published',
                                    'reprovado' => 'bg-danger text-white',
                                    'aprovado_com_consideracoes' => 'bg-info text-white',
                                    'submetido_com_revisao' => 'bg-info text-white',
                                    'apto_revisao' => 'bg-info text-white',
                                    'apto_publicacao' => 'bg-success text-white',
                                );

                                $cultura = get_post_meta($post_id, '_sciflow_cultura', true);
                                $area = get_post_meta($post_id, '_sciflow_knowledge_area', true);
                                $status_label = isset($status_labels[$sciflow_status]) ? $status_labels[$sciflow_status] : $sciflow_status;
                                $badge_class = isset($badge_classes[$sciflow_status]) ? $badge_classes[$sciflow_status] : 'bg-light';

                                $is_editor = current_user_can('manage_sciflow');
                                $display_author_name = $author_name;
                                if (!$is_editor && !current_user_can('administrator')) {
                                    $display_author_name = ''; // Hide from search if blind review
                                }
                                ?>
                                <tr class="sciflow-dashboard-row"
                                    data-search="<?php echo esc_attr(strtolower(get_the_title() . ' ' . $display_author_name . ' ' . $cultura . ' ' . $area)); ?>"
                                    data-status="<?php echo esc_attr($sciflow_status); ?>"
                                    data-cultura="<?php echo esc_attr($cultura); ?>" data-area="<?php echo esc_attr($area); ?>">
                                    <td class="ps-4">
                                        <span class="text-muted small">#<?php
                                        $event_type_slug = get_post_type($post_id);
                                        $number = $post_id;
                                        if ($event_type_slug === 'enfrute_trabalhos' && isset($enfrute_numbers[$post_id])) {
                                            $number = $enfrute_numbers[$post_id] + 1;
                                        } elseif ($event_type_slug === 'semco_trabalhos' && isset($semco_numbers[$post_id])) {
                                            $number = $semco_numbers[$post_id] + 1;
                                        }
                                        echo esc_html(str_pad($number, 4, '0', STR_PAD_LEFT));
                                        ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center me-2"
                                                style="width: 32px; height: 32px;">
                                                <i class="bi bi-person text-muted"></i>
                                            </div>
                                            <span class="small fw-semibold">
                                                <?php
                                                $is_editor = current_user_can('manage_sciflow');
                                                if (!$is_editor && !current_user_can('administrator')): ?>
                                                    <i class="opacity-50 italic">Ocultado (Blind Review)</i>
                                                <?php else: ?>
                                                    <?php echo esc_html($author_name); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="sciflow-table-title fw-bold text-dark text-truncate"
                                            style="max-width: 300px;">
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
                                            <i class="bi bi-eye me-1"></i> Ver
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
                    <i class="bi bi-folder2-open display-1 text-light"></i>
                </div>
                <h2 class="h3 fw-bold mb-3">Nenhum trabalho encontrado</h2>
                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">
                    Ainda não há trabalhos submetidos no sistema para os eventos Enfrute e Semco.
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filters = document.getElementById('sciflow-dashboard-filters');
        if (!filters) return;

        const textFilter = filters.querySelector('.sciflow-filter-text');
        const statusFilter = filters.querySelector('.sciflow-filter-status');
        const culturaFilter = filters.querySelector('.sciflow-filter-cultura');
        const areaFilter = filters.querySelector('.sciflow-filter-area');

        const rows = document.querySelectorAll('.sciflow-dashboard-row');

        function applyFilters() {
            const textValue = textFilter.value.toLowerCase();
            const statusValue = statusFilter ? statusFilter.value : '';
            const culturaValue = culturaFilter.value;
            const areaValue = areaFilter.value;

            rows.forEach(row => {
                let show = true;
                if (textValue && row.dataset.search.indexOf(textValue) === -1) show = false;
                if (statusValue && row.dataset.status !== statusValue) show = false;
                if (culturaValue && row.dataset.cultura !== culturaValue) show = false;
                if (areaValue && row.dataset.area !== areaValue) show = false;

                row.style.display = show ? '' : 'none';
            });
        }

        [textFilter, statusFilter, culturaFilter, areaFilter].forEach(el => {
            if (el) {
                el.addEventListener('input', applyFilters);
                el.addEventListener('change', applyFilters);
            }
        });
    });
</script>

<?php get_footer(); ?>