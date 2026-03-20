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
$is_reviewer = (int)get_post_meta($article_id, '_sciflow_reviewer_id', true) === $current_user_id;
$is_author = (int)get_post_meta($article_id, '_sciflow_author_id', true) === $current_user_id;

// Authorization check
$current_user = wp_get_current_user();
$user_roles = (array)$current_user->roles;

// Specific Event Checks
$is_semco_role = in_array('sciflow_semco_editor', $user_roles) || in_array('sciflow_semco_revisor', $user_roles);
$is_enfrute_role = in_array('sciflow_enfrute_editor', $user_roles) || in_array('sciflow_enfrute_revisor', $user_roles);

$can_view = false;

// 1. Editor/Admin can view everything EXCEPT their own article if they are only an editor and not admin
    // If user is editor/admin, they can view regardless of being author or not
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



// Data fetching
$event_slug = get_post_meta($article_id, '_sciflow_event', true);
$event_name = ($event_slug === 'enfrute') ? 'Enfrute' : 'Semco';
$sciflow_status = get_post_meta($article_id, '_sciflow_status', true) ?: 'rascunho';
$coauthors = get_post_meta($article_id, '_sciflow_coauthors', true) ?: array();
$keywords = get_post_meta($article_id, '_sciflow_keywords', true) ?: array();
$reviewer_id = (int)get_post_meta($article_id, '_sciflow_reviewer_id', true);

// Get Status Labels
$status_manager = new SciFlow_Status_Manager();
$status_label = $status_manager->get_status_label($sciflow_status);

$reviewer_notes = get_post_meta($article_id, '_sciflow_reviewer_notes', true);
$editorial_notes = get_post_meta($article_id, '_sciflow_editorial_notes', true);
$reviewer_decision = get_post_meta($article_id, '_sciflow_reviewer_decision', true);
$reviewer_scores = get_post_meta($article_id, '_sciflow_scores', true) ?: array();
$poster_id = (int)get_post_meta($article_id, '_sciflow_poster_id', true);
$poster_editorial_notes = get_post_meta($article_id, '_sciflow_poster_editorial_notes', true);
$ranking_score = get_post_meta($article_id, '_sciflow_ranking_score', true);
?>

<main class="sciflow-article-detail py-5 bg-light min-vh-100">
    <div class="container py-4">
        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <?php if ($is_editor): ?>
                    <a href="<?php echo esc_url(home_url('/editor/dashboard/')); ?>"
                        class="text-decoration-none text-success fw-bold">Dashboard</a>
                    <?php
elseif ($is_reviewer): ?>
                    <a href="<?php echo esc_url(home_url('/revisor/dashboard/')); ?>"
                        class="text-decoration-none text-success fw-bold">Minhas Revisões</a>
                    <?php
elseif ($is_semco_role || $is_enfrute_role): ?>
                    <a href="<?php echo esc_url(home_url('/meus-artigos')); ?>"
                        class="text-decoration-none text-success fw-bold">Meus Resumos</a>
                    <?php
else: ?>
                    <a href="<?php echo esc_url(home_url('/meus-trabalhos')); ?>"
                        class="text-decoration-none text-success fw-bold">Meus Resumos</a>
                    <?php
endif; ?>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Trabalho
                    #
                    <?php
$visual_id = SciFlow_Status_Manager::get_visual_id($article_id);
echo esc_html(str_pad($visual_id, 4, '0', STR_PAD_LEFT));
?>
                </li>
            </ol>
        </nav>

        <div id="sciflow-editor-messages" class="sciflow-notice mb-4" style="display:none;"></div>

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

                        <div class="sciflow-content-section mb-5">
                            <h3 class="h5 fw-bold text-dark border-bottom pb-2 mb-3">Agradecimentos</h3>
                            <div class="text-muted lh-lg">
                                <?php echo wpautop(get_post_field('_sciflow_acknowledgement', $article_id)); ?>
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
                                    <?php
endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mt-4 mt-md-0">
                                <h3 class="h6 fw-bold text-dark mb-3">Coautores</h3>
                                <?php if ($is_reviewer && !$is_editor && !current_user_can('administrator')): ?>
                                <p class="small text-muted italic">Ocultado para revisão às cegas.</p>
                                <?php
elseif (!empty($coauthors)): ?>
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
                                    <?php
    endforeach; ?>
                                </ul>
                                <?php
else: ?>
                                <p class="small text-muted italic">Nenhum coautor registrado.</p>
                                <?php
endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Meta -->
            <div class="col-lg-4">
                <!-- 1. Informações do Sistema -->
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
                        <?php
endif; ?>

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
$visual_id = SciFlow_Status_Manager::get_visual_id($article_id);
echo esc_html(str_pad($visual_id, 4, '0', STR_PAD_LEFT));
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
                        <?php
endif; ?>

                        <?php
$knowledge_area = get_post_meta($article_id, '_sciflow_knowledge_area', true);
if (!empty($knowledge_area)): ?>
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <span class="text-muted small">Área do Conhecimento</span>
                            <span class="small fw-bold text-end">
                                <?php echo esc_html($knowledge_area); ?>
                            </span>
                        </div>
                        <?php
endif; ?>

                        <?php if ($is_editor || current_user_can('administrator')): // Only editors/admins see who the reviewer is ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Revisor Atribuído</span>
                            <span class="small fw-bold text-end">
                                <?php
                                if ($reviewer_id) {
                                    $rev_user = get_userdata($reviewer_id);
                                    echo esc_html($rev_user->display_name);
                                }
                                else {
                                    echo '<span class="text-danger">Não atribuído</span>';
                                }
                                ?>
                            </span>
                        </div>
                        <?php
endif; ?>
                    </div>
                </div>

                <!-- 2. Ações Específicas -->

                <!-- Feedback do Editor para o Autor (Destaque quando em correção de pôster) -->
                <?php if ($is_author && in_array($sciflow_status, array('poster_em_correcao', 'poster_reenviado'), true)): ?>
                <div class="card border-0 shadow-sm rounded-4 border-start border-warning border-4 mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-bold text-dark mb-3"><i
                                class="bi bi-exclamation-triangle-fill text-warning me-2"></i>Pôster Necessita Correção
                        </h3>
                        <?php if ($poster_editorial_notes): ?>
                        <div class="bg-light p-3 rounded-3 border-start border-primary border-4 small mb-3">
                            <strong>📋
                                <?php esc_html_e('Pôster Necessita Correção', 'sciflow-wp'); ?>
                            </strong><br>
                            <?php echo wpautop(esc_html($poster_editorial_notes)); ?>
                        </div>
                        <?php
    else: ?>
                        <p class="small text-muted mb-3">O editor solicitou ajustes no seu pôster. Por favor, revise o
                            arquivo e envie uma nova versão.</p>
                        <?php
    endif; ?>

                        <?php
    $poster_pages = get_pages(array('meta_key' => '_wp_page_template', 'meta_value' => 'template-poster-upload.php', 'number' => 1, 'post_status' => 'publish'));
    $poster_upload_url = !empty($poster_pages) ? get_permalink($poster_pages[0]->ID) : home_url('/');
?>
                        <?php if ($sciflow_status === 'poster_em_correcao'): ?>
                        <a href="<?php echo esc_url(add_query_arg('article_id', $article_id, $poster_upload_url)); ?>"
                            class="btn btn-warning btn-sm w-100 rounded-pill py-2 fw-bold">
                            <i class="bi bi-upload me-1"></i> Enviar Novo Pôster (PDF)
                        </a>
                        <?php
    endif; ?>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- Ações do Editor -->
                <?php
$finalized_statuses = array('apto_publicacao', 'poster_aprovado', 'poster_reprovado', 'reprovado');
if ($is_editor && !in_array($sciflow_status, array('poster_em_correcao', 'apto_publicacao', 'poster_aprovado', 'poster_reprovado', 'reprovado'))): ?>
                <div class="card border-0 shadow-sm rounded-4 border-start border-primary border-4 mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-bold text-dark mb-3">Ações do Editor</h3>

                        <?php if ($reviewer_decision):
        $decision_labels = array('approved' => 'Aprovar', 'approved_with_considerations' => 'Alterações', 'rejected' => 'Reprovar');
        $decision_colors = array('approved' => 'success', 'approved_with_considerations' => 'warning', 'rejected' => 'danger');
        $lbl = $decision_labels[$reviewer_decision] ?? $reviewer_decision;
        $clr = $decision_colors[$reviewer_decision] ?? 'secondary';
?>
                        <div class="alert alert-<?php echo $clr; ?> bg-<?php echo $clr; ?>-subtle py-2 px-3 small mb-2">
                            <strong>Revisor:</strong>
                            <?php echo esc_html($lbl); ?>
                        </div>

                        <?php
    endif; ?>

                        <?php if ($sciflow_status === 'submetido'): ?>
                        <div class="mb-3">
                            <form id="sciflow-assign-form" class="mb-2">
                                <label class="form-label small fw-bold text-muted">Atribuir Revisor</label>
                                <div class="d-flex gap-2">
                                    <select name="reviewer_id" class="form-select form-select-sm shadow-none">
                                        <option value="">Selecione...</option>
                                        <?php
        $roles = ($event_slug === 'enfrute') ? ['sciflow_revisor', 'sciflow_enfrute_revisor', 'administrator'] : ['sciflow_revisor', 'sciflow_semco_revisor', 'administrator'];
        $reviewers = get_users(['role__in' => $roles]);
        foreach ($reviewers as $u):
            // Conflict of Interest: Author cannot be a reviewer for their own work
            if ($u->ID === (int)$post->post_author)
                continue;
?>
                                        <option value="<?php echo $u->ID; ?>" <?php selected($reviewer_id, $u->ID); ?>>
                                            <?php echo esc_html($u->display_name); ?>
                                        </option>
                                        <?php
        endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">OK</button>
                                </div>
                                <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                <input type="hidden" name="action" value="sciflow_assign_reviewer">
                            </form>

                            <?php if ($reviewer_id != $current_user_id): ?>
                            <form id="sciflow-assume-form" class="mt-2 border-top pt-2">
                                <button type="submit" class="btn btn-warning btn-sm w-100 rounded-pill fw-bold">Assumir
                                    Avaliação</button>
                                <input type="hidden" name="reviewer_id" value="<?php echo $current_user_id; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                                <input type="hidden" name="action" value="sciflow_assign_reviewer">
                            </form>
                            <?php
        endif; ?>
                        </div>
                        <?php
    endif; ?>

                        <?php if ($sciflow_status === 'aguardando_pagamento' && current_user_can('administrator')): ?>
                        <form id="sciflow-confirm-payment-form" class="mb-3 border-bottom pb-3">
                            <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-bold">Confirmar
                                Pagamento</button>
                            <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                            <input type="hidden" name="action" value="sciflow_confirm_payment_admin">
                        </form>
                        <?php
    endif; ?>

                        <?php if (in_array($sciflow_status, ['aguardando_decisao', 'submetido_com_revisao', 'submetido'])): ?>
                        <form id="sciflow-decision-form" class="pt-2 border-top">
                            <label class="form-label small fw-bold text-muted">Decisão Editorial</label>
                            <select name="decision" class="form-select form-select-sm mb-2 shadow-none">
                                <option value="">Selecione...</option>
                                <option value="approve">Aprovar e Publicar</option>
                                <option value="return_to_author">Solicitar Alterações</option>
                                <option value="reject">Reprovar</option>
                            </select>
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="3"
                                placeholder="Observações..."></textarea>
                            <button type="submit"
                                class="btn btn-success btn-sm w-100 rounded-pill fw-bold">Registrar</button>
                            <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                            <input type="hidden" name="action" value="sciflow_editorial_decision">
                        </form>
                        <?php
    endif; ?>

                        <!-- Decisão do Pôster -->
                        <?php
    $poster_id = get_post_meta($article_id, '_sciflow_poster_id', true);
    if ($poster_id || $sciflow_status === 'poster_em_correcao'): ?>
                        <div class="mt-4 pt-3 border-top sciflow-decision-form"
                            data-post-id="<?php echo $article_id; ?>">
                            <label class="form-label small fw-bold text-muted">Decisão do Pôster</label>
                            <textarea class="form-control form-control-sm mb-2 sciflow-poster-decision-notes"
                                placeholder="Notas para o autor..." rows="3"></textarea>
                            <div class="d-flex flex-column gap-2">
                                <button
                                    class="btn btn-success btn-sm w-100 rounded-pill fw-bold sciflow-poster-decision-btn"
                                    data-decision="approve_poster">Aprovar Pôster</button>
                                <button
                                    class="btn btn-warning btn-sm w-100 rounded-pill fw-bold sciflow-poster-decision-btn"
                                    data-decision="request_new_poster">Pedir Ajustes</button>
                                <button
                                    class="btn btn-danger btn-sm w-100 rounded-pill fw-bold sciflow-poster-decision-btn"
                                    data-decision="reject_poster">Reprovar Pôster</button>
                            </div>
                        </div>
                        <?php
    endif; ?>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- Ações do Revisor -->
                <?php if ($is_reviewer && !$is_author && (in_array($sciflow_status, ['em_avaliacao', 'submetido']) || $reviewer_decision)): ?>
                <div class="card border-0 shadow-sm rounded-4 border-start border-info border-4 mb-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-bold text-dark mb-3">Sua Avaliação</h3>
                        <?php $can_rev_edit = in_array($sciflow_status, ['em_avaliacao', 'submetido']); ?>
                        <form id="<?php echo $can_rev_edit ? 'sciflow-review-form' : ''; ?>">
                            <div class="mb-3">
                                <?php $criteria = ['originalidade' => 'Originalidade', 'objetividade' => 'Objetividade', 'organizacao' => 'Organização', 'metodologia' => 'Metodologia', 'aderencia' => 'Aderência'];
    foreach ($criteria as $key => $label): ?>
                                <div class="d-flex align-items-center justify-content-between mb-2 small">
                                    <span class="text-muted">
                                        <?php echo $label; ?>
                                    </span>
                                    <input type="number" name="scores[<?php echo $key; ?>]"
                                        class="form-control form-control-sm w-25 text-center shadow-none" step="0.5"
                                        min="0" max="10" value="<?php echo esc_attr($reviewer_scores[$key] ?? ''); ?>"
                                        <?php echo $can_rev_edit ? '' : 'disabled' ; ?>>
                                </div>
                                <?php
    endforeach; ?>
                            </div>
                            <select name="decision" class="form-select form-select-sm mb-2" <?php echo $can_rev_edit
                                ? 'required' : 'disabled' ; ?>>
                                <option value="">Decisão...</option>
                                <option value="approved" <?php selected($reviewer_decision, 'approved' ); ?>>Aprovado
                                </option>
                                <option value="approved_with_considerations" <?php
                                    selected($reviewer_decision, 'approved_with_considerations' ); ?>>Ajustes</option>
                                <option value="rejected" <?php selected($reviewer_decision, 'rejected' ); ?>>Reprovar
                                </option>
                            </select>
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="3"
                                placeholder="Parecer..." <?php echo $can_rev_edit ? '' : 'disabled' ;
                                ?>><?php echo esc_textarea($reviewer_notes); ?></textarea>
                            <?php if ($can_rev_edit): ?>
                            <button type="submit"
                                class="btn btn-info text-white btn-sm w-100 rounded-pill fw-bold">Enviar</button>
                            <?php
    endif; ?>
                            <input type="hidden" name="post_id" value="<?php echo $article_id; ?>">
                            <input type="hidden" name="action" value="sciflow_submit_review">

                            <?php if ($ranking_score && $reviewer_decision): ?>
                            <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                <span class="small fw-bold text-muted">Nota Final Calculada:</span>
                                <span class="badge bg-info text-white">
                                    <?php echo esc_html($ranking_score); ?>
                                </span>
                            </div>
                            <?php
    endif; ?>
                        </form>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- 2.5. Avaliação Detalhada (Notas) -->
                <?php if (($is_editor || $is_reviewer) && !empty($reviewer_scores)): ?>
                    <div class="card border-0 shadow-sm rounded-4 border-start border-info border-4 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-dark mb-3">Avaliação Detalhada (Notas)</h3>
                            <div class="row g-2">
                                <?php 
                                $criteria = ['originalidade' => 'Originalidade', 'objetividade' => 'Objetividade', 'organizacao' => 'Organização', 'metodologia' => 'Metodologia', 'aderencia' => 'Aderência'];
                                foreach ($criteria as $key => $label): 
                                    if (isset($reviewer_scores[$key])): ?>
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between small border-bottom pb-1">
                                                <span class="text-muted"><?php echo $label; ?>:</span>
                                                <span class="fw-bold"><?php echo esc_html($reviewer_scores[$key]); ?></span>
                                            </div>
                                        </div>
                                    <?php endif;
                                endforeach; ?>
                            </div>

                            <?php if ($ranking_score): ?>
                                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="h6 fw-bold mb-0">Nota Final:</span>
                                    <span class="badge bg-primary fs-6"><?php echo esc_html($ranking_score); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- 3. Seção do Pôster (Arquivos e Upload) -->
                <?php
$is_approved = in_array($sciflow_status, ['aprovado', 'poster_em_correcao']);
$show_upload = ($is_author && $is_approved);
if ($poster_id || $show_upload):
    $poster_url = $poster_id ? wp_get_attachment_url($poster_id) : '';
    $poster_file = $poster_id ? get_attached_file($poster_id) : '';
    $poster_size = $poster_file && file_exists($poster_file) ? size_format(filesize($poster_file)) : 'N/A';
    $upload_pg = get_pages(['meta_key' => '_wp_page_template', 'meta_value' => 'template-poster-upload.php', 'number' => 1]);
    $upload_url = !empty($upload_pg) ? get_permalink($upload_pg[0]->ID) : home_url('/');
?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 border-start border-success border-4">
                    <div class="card-body p-4">
                        <h3 class="h6 fw-bold text-dark mb-3"><i
                                class="bi bi-file-earmark-pdf text-danger me-2"></i>Pôster Arquivado</h3>
                        <?php if ($poster_id): ?>
                        <div class="bg-light p-2 rounded mb-3 d-flex justify-content-between align-items-center">
                            <span class="small text-truncate me-2" style="max-width: 120px;">
                                <?php echo esc_html(basename($poster_file)); ?>
                            </span>
                            <span class="badge bg-secondary">
                                <?php echo esc_html($poster_size); ?>
                            </span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?php echo esc_url($poster_url); ?>" target="_blank"
                                class="btn btn-success btn-sm flex-grow-1 rounded-pill fw-bold">Ver</a>
                            <?php if ($show_upload): ?>
                            <a href="<?php echo esc_url(add_query_arg('article_id', $article_id, $upload_url)); ?>"
                                class="btn btn-outline-success btn-sm flex-grow-1 rounded-pill fw-bold">Substituir</a>
                            <?php
        endif; ?>
                        </div>
                        <?php
    elseif ($show_upload): ?>
                        <p class="small text-muted mb-3">Envie o pôster em PDF para prosseguir.</p>
                        <a href="<?php echo esc_url(add_query_arg('article_id', $article_id, $upload_url)); ?>"
                            class="btn btn-primary btn-sm w-100 rounded-pill fw-bold">Enviar Pôster</a>
                        <?php
    endif; ?>
                    </div>
                </div>
                <?php
endif; ?>

                <!-- 4. Histórico de Mensagens e Pareceres -->
                <?php 
                $message_history = SciFlow_Editorial::get_message_history($article_id);
                if (!empty($message_history)): 
                ?>
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h3 class="h6 fw-bold text-dark border-bottom pb-2 mb-3">Histórico de Mensagens e Pareceres</h3>
                            <div class="sciflow-message-history d-flex flex-column gap-3">
                                <?php foreach ($message_history as $msg): 
                                    $role_bg = ($msg['role'] === 'revisor') ? 'bg-info-subtle border-info' : 'bg-primary-subtle border-primary';
                                    $role_text = ($msg['role'] === 'revisor') ? 'Parecer do Revisor' : 'Decisão/Nota Editorial';
                                    $date = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($msg['timestamp']));
                                ?>
                                    <div class="message-card p-3 rounded-3 border-start border-4 <?php echo $role_bg; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                                            <strong class="small text-dark"><?php echo esc_html($role_text); ?></strong>
                                            <span class="text-muted" style="font-size: 0.75rem;"><?php echo esc_html($date); ?></span>
                                        </div>
                                        <div class="small text-dark">
                                            <?php echo wpautop(wp_kses_post($msg['content'])); ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Link para Edição (Caso o status permita) -->
                <?php
$editable_statuses = array('rascunho', 'em_correcao', 'reprovado', 'aprovado_com_consideracoes');
if ($is_author && in_array($sciflow_status, $editable_statuses)): ?>
                <div
                    class="card border-0 shadow-sm rounded-4 border-start border-warning border-4 mb-4 text-center p-4">
                    <i class="bi bi-pencil-square display-6 text-warning mb-3"></i>
                    <h3 class="h6 fw-bold text-dark">Edição Aberta</h3>
                    <a href="<?php echo esc_url(add_query_arg('edit_id', $article_id, home_url('/submissao'))); ?>"
                        class="btn btn-warning btn-sm w-100 rounded-pill fw-bold">Editar Trabalho</a>
                </div>
                <?php
endif; ?>
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