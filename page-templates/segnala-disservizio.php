<?php
   /* Template Name: segnala-disservizio
    *
    * Segnala disservizio template file
    *
    * @package Design_Comuni_Italia
    *
    */
  global $post;

function dci_enqueue_segnalazione_disservizio_script()  {
    $script_path = get_template_directory() . '/assets/js/segnalazione.js';
    wp_enqueue_script(
        'dci-segnalazione',
        get_template_directory_uri() . '/assets/js/segnalazione.js',
        array(),
        file_exists($script_path) ? filemtime($script_path) : null,
        true
    );
    wp_localize_script('dci-segnalazione', 'dciSegnalazione', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('dci_segnalazione_disservizio'),
    ));
}
add_action( 'wp_enqueue_scripts', 'dci_enqueue_segnalazione_disservizio_script' );
   
    get_header();
   
   ?>
<main>
   <?php
      while ( have_posts() ) :
      	the_post();
      	?>
   <div class="container" id="main-container">
      <div class="row justify-content-center">
         <div class="col-12 col-lg-10">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
         </div>
      </div>
   </div>
   <div id="form-steps">
      <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <div class="cmp-hero">
                                <section class="it-hero-wrapper bg-white align-items-start">
                                    <div class="it-hero-text-wrapper pt-0 ps-0 pb-3 pb-lg-4">
                                        <h1 class="text-black hero-title" data-element="page-name">
                                            Segnala disservizio
                                        </h1>
                                        <p class="hero-text text-paragraph mb-0">
                                            Comunica al Comune un problema riscontrato sul territorio o nell’utilizzo dei servizi,
                                            indicando il luogo e le informazioni utili per consentirne la verifica.
                                        </p>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
              <?php get_template_part("template-parts/segnalazione/tabs"); ?>
              <div class="container">
                    <div class="row report-form-layout">
                        <?php get_template_part("template-parts/segnalazione/index"); ?>
                        <div class="col-12 col-lg-8 offset-lg-1 section-wrapper">
                            <div class="steppers-content" aria-live="polite">
                                <?php get_template_part("template-parts/segnalazione/content"); ?>
                                <?php get_template_part("template-parts/segnalazione/buttons-bar"); ?>
                            </div>
                        </div>
                    </div>
                </div>
   </div>
   <section id="report-success" class="container d-none" aria-live="polite" tabindex="-1">
      <div class="row justify-content-center">
         <div class="col-12 col-lg-10">
            <div class="report-success-heading">
               <p class="text-uppercase title-xsmall-semi-bold t-primary mb-2">Segnala disservizio</p>
               <h1 class="title-xxlarge mb-2">Esito della segnalazione</h1>
               <p class="text-paragraph mb-4">
                  La procedura di segnalazione del disservizio è stata completata.
               </p>
            </div>
            <div class="alert alert-success cmp-disclaimer rounded p-4" role="status">
               <h2 class="title-large mb-2">Segnalazione inviata correttamente</h2>
               <p class="mb-2">
                  Grazie. La segnalazione è stata acquisita con il codice
                  <strong id="report-ticket-code"></strong>.
               </p>
               <p class="mb-0">Il Comune potrà contattarti ai recapiti indicati se serviranno ulteriori informazioni.</p>
            </div>
         </div>
      </div>
   </section>
   <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
   <?php
      endwhile; // End of the loop.
      ?>
</main>
<?php
get_footer();
