<?php
/**
 * Template Name: Baixar Certificado
 */

// Handle download BEFORE any headers are sent
$error_msg = '';
if ( isset( $_POST['baixar_certificado_submit'] ) && isset( $_POST['certificado_email'] ) ) {
    if ( isset( $_POST['baixar_certificado_nonce'] ) && wp_verify_nonce( $_POST['baixar_certificado_nonce'], 'baixar_certificado_action' ) ) {
        $input_email = sanitize_email( $_POST['certificado_email'] );
        if ( ! is_email( $input_email ) ) {
            $error_msg = 'Por favor, insira um e-mail válido.';
        } else {
            $upload_dir = wp_upload_dir();
            $certificados_dir = $upload_dir['basedir'] . '/certificados/';
            $file_name = $input_email . '.pdf';
            $file_path = $certificados_dir . $file_name;
            
            if ( file_exists( $file_path ) ) {
                // Clear output buffers to prevent file corruption
                while ( ob_get_level() ) {
                    ob_end_clean();
                }
                
                header( 'Content-Description: File Transfer' );
                header( 'Content-Type: application/pdf' );
                header( 'Content-Disposition: attachment; filename="' . basename( $file_path ) . '"' );
                header( 'Expires: 0' );
                header( 'Cache-Control: must-revalidate' );
                header( 'Pragma: public' );
                header( 'Content-Length: ' . filesize( $file_path ) );
                readfile( $file_path );
                exit;
            } else {
                $error_msg = 'Nenhum certificado encontrado para o e-mail informado.';
            }
        }
    } else {
        $error_msg = 'Sessão expirada. Por favor, recarregue a página e tente novamente.';
    }
}

// Get header AFTER handling download logic
get_header();

$email = '';
if ( isset($_POST['certificado_email']) ) {
    $email = sanitize_email($_POST['certificado_email']);
} elseif ( is_user_logged_in() ) {
    $current_user = wp_get_current_user();
    $email = $current_user->user_email;
}

?>

<main id="main" class="site-main py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h3 mb-4 text-center">Baixar Certificado</h1>
                        
                        <?php if ( ! empty( $error_msg ) ) : ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo esc_html( $error_msg ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <?php wp_nonce_field( 'baixar_certificado_action', 'baixar_certificado_nonce' ); ?>
                            
                            <div class="mb-4">
                                <label for="certificado_email" class="form-label fw-bold">E-mail cadastrado no evento:</label>
                                <input type="email" class="form-control form-control-lg" id="certificado_email" name="certificado_email" value="<?php echo esc_attr( $email ); ?>" required placeholder="Digite seu e-mail">
                                <div class="form-text">O arquivo deve ser baixado utilizando o mesmo e-mail usado na inscrição.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" name="baixar_certificado_submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-download me-2"></i> Baixar Arquivo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php
                // Display page content if any
                while ( have_posts() ) :
                    the_post();
                    if ( get_the_content() ) {
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
