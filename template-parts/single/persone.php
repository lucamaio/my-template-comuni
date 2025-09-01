<?php 
global $persone;
if ($persone && is_array($persone) && count($persone) > 0) { ?>
  <?php foreach ($persone as $person_id) { 
    $prefix = '_dci_persona_pubblica_';
    $nome = dci_get_meta('nome', $prefix, $person_id);
    $cognome = dci_get_meta('cognome', $prefix, $person_id); 
    $descrizione = dci_get_meta('descrizione_breve', $prefix,  $person_id);
      // Recupera gli incarichi utilizzando la funzione dci_get_meta
      $incarichi = dci_get_meta("incarichi", $prefix, $person_id);
      
      // Controlla se $incarichi è un array e se ha almeno un elemento
      if (is_array($incarichi) && !empty($incarichi) && isset($incarichi[0])) {
          $incarico = get_the_title($incarichi[0]); // Recupera il titolo se il primo incarico esiste
      } else {
          $incarico = ''; // Imposta $incarico a una stringa vuota se non ci sono incarichi
      }
    $img = dci_get_meta("foto", $prefix, $person_id);

    if ($img != null) { ?>
      <div class="col-12 col-md-8 col-lg-6 mb-30">
        <div class="card-wrapper rounded h-auto mt-10">
          <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
            <div class="card-body pe-3">
              <p class="card-title text-paragraph-regular-medium-semi mb-3">
                <a href="<?php echo get_permalink($person_id); ?>">
                  <span class="chip-label"><?php echo $nome . ' ' . $cognome; ?></span>
                </a>
              </p>
              <div class="card-text">
                <div class="richtext-wrapper lora">
                  <?php echo $descrizione; ?>
                </div>
              </div>
            </div>
            <!-- Mostra immagine -->
            <div class="avatar size-xl">
                <?php dci_get_img($img); ?>
              </div>   
          </div>
        </div>
      </div>
    <?php } else { ?>
      <div class="col-12 col-md-8 col-lg-6 mb-30">
        <div class="card-wrapper rounded h-auto mt-10">
          <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
            <div class="card-body pe-3">
              <p class="card-title text-paragraph-regular-medium-semi mb-3">
                <a href="<?php echo get_permalink($person_id); ?>">
                  <span class="chip-label"><?php echo $nome . ' ' . $cognome; ?></span>
                </a>
              </p>
              <div class="card-text">
                <div class="richtext-wrapper lora">
                  <?php echo $descrizione; ?>
                </div>
              </div>
            </div>    
          </div>
        </div>
      </div>
    <?php } ?>
  <?php } ?>
<?php } ?>
