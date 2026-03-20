<?php

function dci_register_strip_home_options(){
    $prefix = '';

    $args = array(
        'id'           => 'dci_strip_home',
        'title'        => esc_html__( 'Strip Home', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'strip_home',
        'capability'   => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Strip Home', "design_comuni_italia"),
    );

    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $cmb = new_cmb2_box( $args );

    // TITOLO SEZIONE
    $cmb->add_field( array(
        'id' => 'strip_home_title',
        'name' => __( 'Configurazione Strip Home', 'design_comuni_italia' ),
        'desc' => __( 'Gestisci i box della striscia inclinata in homepage.', 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    // GRUPPO RIPETIBILE
    $group_id = $cmb->add_field( array(
        'id'          => 'strip_items',
        'type'        => 'group',
        'desc'        => __( 'Aggiungi elementi alla strip', 'design_comuni_italia' ),
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __( 'Elemento {#}', 'design_comuni_italia' ),
            'add_button'    => __( 'Aggiungi elemento', 'design_comuni_italia' ),
            'remove_button' => __( 'Rimuovi elemento', 'design_comuni_italia' ),
            'sortable'      => true,
        ),
    ) );

    // ICONA
    $cmb->add_group_field( $group_id, array(
        'id'   => 'icon',
        'name' => __( 'Icona (FontAwesome)', 'design_comuni_italia' ),
        'desc' => __( 'Es: fa-solid fa-file', 'design_comuni_italia' ),
        'type' => 'text',
    ) );

    // TITOLO
    $cmb->add_group_field( $group_id, array(
        'id'   => 'title',
        'name' => __( 'Titolo', 'design_comuni_italia' ),
        'type' => 'text',
    ) );

    // DESCRIZIONE
    $cmb->add_group_field( $group_id, array(
        'id'   => 'desc',
        'name' => __( 'Descrizione', 'design_comuni_italia' ),
        'type' => 'text',
    ) );

    // URL
    $cmb->add_group_field( $group_id, array(
        'id'   => 'url',
        'name' => __( 'Link', 'design_comuni_italia' ),
        'type' => 'text_url',
    ) );

    // TARGET BLANK
    $cmb->add_group_field( $group_id, array(
        'id'   => 'blank',
        'name' => __( 'Apri in nuova pagina', 'design_comuni_italia' ),
        'type' => 'checkbox',
    ) );
}

add_action( 'cmb2_admin_init', 'dci_register_strip_home_options' );
