<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>
<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
                <div class="cmp-hero">
                    <section class="it-hero-wrapper bg-light align-items-start">
                        <div class="it-hero-text-wrapper pt-0 ps-0 pb-4 pb-lg-60"><br><br><br>
                            <h2 class="text-center text-danger mb-4"><?php esc_html_e( 'Pagina non trovata', 'design_comuni_italia' ); ?></h2>
                            <p class="text-center text-muted mb-5 fs-5">
                                <?php _e( 
                                    'Siamo spiacenti, ma non siamo riusciti a trovare la pagina o la categoria che stavi cercando. <br><br> 
                                    Ti consigliamo di tornare indietro o esplorare il nostro sito tramite il menu. Puoi anche cliccare il link qui sotto per tornare facilmente alla pagina precedente.',
                                    'design_comuni_italia'
                                ); ?>
                            </p>
                            <div class="text-center">
                                <a href="javascript:history.back();" title="Torna alla pagina precedente" class="btn btn-primary btn-lg">
                                    <?php esc_html_e( 'Torna indietro', 'design_comuni_italia' ); ?>
                                </a>
                            </div>

                            <!-- Inizio link suggeriti -->
                            <div class="text-center mt-5">
                                <p class="text-muted"><?php esc_html_e( 'Forse stavi cercando:', 'design_comuni_italia' ); ?></p>
                                <ul class="list-unstyled">
                                    <li><a href="/luoghi" class="text-decoration-none text-primary"><?php esc_html_e( 'Luoghi', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/eventi" class="text-decoration-none text-primary"><?php esc_html_e( 'Eventi', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/tipi_notizia/notizie" class="text-decoration-none text-primary"><?php esc_html_e( 'Notizie', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/tipi_notizia/comunicati" class="text-decoration-none text-primary"><?php esc_html_e( 'Comunicati', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/tipi_notizia/avvisi" class="text-decoration-none text-primary"><?php esc_html_e( 'Avvisi', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/servizi" class="text-decoration-none text-primary"><?php esc_html_e( 'Servizi', 'design_comuni_italia' ); ?></a><br>
                                    <a href="/amministrazione/documenti-e-dati" class="text-decoration-none text-primary"><?php esc_html_e( 'Documenti e Dati', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/amministrazione/uffici" class="text-decoration-none text-primary"><?php esc_html_e( 'Uffici', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/tipi_documento/modulistica" class="text-decoration-none text-primary"><?php esc_html_e( 'Modulistica', 'design_comuni_italia' ); ?></a>, 
                                    <a href="/amministrazione/personale-amministrativo" class="text-decoration-none text-primary"><?php esc_html_e( 'Personale Amministrativo', 'design_comuni_italia' ); ?></a>
                                </ul>
                            </div>
                            <!-- Fine link suggeriti -->
                        </div>
                    </section>
                    <br>            
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_template_part("template-parts/home/ricerca"); ?>
<?php get_template_part("template-parts/common/valuta-servizio"); ?>
<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
<?php
get_footer();  

