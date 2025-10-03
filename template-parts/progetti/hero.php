<?php
    global $title, $description, $with_shadow, $data_element;

    if (!$title) $title = get_the_title();
    if (!$description && $post) $description = dci_get_meta('descrizione','_dci_page_',$post->ID);
?>

<div class="it-hero-wrapper it-wrapped-container" id="main-container" style="margin-bottom:100px">
    <div class="img-responsive-wrapper">
        <div class="img-responsive">
          <div class="img-wrapper">
            <!-- <?php dci_get_img($img); ?> -->
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/pnnr_sfo.png">
          </div>
        </div>
      </div>
    <div class="container">
        <div class="row">
            <div class="col-12 px-0 px-lg-2">
                <div class="it-hero-card it-hero-bottom-overlapping rounded hero-p pb-lg-80 drop-shadow">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10">
                            <?php 
                                $custom_class = 'mt-0';
                                get_template_part("template-parts/common/breadcrumb"); 
                            ?>
                        </div>
                    </div>
                    <div class="row sport-wrapper justify-content-between mt-lg-2">
                        <div class="col-12 col-lg-5 offset-lg-1">
                            <h1 class="mb-3 mb-lg-4 title-xxlarge">
                                <?php echo $title; ?>
                            </h1>
                            <h2 class="visually-hidden" id="news-details">Dettagli dell'argomento</h2>
                            <p class="u-main-black text-paragraph-regular-medium mb-60">
                                <?php echo $description; ?>
                            </p>
                        </div>
                        <div class="col-12 col-lg-5 me-lg-5">
                            <div class="card-wrapper card-column">
                                <div class="col-12 footer-items-wrapper logo-wrapper">
                                    <img class="ue-logo" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRnDeHmoF5L5Fkqq-Ohesy45F6z-_ku02O2Fg&s" width="100%">
                                    </div>                    
                            </div>
                        </div>
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</div>

