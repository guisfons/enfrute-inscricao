<?php
/**
 * The template for displaying all pages
 */

get_header();
?>

<main id="primary" class="site-main py-5 bg-light">
    <div class="container py-4">
        <header class="entry-header mb-5">
            <?php the_title('<h1 class="entry-title fw-900 text-dark">', '</h1>'); ?>
        </header>

        <div class="entry-content">
            <?php
            while (have_posts()):
                the_post();
                the_content();
            endwhile;
            ?>
        </div>
    </div>
</main>

<?php
get_footer();
