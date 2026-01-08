jQuery( document ).ready(function() {

    /**
     * gestione campi obbligatori
     */


    

    // Gestione del campo Categoria Trasparenza (radio buttons)
    let inputCategoriaTrasparenza = jQuery('input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]');
    inputCategoriaTrasparenza.each(function() {
        jQuery(this).click(function(){
            dci_remove_highlight_missing_field('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
        });
    });

    // Gestione del campo Titolo del Post
    jQuery('#title').on('input', function() {
        dci_remove_highlight_missing_field('#titlewrap'); // Rimuove l'highlighting quando si digita
    });

    // Nota: L'ID _dci_documento_pubblico_box_documento non sembra esistere nel PHP fornito.
    // Ho commentato questa parte o l'ho modificata con un ID del metabox se l'intento era quello.
    // Se intendi fare riferimento all'intero metabox "Documento/Link *", l'ID generato da CMB2 è cmb2-metabox-_dci_elemento_trasparenza_box_documento
    jQuery("body").on('click', "#cmb2-metabox-_dci_elemento_trasparenza_box_documento", function() {
        // Assicurati che questa funzione dci_remove_highlight_alternative_field esista e faccia ciò che intendi.
        // Se non è definita, potresti voler rimuovere questa riga o definirla.
        // dci_remove_highlight_alternative_field('#cmb2-metabox-_dci_elemento_trasparenza_box_documento');
    });


    /**
     * controllo all'invio del form (per la pagina di singolo post)
     */
    jQuery( 'form[name="post"]' ).on('submit', function(e) {
        let is_publish_button = (document.activeElement.id === 'publish' || document.activeElement.id === 'save-post'); // Controlla sia "Pubblica" che "Salva Bozza"

        // *** Controllo compilazione campo Titolo ***
        let postTitle = jQuery('#title').val().trim();
        if (is_publish_button && postTitle === '') {
            dci_highlight_missing_field('#titlewrap'); // Targetta il wrapper del titolo
            e.preventDefault(); // Impedisce l'invio del form
            return false;
        }

        // *** Controllo compilazione campo Categoria Trasparenza ***
        if(is_publish_button && jQuery('input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]:checked').length === 0) {
            dci_highlight_missing_field('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
            e.preventDefault(); // Impedisce l'invio del form
            return false;
        }

        return true; // Se tutti i controlli passano, il form viene inviato
    });

    /**
     * controllo all'invio del form (per la pagina Multi-Post)
     */
    // Ho aggiunto un ID al form nella pagina PHP per targettarlo più facilmente: id="dci-multipost-form"
    // Se non vuoi modificare il PHP, puoi usare: jQuery('.wrap form[method="post"]').on('submit', function(e) {
    jQuery('#dci-multipost-form').on('submit', function(e) {
        let defaultCategory = jQuery('#dci_default_category').val();
        let multiFiles = jQuery('#dci_multi_files')[0].files.length;

        // Controlla se il pulsante di submit è stato cliccato
        if (jQuery(document.activeElement).attr('type') === 'submit') {

            // Validazione della categoria predefinita
            if (defaultCategory === "0" || defaultCategory === "" || defaultCategory === null) {
                showAlert('Seleziona una categoria predefinita per gli elementi.');
                jQuery('#dci_default_category').focus();
                e.preventDefault();
                return false;
            }

            // Validazione del caricamento dei file
            if (multiFiles === 0) {
                showAlert('Seleziona almeno un file da caricare.');
                jQuery('#dci_multi_files').focus();
                e.preventDefault();
                return false;
            }
        }
        return true;
    });


    // --- Funzioni di utilità per l'highlighting e i messaggi ---

    function dci_highlight_missing_field(fieldSelector) {
        // Rimuovi eventuali messaggi precedenti e classi per evitare duplicati
        jQuery('.field-required-msg').remove();
        jQuery('.highlighted_missing_field').removeClass('highlighted_missing_field');

        let targetElement = jQuery(fieldSelector);
        targetElement.addClass("highlighted_missing_field")
            .append('<div id="field-required-msg" class="field-required-msg"><em>Campo obbligatorio</em></div>');

        // Scorre fino al campo evidenziato
        jQuery('html,body').animate({
            scrollTop: targetElement.offset().top - 100
        }, 'slow');
    }

    function dci_remove_highlight_missing_field(fieldSelector) {
        jQuery(fieldSelector).removeClass("highlighted_missing_field");
        // Rimuovi solo il messaggio specifico del campo se vuoi
        jQuery(fieldSelector).find('.field-required-msg').remove();
        // O rimuovi tutti i messaggi globalmente se preferisci
        // jQuery('.field-required-msg').remove();
    }

    // Funzione placeholder per i messaggi di errore nella pagina Multi-Post
    function showAlert(message) {
        // Puoi sostituire alert() con una visualizzazione più user-friendly
        // ad esempio, aggiungendo un div con classe 'notice notice-error' come fa WordPress
        console.error(message); // Utile per il debug
        // Esempio: aggiungere un messaggio di errore dinamico sotto il titolo della pagina
        if (jQuery('.wrap .notice-error').length === 0) { // Evita duplicati
            jQuery('.wrap h1').after('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>');
            jQuery('.notice-error').on('click', '.notice-dismiss', function() {
                jQuery(this).closest('.notice').remove();
            });
        }
    }

    // Funzione dci_remove_highlight_alternative_field non definita nel codice originale,
    // se la usi, assicurati di definirla. Per ora, l'ho lasciata commentata.
    /*
    function dci_remove_highlight_alternative_field(fieldSelector) {
        jQuery(fieldSelector).removeClass("highlighted_missing_field");
        // Logica specifica per rimuovere l'highlighting per i campi alternativi
    }
    */
});
