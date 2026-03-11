<?php
/**
 * Template Name: Detalhes do Artigo (Review)
 */

if (!defined('ABSPATH')) {
    exit;
}

$article_id = isset($_GET['article_id']) ? absint($_GET['article_id']) : 0;
$post = get_post($article_id);

if (!$post || !in_array($post->post_type, array('enfrute_trabalhos', 'semco_trabalhos'))) {
    wp_redirect(home_url());
    exit;
}

$current_user_id = get_current_user_id();
$is_editor = current_user_can('manage_sciflow');
$is_reviewer = (int) get_post_meta($article_id, '_sciflow_reviewer_id', true) === $current_user_id;
$is_author = (int) get_post_meta($article_id, '_sciflow_author_id', true) === $current_user_id;

// Authorization check
$current_user = wp_get_current_user();
$user_roles = (array) $current_user->roles;

// Specific Event Checks
$is_semco_role = in_array('sciflow_semco_editor', $user_roles) || in_array('sciflow_semco_revisor', $user_roles);
$is_enfrute_role = in_array('sciflow_enfrute_editor', $user_roles) || in_array('sciflow_enfrute_revisor', $user_roles);

$can_view = false;

// 1. Editor/Admin can view everything
if ($is_editor || current_user_can('administrator')) {
    $can_view = true;
}

// 2. Reviewer assigned to this article
if ($is_reviewer) {
    $can_view = true;
}

// 3. Author can view own article
if ($is_author) {
    $can_view = true;
}

// Event specific roles (ONLY PUBLISHED)
if ($post->post_status === 'publish') {
    $sciflow_status_meta = get_post_meta($article_id, '_sciflow_status', true);
    if ($sciflow_status_meta !== 'rascunho') {
        if ($is_semco_role && $post->post_type === 'semco_trabalhos') {
            $can_view = true;
        }
        if ($is_enfrute_role && $post->post_type === 'enfrute_trabalhos') {
            $can_view = true;
        }
    }
}

if (!$can_view) {
    wp_redirect(home_url());
    exit;
}

get_header();

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

// Data fetching
$event_slug = get_post_meta($article_id, '_sciflow_event', true);
$event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Semco';
$sciflow_status = get_post_meta($article_id, '_sciflow_status', true) ?: 'rascunho';
$coauthors = get_post_meta($article_id, '_sciflow_coauthors', true) ?: array();
$keywords = get_post_meta($article_id, '_sciflow_keywords', true) ?: array();
$reviewer_id = (int) get_post_meta($article_id, '_sciflow_reviewer_id', true);

// Get Status Labels
$status_manager = new SciFlow_Status_Manager();
$status_label = $status_manager->get_status_label($sciflow_status);

$reviewer_notes = get_post_meta($article_id, '_sciflow_reviewer_notes', true);
$editorial_notes = get_post_meta($article_id, '_sciflow_editorial_notes', true);
$reviewer_decision = get_post_meta($article_id, '_sciflow_reviewer_decision', true);
$reviewer_scores = get_post_meta($article_id, '_sciflow_scores', true) ?: array();
?>

<main class="sciflow-article-detail py-5 bg-light min-vh-100">
    <div class="container py-4">
        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <?php if ($is_editor): ?>
                        <a href="<?php echo esc_url(home_url('/editor-dashboard')); ?>"
                            class="text-decoration-none text-success fw-bold">Dashboard</a>
                    <?php elseif ($is_reviewer): ?>
                        <a href="<?php echo esc_url(home_url('/reviewer-dashboard')); ?>"
                            class="text-decoration-none text-success fw-bold">Minhas Revisões</a>
                    <?php elseif ($is_semco_role || $is_enfrute_role): ?>
                        <a href="<?php echo esc_url(home_url('/meus-artigos')); ?>"
                            class="text-decoration-none text-success fw-bold">Meus Artigos</a>
                    <?php else: ?>
                        <a href="<?php echo esc_url(home_url('/meus-trabalhos')); ?>"
                            class="text-decoration-none text-success fw-bold">Meus Trabalhos</a>
                    <?php endif; ?>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Trabalho
                    #<?php
                    $event_type_slug = get_post_type($article_id);
                    $number = $article_id;
                    if ($event_type_slug === 'enfrute_trabalhos' && isset($enfrute_numbers[$article_id])) {
                        $number = $enfrute_numbers[$article_id] + 1;
                    } elseif ($event_type_slug === 'semco_trabalhos' && isset($semco_numbers[$article_id])) {
                        $number = $semco_numbers[$article_id] + 1;
                    }
                    echo esc_html(str_pad($number, 4, '0', STR_PAD_LEFT));
                    ?>
                </li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left Column: Article Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span
                                class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold text-uppercase fs-xs">
                                <i class="bi bi-journal-bookmark me-1"></i>
                                <?php echo esc_html($event_name); ?>
                            </span>
                            <?php echo $status_manager->get_status_badge($sciflow_status); ?>
                        </div>

                        <h1 class="display-6 fw-900 text-dark mb-4">
                            <?php echo get_the_title($article_id); ?>
                        </h1>

                        <div class="sciflow-content-section mb-5">
                            <h3 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Resumo / Conteúdo</h3>
                            <div class="text-muted lh-lg">
                                <?php echo wpautop(get_post_field('post_content', $article_id)); ?>
                            </div>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6">
                                <h3 class="h6 fw-bold text-dark mb-3">Palavras-chave</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($keywords as $kw): ?>
                                        <span class="badge bg-white text-muted border px-3 py-2 rounded-pill">
                                            <?php echo esc_html($kw); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mt-4 mt-md-0">
                                <h3 class="h6 fw-bold text-dark mb-3">Coautores</h3>
                                <?php if ($is_reviewer && !$is_editor && !current_user_can('administrator')): ?>
                                    <p class="small text-muted italic">Ocultado para revisão às cegas.</p>
                                <?php elseif (!empty($coauthors)): ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($coauthors as $ca): ?>
                                            <li class="small text-muted mb-2">
                                                <i class="bi bi-person-check me-2 text-success"></i>
                                                <strong>
                                                    <?php echo esc_html($ca['name']); ?>
                                                </strong>
                                                <span class="opacity-75">(
                                                    <?php echo esc_html($ca['institution']); ?>)
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p class="small text-muted italic">Nenhum coautor registrado.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Meta -->
            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-bold text-dark mb-4">Informações do Sistema</h3>

                        <?php if (!$is_reviewer || $is_editor || current_user_can('administrator')): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Autor Principal</span>
                                <span class="small fw-bold">
                                    <?php
                                    $author_user = get_userdata($post->post_author);
                                    echo esc_html($author_user ? $author_user->display_name : 'N/A');
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted small">Data de Submissão</span>
                            <span class="small fw-bold">
                                <?php echo get_the_date('d/m/Y', $article_id); ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted small">ID do Trabalho</span>
                            <span class="small fw-bold">#
                                <?php
                                $event_type_slug = get_post_type($article_id);
                                $number = $article_id;
                                if ($event_type_slug === 'enfrute_trabalhos' && isset($enfrute_numbers[$article_id])) {
                                    $number = $enfrute_numbers[$article_id] + 1;
                                } elseif ($event_type_slug === 'semco_trabalhos' && isset($semco_numbers[$article_id])) {
                                    $number = $semco_numbers[$article_id] + 1;
                                }
                                echo esc_html(str_pad($number, 4, '0', STR_PAD_LEFT));
                                ?>
                            </span>
                        </div>

                        <?php
                        $cultura = get_post_meta($article_id, '_sciflow_cultura', true);
                        if (!empty($cultura)): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Cultura / Fruta</span>
                                <span class="small fw-bold text-end">
                                    <?php echo esc_html($cultura); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $knowledge_area = get_post_meta($article_id, '_sciflow_knowledge_area', true);
                        if (!empty($knowledge_area)): ?>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted small">Área do Conhecimento</span>
                                <span class="small fw-bold text-end">
                                    <?php echo esc_html($knowledge_area); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if (!$is_author): ?>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">Revisor Atribuído</span>
                                <span class="small fw-bold text-end">
                                    <?php
                                    if ($reviewer_id) {
                                        $rev_user = get_userdata($reviewer_id);
                                        echo esc_html($rev_user->display_name);
                                    } else {
                                        echo '<span class="text-danger">Não atribuído</span>';
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Poster Section -->
                <?php
                $poster_id = get_post_meta($article_id, '_sciflow_poster_id', true);
                if ($poster_id): 
                    $poster_url = wp_get_attachment_url($poster_id);
                    $poster_file = get_attached_file($poster_id);
                    $poster_size = $poster_file && file_exists($poster_file) ? size_format(filesize($poster_file)) : 'N/A';
                ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-success border-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-dark mb-3"><i class="bi bi-file-earmark-pdf text-danger me-2"></i>Pôster Anexado</h3>
                            <p class="small text-muted mb-3">O arquivo PDF do pôster está disponível para este trabalho.</p>
                            <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-3">
                                <?php if ($poster_file): ?>
                                    <span class="small text-truncate me-2" style="max-width: 200px;" title="<?php echo esc_attr(basename($poster_file)); ?>">
                                        <i class="bi bi-file-pdf me-1"></i><?php echo esc_html(basename($poster_file)); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-secondary"><?php echo esc_html($poster_size); ?></span>
                            </div>
                            <a href="<?php echo esc_url($poster_url); ?>" target="_blank" class="btn btn-success btn-sm w-100 rounded-pill py-2 fw-bold">
                                <i class="bi bi-eye me-1"></i> Visualizar Pôster
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Observations Section (Visible only to Editor and Reviewer) -->
                <?php if ($reviewer_notes && !$is_author): ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-body p-4">
                            <h3 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Observações Gerais</h3>
                            
                            <div class="mb-4">
                                <h4 class="h6 fw-bold text-info"><i class="bi bi-person-badge me-2"></i>Parecer do Revisor
                                </h4>
                                
                                <?php if (!empty($reviewer_scores) && ($is_editor || current_user_can('administrator'))): ?>
                                    <div class="bg-white border rounded-3 p-3 mb-3 small">
                                        <strong class="d-block mb-2 text-dark border-bottom pb-1">Notas por Critério:</strong>
                                        <ul class="list-unstyled mb-0 column">
                                            <?php
                                            $criteria = array(
                                                'originalidade' => 'Originalidade',
                                                'objetividade' => 'Objetividade',
                                                'organizacao' => 'Organização',
                                                'metodologia' => 'Metodologia',
                                                'aderencia' => 'Aderência'
                                            );
                                            $total_score = 0;
                                            foreach ($criteria as $key => $label) {
                                                $score = isset($reviewer_scores[$key]) ? floatval($reviewer_scores[$key]) : 0;
                                                $total_score += $score;
                                                echo '<li class="col-sm-12 mb-1 d-flex justify-content-between">';
                                                echo '<span class="text-muted">' . esc_html($label) . ':</span> ';
                                                echo '<span class="fw-bold">' . number_format($score, 1, ',', '.') . ' / 10</span>';
                                                echo '</li>';
                                            }
                                            ?>
                                        </ul>
                                        <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                            <strong class="text-dark">Nota Total:</strong>
                                            <strong class="text-success fs-6"><?php echo number_format($total_score, 1, ',', '.'); ?> / 50</strong>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($reviewer_notes): ?>
                                    <div class="bg-light p-3 rounded-3 border-start border-info border-4 small">
                                        <?php echo wpautop(esc_html($reviewer_notes)); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="small text-muted italic">Nenhuma observação textual preenchida.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Editor Notes Section (Conditionally Visible) -->
                <?php 
                $decision = get_post_meta($article_id, '_sciflow_decision', true);
                
                // Author should see notes if: 
                // 1) The status requires their attention (em_correcao)
                // 2) There is no reviewer
                // 3) The decision explicitly mentions them
                $is_note_for_author = $sciflow_status === 'em_correcao' 
                                    || !$reviewer_id 
                                    || in_array($decision, ['return_to_author', 'approve', 'reject', 'approved_with_considerations']);
                
                if ($editorial_notes && (!$is_author || $is_note_for_author)): 
                    $note_title = ($is_note_for_author && $is_author) ? 'Observações Editoriais' : 'Decisão Editorial (Para o Revisor)';
                ?>
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                        <div class="card-body p-4">
                            <h4 class="h6 fw-bold text-primary"><i class="bi bi-person-workspace me-2"></i><?php echo esc_html($note_title); ?></h4>
                            <div class="bg-light p-3 rounded-3 border-start border-primary border-4 small">
                                <?php echo wpautop(esc_html($editorial_notes)); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Role Specific Actions -->
                <?php if ($is_editor): ?>
                    <!-- EDITOR ACTIONS -->
                    <div class="card border-0 shadow-sm rounded-4 border-start border-primary border-4 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-dark mb-3">Ações do Editor</h3>

                            <?php if ($reviewer_decision):
                                $decision_labels = array(
                                    'approved' => 'Aprovar e Publicar',
                                    'approved_with_considerations' => 'Necessita Alterações',
                                    'rejected' => 'Reprovar'
                                );
                                $decision_colors = array(
                                    'approved' => 'success',
                                    'approved_with_considerations' => 'warning',
                                    'rejected' => 'danger'
                                );
                                $lbl = isset($decision_labels[$reviewer_decision]) ? $decision_labels[$reviewer_decision] : $reviewer_decision;
                                $clr = isset($decision_colors[$reviewer_decision]) ? $decision_colors[$reviewer_decision] : 'secondary';
                                ?>
                                <div
                                    class="alert alert-<?php echo $clr; ?> bg-<?php echo $clr; ?>-subtle border-<?php echo $clr; ?>-subtle py-2 px-3 small mb-3">
                                    <i class="bi bi-info-circle-fill me-1"></i>
                                    <strong>Recomendação do Revisor:</strong> <?php echo esc_html($lbl); ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($sciflow_status === 'submetido'): ?>
                                <div class="mt-4">
                                    <label class="form-label small fw-bold text-muted">
                                        <?php echo $reviewer_id ? 'Revisor Atribuído' : 'Atribuir Revisor'; ?>
                                    </label>
                                    <form id="sciflow-assign-form" class="d-flex flex-column gap-2">
                                        <div class="d-flex gap-2">
                                            <select name="reviewer_id"
                                                class="form-select form-select-sm shadow-none border-light-subtle">
                                                <option value="">Selecione um revisor...</option>
                                                <?php
                                                $allowed_reviewer_roles = array('sciflow_revisor', 'administrator');
                                                if ($event_slug === 'enfrute') {
                                                    $allowed_reviewer_roles[] = 'sciflow_enfrute_revisor';
                                                } else {
                                                    $allowed_reviewer_roles[] = 'sciflow_semco_revisor';
                                                }
                                                $reviewers = get_users(array('role__in' => $allowed_reviewer_roles));
                                                foreach ($reviewers as $u): ?>
                                                    <option value="<?php echo $u->ID; ?>" <?php selected($reviewer_id, $u->ID); ?>>
                                                        <?php echo esc_html($u->display_name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <?php echo $reviewer_id ? 'Alterar' : 'Atribuir'; ?>
                                            </button>
                                        </div>
                                        <?php if ($reviewer_id): ?>
                                            <button type="submit"
                                                class="btn btn-outline-primary btn-sm rounded-pill w-100 mt-2 fw-bold">
                                                <i class="bi bi-play-circle me-1"></i> Iniciar/Retornar Avaliação
                                            </button>
                                        <?php endif; ?>
                                        <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="action" value="sciflow_assign_reviewer">
                                    </form>

                                    <?php if ($reviewer_id != $current_user_id): ?>
                                    <form id="sciflow-assume-form" class="mt-3 border-top pt-3">
                                        <p class="small text-muted mb-2">Deseja assumir a avaliação deste trabalho?</p>
                                        <button type="submit" class="btn btn-warning btn-sm rounded-pill w-100 fw-bold">
                                            <i class="bi bi-person-check-fill me-1"></i> Assumir Avaliação (Como Editor)
                                        </button>
                                        <input type="hidden" name="reviewer_id" value="<?php echo $current_user_id; ?>">
                                        <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="action" value="sciflow_assign_reviewer">
                                    </form>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($sciflow_status === 'aguardando_pagamento' && current_user_can('administrator')): ?>
                                <div class="mt-4">
                                    <label class="form-label small fw-bold text-muted">Ações Financeiras</label>
                                    <form id="sciflow-confirm-payment-form" class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 w-100 fw-bold">
                                            <i class="bi bi-check-circle me-1"></i> Confirmar Pagamento Manualmente
                                        </button>
                                        <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="action" value="sciflow_confirm_payment_admin">
                                    </form>
                                    <p class="text-muted small mt-2 mb-0">Esta ação avançará o trabalho para "Submetido".</p>
                                </div>
                            <?php endif; ?>

                            <?php if (in_array($sciflow_status, ['aguardando_decisao', 'submetido_com_revisao', 'submetido'])): ?>
                                <div class="mt-4 pt-3 border-top">
                                    <label class="form-label small fw-bold text-muted">Decisão Editorial</label>
                                    <form id="sciflow-decision-form">
                                        <div class="mb-3">
                                            <select name="decision"
                                                class="form-select form-select-sm mb-2 shadow-none border-light-subtle">
                                                <option value="">Selecione a decisão...</option>
                                                <option value="approve">Aprovar e Publicar</option>
                                                <!-- <option value="return_to_author">Solicitar Alterações (Autor)</option> -->
                                                <?php if ($sciflow_status !== 'submetido' && !empty($reviewer_id)): ?>
                                                    <option value="return_to_reviewer">Voltar para Revisão (Revisor)</option>
                                                <?php endif; ?>
                                                <option value="reject">Reprovar Trabalho</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <textarea name="notes"
                                                class="form-control form-control-sm shadow-none border-light-subtle" rows="3"
                                                placeholder="Observações da Decisão..."></textarea>
                                        </div>
                                        <button type="submit"
                                            class="btn btn-success btn-sm w-100 rounded-pill py-2 fw-bold">Registrar
                                            Decisão</button>
                                        <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                        <input type="hidden" name="action" value="sciflow_editorial_decision">

                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $reviewer_can_edit = in_array($sciflow_status, array('em_avaliacao', 'submetido'));
                if ($is_reviewer && ($reviewer_can_edit || $reviewer_decision)): ?>
                    <!-- REVIEWER ACTIONS -->
                    <div class="card border-0 shadow-sm rounded-4 border-start border-info border-4 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-dark mb-3">Sua Avaliação</h3>

                            <?php if (!$reviewer_can_edit): ?>
                                <div class="alert alert-secondary py-3 px-3 small mx-0 mb-3 border bg-light">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                                        <strong style="font-size: 14px;">Parecer Técnico Concluído</strong>
                                    </div>
                                    <p class="mb-0 text-muted" style="line-height: 1.4;">
                                        Sua avaliação foi enviada com sucesso e <strong>entregue ao Editor</strong>. O trabalho
                                        está sob análise editorial. Caso o editor solicite uma nova rodada de revisão, este
                                        formulário será reaberto.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <form id="<?php echo $reviewer_can_edit ? 'sciflow-review-form' : ''; ?>">
                                <div class="mb-3">
                                    <label class="small fw-bold text-muted mb-2">Notas (0 a 10)</label>
                                    <?php
                                    $criteria = array(
                                        'originalidade' => 'Originalidade',
                                        'objetividade' => 'Objetividade',
                                        'organizacao' => 'Organização',
                                        'metodologia' => 'Metodologia',
                                        'aderencia' => 'Aderência'
                                    );
                                    foreach ($criteria as $key => $label): ?>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small text-muted">
                                                <?php echo $label; ?>
                                            </span>
                                            <input type="number" name="scores[<?php echo $key; ?>]"
                                                class="form-control form-control-sm w-25 text-center shadow-none border-light-subtle"
                                                step="0.5" min="0" max="10"
                                                value="<?php echo esc_attr($reviewer_scores[$key] ?? ''); ?>" <?php echo $reviewer_can_edit ? 'required' : 'disabled'; ?>>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mb-3 border-top pt-3">
                                    <label class="small fw-bold text-muted mb-2">Parecer Técnico</label>
                                    <select name="decision"
                                        class="form-select form-select-sm mb-2 shadow-none border-light-subtle" <?php echo $reviewer_can_edit ? 'required' : 'disabled'; ?>>
                                        <option value="">Selecione...</option>
                                        <option value="approved" <?php selected($reviewer_decision, 'approved'); ?>>Aprovado
                                        </option>
                                        <option value="approved_with_considerations" <?php selected($reviewer_decision, 'approved_with_considerations'); ?>>Necessita Alterações</option>
                                        <option value="rejected" <?php selected($reviewer_decision, 'rejected'); ?>>Reprovar
                                        </option>
                                    </select>
                                    <textarea name="notes"
                                        class="form-control form-control-sm shadow-none border-light-subtle" rows="4"
                                        placeholder="Observações Gerais..." <?php echo $reviewer_can_edit ? 'required' : 'disabled'; ?>><?php echo esc_textarea($reviewer_notes); ?></textarea>
                                </div>
                                <?php if ($reviewer_can_edit): ?>
                                    <button type="submit"
                                        class="btn btn-info text-white btn-sm w-100 rounded-pill py-2 fw-bold shadow-sm">
                                        Enviar Avaliação
                                    </button>
                                <?php endif; ?>
                                <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                <input type="hidden" name="action" value="sciflow_submit_review">
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                $editable_statuses = array('rascunho', 'em_correcao', 'reprovado', 'aprovado_com_consideracoes');
                if ($is_author && in_array($sciflow_status, $editable_statuses)): ?>
                    <div
                        class="card border-0 shadow-sm rounded-4 border-start border-warning border-4 mb-4 text-center p-4">
                        <i class="bi bi-pencil-square display-5 text-warning mb-3"></i>
                        <h3 class="h6 fw-bold text-dark">Ação Necessária</h3>
                        <p class="small text-muted mb-4">Este trabalho está aberto para edições.</p>
                        <a href="<?php echo esc_url(add_query_arg('edit_id', $article_id, home_url('/submissao'))); ?>"
                            class="btn btn-warning btn-sm w-100 rounded-pill py-2 fw-bold">Editar Trabalho</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
    jQuery(document).ready(function ($) {
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        const nonce = '<?php echo wp_create_nonce('sciflow_nonce'); ?>';

        function handleAjax(formId) {
            $(`#${formId}`).on('submit', function (e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $form.find('button[type="submit"]');
                if (formId === 'sciflow-decision-form') {
                    const decision = $form.find('select[name="decision"]').val();
                    if (!decision) {
                        alert('Selecione uma decisão.');
                        return;
                    }
                    const labels = {
                        approve: 'aprovar e publicar',
                        reject: 'reprovar',
                        return_to_author: 'devolver para o autor (alterações)',
                        approved_with_considerations: 'necessita alterações',
                        return_to_reviewer: 'mandar de volta para o revisor'
                    };
                    if (!confirm('Tem certeza que deseja ' + (labels[decision] || decision) + ' este trabalho?')) return;
                }

                const data = $form.serialize() + '&nonce=' + nonce;

                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processando...');

                $.post(ajaxUrl, data, function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Erro: ' + response.data.message);
                        $btn.prop('disabled', false).text('Registrar');
                    }
                });
            });
        }

        handleAjax('sciflow-assign-form');
        handleAjax('sciflow-decision-form');
        handleAjax('sciflow-review-form');
        handleAjax('sciflow-assume-form');
        handleAjax('sciflow-confirm-payment-form');
    });
</script>

<?php get_footer(); ?>