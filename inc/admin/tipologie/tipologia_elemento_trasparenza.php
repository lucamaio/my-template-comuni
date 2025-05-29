<?php
/**
 * Definisce post type Elemento Trasparenza
 */
add_action('init', 'dci_register_post_type_elemento_trasparenza');
function dci_register_post_type_elemento_trasparenza()
{
    $labels = array(
        'name'               => _x('Amministrazione Trasparente', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Amministrazione Trasparente', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'),
        'add_new_item'       => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'),
        'edit_item'          => _x('Modifica l\'Elemento Trasparenza', 'design_comuni_italia'),
    );

    $args = array(
        'label'             => __('Elemento Trasparenza', 'design_comuni_italia'),
        'labels'            => $labels,
        'supports'          => array('title', 'editor', 'thumbnail'),
        'taxonomies'        => array('tipologia'),
        'hierarchical'      => false,
        'public'            => true,
        'menu_position'     => 5,
        'menu_icon'         => 'dashicons-archive',
        'has_archive'       => false,
       //capability_type'   => array('elemento_trasparenza'),
        'map_meta_cap'      => true,
        'description'       => __('Struttura delle informazioni relative utili a presentare un Elemento Trasparenza', 'design_comuni_italia'),
    );

    register_post_type('elemento_trasparenza', $args);

    remove_post_type_support('elemento_trasparenza', 'editor');
}

add_action('edit_form_after_title', 'dci_elemento_trasparenza_add_content_after_title');
function dci_elemento_trasparenza_add_content_after_title($post)
{
    if ($post->post_type === 'elemento_trasparenza') {
        echo "<span><i>Il <b>Titolo</b> è il <b>Nome del elemento dell'amministrazione trasparente</b>.</i></span><br><br>";
    }
}

add_action('cmb2_init', 'dci_add_elemento_trasparenza_metaboxes');
function dci_add_elemento_trasparenza_metaboxes()
{
    $prefix = '_dci_elemento_trasparenza_';

    $cmb_apertura = new_cmb2_box(array(
        'id'            => $prefix . 'box_apertura',
        'title'         => __('Apertura', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_apertura->add_field(array(
    'id'                => $prefix . 'tipo_cat_amm_trasp',
    'name'              => __('Categoria Trasparenza *', 'design_comuni_italia'),
    'desc'              => __('Selezionare una categoria per determinare la sezione dell’Amministrazione Trasparente in cui verrà posizionato l’elemento o il link.', 'design_comuni_italia'),
    'type'              => 'taxonomy_radio_hierarchical',
    'taxonomy'          => 'tipi_cat_amm_trasp',
    'show_option_none'  => false,
    'remove_default'    => true,
));


    $cmb_apertura->add_field(array(
        'id'            => $prefix . 'descrizione_breve',
        'name'          => __('Descrizione breve ', 'design_comuni_italia'),
        'desc'          => __('Indicare una sintetica descrizione (max 255 caratteri spazi inclusi)', 'design_comuni_italia'),
        'type'          => 'textarea',
        'attributes'    => array(
            'maxlength' => '255',
        ),
    ));

    $cmb_documento = new_cmb2_box(array(
        'id'            => $prefix . 'box_documento',
        'title'         => __('Documento/Link *', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_documento->add_field(array(
        'id'        => $prefix . 'url',
        'name'      => __('URL', 'design_comuni_italia'),
        'desc'      => __('Link ad una pagina interna o esterna al sito', 'design_comuni_italia'),
        'type'      => 'text_url',
    ));

    $cmb_documento->add_field(array(
        'id'        => $prefix . 'file',
        'name'      => __('Documento: Carica file', 'design_comuni_italia'),
        'desc'      => __('Caricare il file del documento se non è disponibile un link esterno', 'design_comuni_italia'),
        'type'      => 'file',
        'text'      => array(
            'add_upload_files_text' => __('Aggiungi un nuovo allegato', 'design_comuni_italia'),
            'remove_image_text'     => __('Rimuovi allegato', 'design_comuni_italia'),
            'remove_text'           => __('Rimuovi', 'design_comuni_italia'),
        ),
    ));
    
     $cmb_extra = new_cmb2_box(array(
        'id'            => $prefix . 'box_extra',
        'title'         => __('Extra', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'side',
        'priority'      => 'high',
    ));


    $cmb_extra->add_field(array(
    'id'      => $prefix . 'open_in_new_tab',
    'name'    => __('Apri in una nuova finestra', 'design_comuni_italia'),
    'desc'    => __('Spuntare per aprire il documento in una nuova finestra del browser', 'design_comuni_italia'),
    'type'    => 'checkbox',
));
  $cmb_extra->add_field(array(
    'id'      => $prefix . 'open_direct',
    'name'    => __('Apri link in modo diretto', 'design_comuni_italia'),
    'desc'    => __('Link diretto al link senza visualizzare alcuna pagina intermedia', 'design_comuni_italia'),
    'type'    => 'checkbox',
));


}

add_action('admin_print_scripts-post-new.php', 'dci_elemento_trasparenza_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_elemento_trasparenza_admin_script', 11);
function dci_elemento_trasparenza_admin_script()
{
    global $post_type;
    if ($post_type === 'elemento_trasparenza') {
        wp_enqueue_script('elemento-trasparenza-admin-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js');
    }
}

add_filter('wp_insert_post_data', 'dci_elemento_trasparenza_set_post_content', 99, 1);
function dci_elemento_trasparenza_set_post_content($data)
{
    if ($data['post_type'] === 'elemento_trasparenza') {
        // personalizzazione futura del content
    }
    return $data;
}
