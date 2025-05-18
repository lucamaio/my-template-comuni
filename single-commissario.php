<?php
/**
 * Commisario OSL template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */

global $uo_id, $inline, $audio;

get_header();



?>

<main>
    <?php 
    while ( have_posts() ) {
        the_post();
        $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        $prefix= '_dci_commissario_';
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $data_pubblicazione_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $date = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione_arr[1], $data_pubblicazione_arr[0], $data_pubblicazione_arr[2]));
        $descrizione = dci_get_wysiwyg_field("descrizione", $prefix, $post->ID);
        $allegati = dci_get_meta("allegati", $prefix, $post->ID);
        $a_cura_di = dci_get_meta("a_cura_di", $prefix, $post->ID);
        
        ?>
        <div class="container" id="main-container">
            <div class="row">
                <div class="col px-lg-4">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 px-lg-4 py-lg-2">
                <?php if (preg_match('/[A-Z]{5,}/', get_the_title())) {
                               echo '<h1 data-audio>'.ucfirst(strtolower(get_the_title())).'</h1>';
                            }else{
                                echo '<h1 data-audio>'. get_the_title().'</h1>';
                            }
                    ?>
                    <h2 class="visually-hidden" data-audio>Dettagli della notizia</h2>
                    <?php if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
                               echo '<p data-audio>'.ucfirst(strtolower($descrizione_breve)).'</p>';
                            }else{
                                echo '<p data-audio>'. $descrizione_breve.'</p>';
                            }
                    ?>       
                    <div class="row mt-5 mb-4">
                        <div class="col-6">
                            <small>Data:</small>
                            <p class="fw-semibold font-monospace">
                                <?php echo $date; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <?php
                    $inline = true;
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>                 
            </div>                      
        </div>


<?php

// Se la sezione viene disattivata, non caricare nulla
$ck_osl = dci_get_option('ck_osl', 'Amministrazione');
if ($ck_osl !== 'true') {
?>
    <div style="max-width: 800px; margin: 20px auto; padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f44336; color: white;">
                    <th colspan="2" style="text-align: center; padding: 10px; font-size: 1.5em;">Sezione Temporaneamente Disabilitata</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="padding: 20px; text-align: center; font-size: 1.2em; color: #333;">
                        <p><strong>Attenzione:</strong></p>
                        <p>La sezione che stai cercando di accedere è attualmente disabilitata dal pannello di amministrazione. Ci scusiamo per l'inconveniente.</p>
                        <p>Se desideri visualizzare i dati, contatta l'amministratore del sito per ulteriori informazioni o attivazioni.</p>
                        <p><em>Ti ringraziamo per la pazienza e comprensione!</em></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php
get_footer();         
    exit; // Fermiamo l'esecuzione del codice dopo aver mostrato il messaggio
 }?>

        <div class="container">
            <div class="row border-top border-light row-column-border row-column-menu-left">
                <aside class="col-lg-4">
                    <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                        <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                            <div class="navbar-custom" id="navbarNavProgress">
                                <div class="menu-wrapper">
                                    <div class="link-list-wrapper">
                                        <div class="accordion">
                                            <div class="accordion-item">
                                                <span class="accordion-header" id="accordion-title-one">
                                                    <button
                                                        class="accordion-button pb-10 px-3 text-uppercase"
                                                        type="button"
                                                        aria-controls="collapse-one"
                                                        aria-expanded="true"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#collapse-one"
                                                    >INDICE DELLA PAGINA
                                                        <svg class="icon icon-sm icon-primary align-top">
                                                            <use xlink:href="#it-expand"></use>
                                                        </svg>
                                                    </button>
                                                </span>
                                                <div class="progress">
                                                    <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                                    <div class="accordion-body">
                                                        <ul class="link-list" data-element="page-index">
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#descrizione">
                                                                <span class="title-medium">Descrizione</span>
                                                                </a>
                                                            </li>
                                                            <?php if( is_array($allegati) && count($allegati) ) { ?>
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#allegati">
                                                                    <span class="title-medium">Allegati</span>
                                                                </a>
                                                            </li>
                                                            <?php } ?>
                                                            
                                                            <li class="nav-item">
                                                                <a class="nav-link" href="#a-cura-di">
                                                                    <span class="title-medium">A cura di</span>
                                                                </a>
                                                            </li>  
                                                                                                        
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </aside>
                    <section class="col-lg-8 it-page-sections-container border-light">
                    <?php get_template_part('template-parts/single/image-large'); ?>
                    <article class="it-page-section anchor-offset" data-audio>                        
                        <h4 id="descrizione">Descrizione</h4>
                        <div class="richtext-wrapper lora">
                            <?php if (preg_match('/[A-Z]{5,}/', $descrizione)) {
                                   echo ucfirst(strtolower($descrizione));
                                }else{
                                    echo $descrizione;
                                }
                            ?>
                        </div>
                    </article>

                       
                    <?php if( is_array($allegati) && count($allegati) ) { ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="allegati">Allegati</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($allegati as $all_url) {
                                $all_id = attachment_url_to_postid($all_url);
                                $allegato = get_post($all_id);
                            ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_the_guid($allegato); ?>" aria-label="Scarica l'allegato <?php echo $allegato->post_title; ?>" title="Scarica l'allegato <?php echo $allegato->post_title; ?>">

                                      <?php  
                    					$title_allegato = $allegato->post_title;					
                    				
                    					if (strlen($title_allegato) > 50) {
                    					    $title_allegato = substr($title_allegato, 0, 50) . '...';
                    					}					
                    					if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                    					    $title_allegato = ucfirst(strtolower($title_allegato));
                    					}				
                    				                                
                                      echo $title_allegato; ?>

                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </article>
                    <?php }?>
                       <article class="it-page-section anchor-offset mt-5">
                        <h4 id="a-cura-di">A cura di</h4>
                        <div class="row">
                            <div class="col-12 col-sm-8">
                                <h6><small>Questa pagina è gestita da</small></h6>
                                
                                <?php foreach ($a_cura_di as $uo_id) {
                                    $with_border = true;
                                    get_template_part("template-parts/unita-organizzativa/card");
                                } ?>
                            </div>
                        </div>
                       </article>
                        
                     
                            <article id="ulteriori-informazioni" class="it-page-section anchor-offset mt-5">
                                <h4 class="mb-3">Ulteriori informazioni</h4>
                            </article>
                            <?php get_template_part('template-parts/single/page_bottom'); ?>
                            </section>
                        </div>
                      </div>
                </div>
         

            <?php get_template_part("template-parts/common/valuta-servizio"); ?>

    <?php }?>
    </main>
       <?php
get_footer();           







