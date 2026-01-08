<?php

function dci_register_pagina_multimedia_options(){
    $prefix = '';

       /**
     * Registers options page "Alerts".
     */

    $args = array(
        'id'           => 'dci_options_multimedia',
        'title'        => esc_html__( 'Multimedia', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'multimedia',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Multimedia', "design_comuni_italia"),	);

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $media_options = new_cmb2_box( $args );

    $media_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni_g',
        'name'        => __( 'Galleria Multimediale', 'design_comuni_italia' ),
        'desc' => __( 'Da qui puoi gestire la tua Galleria Multimediale' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $media_options->add_field( array(
        'id' => $prefix . 'multimedia_title',
        'name' => 'Nome galleria',
        'desc' => 'Scegli il titolo da dare alla Galleria Multimediale.',
        'type' => 'text',
        'default' => 'Esplora i video del Comune'
      ) 
    );

    $media_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni',
        'name'        => __( 'Box in evidenza su Multimedia', 'design_comuni_italia' ),
        'desc' => __( 'Inserisci i video che verrano visualizzati nella pagina multimedia.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $media_group_id = $media_options->add_field( array(
        'id'           => $prefix . 'quickboxes',
        'type'        => 'group',
        'desc' => __( 'Inserisci il nome e il link' , 'design_comuni_italia' ),
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __( 'Box {#}', 'design_comuni_italia' ),
            'add_button'    => __( 'Aggiungi un video', 'design_comuni_italia' ),
            'remove_button' => __( 'Rimuovi il video', 'design_comuni_italia' ),
            'sortable'      => true,  // Allow changing the order of repeated groups.
        ),
    ) );


    $media_options->add_group_field( $media_group_id, array(
        'id' => $prefix . 'titolo_video',
        'name'        => __( 'Titolo', 'design_comuni_italia' ),
        'desc' => __( 'Massimo 100 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea_small',
        'attributes'    => array(
            'rows'  => 1,
            'maxlength'  => '100',
        ),
    ) );

    $media_options->add_group_field( $media_group_id, array(
        'id' => $prefix . 'link_video',
        'name'        => __( 'Indirizzo video youtube', 'design_comuni_italia' ),
        'desc' => __( 'Link al video Youtube' , 'design_comuni_italia' ),
        'type' => 'text_url',
    ) );

     $media_options->add_group_field( $media_group_id, array(
        'name' => __('Video: ', 'design_comuni_italia'),
        'desc' => __('Carica un video da mostrare se non è presente su youtube.', 'design_comuni_italia'),
        'id' => $prefix . 'video_item',
        'type' => 'file',
        'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
        'query_args' => array( 'type' => 'video' ),
    )
    );

}

