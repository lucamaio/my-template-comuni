<?php

/**
 * Definisce la tassonomia Tipi di galleria
 */
add_action( 'init', 'dci_register_taxonomy_tipi_galleria', 60);
function dci_register_taxonomy_tipi_galleria() {

    $labels = array(
        'name'              => _x( 'Tipi di Galleria', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo di Galleria', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo di Galleria', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi di Galleria ', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo di Galleria', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo di Galleria', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo di Galleria', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di Galleria', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi di Galleria', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true, //enable to get term archive page
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => true,    //archive page
        //'rewrite'           => array( 'slug' => 'novita' ),
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_galleria',
            'edit_terms'    => 'edit_tipi_galleria',
            'delete_terms'  => 'delete_tipi_galleria',
            'assign_terms'  => 'assign_tipi_galleria'
        )
    );

    register_taxonomy( 'tipi_galleria', array( 'galleria' ), $args );
}