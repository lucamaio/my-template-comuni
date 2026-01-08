<?php

/**
 * Definisce post type commissario
 */
add_action('init', 'dci_register_post_type_commissario');
function dci_register_post_type_commissario() {

    $labels = array(
        'name'               => _x('OSL', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Commissario', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Documento', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => _x('Aggiungi un nuovo Documento', 'Post Type', 'design_comuni_italia'),
        'edit_item'          => _x('Modifica il Documento', 'Post Type', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Commissario', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author', 'thumbnail'), // ho rimosso 'editor' dato che lo rimuovi dopo
        'hierarchical'        => false,
        'public'              => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        'rewrite'             => array('slug' => 'commissario', 'with_front' => false),
        'capability_type'     => array('elemento_osl', 'elementi_osl'), // singolare/plurale
        'map_meta_cap'        => true,
        'capabilities'        => array(
            'edit_post'             => 'edit_elemento_osl',
            'read_post'             => 'read_elemento_osl',
            'delete_post'           => 'delete_elemento_osl',
            'edit_posts'            => 'edit_elementi_osl',
            'edit_others_posts'     => 'edit_others_elementi_osl',
            'publish_posts'         => 'publish_elementi_osl',
            'read_private_posts'    => 'read_private_elementi_osl',
            'delete_posts'          => 'delete_elementi_osl',
            'delete_private_posts'  => 'delete_private_elementi_osl',
            'delete_published_posts'=> 'delete_published_elementi_osl',
            'delete_others_posts'   => 'delete_others_elementi_osl',
            'edit_private_posts'    => 'edit_private_elementi_osl',
            'edit_published_posts'  => 'edit_published_elementi_osl',
            'create_posts'          => 'create_elementi_osl'
        ),
        'description'         => __("Tipologia che consente l'inserimento dei Documenti per la sezione OSL del comune", 'design_comuni_italia'),
    );

    register_post_type('commissario', $args);

    // Rimuove il supporto all'editor standard
    remove_post_type_support('commissario', 'editor');
}


/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_commissario_add_content_after_title' );
function dci_commissario_add_content_after_title($post) {
    if($post->post_type == "commissario")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo del Documento per la sezione OSL del Comune</b>.</i></span><br><br>', 'design_comuni_italia' );
}

add_action( 'cmb2_init', 'dci_add_commissario_metaboxes' );
function dci_add_commissario_metaboxes() {
    $prefix = '_dci_commissario_';

    //APERTURA
    $cmb_apertura = new_cmb2_box( array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __( 'Apertura', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_apertura->add_field( array(
        'name'       => __('Immagine', 'design_comuni_italia' ),
        'desc' => __( 'Immagine principale del commissario' , 'design_comuni_italia' ),
        'id'             => $prefix . 'immagine',
        'type' => 'file',
        'query_args' => array( 'type' => 'image' ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'data_pubblicazione',
        'name'    => __( 'Data di pubblicazione', 'design_comuni_italia' ),
        'desc' => __( 'Data di pubblicazione del commissario.' , 'design_comuni_italia' ),
        'type'    => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'tipo_commissario',
        'name'        => __( 'Tipo di commissario *', 'design_comuni_italia' ),
        'type'             => 'taxonomy_radio_hierarchical',
        'taxonomy'       => 'tipi_commissario',
        'show_option_none' => false,
        'remove_default' => 'true',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'a_cura_di',
        'name'    => __( 'A cura di *', 'design_comuni_italia' ),
        'desc' => __( 'Ufficio che ha curato il commissario PNRR' , 'design_comuni_italia' ),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('unita_organizzativa'),
        'attributes'    => array(
            'required'    => 'required',
            'placeholder' =>  __( 'Seleziona le unità organizzative', 'design_comuni_italia' ),
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __( 'Descrizione breve *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione sintentica del commissario, inferiore a 255 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea',
        'attributes'    => array(
            'maxlength'  => '255',
            'required'    => 'required'
        ),
    ) );

    
    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione',
        'name'        => __( 'Descrizione scopo *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione e scopo del commissario' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ) );

    //Allegati
    $cmb_allegati = new_cmb2_box( array(
        'id'           => $prefix . 'box_allegtati',
        'title'        => __( 'Allegati', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'low',
    ) );

    $cmb_allegati->add_field( array(
        'id' => $prefix . 'allegati',
        'name'        => __( 'Allegati', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di altri allegati collegati con il commissario PNRR' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );


}


/**
 * aggiungo js per controllo compilazione campi
 */

add_action( 'admin_print_scripts-post-new.php', 'dci_commissario_admin_script', 11 );
add_action( 'admin_print_scripts-post.php', 'dci_commissario_admin_script', 11 );

function dci_commissario_admin_script() {
    global $post_type;
    if( 'commissario' == $post_type )
        wp_enqueue_script( 'commissario-admin-script', get_template_directory_uri() . '/inc/admin-js/commissario.js' );
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */
function dci_commissario_set_post_content( $data ) {

    if($data['post_type'] == 'commissario') {

        $descrizione_scopo = '';
        if (isset($_POST['_dci_commissario_descrizione_scopo'])) {
            $descrizione_scopo = $_POST['_dci_commissario_descrizione_scopo'];
        }

        $testo_completo = '';
        if (isset($_POST['_dci_commissario_testo_completo'])) {
            $testo_completo = $_POST['_dci_commissario_testo_completo'];
        }

        $content = $descrizione_scopo.'<br>'.$testo_completo;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter( 'wp_insert_post_data' , 'dci_commissario_set_post_content' , '99', 1 );
