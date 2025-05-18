<?php
    global $title, $description, $with_shadow, $data_element;

    if ($title == 'notizia') $title = 'Notizie';
    if (!$title) $title = get_the_title();
    if (!$description) $description = dci_get_meta('descrizione','_dci_page_', $post->ID ?? null);
?>
<div class="container" id="main-container">
    <div class="row justify-content-start"> <!-- Modificato per allineare a sinistra -->
        <div class="col-12 col-lg-10">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>
</div>

<div class="container" id="custom-section"> <!-- ID specifico per questa sezione -->
    <div class="row justify-content-start <?php echo $with_shadow ? 'row-shadow' : '' ?>"> 
        <div class="col-12 col-lg-10 custom-content-wrapper"> 
            <div class="cmp-hero">
                <section class="it-hero-wrapper bg-white align-items-start">
                    <div class="it-hero-text-wrapper pt-0 ps-0 pb-4 pb-lg-60">
                        <h1 class="text-black hero-title" <?php echo $data_element ? $data_element : null ?>>
                            <?php echo $title; ?>
                        </h1>
                        <div class="hero-text">
                            <p><?php echo $description; ?></p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>	
</div>
<style>
/* Stili applicati solo alla sezione specifica */
#custom-section .custom-content-wrapper {
    margin-left: 0; /* Elimina margini extra */
    text-align: left; /* Assicura il testo allineato a sinistra */
}

/* Se necessario, puoi anche ridurre la larghezza per enfatizzare l'allineamento */
@media (min-width: 992px) {
    #custom-section .custom-content-wrapper {
        max-width: 100%;
    }
}    
</style>



