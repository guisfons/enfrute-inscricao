<?php
/**
 * Template Name: Artigos Publicados (Editor/Revisor)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Authorization check: User must be a Semco/Enfrute editor or revisor
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;

$allowed_roles = array(
    'sciflow_inscrito',
    'sciflow_editor',
    'sciflow_revisor',
    'sciflow_semco_editor',
    'sciflow_semco_revisor',
    'sciflow_enfrute_editor',
    'sciflow_enfrute_revisor',
    'administrator'
);

$has_access = false;
foreach ($allowed_roles as $role) {
    if (in_array($role, $user_roles)) {
        $has_access = true;
        break;
    }
}

if (!$has_access) {
    wp_redirect(home_url());
    exit;
}

// Determine which post type to show based on role
$post_types = array();
if (in_array('sciflow_semco_editor', $user_roles) || in_array('sciflow_semco_revisor', $user_roles)) {
    $post_types[] = 'semco_trabalhos';
}
if (in_array('sciflow_enfrute_editor', $user_roles) || in_array('sciflow_enfrute_revisor', $user_roles)) {
    $post_types[] = 'enfrute_trabalhos';
}

if (empty($post_types)) {
    $post_types = array('semco_trabalhos', 'enfrute_trabalhos');
}

get_header();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type' => $post_types,
    'post_status' => 'publish', // Works that follow the submission flow
    'posts_per_page' => 20,
    'paged' => $paged,
    'meta_query' => array(
        array(
            'key' => '_sciflow_status',
            'value' => 'rascunho',
            'compare' => '!=',
        ),
    ),
);

$query = new WP_Query($args);
?>

<main class="sciflow-submissions-list py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="h2 fw-900 text-dark mb-1">Artigos Publicados</h1>
                <p class="text-muted mb-0">Visualização de trabalhos técnicos aprovados e publicados.</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-white text-dark border p-2 px-3 rounded-pill fw-bold">
                    <i class="bi bi-file-earmark-check me-1 text-success"></i>
                    <?php echo esc_html($query->found_posts); ?> Artigos
                </span>
            </div>
        </div>

        <?php if ($query->have_posts()): ?>
            <!-- Filters -->
            <div class="row g-3 mb-4 sciflow-filters" id="sciflow-published-filters">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text"
                            class="form-control border-start-0 ps-0 fw-medium shadow-none sciflow-filter-text"
                            placeholder="Buscar (Título, Autor, Fruta, Área)...">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <select class="form-select form-select-sm fw-medium text-secondary shadow-none sciflow-filter-status">
                        <option value="">Todos os Status</option>
                        <option value="em_avaliacao">Em Avaliação (Revisor)</option>
                        <option value="aguardando_decisao">Aguardando Decisão (Editor)</option>
                        <option value="aprovado">Aprovado</option>
                        <option value="publicado">Publicado</option>
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
                <div class="col-12 col-md-2">
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
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Título do Trabalho</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Autor</th>
                                <th class="py-3 text-uppercase fs-xs fw-bold text-muted">Status</th>
                                <th class="pe-4 py-3 text-uppercase fs-xs fw-bold text-muted text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($query->have_posts()):
                                $query->the_post();
                                $post_id = get_the_ID();
                                $event_slug = get_post_meta($post_id, '_sciflow_event', true);
                                $event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Semco';

                                $cultura = get_post_meta($post_id, '_sciflow_cultura', true);
                                $area = get_post_meta($post_id, '_sciflow_knowledge_area', true);

                                $is_editor = current_user_can('manage_sciflow');
                                $author_name = get_the_author();
                                if (!$is_editor && !current_user_can('administrator')) {
                                    $author_name = ''; // Hide from search if blind review
                                }
                                ?>
                                <tr class="sciflow-published-row"
                                    data-search="<?php echo esc_attr(strtolower(get_the_title() . ' ' . $author_name . ' ' . $cultura . ' ' . $area)); ?>"
                                    data-status="<?php echo esc_attr(get_post_meta($post_id, '_sciflow_status', true) ?: 'rascunho'); ?>"
                                    data-cultura="<?php echo esc_attr($cultura); ?>" data-area="<?php echo esc_attr($area); ?>">
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
                                    </td>
                                    <td>
                                        <span class="small text-muted">
                                            <?php
                                            $is_editor = current_user_can('manage_sciflow');
                                            if (!$is_editor && !current_user_can('administrator')): ?>
                                                <i class="opacity-50 italic">Ocultado para revisão às cegas</i>
                                            <?php else: ?>
                                                <?php echo get_the_author(); ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $sciflow_status = get_post_meta($post_id, '_sciflow_status', true) ?: 'rascunho';
                                        $status_manager = new SciFlow_Status_Manager();
                                        $status_label = $status_manager->get_status_label($sciflow_status);
                                        ?>
                                        <?php echo $status_manager->get_status_badge($sciflow_status); ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php
                                        $detail_page = get_pages(array('meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/template-article-detail.php'));
                                        $detail_url = !empty($detail_page) ? get_permalink($detail_page[0]->ID) : home_url('/avaliar-artigo');
                                        $view_url = add_query_arg('article_id', $post_id, $detail_url);
                                        ?>
                                        <a href="<?php echo esc_url($view_url); ?>"
                                            class="btn btn-sm btn-outline-success rounded-pill px-4 fw-bold">
                                            <i class="bi bi-eye me-1"></i> Visualizar
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile;
                            wp_reset_postdata(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-4 d-flex justify-content-center">
                <?php
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'type' => 'list',
                    'class' => 'pagination justify-content-center'
                ));
                ?>
            </div>

        <?php else: ?>
            <div class="sciflow-empty-state text-center py-5 shadow-sm rounded-4 bg-white border mt-4">
                <div class="sciflow-empty-icon mb-4">
                    <i class="bi bi-journal-x display-1 text-light"></i>
                </div>
                <h2 class="h3 fw-bold mb-3">Nenhum artigo publicado</h2>
                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">
                    Ainda não há artigos publicados para o seu evento. Assim que os trabalhos forem aprovados e publicados,
                    eles aparecerão aqui.
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filters = document.getElementById('sciflow-published-filters');
        if (!filters) return;

        const textFilter = filters.querySelector('.sciflow-filter-text');
        const statusFilter = filters.querySelector('.sciflow-filter-status');
        const culturaFilter = filters.querySelector('.sciflow-filter-cultura');
        const areaFilter = filters.querySelector('.sciflow-filter-area');

        const rows = document.querySelectorAll('.sciflow-published-row');

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