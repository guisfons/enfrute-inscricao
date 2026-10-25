<?php
/**
 * Template Name: Baixar Certificado
 *
 * Presentation only: the download logic (access rules, rate limiting,
 * protected storage) lives in the SciFlow WP plugin ([sciflow_certificados]).
 */

if (!defined('ABSPATH')) {
    exit;
}

if (inscricao_enfrute_require('sciflow')) {
    return;
}

get_header();
?>

<main id="main" class="site-main py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <?php echo do_shortcode('[sciflow_certificados title="' . esc_attr__('Baixar Certificado', 'inscricao-enfrute') . '"]'); ?>

                <?php
                while (have_posts()) :
                    the_post();
                    if (get_the_content()) {
                        echo '<div class="mt-4 p-4 bg-white rounded shadow-sm">';
                        the_content();
                        echo '</div>';
                    }
                endwhile;
                ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();
