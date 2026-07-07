jQuery( document ).ready(function() {

    if (
        typeof dciElementoTrasparenzaConfig !== 'undefined' &&
        dciElementoTrasparenzaConfig.is_new_post &&
        Array.isArray(dciElementoTrasparenzaConfig.allowed_term_ids)
    ) {
        let allowedIds = dciElementoTrasparenzaConfig.allowed_term_ids.map(function(id) {
            return String(id);
        });

        let taxonomyField = jQuery('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
        let checklist = taxonomyField.find('ul.cmb2-checkbox-list, ul.cmb2-list');

        if (checklist.length) {
            let itemsById = {};

            checklist.find('input[type="radio"]').each(function() {
                let input = jQuery(this);
                let value = String(input.val());
                let item = input.closest('li');
                if (item.length) {
                    itemsById[value] = item.detach();
                }
            });

            checklist.empty();

            allowedIds.forEach(function(id) {
                if (itemsById[id]) {
                    checklist.append(itemsById[id]);
                }
            });
        }
    }

    /**
     * Ricerca e presentazione guidata della categoria Trasparenza.
     */
    let taxonomyCategoryField = jQuery('.cmb2-id--dci-elemento-trasparenza-tipo-cat-amm-trasp');
    let taxonomyCategoryList = taxonomyCategoryField.find('ul.cmb2-radio-list').first();

    if (taxonomyCategoryField.length && taxonomyCategoryList.length) {
        let categoryItems = taxonomyCategoryList
            .find('input[type="radio"]')
            .map(function() {
                return jQuery(this).closest('li')[0];
            })
            .get();

        categoryItems = jQuery(categoryItems);

        let normalizeCategoryText = function(value) {
            let normalized = String(value || '').toLocaleLowerCase('it-IT');

            if (typeof normalized.normalize === 'function') {
                normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            return normalized.replace(/\s+/g, ' ').trim();
        };

        let getPublishedCountLabel = function(count) {
            let normalizedCount = Math.max(0, parseInt(count, 10) || 0);
            return normalizedCount.toLocaleString('it-IT') +
                (normalizedCount === 1 ? ' pubblicato' : ' pubblicati');
        };

        let categorySearch = jQuery(
            '<div class="dci-transparency-category-search">' +
                '<label for="dci-transparency-category-search-input">' +
                    '<span class="dashicons dashicons-search" aria-hidden="true"></span>' +
                    '<span>Cerca la sezione in cui pubblicare</span>' +
                '</label>' +
                '<div class="dci-transparency-category-search__controls">' +
                    '<input type="search" id="dci-transparency-category-search-input" ' +
                        'placeholder="Scrivi il nome della categoria…" autocomplete="off">' +
                    '<button type="button" class="button dci-transparency-category-search__clear" ' +
                        'aria-label="Cancella la ricerca">Cancella</button>' +
                '</div>' +
                '<p class="description">Cerca per una o più parole, ad esempio “uffici”, “bilanci” o “personale”.</p>' +
                '<p class="dci-transparency-category-search__status" role="status" aria-live="polite"></p>' +
            '</div>'
        );
        let selectedCategory = jQuery(
            '<div class="dci-transparency-category-selected" aria-live="polite">' +
                '<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>' +
                '<span><strong>Sezione selezionata:</strong> <span class="dci-transparency-category-selected__name"></span></span>' +
            '</div>'
        );
        let noResults = jQuery(
            '<div class="dci-transparency-category-empty" role="status">' +
                '<strong>Nessuna sezione trovata.</strong><br>' +
                '<span>Prova con un termine più breve o con un’altra parola.</span>' +
            '</div>'
        ).hide();
        let categorySelector = jQuery(
            '<div class="dci-transparency-category-selector" aria-label="Selezione categoria Trasparenza"></div>'
        );

        taxonomyCategoryList.before(categorySelector);
        categorySelector.append(categorySearch, selectedCategory, taxonomyCategoryList, noResults);
        taxonomyCategoryList.addClass('dci-transparency-category-list');

        taxonomyCategoryList.children('li').not('.cmb2-indented-hierarchy')
            .addClass('dci-transparency-category-item--primary');
        taxonomyCategoryList.find('.cmb2-indented-hierarchy input[type="radio"]')
            .closest('li')
            .addClass('dci-transparency-category-item--child');
        taxonomyCategoryList.find('li.cmb2-indented-hierarchy').each(function() {
            jQuery(this)
                .prevAll('li')
                .not('.cmb2-indented-hierarchy')
                .first()
                .addClass('dci-transparency-category-item--has-children');
        });

        categoryItems.each(function() {
            let item = jQuery(this);
            let input = item.children('input[type="radio"]').first();
            let categoryName = item.children('label').first().text().replace(/\s+/g, ' ').trim();
            let categoryDescription = '';
            let categoryCount = null;

            if (
                input.length &&
                typeof dciElementoTrasparenzaUi !== 'undefined' &&
                dciElementoTrasparenzaUi.termDescriptions &&
                dciElementoTrasparenzaUi.termDescriptions[input.val()]
            ) {
                categoryDescription = dciElementoTrasparenzaUi.termDescriptions[input.val()];
                jQuery('<span class="dci-transparency-category-description"></span>')
                    .text(categoryDescription)
                    .appendTo(item);
            }

            if (
                input.length &&
                typeof dciElementoTrasparenzaUi !== 'undefined' &&
                dciElementoTrasparenzaUi.termCounts &&
                Object.prototype.hasOwnProperty.call(dciElementoTrasparenzaUi.termCounts, input.val())
            ) {
                categoryCount = dciElementoTrasparenzaUi.termCounts[input.val()];
            }

            if (categoryCount !== null) {
                jQuery('<span class="dci-transparency-content-count"></span>')
                    .text(getPublishedCountLabel(categoryCount))
                    .attr('aria-label', getPublishedCountLabel(categoryCount) + ' in questa categoria')
                    .insertAfter(item.children('label').first());
            }

            item.data(
                'searchable-text',
                normalizeCategoryText(categoryName + ' ' + categoryDescription)
            );
        });

        let searchInput = categorySearch.find('input[type="search"]');
        let clearSearch = categorySearch.find('.dci-transparency-category-search__clear');
        let searchStatus = categorySearch.find('.dci-transparency-category-search__status');

        let getCategoryLabel = function(item) {
            return jQuery(item).children('label').first().text().replace(/\s+/g, ' ').trim();
        };

        let showCategoryWithContext = function(item) {
            let currentItem = jQuery(item);
            currentItem.show();

            currentItem.parents('li.cmb2-indented-hierarchy').each(function() {
                let hierarchy = jQuery(this);
                hierarchy.show();

                let parentItem = hierarchy.prevAll('li').not('.cmb2-indented-hierarchy').first();
                if (parentItem.length) {
                    parentItem.show().addClass('dci-transparency-category-item--context');
                }
            });
        };

        let updateSelectedCategory = function() {
            let checked = taxonomyCategoryList.find('input[type="radio"]:checked').first();

            categoryItems.removeClass('dci-transparency-category-item--selected');
            if (!checked.length) {
                selectedCategory.hide();
                return;
            }

            let item = checked.closest('li');
            item.addClass('dci-transparency-category-item--selected');
            selectedCategory.find('.dci-transparency-category-selected__name').text(getCategoryLabel(item));
            selectedCategory.show();
        };

        let filterCategories = function() {
            let query = normalizeCategoryText(searchInput.val());
            let words = query.split(' ').filter(Boolean);

            categoryItems.removeClass('dci-transparency-category-item--context');
            taxonomyCategoryList.find('li').show();

            if (!words.length) {
                noResults.hide();
                clearSearch.hide();
                searchStatus.text(categoryItems.length + ' sezioni selezionabili.');
                return;
            }

            clearSearch.show();
            categoryItems.hide();
            taxonomyCategoryList.find('li.cmb2-indented-hierarchy').hide();

            let matches = categoryItems.filter(function() {
                let label = jQuery(this).data('searchable-text') || normalizeCategoryText(getCategoryLabel(this));
                return words.every(function(word) {
                    return label.indexOf(word) !== -1;
                });
            });

            matches.each(function() {
                showCategoryWithContext(this);
            });

            noResults.toggle(matches.length === 0);
            searchStatus.text(
                matches.length === 1
                    ? '1 sezione trovata.'
                    : matches.length + ' sezioni trovate.'
            );
        };

        searchInput.on('input', filterCategories);
        searchInput.on('keydown', function(event) {
            if (event.key === 'Escape') {
                searchInput.val('');
                filterCategories();
            }
        });
        clearSearch.on('click', function() {
            searchInput.val('').trigger('input').focus();
        });
        taxonomyCategoryList.on('change', 'input[type="radio"]', updateSelectedCategory);

        filterCategories();
        updateSelectedCategory();
    }

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
        let hasSelectedTransparencyCategory =
            jQuery('input[name^="_dci_elemento_trasparenza_tipo_cat_amm_trasp"]:checked').length > 0 ||
            jQuery('input[data-dci-preserved-category="1"]').length > 0;

        if(is_publish_button && !hasSelectedTransparencyCategory) {
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
