<?php
/**
 * Notizia template file
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
        while ( have_posts() ) :
            the_post();
            $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

            $prefix= '_dci_notizia_';
            $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
            $data_pubblicazione_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
            $date = date_i18n('d F Y', mktime(0, 0, 0, $data_pubblicazione_arr[1], $data_pubblicazione_arr[0], $data_pubblicazione_arr[2]));
            $persone = dci_get_meta("persone", $prefix, $post->ID);
            $descrizione = dci_get_wysiwyg_field("testo_completo", $prefix, $post->ID);
            $documenti = dci_get_meta("documenti", $prefix, $post->ID);
            $luoghi = dci_get_meta("luoghi", $prefix, $post->ID);
            $allegati = dci_get_meta("allegati", $prefix, $post->ID);
            $datasets = dci_get_meta("dataset", $prefix, $post->ID);
            $a_cura_di = dci_get_meta("a_cura_di", $prefix, $post->ID);
            

            $gallery = dci_get_meta("gallery", $prefix, $post->ID);
            $video = dci_get_meta("video", $prefix, $post->ID);
            $trascrizione = dci_get_meta("trascrizione", $prefix, $post->ID);
            $audio= dci_get_meta("audio", $prefix, $post->ID);
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
                            <div class="col-6">
                                <small>Tempo di lettura:</small>
                                <p class="fw-semibold" id="readingTime"></p>
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
                                                                <?php if(!empty($gallery) OR !empty($video) OR !empty($audio)){?> 
                                                                    <li class="nav-item">
                                                                    <a class="nav-link" href="#media">
                                                                    <span class="title-medium">Media</span>
                                                                    </a>
                                                                </li>
                                                                <?php }?>
                                                                <?php if( is_array($documenti) && count($documenti) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#documenti">
                                                                    <span class="title-medium">Documenti</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if( is_array($allegati) && count($allegati) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#allegati">
                                                                    <span class="title-medium">Allegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if( is_array($datasets) && count($datasets) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#dataset">
                                                                    <span class="title-medium">Dataset</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>                                                              
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#a-cura-di">
                                                                    <span class="title-medium">A cura di</span>
                                                                    </a>
                                                                </li>  
                                                                <?php if(is_array($persone) && count($persone)) { ?>
                                                                     <li class="nav-item">
                                                                    <a class="nav-link" href="#idpersone">
                                                                    <span class="title-medium">Persone</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>                                                  
                                                                <?php if(is_array($luoghi) && count($luoghi)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#luoghi">
                                                                    <span class="title-medium">Luoghi</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
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

                       
                    <?php if(!empty($gallery) OR !empty($video) OR !empty($audio)){?>                        
                            <article class="it-page-section it-grid-list-wrapper anchor-offset mt-5" id="media">
                                <?php if (is_array($gallery) && count($gallery)) {?>
                                    <h3 class="h3">Multimedia</h3>
                                    <?php get_template_part("template-parts/single/gallery");
                                } ?>
                                <?php if ($video) {?>
                                    <?php get_template_part("template-parts/single/video");
                                }?>
                                <?php if (!empty($audio)) { ?>
                                    <div class="mb-4">
                                        <h3 class="h3">File Audio</h3>
                                        <div class="grid-row">
                                            <?php foreach($audio as $audio_id){
                                                $extension = pathinfo($audio_id, PATHINFO_EXTENSION);
                                                ?>
                                                <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                                <div class="audio-wrapper">
                                                    <div class="custom-audio-player">
                                                        <audio id="<?= $audio_id;?>" controls>
                                                            <source src="<?= $audio_id;?>" type="audio/<?= $extension;?>">
                                                                Il tuo browser non supporta l'elemento audio.
                                                            </audio>
                                                        <div class="audio-controls">
                                                            <button onclick="playAudio('<?php echo $audio_id; ?>')">Play</button>
                                                            <button onclick="pauseAudio('<?php echo $audio_id; ?>')">Pause</button>
                                                            <button onclick="stopAudio('<?php echo $audio_id; ?>')">Stop</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php }?>                                            
                                        </div>
                                    </div>
                                <?php } ?>
                            </article>
                    <?php } ?>                           
                    <?php if( is_array($documenti) && count($documenti) ) { ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="documenti">Documenti</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($documenti as $doc_id) {
                                $documento = get_post($doc_id);
                            ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_permalink($doc_id); ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Visualizza il documento <?php echo $documento->post_title; ?>">
                                        <?php echo $documento->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </article>
                    <?php } ?>
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

                                      <?php  // Recupera il titolo della pagina
                    					$title_allegato = $allegato->post_title;					
                    				
                    					if (strlen($title_allegato) > 50) {
                    					    $title_allegato = substr($title_allegato, 0, 50) . '...';
                    					}					
                    					// Controlla se il titolo contiene almeno 5 lettere maiuscole consecutive
                    					if (preg_match('/[A-Z]{5,}/', $title_allegato)) {
                    					    // Se sì, lo trasforma in minuscolo con la prima lettera maiuscola
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
                    <?php } ?>
                        <?php if( is_array($datasets) && count($datasets) ) { ?>
                        <article class="it-page-section anchor-offset mt-5">
                            <h4 id="dataset">Dataset</h4>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                <?php foreach ($datasets as $dataset_id) {
                                    $dataset = get_post($dataset_id);
                                    ?>
                                    <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                        <svg class="icon" aria-hidden="true">
                                            <use
                                                    xlink:href="#it-clip"
                                            ></use>
                                        </svg>
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <a class="text-decoration-none" href="<?php echo get_permalink($dataset_id); ?>" aria-label="Visualizza il dataset <?php echo $dataset->post_title; ?>" title="Visualizza il dataset <?php echo $dataset->post_title; ?>">
                                                    <?php echo $dataset->post_title; ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </article>
                        <?php } ?>
                       <article class="it-page-section anchor-offset mt-5">
                        <h4 id="a-cura-di">A cura di</h4>
                        <div class="row">
                            <!-- Colonna centrale per il contenuto -->
                            <div class="col-12 col-sm-8">
                                <!-- Piccolo testo introduttivo -->
                                <h6><small>Questa pagina è gestita da</small></h6>
                                
                                <!-- Loop per visualizzare le unità organizzative -->
                                <?php foreach ($a_cura_di as $uo_id) {
                                    $with_border = true;
                                    get_template_part("template-parts/unita-organizzativa/card");
                                } ?>
                            </div>
                        </div>
                       </article>
                        
                                <!-- Sezione per visualizzare le persone, se ci sono -->
                                <?php if(is_array($persone) && count($persone)) { ?>     
                                    <article class="it-page-section anchor-offset mt-5">
                                    <h4 id="idpersone">Persone</h4>
                                    <div class="row">
                                        <?php get_template_part("template-parts/single/persone"); ?>
                                    </div>
                                   </article>
                                <?php } ?>
                    
                                <!-- Sezione per visualizzare i luoghi correlati -->
                                <?php if ($luoghi && is_array($luoghi) && count($luoghi) > 0) { ?>
                                  <article class="it-page-section anchor-offset mt-5">
                                    <section id="luoghi" class="it-page-section mb-4">
                                        <h2 class="h4">Luoghi correlati</h2> 
                                        <h6><small>Luogo collegato alla notizia</small></h6>
                                        <div class="row">
                                            <?php foreach ($luoghi as $luogo_id) {
                                                ?><div class="col-xl-6 col-lg-8 col-md-12"><?php
                                                $with_border = true;
                                                $luogo = get_post($luogo_id);
                                                get_template_part("template-parts/luogo/card-title");
                                                ?></div><?php
                                            } ?>
                                        </div>
                                    </section>
                                  </article>
                                <?php } ?>
                            
                    
                      
                     
                            <article id="ulteriori-informazioni" class="it-page-section anchor-offset mt-5">
                                <h4 class="mb-3">Ulteriori informazioni</h4>
                            </article>
                            <?php get_template_part('template-parts/single/page_bottom'); ?>
                            </section>
                        </div>
                      </div>
                </div>
            </div>

            <?php get_template_part("template-parts/common/valuta-servizio"); ?>


        <?php
        endwhile; // End of the loop.
        ?>
    </main>
    <script>
        const descText = document.querySelector('#descrizione')?.closest('article').innerText;
        const wordsNumber = descText.split(' ').length
        document.querySelector('#readingTime').innerHTML = `${Math.ceil(wordsNumber / 200)} min`;
    </script>


<?php
get_footer();?>

<style>
.it-grid-item-wrapper {
    /* Imposta una larghezza fissa per il contenitore dell'immagine/audio se necessario */
    width: 100%;
}

.img-responsive-wrapper {
    display: flex;
    justify-content: center; /* Centratura orizzontale */
}

.audio-wrapper {
    width: 100%; /* Usa tutta la larghezza disponibile */
    padding: 10px; /* Padding per evitare che il player tocchi i bordi */
    box-sizing: border-box; /* Include padding e bordi nella larghezza totale */
}

.custom-audio-player {
    width: 100%; /* Usa tutta la larghezza disponibile del contenitore */
}

.audio-controls {
    margin-top: 10px; /* Spazio sopra i controlli audio */
    text-align: center; /* Centratura dei bottoni dei controlli */
}

.audio-controls button {
    margin: 0 5px; /* Spazio tra i bottoni */
}
</style>

<script>
// Funzione per riprodurre l'audio
function playAudio(id) {
    var audio = document.getElementById(id);
    if (audio) {
        audio.play();
    }
}

// Funzione per mettere in pausa l'audio
function pauseAudio(id) {
    var audio = document.getElementById(id);
    if (audio) {
        audio.pause();
    }
}

// Funzione per fermare l'audio
function stopAudio(id) {
    var audio = document.getElementById(id);
    if (audio) {
        audio.pause();
        audio.currentTime = 0;
    }
}
</script>

<?php
get_footer();
