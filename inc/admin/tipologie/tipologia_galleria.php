<?php

/**
 * Definisce post type galleria
 */
add_action('init', 'dci_register_post_type_galleria');
function dci_register_post_type_galleria() {

    /** Galleria **/
    $labels = array(
        'name'                  => _x('Gallerie', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'         => _x('Galleria', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'               => _x('Aggiungi una galleria', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new_item'          => _x('Aggiungi una galleria', 'Post Type Singular Name', 'design_comuni_italia'),
        'featured_image'        => __('Logo identificativo della galleria', 'design_comuni_italia'),
        'edit_item'             => _x('Modifica la galleria', 'Post Type Singular Name', 'design_comuni_italia'),
        'view_item'             => _x('Visualizza la galleria', 'Post Type Singular Name', 'design_comuni_italia'),
        'set_featured_image'    => __('Seleziona immagine galleria', 'design_comuni_italia'),
        'remove_featured_image' => __('Rimuovi immagine galleria', 'design_comuni_italia'),
        'use_featured_image'    => __('Usa come immagine galleria', 'design_comuni_italia'),
    );

    $args = array(
        'label'                 => __('galleria', 'design_comuni_italia'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor'),
        'hierarchical'          => false,
        'public'                => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-tickets-alt',
        'has_archive'           => false,
        // 'rewrite'               => array('slug' => 'vivere-il-comune/galleria', 'with_front' => false),
        'capability_type'       => 'galleria',
        'map_meta_cap'          => true,
        'description'           => __("Tipologia che struttura le informazioni relative a una galleria di interesse pubblico pubblicata sul sito di un comune", 'design_comuni_italia'),
    );

    register_post_type('galleria', $args);

    remove_post_type_support('galleria', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action('edit_form_after_title', 'dci_galleria_add_content_after_title');
function dci_galleria_add_content_after_title($post) {
    if ($post->post_type == "galleria") {
        echo __('<span><i>Il <b>Titolo</b> è il <b>Nome della galleria</b>.</i></span><br><br>', 'design_comuni_italia');
    }
}

/**
 * Crea i metabox del post type galleria
 */
add_action('cmb2_init', 'dci_add_galleria_metaboxes');
function dci_add_galleria_metaboxes() {
    $prefix = '_dci_galleria_';

    // Apertura
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Apertura', 'design_comuni_italia'),
        'object_types' => array('galleria'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    // Immagine galleria
    $cmb_apertura->add_field(array(
        'name'       => __('Immagine Principale: *', 'design_comuni_italia'),
        'desc'       => __('Immagine della galleria', 'design_comuni_italia'),
        'id'         => $prefix . 'immagine',
        'type'       => 'file',
        'query_args' => array('type' => 'image'),
        'attributes'    => array( 'required' => 'required')
    ));

    // Descrizione breve
    $cmb_apertura->add_field(array(
        'id'         => $prefix . 'descrizione_breve',
        'name'       => __('Descrizione breve:', 'design_comuni_italia'),
        'desc'       => __('Descrizione sintetica della galleria, inferiore a 255 caratteri', 'design_comuni_italia'),
        'type'       => 'textarea',
        'attributes'=> array(
            'maxlength' => '255',
            // 'required'  => 'required'
        ),
    ));

     $cmb_apertura->add_field(array(
        'id'            => $prefix . 'tipo_galleria',
        'name'       => __('Tipo Galleria: *', 'design_comuni_italia'),
        'desc'       => __('Selzionare il tipo di galleria, se è una galleria fotografica oppure una galleria di video.', 'design_comuni_italia'),
        'type'          => 'taxonomy_radio_hierarchical',
        'taxonomy'      => 'tipi_galleria',
        'remove_default'=> true,
        'show_option_none'=> false,
        'attributes'    => array(
            'required' => 'required'
        )
    ));

    // Gallerie multimediali
    $cmb_gallerie_multimediali = new_cmb2_box(array(
        'id'           => $prefix . 'box_gallerie_multimediali',
        'title'        => __('Gallerie multimediali', 'design_comuni_italia'),
        'object_types' => array('galleria'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_gallerie_multimediali->add_field(array(
        'id'         => $prefix . 'foto_gallery',
        'name'       => __('Galleria di immagini', 'design_comuni_italia'),
        'desc'       => __('Una o più immagini corredate da didascalie', 'design_comuni_italia'),
        'type'       => 'file_list',
        'query_args' => array('type' => 'image'),
    ));

      //Video
    
    $cmb_video = new_cmb2_box(array(
        'id' => $prefix . 'box_video',
        'title' => __('Video', 'design_comuni_italia'),
        'object_types' => array('galleria'),
        'context' => 'normal',
        'priority' => 'low',
    ));

    // add_action('cmb2_after_init', function () {
    //     if (!is_admin()) return;

    //     $prefix = '_dci_documento_pubblico_';
    //     $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
    //     if (!$post_id) return;

    //     // Migrate file_documento (da singolo a file_list)
    //     $file_key = $prefix . 'file_documento';
    //     $file_val = get_post_meta($post_id, $file_key, true);
    //     if (!empty($file_val) && is_string($file_val)) {
    //         $new_files = [];
    //         $attachment_id = attachment_url_to_postid($file_val);
    //         if ($attachment_id) {
    //             $new_files[$attachment_id] = $file_val;
    //         } else {
    //             $new_files[] = $file_val;
    //         }
    //         update_post_meta($post_id, $file_key, $new_files);
    //     }

    //     // Ho rimosso la migrazione di url_documento da singolo a gruppo
    // });



    // Gruppo per URL multipli
    $cmb_video->add_field(array(
        'id'          => $prefix . 'url_video_group',
        'type'        => 'group',
        'description' => __('Aggiungi uno o più video alla galleria', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Video {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi video', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi video', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));
    
    // Titolo del documento
    $cmb_video->add_group_field($prefix . 'url_video_group', array(
        'name' => __('Titolo del video: ', 'design_comuni_italia'),
        'id'   => 'titolo',
        'type' => 'text',
    ));

    // URL del documento
    $cmb_video->add_group_field($prefix . 'url_video_group', array(
        'name' => __('URL del video (youtube):', 'design_comuni_italia'),
        'id'   => 'url_video',
        'type' => 'text_url',
    ));
   
    
    // CAMPO NUOVO - MULTIPLI
    $cmb_video->add_field(array(
        'id' => $prefix . 'video',
        'name' => __('Carica più Video: ', 'design_comuni_italia'),
        'desc' => __('Carica uno o più video, importandoli dal tuo pc.', 'design_comuni_italia'),
        'type' => 'file_list',
        'preview_size' => array(100, 100),
        'query_args' => array( 'type' => 'video' ),
    ));

    


}

/**
 * Aggiungo JS per controllo compilazione campi
 */
add_action('admin_print_scripts-post-new.php', 'dci_galleria_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_galleria_admin_script', 11);
function dci_galleria_admin_script() {
    // wp_enqueue_script('galleria-admin-script', get_template_directory_uri() . '/inc/admin-js/galleria.js');
}

/**
 * Valorizzo il post content in base ai campi custom
 */
function dci_galleria_set_post_content($data) {
    // Logica per valorizzare post_content eventualmente
    return $data;
}
add_filter('wp_insert_post_data', 'dci_galleria_set_post_content', 99, 1);
