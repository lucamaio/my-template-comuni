<?php

/**
 * Definisce post type Progetto
 */
add_action( 'init', 'dci_register_post_type_progetto');
function dci_register_post_type_progetto() {

    $labels = array(
        'name'          => _x( 'Progetti', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name' => _x( 'Progetto', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'       => _x( 'Aggiungi un Progetto', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'  => _x( 'Aggiungi un nuovo Progetto', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'       => _x( 'Modifica il Progetto', 'Post Type Singular Name', 'design_comuni_italia' ),
        'featured_image' => __( 'Immagine di riferimento', 'design_comuni_italia' ),
    );
    $args   = array(
        'label'         => __( 'Progetto', 'design_comuni_italia'),
        'labels'        => $labels,
        'supports'      => array( 'title', 'editor', 'author', 'thumbnail'),
        'hierarchical'  => false,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-media-interactive',
        'has_archive'   => false,
        'rewrite' => array('slug' => 'progetti','with_front' => false),
        'map_meta_cap'    => true,
        'capabilities' => array(
            'edit_post' => 'edit_elemento_progetto',
            'read_post' => 'read_elemento_progetto',
            'delete_post' => 'delete_elemento_progetto',
            'edit_posts' => 'edit_elementi_progetto',
            'edit_others_posts' => 'edit_others_elementi_progetto',
            'publish_posts' => 'publish_elementi_progetto',
            'read_private_posts' => 'read_private_elementi_progetto',
            'delete_posts' => 'delete_elementi_progetto',
            'delete_private_posts' => 'delete_private_elementi_progetto',
            'delete_published_posts' => 'delete_published_elementi_progetto',
            'delete_others_posts' => 'delete_others_elementi_progetto',
            'edit_private_posts' => 'edit_private_elementi_progetto',
            'edit_published_posts' => 'edit_published_elementi_progetto',
            'create_posts' => 'create_elementi_progetto'
        ),
        'description'    => __( "Tipologia che consente l'inserimento dei progetti PNRR del comune", 'design_comuni_italia' ),
    );
    register_post_type('progetto', $args );

    remove_post_type_support( 'progetto', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_progetto_add_content_after_title' );
function dci_Progetto_add_content_after_title($post) {
    if($post->post_type == "progetto")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo delle progetto PNRR</b>.</i></span><br><br>', 'design_comuni_italia' );
}

add_action( 'cmb2_init', 'dci_add_progetto_metaboxes' );
function dci_add_Progetto_metaboxes() {
    $prefix = '_dci_progetto_';

    //APERTURA
    $cmb_apertura = new_cmb2_box( array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __( 'Apertura', 'design_comuni_italia' ),
        'object_types' => array( 'progetto' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_apertura->add_field( array(
        'name'       => __('Immagine', 'design_comuni_italia' ),
        'desc' => __( 'Immagine principale del progetto' , 'design_comuni_italia' ),
        'id'             => $prefix . 'immagine',
        'type' => 'file',
        'query_args' => array( 'type' => 'image' ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'data_pubblicazione',
        'name'    => __( 'Data della Progetto', 'design_comuni_italia' ),
        'desc' => __( 'Data di pubblicazione del progetto.' , 'design_comuni_italia' ),
        'type'    => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'tipo_progetto',
        'name'        => __( 'Tipo di Progetto *', 'design_comuni_italia' ),
        'type'             => 'taxonomy_radio_hierarchical',
        'taxonomy'       => 'tipi_progetto',
        'show_option_none' => false,
        'remove_default' => 'true',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'a_cura_di',
        'name'    => __( 'A cura di:', 'design_comuni_italia' ),
        'desc' => __( 'Ufficio che ha curato il progetto PNRR' , 'design_comuni_italia' ),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('unita_organizzativa'),
        'attributes'    => array(
            // 'required'    => 'required',
            'placeholder' =>  __( 'Seleziona le unità organizzative', 'design_comuni_italia' ),
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'nome_misura',
        'name' => __( 'Nome della misura *', 'design_comuni_italia' ),
        'desc' => __( 'Inserire il nome della misura seguito dal tipo di intervento, ad esempio: "M1C1 Intervento 1.2". Il testo deve essere inferiore a 100 caratteri.', 'design_comuni_italia' ),
        'type' => 'textarea',
        'attributes' => array(
            'maxlength' => '100',
            'required' => 'required'
        ),
    ) );
    
    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione_scopo',
        'name'        => __( 'Descrizione scopo *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione e scopo del progetto' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ) );

    // DETTAGLI
    $cmb_dettagli = new_cmb2_box( array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __( 'Dettagli', 'design_comuni_italia' ),
        'object_types' => array( 'progetto' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'componente',
        'name'        => __( 'Componente del Progetto *', 'design_comuni_italia' ),
        'desc' => __( 'Testo della componente del Progetto' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'investimento',
        'name'        => __( 'investimento del Progetto *', 'design_comuni_italia' ),
        'desc' => __( 'Testo del investimento del Progetto' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'intervento',
        'name'        => __( 'intervento del Progetto *', 'design_comuni_italia' ),
        'desc' => __( 'Testo del intervento del Progetto' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );
    
    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'titolare',
        'name'        => __( 'Titolare del Progetto *', 'design_comuni_italia' ),
        'desc' => __( 'inserire il titolare del Progetto' , 'design_comuni_italia' ),
        'type' => 'text',
        'default' => 'PCM PRESIDENZA CONSIGLIO MINISTRI'
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'cup',
        'name'        => __( 'CUP *', 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );
    
    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'importo',
        'name'        => __( 'Importo Finanziato *', 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_modalita= new_cmb2_box( array(
        'id'           => $prefix . 'box_modalita',
        'title'        => __( 'Modalità/ Attività', 'design_comuni_italia' ),
        'object_types' => array( 'progetto' ),
        'context'      => 'normal',
        'priority'     => 'low',
        
    ) );

    $cmb_modalita->add_field(array(
        'id' => $prefix . 'modalita',
        'name'        => __( 'Modalità di Accesso al Finanziamento *', 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ));

    $cmb_modalita->add_field(array(
        'id' => $prefix . 'attivita',
        'name'        => __( 'Attività Finanziata *', 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ));

   
    // Avanzamento del progetto

    $cmb_avanzamento= new_cmb2_box( array(
        'id'           => $prefix . 'box_avanzamento',
        'title'        => __( 'Avanzamento del Progetto', 'design_comuni_italia' ),
        'object_types' => array( 'progetto' ),
        'context'      => 'normal',
        'priority'     => 'low',
    ) );

    $cmb_avanzamento->add_field(array(
        'id' => $prefix . 'avanzamento',
        'name'        => __( 'Avanzamento del progetto *', 'design_comuni_italia' ),
        'type' => 'text',
    ));

    $cmb_avanzamento->add_field( array(
        'id' => $prefix . 'avanzamento_allegati',
        'name'        => __( 'Allegati Avanzamento del ptogegtto', 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );

    //Atti Legislsativi e Amministativi
    $cmb_allegati = new_cmb2_box( array(
        'id'           => $prefix . 'box_allegtati',
        'title'        => __( 'Atti Leggislativi e Amministrativi', 'design_comuni_italia' ),
        'object_types' => array( 'progetto' ),
        'context'      => 'normal',
        'priority'     => 'low',
    ) );


    $cmb_allegati->add_field( array(
        'id' => $prefix . 'atti',
        'name'        => __( 'Allegati Atti', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di Atti allegati Legislativi e Amministrativi' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );

    $cmb_allegati->add_field( array(
        'id' => $prefix . 'allegati',
        'name'        => __( 'Allegati', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di altri allegati collegati con il progetto PNRR' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );


}

/**
 * aggiungo js per controllo compilazione campi
 */

add_action( 'admin_print_scripts-post-new.php', 'dci_progetto_admin_script', 11 );
add_action( 'admin_print_scripts-post.php', 'dci_progetto_admin_script', 11 );

function dci_progetto_admin_script() {
    global $post_type;
    if( 'progetto' == $post_type )
        wp_enqueue_script( 'progetto-admin-script', get_template_directory_uri() . '/inc/admin-js/progetto.js' );
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */
function dci_progetto_set_post_content( $data ) {

    if($data['post_type'] == 'progetto') {

        $descrizione_scopo = '';
        if (isset($_POST['_dci_progetto_descrizione_scopo'])) {
            $descrizione_scopo = $_POST['_dci_progetto_descrizione_scopo'];
        }

        $testo_completo = '';
        if (isset($_POST['_dci_progetto_testo_completo'])) {
            $testo_completo = $_POST['_dci_progetto_testo_completo'];
        }

        $content = $descrizione_scopo.'<br>'.$testo_completo;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter( 'wp_insert_post_data' , 'dci_progetto_set_post_content' , '99', 1 );
