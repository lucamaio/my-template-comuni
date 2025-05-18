<?php

function dci_register_pagina_galleria_options(){
    $prefix = '';

 
    $args = array(
        'id'           => 'dci_options_galleria',
        'title'        => esc_html__( 'Galleria', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'Galleria',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Galleria', "design_comuni_italia"),	);
    
    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }
   
    $gallery_options = new_cmb2_box( $args );

    $gallery_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni_g',
        'name'        => __( 'Galleria Fotografica', 'design_comuni_italia' ),
        'desc' => __( 'Da qui puoi gestire la tua Galleria Fotografica' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $gallery_options->add_field( array(
        'id' => $prefix . 'gallery_title',
        'name' => 'Nome galleria',
        'desc' => 'Scegli il titolo da dare alla Galleria.',
        'type' => 'text',
        'default' => 'Le nostre foto'
      ) 
    );

    
     $gallery_options->add_field(array(
        'name' => __('', 'design_comuni_italia'),
        'desc' => __('Seleziona le foto da mostrare.', 'design_comuni_italia'),
        'id' => $prefix . 'gallery_items',
        'type' => 'file_list',
        'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
        'query_args' => array( 'type' => 'image' ), // Only images attachment
    )
  );




    $gallery_options->add_field( array(
        'id' => $prefix . 'mostra_gallery',
        'name' => 'Galleria sulla HOME PAGE',
        'desc' => 'Mostra le immagini caricate sulla Home.',
        'type' => 'checkbox',
    ) ); 


   $gallery_options->add_field( array(
        'id' => $prefix . 'mostra_gallery_vivereilcomune',
        'name' => 'Galleria su VIVERE IL COMUNE',
        'desc' => 'Mostra le immagini caricate su Vivere il Comune.',
        'type' => 'checkbox',
    ) ); 

    
}
