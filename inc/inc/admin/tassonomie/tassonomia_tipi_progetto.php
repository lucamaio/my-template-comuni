<?php

/**
 * Definisce la tassonomia Tipi di Progetti
 */
add_action( 'init', 'dci_register_taxonomy_tipi_progetto', -10 );
function dci_register_taxonomy_tipi_progetto() {

    $labels = array(
        'name'              => _x( 'Tipi di Progetto', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo di Progetto', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo di Progetto', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi di Progetti ', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo di Progetto', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo di Progetto', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo di Progetto', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di Progetto', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi di Progetto', 'design_comuni_italia' ),
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
            'manage_terms'  => 'manage_tipi_progetto',
            'edit_terms'    => 'edit_tipi_progetto',
            'delete_terms'  => 'delete_tipi_progetto',
            'assign_terms'  => 'assign_tipi_progetto'
        )
    );

    register_taxonomy( 'tipi_progetto', array( 'progetto' ), $args );
}
