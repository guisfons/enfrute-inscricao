<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <header id="masthead" class="site-header">
        <div class="container sticky-header">
            <div class="site-branding">
                <?php inscricao_enfrute_logo(); ?>
            </div>

            <nav id="site-navigation" class="main-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_id' => 'primary-menu',
                ));

                $product = enfrute_get_registration_product();
                if ($product):
                    ?>
                    <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="btn-primary"><?php esc_html_e('INSCREVA-SE', 'inscricao-enfrute'); ?></a>
                <?php endif; ?>
            </nav>
        </div>
    </header>