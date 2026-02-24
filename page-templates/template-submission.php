<?php
/*
Template Name: Submissão de Artigo
Template Post Type: page
*/

get_header(); ?>

<main id="primary" class="site-main submission-page">
    <header class="page-header">
        <div class="container text-center">
            <h1 class="page-title">
                <?php the_title(); ?>
            </h1>
            <p class="page-description">Preencha todos os campos obrigatórios (*) para submeter seu resumo científico
                para avaliação.</p>
        </div>
    </header>

    <div class="container">
        <div class="sciflow-submission-container">
            <?php
            // Render the submission form shortcode
            echo do_shortcode('[sciflow_submission_form]');
            ?>
        </div>
    </div>
</main>

<style>
    .submission-page {
        padding-bottom: 80px;
    }

    .page-header {
        background-color: var(--primary-green);
        color: #fff;
        padding: 60px 0;
        margin-bottom: 50px;
    }

    .page-title {
        margin: 0;
        font-size: 36px;
    }

    .page-description {
        margin-top: 15px;
        opacity: 0.9;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .sciflow-submission-container {
        background: #fff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        margin-top: -80px;
        position: relative;
        z-index: 10;
    }
</style>

<?php get_footer(); ?>