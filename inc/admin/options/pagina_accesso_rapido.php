<?php

function dci_register_pagina_accessorapido_options(){
    $prefix = '';

       /**
     * Registers options page "Alerts".
     */

    $args = array(
        'id'           => 'dci_quick_messages',
        'title'        => esc_html__( 'Accesso rapido', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'accesso_rapido',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Accesso rapido', "design_comuni_italia"),	);

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $boxes_options = new_cmb2_box( $args );

    $boxes_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni',
        'name'        => __( 'Box in evidenza su Accesso Rapido', 'design_comuni_italia' ),
        'desc' => __( 'Inserisci i box che verrano visualizzati nella home page.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $boxes_group_id = $boxes_options->add_field( array(
        'id'           => $prefix . 'quickboxes',
        'type'        => 'group',
        'desc' => __( 'Inserisci il nome, il link e icona.' , 'design_comuni_italia' ),
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __( 'Box {#}', 'design_comuni_italia' ),
            'add_button'    => __( 'Aggiungi un box', 'design_comuni_italia' ),
            'remove_button' => __( 'Rimuovi il box', 'design_comuni_italia' ),
            'sortable'      => true,  // Allow changing the order of repeated groups.
        ),
    ) );

    
    $boxes_options->add_group_field( $boxes_group_id, array(
        'name' => 'Visualizza icona',
        'id'   => 'icona_message',
        'type' => 'checkbox',
    ) );

    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'titolo_message',
        'name'        => __( 'Titolo', 'design_comuni_italia' ),
        'desc' => __( 'Massimo 100 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea_small',
        'attributes'    => array(
            'rows'  => 1,
            'maxlength'  => '100',
        ),
    ) );

    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'desc_message',
        'name'        => __( 'Descrizione', 'design_comuni_italia' ),
        'desc' => __( 'Massimo 160 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea_small',
        'attributes'    => array(
            'rows'  => 1,
            'maxlength'  => '160',
        ),
    ) );

    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'icon',
        'name'        => __( 'Icona', 'design_comuni_italia' ),
        'desc' => __( 'Icona da visualizzare accanto al box (fa-icon), prendila da <a href="https://fontawesome.com/search?m=free&o=r" target="_blank">https://fontawesome.com/search?m=free&o=r</a>' , 'design_comuni_italia' ),
        'type' => 'text',
    ) );

    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'link_message',
        'name'        => __( 'Collegamento', 'design_comuni_italia' ),
        'desc' => __( 'Link al una pagina di approfondimento anche esterna al sito' , 'design_comuni_italia' ),
        'type' => 'text_url',
    ) );

}