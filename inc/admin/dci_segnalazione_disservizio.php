<?php
/**
 * dci_segnalazione_disservizio.php
 * Post type Segnalazione Disservizio per gestione segnalazioni cittadini
 */

/**
 * Registrazione CPT Segnalazioni Disservizio
 */
add_action( 'init', 'dci_register_post_type_segnala_disservizio', 100 );
function dci_register_post_type_segnala_disservizio() {

    $labels = array(
        'name'               => _x( 'Segnalazioni disservizio', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name'      => _x( 'Segnalazione disservizio', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'            => _x( 'Aggiungi una segnalazione', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'       => _x( 'Aggiungi una nuova segnalazione', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'          => _x( 'Dettagli segnalazione disservizio', 'Post Type Singular Name', 'design_comuni_italia' ),
        'view_item'          => _x( 'Visualizza la segnalazione', 'Post Type Singular Name', 'design_comuni_italia' ),
        'all_items'          => _x( 'Tutte le segnalazioni disservizio', 'Post Type General Name', 'design_comuni_italia' ),
    );

    $args = array(
        'label'              => __( 'Segnalazioni disservizio', 'design_comuni_italia' ),
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-warning',
        'has_archive'        => false,
        'capability_type'    => 'post',
        'capabilities'       => array(
            'create_posts' => 'do_not_allow'
        ),
        'map_meta_cap'       => true,
        'hierarchical'       => false,
    );

    register_post_type( 'segnala_disservizio', $args );

    remove_post_type_support( 'segnala_disservizio', 'title');
    remove_post_type_support( 'segnala_disservizio', 'editor');
}

/**
 * Mostra titolo sotto edit form
 */
add_action( 'edit_form_after_title', 'dci_segnala_disservizio_add_content_after_title' );
function dci_segnala_disservizio_add_content_after_title($post) {
    if($post->post_type == "segnala_disservizio") {
        $post_title = get_the_title($post->ID);
        echo '<h1>' . esc_html($post_title) . '</h1>';
    }
}

/**
 * Metabox readonly segnalazioni disservizio.
 */
add_action( 'cmb2_init', 'dci_add_segnala_disservizio_metaboxes' );
function dci_add_segnala_disservizio_metaboxes() {
    $prefix = '_dci_segnala_disservizio_';

    $cmb_richiedente = new_cmb2_box(array(
        'id' => $prefix . 'box_richiedente',
        'title' => __('Richiedente'),
        'object_types' => array('segnala_disservizio'),
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
    $cmb_richiedente->add_field(array(
        'id' => $prefix . 'telefono',
        'name' => __('Telefono', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));

    $cmb_disservizio = new_cmb2_box(array(
        'id' => $prefix . 'box_disservizio',
        'title' => __('Segnalazione'),
        'object_types' => array('segnala_disservizio'),
        'context' => 'normal',
        'priority' => 'high',
    ));
    $cmb_disservizio->add_field(array(
        'id' => $prefix . 'tipologia',
        'name' => __('Tipologia di disservizio', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_disservizio->add_field(array(
        'id' => $prefix . 'luogo',
        'name' => __('Luogo', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_disservizio->add_field(array(
        'id' => $prefix . 'riferimento_luogo',
        'name' => __('Indirizzo o riferimento', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_disservizio->add_field(array(
        'id' => $prefix . 'motivo',
        'name' => __('Motivo', 'design_comuni_italia'),
        'type' => 'text',
        'attributes' => array('readonly' => true)
    ));
    $cmb_disservizio->add_field(array(
        'id' => $prefix . 'dettagli',
        'name' => __('Dettagli', 'design_comuni_italia'),
        'type' => 'textarea',
        'attributes' => array('readonly' => true)
    ));
}

add_filter( 'manage_segnala_disservizio_posts_columns', 'dci_filter_segnala_disservizio_columns' );
function dci_filter_segnala_disservizio_columns($columns) {
    $columns['richiedente'] = __('Richiedente','design_comuni_italia');
    $columns['email'] = __('Email','design_comuni_italia');
    $columns['tipologia'] = __('Tipologia','design_comuni_italia');
    $columns['luogo'] = __('Luogo','design_comuni_italia');
    $columns['motivo'] = __('Motivo','design_comuni_italia');
    return $columns;
}

add_action( 'manage_segnala_disservizio_posts_custom_column', 'dci_manage_segnala_disservizio_posts_custom_column', 10, 2 );
function dci_manage_segnala_disservizio_posts_custom_column($column, $post_id) {
    $prefix = '_dci_segnala_disservizio_';
    switch($column){
        case 'richiedente':
            $nome = get_post_meta($post_id, $prefix.'nome', true);
            $cognome = get_post_meta($post_id, $prefix.'cognome', true);
            echo esc_html($cognome.' '.$nome);
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, $prefix.'email', true));
            break;
        case 'tipologia':
            echo esc_html(get_post_meta($post_id, $prefix.'tipologia', true));
            break;
        case 'luogo':
            echo esc_html(get_post_meta($post_id, $prefix.'luogo', true));
            break;
        case 'motivo':
            echo esc_html(get_post_meta($post_id, $prefix.'motivo', true));
            break;
    }
}

add_filter( 'manage_edit-segnala_disservizio_sortable_columns', 'dci_segnala_disservizio_sortable_columns');
function dci_segnala_disservizio_sortable_columns($columns){
    $columns['richiedente'] = 'segnala_disservizio_richiedente';
    $columns['email'] = 'segnala_disservizio_email';
    $columns['tipologia'] = 'segnala_disservizio_tipologia';
    $columns['luogo'] = 'segnala_disservizio_luogo';
    return $columns;
}

add_action( 'pre_get_posts', 'dci_segnala_disservizio_posts_orderby' );
function dci_segnala_disservizio_posts_orderby($query){
    if(!is_admin() || !$query->is_main_query()) return;
    if('segnala_disservizio' !== $query->get('post_type')) return;

    $orderby = $query->get('orderby');
    $meta_keys = array(
        'segnala_disservizio_richiedente' => '_dci_segnala_disservizio_cognome',
        'segnala_disservizio_email' => '_dci_segnala_disservizio_email',
        'segnala_disservizio_tipologia' => '_dci_segnala_disservizio_tipologia',
        'segnala_disservizio_luogo' => '_dci_segnala_disservizio_luogo',
    );

    if($orderby && isset($meta_keys[$orderby])){
        $query->set('meta_key', $meta_keys[$orderby]);
        $query->set('orderby', 'meta_value');
    }
}

add_filter( 'post_row_actions', 'dci_segnala_disservizio_row_actions', 10, 2 );
function dci_segnala_disservizio_row_actions($actions, $post){
    if($post->post_type === 'segnala_disservizio'){
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}

add_action('admin_menu','dci_segnala_disservizio_remove_add_new_menu');
function dci_segnala_disservizio_remove_add_new_menu(){
    remove_submenu_page('edit.php?post_type=segnala_disservizio','post-new.php?post_type=segnala_disservizio');
}

add_action( 'do_meta_boxes', 'dci_segnala_disservizio_remove_publish_mbox', 10, 3 );
function dci_segnala_disservizio_remove_publish_mbox($post_type, $position, $post){
    remove_meta_box('submitdiv','segnala_disservizio','side');
}
