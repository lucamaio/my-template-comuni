<?php

/**
 * Definisce la tassonomia Tipi procedura contraente
 */
add_action( 'init', 'dci_register_taxonomy_tipi_procedura_contraente', -10 );
function dci_register_taxonomy_tipi_procedura_contraente() {

    $labels = array(
        'name'              => _x( 'Tipi procedura contraente', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo  procedura contraente', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo procedura contraente', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi procedura contraente ', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo procedura contraente', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo procedura contraente', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo procedura contraente', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo  procedura contraente', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi  procedura contraente', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true, 
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => false,       
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_procedura_contraente',
            'edit_terms'    => 'edit_tipi_procedura_contraente',
            'delete_terms'  => 'delete_tipi_procedura_contraente',
            'assign_terms'  => 'assign_tipi_procedura_contraente'
        )
    );

    register_taxonomy( 'tipi_procedura_contraente', array( 'bando' ), $args );
}
