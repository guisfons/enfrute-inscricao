<?php
/**
 * Template Name: SciFlow - Pôsteres Públicos
 *
 * This template displays the public gallery of approved posters.
 */

get_header(); ?>

<main id="primary" class="site-main">
    <div class="sciflow-page-container">
        <?php echo do_shortcode('[sciflow_public_posters]'); ?>
    </div>
</main>

<?php get_footer(); ?>
