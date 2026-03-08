<?php
/*
Template Name: Submissão de Palestra
Template Post Type: page
*/

get_header(); ?>

<main id="primary" class="site-main submission-page speaker-page">
    <header class="page-header">
        <div class="container text-center">
            <h1 class="page-title">
                <?php the_title(); ?>
            </h1>
            <div class="page-description">
                <p>Formulário exclusivo para palestrantes.</p>
                <p>Preencha os dados abaixo para submeter as informações da sua palestra.</p>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="sciflow-submission-container">
            <?php
            // Render the speaker form shortcode
            echo do_shortcode('[sciflow_speaker_form]');
            ?>
        </div>
    </div>
</main>

<style>
    .speaker-page {
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