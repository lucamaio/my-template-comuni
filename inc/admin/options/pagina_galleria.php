<?php

function dci_register_pagina_galleria_options(){
    $prefix = '';

    $args = array(
        'id'           => 'dci_options_galleria',
        'title'        => esc_html__( 'Galleria', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'Galleria',
        'capability'   => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Galleria', "design_comuni_italia"),
    );
    
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }
   
    $gallery_options = new_cmb2_box( $args );

    // 1. Sezione principale galleria fotografica
    $gallery_options->add_field( array(
        'id'    => $prefix . 'messages_istruzioni_g',
        'name'  => __( 'Galleria Fotografica', 'design_comuni_italia' ),
        'desc'  => __( 'Gestisci qui le tue immagini e impostazioni principali della galleria fotografica.', 'design_comuni_italia' ),
        'type'  => 'title',
    ) );

    // Scelta stile visualizzazione
    $gallery_options->add_field(array(
        'id'      => $prefix . 'stile_galleria',
        'name'    => __('Stile visualizzazione galleria', 'design_comuni_italia'),
        'desc'    => __("Scegli il tipo di visualizzazione della galleria. 
                        'Solo foto' mostra tutte le immagini in sequenza.
                        'Foto gallery' permette di selezionare specifiche gallerie da mostrare nella homepage.", 'design_comuni_italia'),
        'type'    => 'radio_inline',
        'default' => 'foto-gallery',
        'options' => array(
            'solo-foto'    => __('Solo foto', 'design_comuni_italia'),
            'foto-gallery' => __('Foto gallery', 'design_comuni_italia'),
        ),
    ));

    $stile_galleria = dci_get_option('stile_galleria','galleria') ?: null;
    if($stile_galleria === 'solo-foto' || $stile_galleria === 'Solo foto'){
        // 2. Selezione immagini
        $gallery_options->add_field( array(
            'id'    => $prefix . 'immagini_section',
            'name'  => __( 'Selezione Immagini', 'design_comuni_italia' ),
            'desc'  => __( 'Inserimento delle immagini da visualizzare nella galleria', 'design_comuni_italia' ),
            'type'  => 'title',
        ) );

        // Titolo galleria
        $gallery_options->add_field( array(
            'id'      => $prefix . 'gallery_title',
            'name'    => 'Nome della galleria',
            'desc'    => 'Inserisci il titolo principale da mostrare per la galleria.',
            'type'    => 'text',
            'default' => 'Le nostre foto'
        ));
        
        $gallery_options->add_field(array(
            'name'         => __('Selezione immagini', 'design_comuni_italia'),
            'desc'         => __('Scegli le immagini da visualizzare nella galleria.', 'design_comuni_italia'),
            'id'           => $prefix . 'gallery_items',
            'type'         => 'file_list',
            'preview_size' => array(100, 100),
            'query_args'   => array('type' => 'image'),
        ));
    }

     if($stile_galleria === 'foto-gallery' || $stile_galleria === 'Foto gallery' || $stile_galleria == null){
            //3. Sezione nuova gallery-foto
            $gallery_options->add_field( array(
                'id'    => $prefix . 'gallery_foto_section',
                'name'  => __( 'Selezione delle gallerie', 'design_comuni_italia' ),
                'desc'  => __( 'Gestisci qui quali gallerie saranno evidenziate per la visualizzazione nella homepage.', 'design_comuni_italia' ),
                'type'  => 'title',
            ));

            $gallery_options->add_field( array(
                'name'       => __('<h5>Gallerie evidenziate</h5>', 'design_comuni_italia'),
                'desc'       => __('Seleziona le gallerie da mostrare nella homepage.', 'design_comuni_italia'),
                'id'         => $prefix . 'gallerie_evidenziate',
                'type'       => 'custom_attached_posts',
                'column'     => true,
                'options'    => array(
                    'show_thumbnails' => false, 
                    'filter_boxes'    => true, 
                    'query_args'      => array(
                        'posts_per_page' => -1,
                        'post_type'      => array('galleria'),
                    ),
                ),
                'attributes' => array(
                    'data-max-items' => 6,
                ),
            ));
     }

    // 4. Sezione check
    $gallery_options->add_field( array(
        'id'    => $prefix . 'chech_section',
        'name'  => __( 'Sezione Visualizzazione', 'design_comuni_italia' ),
        'desc'  => __( 'Gestisci dove e se vissualizzare una galleria fotografica', 'design_comuni_italia' ),
        'type'  => 'title',
    ));

    $gallery_options->add_field( array(
        'id'   => $prefix . 'mostra_gallery',
        'name' => 'Galleria sulla Home Page',
        'desc' => 'Abilita per mostrare le immagini della galleria nella home page.',
        'type' => 'checkbox',
    ));

    $gallery_options->add_field( array(
        'id'   => $prefix . 'mostra_gallery_vivereilcomune',
        'name' => 'Galleria su Vivere il Comune',
        'desc' => 'Abilita per mostrare le immagini della galleria nella sezione "Vivere il Comune".',
        'type' => 'checkbox',
    ));
}