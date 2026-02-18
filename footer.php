<?php
/**
 * The template for displaying the footer
 */
?>

<footer id="colophon" class="site-footer">
    <div class="container">
        <div class="footer-info">
            <p>&copy;
                <?php echo date('Y'); ?>
                <?php bloginfo('name'); ?>. Todos os direitos reservados.
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>

</html>