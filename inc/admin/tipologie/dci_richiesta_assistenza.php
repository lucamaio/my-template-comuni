<?php
/**
 * dci_richiesta_assistenza.php
 * Post type Richiesta Assistenza per gestione ticket utenti
 */

/**
 * Registrazione CPT Richiesta Assistenza
 */
add_action( 'init', 'dci_register_post_type_richiesta_assistenza', 100 );
function dci_register_post_type_richiesta_assistenza() {

    $labels = array(
        'name'               => _x( 'Tickets', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name'      => _x( 'Ticket', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'            => _x( 'Aggiungi un Ticket', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'       => _x( 'Aggiungi un nuovo Ticket', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'          => _x( 'Dettagli Ticket', 'Post Type Singular Name', 'design_comuni_italia' ),
        'view_item'          => _x( 'Visualizza il Ticket', 'Post Type Singular Name', 'design_comuni_italia' ),
        'all_items'          => _x( 'Tutti i Ticket', 'Post Type General Name', 'design_comuni_italia' ),
    );

    $args = array(
        'label'              => __( 'Richiesta Assistenza', 'design_comuni_italia' ),
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-media-spreadsheet',
        'has_archive'        => false,
        'capability_type'    => array('richiesta_assistenza', 'richieste_assistenza'),
        'capabilities'       => array(
            'create_posts' => 'do_not_allow'
        ),
        'map_meta_cap'       => true,
        'hierarchical'       => false,
    );

    register_post_type( 'richiesta_assistenza', $args );

    remove_post_type_support( 'richiesta_assistenza', 'title');
    remove_post_type_support( 'richiesta_assistenza', 'editor');
}

/**
 * Mostra titolo sotto edit form
 */
add_action( 'edit_form_after_title', 'dci_richiesta_assistenza_add_content_after_title' );
function dci_richiesta_assistenza_add_content_after_title($post) {
    if($post->post_type == "richiesta_assistenza") {
        $post_title = get_the_title($post->ID);
        echo '<h1>' . esc_html($post_title) . '</h1>';
    }
}

/**
 * Metabox readonly
 */
add_action( 'cmb2_init', 'dci_add_richiesta_assistenza_metaboxes' );
function dci_add_richiesta_assistenza_metaboxes() {
    $prefix = '_dci_richiesta_assistenza_';

    // Richiedente
    $cmb_richiedente = new_cmb2_box(array(
        'id' => $prefix . 'box_richiedente',
        'title' => __('Richiedente'),
        'object_types' => array('richiesta_assistenza'),
        'context' => 'normal',
        'priority' => 'high',
    ));
    $cmb_richiedente->add_field(array(
        'id' => $prefix . 'nome',
        'name' => __('Nome', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_richiedente->add_field(array(
        'id' => $prefix . 'cognome',
        'name' => __('Cognome', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_richiedente->add_field(array(
        'id' => $prefix . 'email',
        'name' => __('Email', 'design_comuni_italia'),
        'type' => 'text_email',
        'attributes' => array('readonly' => true)
    ));

    // Richiesta
    $cmb_richiesta = new_cmb2_box(array(
        'id' => $prefix . 'box_richiesta',
        'title' => __('Richiesta'),
        'object_types' => array('richiesta_assistenza'),
        'context' => 'normal',
        'priority' => 'high',
    ));
    $cmb_richiesta->add_field(array(
        'id' => $prefix . 'categoria_servizio',
        'name' => __('Categoria', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_richiesta->add_field(array(
        'id' => $prefix . 'servizio',
        'name' => __('Servizio', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_richiesta->add_field(array(
        'id' => $prefix . 'dettagli',
        'name' => __('Dettagli', 'design_comuni_italia'),
        'type' => 'textarea',
        'attributes' => array('readonly' => true)
    ));
}

/**
 * Colonne custom in admin
 */
add_filter( 'manage_richiesta_assistenza_posts_columns', 'dci_filter_richiesta_assistenza_columns' );
function dci_filter_richiesta_assistenza_columns($columns) {
    $columns['richiedente'] = __('Richiedente','design_comuni_italia');
    $columns['email'] = __('Email','design_comuni_italia');
    $columns['categoria_servizio'] = __('Categoria','design_comuni_italia');
    $columns['servizio'] = __('Servizio','design_comuni_italia');
    $columns['dettagli'] = __('Dettagli','design_comuni_italia');
    return $columns;
}

add_action( 'manage_richiesta_assistenza_posts_custom_column', 'dci_manage_richiesta_assistenza_posts_custom_column', 10, 2 );
function dci_manage_richiesta_assistenza_posts_custom_column($column, $post_id) {
    $prefix = '_dci_richiesta_assistenza_';
    switch($column){
        case 'richiedente':
            $nome = get_post_meta($post_id, $prefix.'nome', true);
            $cognome = get_post_meta($post_id, $prefix.'cognome', true);
            echo esc_html($cognome.' '.$nome);
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, $prefix.'email', true));
            break;
        case 'categoria_servizio':
            echo esc_html(get_post_meta($post_id, $prefix.'categoria_servizio', true));
            break;
        case 'servizio':
            echo esc_html(get_post_meta($post_id, $prefix.'servizio', true));
            break;
        case 'dettagli':
            echo esc_html(get_post_meta($post_id, $prefix.'dettagli', true));
            break;
    }
}

/**
 * Ordina colonne admin
 */
add_filter( 'manage_edit-richiesta_assistenza_sortable_columns', 'dci_richiesta_assistenza_sortable_columns');
function dci_richiesta_assistenza_sortable_columns($columns){
    $columns['richiedente'] = 'richiesta_assistenza_richiedente';
    $columns['email'] = 'richiesta_assistenza_email';
    $columns['categoria_servizio'] = 'richiesta_assistenza_categoria_servizio';
    $columns['servizio'] = 'richiesta_assistenza_servizio';
    return $columns;
}

add_action( 'pre_get_posts', 'dci_richiesta_assistenza_posts_orderby' );
function dci_richiesta_assistenza_posts_orderby($query){
    if(!is_admin() || !$query->is_main_query()) return;

    $orderby = $query->get('orderby');

    $meta_keys = array(
        'richiesta_assistenza_richiedente' => array('_dci_richiesta_assistenza_cognome','_dci_richiesta_assistenza_nome'),
        'richiesta_assistenza_email' => '_dci_richiesta_assistenza_email',
        'richiesta_assistenza_categoria_servizio' => '_dci_richiesta_assistenza_categoria_servizio',
        'richiesta_assistenza_servizio' => '_dci_richiesta_assistenza_servizio',
    );

    if($orderby && isset($meta_keys[$orderby])){
        if(is_array($meta_keys[$orderby])){
            $query->set('meta_key', $meta_keys[$orderby][0]);
            $query->set('orderby', 'meta_value');
        } else {
            $query->set('meta_key', $meta_keys[$orderby]);
            $query->set('orderby', 'meta_value');
        }
    }
}

/**
 * Disabilita quick edit e pubblicazione
 */
add_filter( 'post_row_actions', 'dci_richiesta_assistenza_row_actions', 10, 2 );
function dci_richiesta_assistenza_row_actions($actions, $post){
    if($post->post_type === 'richiesta_assistenza'){
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}

add_action('admin_menu','dci_richiesta_assistenza_remove_add_new_menu');
function dci_richiesta_assistenza_remove_add_new_menu(){
    remove_submenu_page('edit.php?post_type=richiesta_assistenza','post-new.php?post_type=richiesta_assistenza');
}

add_action( 'do_meta_boxes', 'dci_richiesta_assistenza_remove_publish_mbox', 10, 3 );
function dci_richiesta_assistenza_remove_publish_mbox($post_type, $position, $post){
    remove_meta_box('submitdiv','richiesta_assistenza','side');
}
