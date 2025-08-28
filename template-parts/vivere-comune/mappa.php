<?php
    // Mi ricavo i campi dalla pagina_vivi
    $show_map = dci_get_option('ck_show_map', 'vivi');
    $link_map = dci_get_option('link_map', 'vivi');

    // Controllo se l'opzione per visualizzare la mappa e se il link non è null
    if ($show_map === 'true' && !empty($link_map) && $link_map != null) {
?>
        <div class="row custom-map-container" style="overflow: hidden; background: transparent; display: flex; justify-content: center; align-items: center; width: 100%; height: 450px;">
            <div style="position: relative; width: 99%; height: 450px; background: transparent; margin-left: 1%; /* Spostato a destra */">
                <iframe style="border: 0; width: 100%; height: 100%; max-width: 100%;" src="<?= $link_map ?>" allowfullscreen scrolling="no"></iframe>
            </div>
        </div>
<?php }
?>

<style>
/* Sovrascrivere --bs-gutter-y solo per la sezione specifica */
.custom-map-container .g-4, .custom-map-container .gy-4 {
    --bs-gutter-y: 0 !important;
}
</style>






