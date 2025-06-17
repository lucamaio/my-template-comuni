<?php

/**
 * Registra il custom post type "Bando"
 */
add_action('init', 'dci_register_post_type_bando');
function dci_register_post_type_bando()
{
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
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        'rewrite'             => array('slug' => 'bandi', 'with_front' => false),
        'map_meta_cap'        => true,
        'capabilities' => array(
            'edit_post'             => 'edit_bando',
            'read_post'             => 'read_bando',
            'delete_post'           => 'delete_bando',
            'edit_posts'            => 'edit_bandi',
            'edit_others_posts'     => 'edit_others_bandi',
            'publish_posts'         => 'publish_bandi',
            'read_private_posts'    => 'read_private_bandi',
            'delete_posts'          => 'delete_bandi',
            'delete_private_posts'  => 'delete_private_bandi',
            'delete_published_posts' => 'delete_published_bandi',
            'delete_others_posts' => 'delete_others_bandi',
            'edit_private_posts' => 'edit_private_bandi',
            'edit_published_posts' => 'edit_published_bandi',
            'create_posts'          => 'create_bandi'
        ),
        'description'         => __("Tipologia personalizzata per la pubblicazione dei bandi di gara del Comune.", 'design_comuni_italia'),
    );

    register_post_type('bando', $args);

    // Rimuove il supporto all'editor
    remove_post_type_support('bando', 'editor');
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
    
    $cmb_apertura->add_field(array(
        'id'                => $prefix . 'tipo_stato_bando',
        'name'              => __('Stato del Bando *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la stato del bando.', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_stato_bando',
        'show_option_none'  => false,
        'remove_default'    => true,
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_inizio',
        'name'        => __('Data di Pubblicazione', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui il bando è stato pubblicato.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_fine',
        'name'        => __('Data di Scadenza', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui scade il bando.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
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
        'name'        => __('Importo di Aggiudicazione *', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo finale con cui è stato aggiudicato il bando.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'importo_somme_liquidate',
        'name'        => __('Importo delle Somme Liquidate *', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo delle somme effettivamente liquidate.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'struttura_proponente',
        'name'        => __('Struttura Proponente *', 'design_comuni_italia'),
        'desc'        => __('Indica la struttura o l’ufficio proponente.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'cig',
        'name'        => __('CIG *', 'design_comuni_italia'),
        'desc'        => __('Indica la CIG', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'cf_sa',
        'name'        => __('Codice fiscale SA *', 'design_comuni_italia'),
        'desc'        => __('Indica il Codice fiscale della stazione appaltante', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

     $cmb_dettagli->add_field(array(
        'id'                => $prefix . 'tipo_sceleta_contraente',
        'name'              => __('Scelta del contraente *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la scelta del contraente', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_procedura_contraente',
        'show_option_none'  => false,
        'remove_default'    => true,
        'attributes'  => array('required' => 'required'),
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
        'id'            => $prefix . 'operatori_group',
        'type'          => 'group',
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
        'id'            => $prefix . 'aggiudicatari_group',
        'type'          => 'group',
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

        $data['post_content'] = $descrizione_scopo . '<br>' . $testo_completo;
    }

    return $data;
}
