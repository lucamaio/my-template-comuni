<?php

/**
 * Definisce la tassonomia Tipi Commissario
 */
add_action( 'init', 'dci_register_taxonomy_tipi_commissario', -10 );
function dci_register_taxonomy_tipi_commissario() {

    $labels = array(
        'name'              => _x( 'Tipi di documento OSL', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo di documento OSL', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo di Documento OSL', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi di Documento OSL', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo di OSL', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo di OSL', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo di Documento OSL', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di Documento OSL', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi di Documento OSL', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => true, 
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_commissario',
            'edit_terms'    => 'edit_tipi_commissario',
            'delete_terms'  => 'delete_tipi_commissario',
            'assign_terms'  => 'assign_tipi_commissario'
        )
    );

    register_taxonomy( 'tipi_commissario', array( 'commissario' ), $args );
}

