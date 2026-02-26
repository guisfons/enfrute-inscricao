<?php
/**
 * Main template file
 */

get_header(); ?>

<main id="primary" class="site-main">
    <section class="hero-section">
        <div class="container">
            <h1>XIX ENFRUTE</h1>
            <p>XIX Encontro Nacional sobre Fruticultura de Clima Temperado. Junte-se a nós para o principal evento do
                setor.</p>
            <?php
            $product = enfrute_get_registration_product();
            if ($product):
                ?>
                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-light">INSCREVA-SE AGORA</a>
            <?php endif; ?>
        </div>
    </section>

    <section class="about-section">
        <div class="container">
            <div class="section-title">
                <h2>Sobre o Evento</h2>
            </div>
            <div class="content-grid">
                <div class="text-content">
                    <p>O ENFRUTE é reconhecido como um dos mais importantes fóruns de discussão técnica e científica da
                        fruticultura de clima temperado do Brasil.</p>
                    <p>O objetivo é promover a integração entre pesquisadores, técnicos, produtores e estudantes,
                        visando o desenvolvimento sustentável da fruticultura.</p>
                </div>
                <div class="image-placeholder"
                    style="background: #eee; height: 300px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <span style="color: #999;">Imagem do Evento</span>
                </div>
            </div>
        </div>
    </section>

    <?php
    if (have_posts()):
        while (have_posts()):
            the_post();
            the_content();
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>