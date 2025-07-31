<?php
    // Mi ricavo i campi dalla pagina_vivi
    $show_map=dci_get_option('ck_show_map', 'vivi');
    $link_map=dci_get_option('link_map', 'vivi');

    // Controllo se l'opzione per visualizzare la mappa e se il link non è null

    if($show_map === 'true' && !empty($link_map) &&$link_map!=null){?>
        <div class="row g-4">
            <iframe style="border: 0;" src="<?= $link_map ?>" width="100%" height="450px" allowfullscreen></iframe>
        </div>
    <?php }
?>
