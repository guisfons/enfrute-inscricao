<?php
/**
 * The template for displaying the footer
 */
?>

<footer id="colophon" class="site-footer">
    <div class="container">
        <div class="footer-info">
            <p>&copy;
                <?php echo esc_html(wp_date('Y')); ?>
                <?php bloginfo('name'); ?>. <?php esc_html_e('Todos os direitos reservados.', 'inscricao-enfrute'); ?>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>