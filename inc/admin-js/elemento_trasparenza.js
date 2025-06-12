jQuery( document ).ready(function() {

    /**
     * gestione campi obbligatori
     */

    let inputAlboPretorio = jQuery('input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]');
    inputAlboPretorio.each(function() {
        jQuery(this).click(function(){
            dci_remove_highlight_missing_field('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
        });
    });

    // Controlla l'ID del metabox o del campo per il documento, se intendi evidenziarlo
    // L'ID del metabox è cmb2-metabox-IDDELTUOMETABOX
    // L'ID del campo file è _dci_elemento_trasparenza_file
    // Se intendi rimuovere l'highlighting quando si clicca sul metabox dei documenti, usa l'ID del metabox generato da CMB2
    jQuery("body").on('click', "#cmb2-metabox-_dci_elemento_trasparenza_box_documento", function() {
        dci_remove_highlight_alternative_field('#cmb2-metabox-_dci_elemento_trasparenza_box_documento'); // Assicurati che questa funzione esista e faccia ciò che intendi
    });


    /**
     * controllo all'invio del form
     */
    jQuery( 'form[name="post"]' ).on('submit', function(e) {

        /**
         * Controllo compilazione campo tipo progetto
         */
        // Seleziona una categoria per determinare la sezione
        if(document.activeElement.id === 'publish' && jQuery('input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]:checked').length === 0) {
            dci_highlight_missing_field('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
            e.preventDefault(); // Impedisce l'invio del form
            return false;
        }

        // Aggiungi qui altri controlli se necessario
        // Esempio: Controlla se è stato caricato un file O se è stato inserito un URL, ma non entrambi
        // Questa logica è più complessa e richiederebbe di esaminare i valori dei campi
        // e decidere quale sia il comportamento desiderato.

        return true; // Se tutti i controlli passano, il form viene inviato
    });

function dci_highlight_missing_field(fieldClass) {
    jQuery(fieldClass).addClass("highlighted_missing_field")
        .append('<div id="field-required-msg" class="field-required-msg"><em>Campo obbligatorio</em></div>');

    jQuery('html,body').animate({
        scrollTop: jQuery(fieldClass).offset().top - 100 // Usa fieldClass per lo scroll
    }, 'slow');
}

function dci_remove_highlight_missing_field(fieldClass) {
    jQuery(fieldClass).removeClass("highlighted_missing_field");
    jQuery('.field-required-msg').remove();
}

// Se questa funzione è definita altrove, ignorala. Altrimenti, deve esistere.
function dci_remove_highlight_alternative_field(fieldSelector) {
    jQuery(fieldSelector).removeClass("highlighted_missing_field"); // Esempio
    // Aggiungi qui la logica specifica per rimuovere l'highlighting per i campi alternativi
}

});