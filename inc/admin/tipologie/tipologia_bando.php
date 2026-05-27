<?php

/**
 * Registra il custom post type "Bando"
 */
add_action('init', 'dci_register_post_type_bando');
function dci_register_post_type_bando()
{
    // Controlla se l'opzione "ck_bandidigaratemplatepersonalizzato" è impostata su 'false' o vuota
    if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }

    $labels = array(
        'name'               => _x('Bandi di Gara', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Bando di Gara', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Bando', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Bando di Gara', 'design_comuni_italia'),
        'edit_item'          => __('Modifica Bando di Gara', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Bando di Gara', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => false,
        'public'              => true,
        'show_in_menu'        => true,
        //'menu_position'       => 5,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        'rewrite'             => array('slug' => 'bandi', 'with_front' => false),
        'capability_type'     => array('bando', 'bandi'), // CAPABILITY TYPE come array singolare/plurale
        'map_meta_cap'        => true,
        'capabilities'        => array(
            'edit_post'              => 'edit_bando',
            'read_post'              => 'read_bando',
            'delete_post'            => 'delete_bando',
            'edit_posts'             => 'edit_bandi',
            'edit_others_posts'      => 'edit_others_bandi',
            'publish_posts'          => 'publish_bandi',
            'read_private_posts'     => 'read_private_bandi',
            'delete_posts'           => 'delete_bandi',
            'delete_private_posts'   => 'delete_private_bandi',
            'delete_published_posts' => 'delete_published_bandi',
            'delete_others_posts'    => 'delete_others_bandi',
            'edit_private_posts'     => 'edit_private_bandi',
            'edit_published_posts'   => 'edit_published_bandi',
            'create_posts'           => 'create_bandi',
        ),
        'description'         => __("Tipologia personalizzata per la pubblicazione dei bandi di gara del Comune.", 'design_comuni_italia'),
    );

    register_post_type('bando', $args);

    // Rimuove il supporto all'editor
    remove_post_type_support('bando', 'editor');
}





// Aggiungi voce al menu admin per Bandi, con "Aggiungi nuovo" nascosta
add_action('admin_menu', 'dci_add_bando_submenu', 9);
function dci_add_bando_submenu()
{
    // Controlla se l'opzione "ck_bandidigaratemplatepersonalizzato" è impostata su 'false' o vuota
    if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }

    $parent_slug = 'edit.php?post_type=elemento_trasparenza';
    $menu_slug   = 'edit.php?post_type=bando';

    if (current_user_can('edit_bandi')) {
        // Lista dei bandi
        add_submenu_page(
            $parent_slug,
            __('Bandi di Gara', 'design_comuni_italia'),
            __('Bandi di Gara', 'design_comuni_italia'),
            'edit_bandi',
            $menu_slug
        );

        // Aggiungi nuovo (necessario per permessi, poi nascosto)
        add_submenu_page(
            $parent_slug,
            __('Aggiungi Nuovo Bando', 'design_comuni_italia'),
            __('Aggiungi Nuovo', 'design_comuni_italia'),
            'edit_bandi',
            'post-new.php?post_type=bando'
        );
    }
}

// Nascondere la voce "Aggiungi nuovo" dal menu
add_action('admin_head', function () {
    // Controlla se l'opzione "ck_bandidigaratemplatepersonalizzato" è impostata su 'false' o vuota
    if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === '') {
        return; // Non registrare il CPT se la condizione non è soddisfatta
    }

    global $submenu;
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';

    if (isset($submenu[$parent_slug])) {
        foreach ($submenu[$parent_slug] as $key => $item) {
            if ($item[2] === 'post-new.php?post_type=bando') {
                unset($submenu[$parent_slug][$key]);
            }
        }
    }
});

// Aggiunge la voce "Aggiungi Bando" nella Admin Bar sotto "+ Nuovo"
add_action('admin_bar_menu', 'dci_add_admin_bar_new_bando', 999);
function dci_add_admin_bar_new_bando($wp_admin_bar)
{
    // Controlla se l'opzione è false o vuota
    if (dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === 'false' || dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") === '') {
        return; // Non aggiungere la voce
    }

    // Controlla se l'utente ha i permessi
    if (!current_user_can('edit_bandi')) {
        return; // Non aggiungere la voce
    }

    // Aggiunge la voce sotto il menu "+ Nuovo" (ID: new-content)
    $wp_admin_bar->add_node(array(
        'id'     => 'new-bando', // ID unico
        'title'  => 'Bando di Gara',
        'href'   => admin_url('post-new.php?post_type=bando'),
        'parent' => 'new-content' // Sotto "+ Nuovo"
    ));
}


/**
 * Pulsanti extra nella schermata elenco Bandi di Gara:
 *  - Tipi stato bandi
 *  - Tipi procedura contraente
 */
add_action('admin_head-edit.php', 'dci_bando_extra_buttons');
function dci_bando_extra_buttons()
{
    $screen = get_current_screen();

    if ($screen->post_type !== 'bando' || $screen->base !== 'edit') {
        return;
    }

    // Pulsanti extra
    $extra_buttons = [
        [
            'id'   => 'dci-extra-tax-stato',
            'text' => __('Tipi stato bandi', 'design_comuni_italia'),
            'href' => admin_url('edit-tags.php?taxonomy=tipi_stato_bando&post_type=bando'),
        ],
        [
            'id'   => 'dci-extra-tax-procedura',
            'text' => __('Tipi procedura contraente', 'design_comuni_italia'),
            'href' => admin_url('edit-tags.php?taxonomy=tipi_procedura_contraente&post_type=bando'),
        ],
    ];
    ?>
    <style>
        /* margine tra i bottoni */
        .wrap .page-title-action {
            margin-right: 8px; /* margine a destra del pulsante Add New */
        }

        .dci-extra-btn {
            margin-left: 8px; /* margine tra pulsanti extra */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const stdBtn = document.querySelector('.wrap .page-title-action'); // bottone WP "Aggiungi"
            if (!stdBtn) return;

            <?php foreach ($extra_buttons as $btn) : ?>
                (function () {
                    const link = document.createElement('a');
                    link.id = '<?php echo esc_js($btn['id']); ?>';
                    link.className = 'page-title-action';
                    link.href = '<?php echo esc_url($btn['href']); ?>';
                    link.textContent = '<?php echo esc_js($btn['text']); ?>';
                    stdBtn.after(link);
                })();
            <?php endforeach; ?>
        });
    </script>
    <?php
}


/**
 * Stile backend per migliorare la leggibilità delle descrizioni
 * associate alle sezioni della trasparenza.
 */
add_action('admin_head', 'dci_bando_admin_custom_styles');
function dci_bando_admin_custom_styles()
{
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'bando') {
        return;
    }
    ?>
    <style>
        .dci-bando-option-title {
            display: inline-block;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .dci-bando-option-desc {
            display: block;
            margin-top: 2px;
            margin-bottom: 10px;
            color: #646970;
            font-size: 12px;
            line-height: 1.4;
            max-width: 760px;
        }
    </style>
    <?php
}




/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_bando_add_content_after_title');
function dci_bando_add_content_after_title($post)
{
    if ($post->post_type == 'bando') {
        echo '<span><i>Il <strong>titolo</strong> corrisponde al <strong>titolo del bando di gara</strong>.</i></span><br><br>';
    }
}

/**
 * CMB2 Metaboxes per il CPT "Bando"
 */
add_action('cmb2_init', 'dci_add_bando_metaboxes');
function dci_add_bando_metaboxes()
{
    $prefix = '_dci_bando_';

   // Metabox: Apertura
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Informazioni sul Bando', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Descrizione introduttiva del metabox
    $cmb_apertura->add_field(array(
        'id'   => $prefix . 'box_apertura_descrizione',
        'type' => 'title',
        'name' => __('Indicazioni per la compilazione', 'design_comuni_italia'),
        'desc' => __('Compilare i seguenti campi in quanto sono obbligatori secondo quanto previsto dalla normativa vigente.', 'design_comuni_italia'),
    ));

    $cmb_apertura->add_field(array(
        'id'                => $prefix . 'tipo_stato_bando',
        'name'              => __('Stato del Bando *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la stato del bando.', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_stato_bando',
        'show_option_none'  => false,
        'remove_default'    => true,
        'attributes'        => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'         => '_dci_bando_sezione',
        'name'       => __('Sezione trasparenza *', 'design_comuni_italia'),
        'desc'       => __('Selezionare la sezione corretta in cui pubblicare il bando di gara. Ogni voce contiene una breve descrizione di supporto.', 'design_comuni_italia'),
        'type'       => 'radio',
        'options'    => dci_get_sezioni_bando(),
        'attributes' => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_inizio',
        'name'        => __('Data di Pubblicazione *', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui il bando è stato pubblicato.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_fine',
        'name'        => __('Data di Scadenza', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui scade il bando.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'cig',
        'name'        => __('CIG *', 'design_comuni_italia'),
        'desc'        => __('Indica la CIG', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'oggetto',
        'name'        => __('Oggetto del Bando *', 'design_comuni_italia'),
        'desc'        => __("Inserisci una descrizione sintetica dell'oggetto del bando.", 'design_comuni_italia'),
        'type'        => 'wysiwyg',
        'attributes'  => array(
            'required' => 'required'
        ),
        'options'     => array(
            'textarea_rows' => 8,
            'teeny'         => false,
        ),
    ));

    // ============================================================
    // SEZIONE: LINK ALLE PIATTAFORME E ATTI
    // ============================================================
    $cmb_links = new_cmb2_box(array(
        'id'           => $prefix . 'box_link_piattaforme',
        'title'        => __('Link e riferimenti esterni', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));


    // ============================================================
    // LINK BDNCP (singolo - obbligatorio)
    // ============================================================
    // $cmb_links->add_field(array(
    //     'name' => __('BDNCP (Banca Dati Nazionale Contratti Pubblici) *', 'design_comuni_italia'),
    //     'desc' => __('Collegamento alla scheda del bando pubblicata sulla piattaforma ANAC.', 'design_comuni_italia'),
    //     'id'   => $prefix . 'link_bdncp',
    //     'type' => 'text_url',
    //     'attributes' => array(
    //         'required' => 'required'
    //     ),
    // ));

    // Rimosso in quanto semplicemnte basta inserire il cig qui e visualizzo i dati relativi:
    // https://dati.anticorruzione.it/superset/dashboard/dettaglio_cig/?cig=


    // ============================================================
    // PIATTAFORMA DI APPROVVIGIONAMENTO - DESCRIZIONE
    // ============================================================
    $cmb_links->add_field(array(
        'id'   => $prefix . 'link_piattaforma_descrizione',
        'type' => 'title',
        'name' => __('Piattaforma di approvvigionamento', 'design_comuni_italia'),
        'desc' => __(
            'La piattaforma di approvvigionamento è il sistema telematico utilizzato dall’Ente per gestire la procedura di gara o affidamento. Può trattarsi, ad esempio, di MEPA, di una piattaforma regionale, di una centrale di committenza o di altro portale certificato utilizzato per la pubblicazione e la gestione della procedura.',
            'design_comuni_italia'
        ),
    ));


    // ============================================================
    // PIATTAFORMA DI APPROVVIGIONAMENTO (singolo - obbligatorio)
    // ============================================================
    $cmb_links->add_field(array(
        'name' => __('Link alla piattaforma di approvvigionamento', 'design_comuni_italia'),
        'desc' => __('Inserire il link alla piattaforma telematica utilizzata per la gestione della procedura.', 'design_comuni_italia'),
        'id'   => $prefix . 'link_piattaforma',
        'type' => 'text_url',
        // 'attributes' => array(
        //     'required' => 'required'
        // ),
    ));


    // ============================================================
    // ATTI DI INDIZIONE (MULTIPLI)
    // ============================================================
    $atti_indizione_group_id = $cmb_links->add_field(array(
        'id'          => $prefix . 'atti_indizione_group',
        'type'        => 'group',
        'name'        => __('Atti di indizione (delibera/determina) *', 'design_comuni_italia'),
        'description' => __('Inserire uno o più atti amministrativi che hanno avviato la procedura. È possibile indicare un link esterno oppure caricare direttamente il documento.', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Atto {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi atto', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi atto', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    $cmb_links->add_group_field($atti_indizione_group_id, array(
        'name' => __('Titolo atto *', 'design_comuni_italia'),
        'desc' => __('Titolo dell’atto.', 'design_comuni_italia'),
        'id'   => 'title',
        'type' => 'text',
        // 'attributes' => array(
        //     'required' => 'required'
        // ),
    ));

    // Campo URL atto indizione
    $cmb_links->add_group_field($atti_indizione_group_id, array(
        'name' => __('Link atto', 'design_comuni_italia'),
        'desc' => __('Collegamento alla pubblicazione ufficiale dell’atto, ad esempio Albo Pretorio, Amministrazione Trasparente o altra piattaforma istituzionale.', 'design_comuni_italia'),
        'id'   => 'url',
        'type' => 'text_url',
        // 'attributes' => array(
        //     'required' => 'required'
        // ),
    ));

    // Campo file atto indizione
    $cmb_links->add_group_field($atti_indizione_group_id, array(
        'name'         => __('File atto', 'design_comuni_italia'),
        'desc'         => __('Caricare il documento dell’atto, se non si desidera inserire solo un link esterno. Formati consigliati: PDF, DOC, DOCX.', 'design_comuni_italia'),
        'id'           => 'file',
        'type'         => 'file',
        'options'      => array(
            'url' => false,
        ),
        'text'         => array(
            'add_upload_file_text' => __('Carica atto', 'design_comuni_italia'),
        ),
        'query_args'   => array(
            'type' => array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ),
        ),
        'preview_size' => 'medium',
    ));


    // ============================================================
    // DETERMINE DI AGGIUDICAZIONE (MULTIPLE)
    // ============================================================
    $determine_aggiudicazione_group_id = $cmb_links->add_field(array(
        'id'          => $prefix . 'determine_aggiudicazione_group',
        'type'        => 'group',
        'name'        => __('Determine di aggiudicazione *', 'design_comuni_italia'),
        'description' => __('Inserire una o più determine relative all’aggiudicazione. È possibile indicare un link esterno oppure caricare direttamente il documento.', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Determina {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi determina', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi determina', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    $cmb_links->add_group_field($determine_aggiudicazione_group_id, array(
        'name' => __('Titolo determina *', 'design_comuni_italia'),
        'desc' => __('Titolo della determina.', 'design_comuni_italia'),
        'id'   => 'title',
        'type' => 'text',
        // 'attributes' => array(
        //     'required' => 'required'
        // ),
    ));

    // Campo URL determina
    $cmb_links->add_group_field($determine_aggiudicazione_group_id, array(
        'name' => __('Link determina', 'design_comuni_italia'),
        'desc' => __('Collegamento alla determina di aggiudicazione, ad esempio Albo Pretorio, Amministrazione Trasparente o altra piattaforma istituzionale.', 'design_comuni_italia'),
        'id'   => 'url',
        'type' => 'text_url',
        // 'attributes' => array(
        //     'required' => 'required'
        // ),
    ));

    // Campo file determina
    $cmb_links->add_group_field($determine_aggiudicazione_group_id, array(
        'name'         => __('File determina', 'design_comuni_italia'),
        'desc'         => __('Caricare il documento della determina, se non si desidera inserire solo un link esterno. Formati consigliati: PDF, DOC, DOCX.', 'design_comuni_italia'),
        'id'           => 'file',
        'type'         => 'file',
        'options'      => array(
            'url' => false,
        ),
        'text'         => array(
            'add_upload_file_text' => __('Carica determina', 'design_comuni_italia'),
        ),
        'query_args'   => array(
            'type' => array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ),
        ),
        'preview_size' => 'medium',
    ));


    // ============================================================
    // ALTRI LINK (FACOLTATIVO - MULTIPLI)
    // ============================================================
    $altri_link_group_id = $cmb_links->add_field(array(
        'id'          => $prefix . 'altri_link_group',
        'type'        => 'group',
        'name'        => __('Altri link utili', 'design_comuni_italia'),
        'description' => __('Eventuali collegamenti aggiuntivi (es. documentazione integrativa, FAQ, chiarimenti, ecc.).', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Link {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi link', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi link', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    // Label del link
    $cmb_links->add_group_field($altri_link_group_id, array(
        'name' => __('Titolo link', 'design_comuni_italia'),
        'desc' => __('Descrizione breve del contenuto del link.', 'design_comuni_italia'),
        'id'   => 'label',
        'type' => 'text',
    ));

    // URL del link
    $cmb_links->add_group_field($altri_link_group_id, array(
        'name' => __('URL', 'design_comuni_italia'),
        'id'   => 'url',
        'type' => 'text_url',
    ));

    // Metabox: Dettagli
    $cmb_dettagli = new_cmb2_box(array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __('Dettagli Economici', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'importo_aggiudicazione',
        'name'        => __('Importo di Aggiudicazione', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo finale con cui è stato aggiudicato il bando.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'importo_somme_liquidate',
        'name'        => __('Importo delle Somme Liquidate', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo delle somme effettivamente liquidate.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'struttura_proponente',
        'name'        => __('Struttura Proponente', 'design_comuni_italia'),
        'desc'        => __('Indica la struttura o l’ufficio proponente.', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'cf_sa',
        'name'        => __('Codice fiscale SA', 'design_comuni_italia'),
        'desc'        => __('Indica il Codice fiscale della stazione appaltante', 'design_comuni_italia'),
        'type'        => 'text',
        // 'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'                => $prefix . 'tipo_sceleta_contraente',
        'name'              => __('Scelta del contraente', 'design_comuni_italia'),
        'desc'              => __('Selezionare la scelta del contraente', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_procedura_contraente',
        'show_option_none'  => false,
        'remove_default'    => true,
        // 'attributes'  => array('required' => 'required'),
    ));

    //  $cmb_dettagli->add_field(array(
    //     'id'          => $prefix . 'scleta_contraente',
    //     'name'        => __('Scelta del contraente*', 'design_comuni_italia'),
    //     'desc'        => __('Indica la Scelta del contraente', 'design_comuni_italia'),
    //     'type'        => 'text',
    //     'attributes'  => array('required' => 'required'),
    // ));

    $cmb_operatori = new_cmb2_box(array(
        'id'           => $prefix . 'box_operatori',
        'title'        => __('Operatori', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_operatori->add_field(array(
        'id'          => $prefix . 'operatori_group',
        'type'        => 'group',
        'description' => __('Elenco degli operatori invitati a presentare offerte/numero di offerenti che hanno partecipato al procedimento', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Operatore {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi un operatore', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi un operatore', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    // URL del documento
    $cmb_operatori->add_group_field($prefix . 'operatori_group', array(
        'name' => __('Ragione sociale', 'design_comuni_italia'),
        'id'   => 'ragione_sociale',
        'type' => 'text',
    ));

    // Checkbox: apri in nuova scheda
    $cmb_operatori->add_group_field($prefix . 'operatori_group', array(
        'name' => __('Codice fiscale/P.Iva', 'design_comuni_italia'),
        'id'   => 'codice_fiscale',
        'type' => 'text',
    ));

    $cmb_aggiudicatari = new_cmb2_box(array(
        'id'           => $prefix . 'box_aggiudicatari',
        'title'        => __('Aggiudicatari', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_aggiudicatari->add_field(array(
        'id'          => $prefix . 'aggiudicatari_group',
        'type'        => 'group',
        'description' => __('Elenco degli aggiudicatari del procedimento', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Aggiudicatore {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi un Aggiudicatore', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi un Aggiudicatore', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    // URL del documento
    $cmb_aggiudicatari->add_group_field($prefix . 'aggiudicatari_group', array(
        'name' => __('Ragione sociale', 'design_comuni_italia'),
        'id'   => 'ragione_sociale',
        'type' => 'text',
    ));

    // Checkbox: apri in nuova scheda
    $cmb_aggiudicatari->add_group_field($prefix . 'aggiudicatari_group', array(
        'name' => __('Codice fiscale/P.Iva', 'design_comuni_italia'),
        'id'   => 'codice_fiscale',
        'type' => 'text',
    ));

    //DOCUMENTI
    $cmb_documenti = new_cmb2_box(array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __('Documenti', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_documenti->add_field(array(
        'id'   => $prefix . 'allegati',
        'name' => __('Allegati', 'design_comuni_italia'),
        'desc' => __('Elenco di documenti allegati al bando di gara', 'design_comuni_italia'),
        'type' => 'file_list',
    ));

    // Metabox: Ulteriori informazioni
    $cmb_ulteriori_info = new_cmb2_box(array(
        'id'           => $prefix . 'box_ulteriori_info',
        'title'        => __('Ulteriori informazioni', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_ulteriori_info->add_field(array(
        'id'   => $prefix . 'more_info',
        'name' => __('Note', 'design_comuni_italia'),
        'desc' => __('Eventuali note o informazioni aggiuntive relative al bando di gara.', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 6,
            'teeny' => false,
        ),
    ));
}

/**
 * Includi JS personalizzato nella pagina di modifica/creazione progetto
 */
add_action('admin_print_scripts-post-new.php', 'dci_bando_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_bando_admin_script', 11);

function dci_bando_admin_script()
{
    global $post_type;

    if ($post_type == 'bando') {
        wp_enqueue_script('bando-admin-script', get_template_directory_uri() . '/inc/admin-js/bando.js');
    }
}

/**
 * Imposta il contenuto del post "Progetto" con i campi personalizzati
 */
add_filter('wp_insert_post_data', 'dci_bando_set_post_content', 99, 1);
function dci_bando_set_post_content($data)
{
    if ($data['post_type'] == 'bando') {
        $descrizione_scopo = isset($_POST['_dci_bando_descrizione_scopo']) ? $_POST['_dci_bando_descrizione_scopo'] : '';
        $testo_completo    = isset($_POST['_dci_bando_testo_completo']) ? $_POST['_dci_bando_testo_completo'] : '';
        $cig               = isset($_POST['_dci_bando_cig']) ? $_POST['_dci_bando_cig'] : '';

        $data['post_content'] = $descrizione_scopo . '<br>' . $testo_completo.'<br><strong>CIG:</strong> ' . $cig;
    }

    return $data;
}

// Funzione che mi restituisce le posizioni della trasparenza x pubblicare i bandi di gara
function dci_get_sezioni_bando()
{
    return array(
        'pubblicazione' => sprintf(
            '<span class="dci-bando-option-title">%s</span><span class="dci-bando-option-desc">%s</span>',
            esc_html__('Pubblicazione', 'design_comuni_italia'),
            esc_html__('Da utilizzare per la fase iniziale della procedura, quando devono essere pubblicati il bando, l’avviso, gli atti di indizione e la documentazione relativa all’avvio della gara.', 'design_comuni_italia')
        ),

        'affidamento' => sprintf(
            '<span class="dci-bando-option-title">%s</span><span class="dci-bando-option-desc">%s</span>',
            esc_html__('Affidamento', 'design_comuni_italia'),
            esc_html__('Da utilizzare per gli atti relativi alla fase di affidamento o aggiudicazione, come determine, verbali, esiti di gara, provvedimenti di aggiudicazione e informazioni sugli operatori economici.', 'design_comuni_italia')
        ),

        'esecutiva' => sprintf(
            '<span class="dci-bando-option-title">%s</span><span class="dci-bando-option-desc">%s</span>',
            esc_html__('Esecutiva', 'design_comuni_italia'),
            esc_html__('Da utilizzare per la fase successiva all’affidamento, relativa all’esecuzione del contratto, alle somme liquidate, agli avanzamenti, alle modifiche contrattuali e agli atti conclusivi.', 'design_comuni_italia')
        ),

        'sponsorizzazioni' => sprintf(
            '<span class="dci-bando-option-title">%s</span><span class="dci-bando-option-desc">%s</span>',
            esc_html__('Sponsorizzazioni', 'design_comuni_italia'),
            esc_html__('Da utilizzare per procedure, avvisi o atti relativi a sponsorizzazioni, accordi di collaborazione, contributi o iniziative sostenute da soggetti esterni.', 'design_comuni_italia')
        ),
    );
}