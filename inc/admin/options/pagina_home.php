<?php
function dci_register_pagina_home_options(){
    $prefix = '';
    /**
     * Opzioni Home
     */
    $args = array(
        'id'           => 'dci_options_home',
        'title'        => esc_html__( 'Home Page', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'homepage',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Home Page', "design_comuni_italia"),	);
    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }
    $home_options = new_cmb2_box( $args );
    // Immagine 
    $home_options->add_field( array(
        'id' => $prefix . 'home_image',
        'name'        => __( 'Sezione Immagine Pagina Home', 'design_comuni_italia' ),
        'desc' => __( 'Configurazione sezione immagine pagina Home.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $home_options->add_field( array(
        'id' => $prefix . 'immagine',
        'name'=> __( 'Immagine', 'design_comuni_italia' ),
        'desc' => __( 'Immagine/ banner (in alto nella pagina)' , 'design_comuni_italia' ),
        'type' => 'file',
        'query_args' => array( 'type' => 'image' ),
    ) );

    $home_options->add_field( array(
        'id' => $prefix . 'schede_evidenziate_title',
        'name'        => __( 'Sezione Schede in Evidenza', 'design_comuni_italia' ),
        'desc' => __( 'Configurazione sezione Schede in Evidenza.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );
    $home_options->add_field( array(
            'name' => __('<h5>Selezione notizia in evidenza</h5>', 'design_comuni_italia'),
            'desc' => __('Seleziona una notizia da mostrare in homepage ', 'design_comuni_italia'),
            'id' => $prefix . 'notizia_evidenziata',
            'type'    => 'custom_attached_posts',
            'column'  => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
            'options' => array(
                'show_thumbnails' => false, // Show thumbnails on the left
                'filter_boxes'    => true, // Show a text box for filtering the results
                'query_args'      => array(
                    'posts_per_page' => -1,
                    'post_type'      => array('notizia'),
                ), // override the get_posts args
            ),
            'attributes' => array(
                'data-max-items' => 1, //change the value here to how many posts may be attached.
            ),
        )
    );




function add_scheda_group($home_options, $prefix, $index) {
    // Recupera il contenuto corrente della scheda
    $scheda_contenuto = get_option($prefix . 'scheda_' . $index . '_contenuto');
    $is_active = is_array($scheda_contenuto) && count($scheda_contenuto) > 0;
    $schede_group_id = $home_options->add_field(array(
        'id'           => $prefix . 'schede_evidenziate_' . $index,
        'type'         => 'group',
        'repeatable'   => false,
        'options'      => array(
            'group_title'   => 'Scheda ' . $index . ':',
            'closed'        => !$is_active, // Chiudi il gruppo se non c'è contenuto attivo
        )
    ));
    $home_options->add_group_field($schede_group_id, array(
        'name'       => __('<h5>Selezione contenuto</h5>', 'design_comuni_italia'),
        'desc'       => __('Seleziona il contenuto da mostrare nella Scheda.', 'design_comuni_italia'),
        'id'         => $prefix . 'scheda_' . $index . '_contenuto',
        'type'       => 'custom_attached_posts',
        'column'     => true, // Output in the admin post-listing as a custom column. https://github.com/CMB2/CMB2/wiki/Field-Parameters#column
        'options'    => array(
            'show_thumbnails' => false, // Show thumbnails on the left
            'filter_boxes'    => true, // Show a text box for filtering the results
            'query_args'      => array(
                'posts_per_page' => -1,
                'post_type'      => array('evento', 'luogo', 'unita_organizzativa', 'documento_pubblico', 'servizio', 'notizia', 'dataset'),
            ), // override the get_posts args
        ),
        'attributes' => array(
            'data-max-items' => 1, //change the value here to how many posts may be attached.
        ),
    ));
}
// Esempio di utilizzo della funzione per creare 12 schede
for ($i = 1; $i <= 12; $i++) {
    add_scheda_group($home_options, $prefix, $i);
}




	
	
    //sezione Siti Tematici
    $home_options->add_field( array(
        'id' => $prefix . 'siti_tematici_title',
        'name'        => __( 'Sezione Siti Tematici', 'design_comuni_italia' ),
        'desc' => __( 'Configurazione sezione Siti Tematici.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );
    $home_options->add_field( array(
        'id' => $prefix . 'siti_tematici',
        'name'        => __( 'Sito Tematico ', 'design_comuni_italia' ),
        'desc' => __( 'Selezionare il sito tematico di cui visualizzare la Card' , 'design_comuni_italia' ),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('sito_tematico'),
        'attributes' => array(
            'data-maximum-selection-length' => '12',
        ),
    ) );


	
// Sezione Argomenti
$home_options->add_field( array(
    'id'   => $prefix . 'argomenti_title',
    'name' => __( 'Sezione Argomenti', 'design_comuni_italia' ),
    'desc' => __( 'Gestione Argomenti mostrati in homepage.', 'design_comuni_italia' ),
    'type' => 'title',
) );

$num_argomenti = 9; // Numero di argomenti

for ($i = 1; $i <= $num_argomenti; $i++) {
    $argomenti_group_id = $home_options->add_field( array(
        'id'          => $prefix . 'argomenti_evidenziati_' . $i,
        'type'        => 'group',
        'repeatable'  => false,
        'options'     => array(
            'group_title' => sprintf( __( 'Argomento %d: ', 'design_comuni_italia' ), $i ),
            'closed'      => true
        ),
    ) );

    $home_options->add_group_field( $argomenti_group_id, array(
        'id'       => $prefix . 'argomento_' . $i . '_argomento',
        'name'     => __( 'Argomento', 'design_comuni_italia' ),
        'desc'     => __( 'Seleziona l\'Argomento', 'design_comuni_italia' ),
        'type'     => 'taxonomy_select',
        'taxonomy' => 'argomenti'
    ) );

    $home_options->add_group_field( $argomenti_group_id, array(
        'id'       => $prefix . 'argomento_' . $i . '_siti_tematici',
        'name'     => __( 'Sito Tematico', 'design_comuni_italia' ),
        'desc'     => __( 'Selezionare il sito tematico da inserire nella Card', 'design_comuni_italia' ),
        'type'     => 'pw_select',
        'options'  => dci_get_posts_options('sito_tematico'),
    ) );

    $home_options->add_group_field( $argomenti_group_id, array(
        'name'     => __('<h5>Selezione contenuti</h5>', 'design_comuni_italia'),
        'desc'     => __('Seleziona i contenuti da mostrare nella Card dell\'Argomento.', 'design_comuni_italia'),
        'id'       => $prefix . 'argomento_' . $i . '_contenuti',
        'type'     => 'custom_attached_posts',
        'column'   => true,
        'options'  => array(
            'show_thumbnails' => false,
            'filter_boxes'    => true,
            'query_args'      => array(
                'posts_per_page' => -1,
                'post_type'      => array('evento', 'luogo', 'unita_organizzativa', 'documento_pubblico', 'servizio', 'notizia'),
            ),
        )
    ) );
}

	
    $home_options->add_field( array(
        'id' => $prefix . 'argomenti_altri',
        'name'        => __( 'Altri argomenti', 'design_comuni_italia' ),
        'desc' => __( 'Seleziona altri Argomenti peri quali compariranno link in homepage.' , 'design_comuni_italia' ),
        'type'             => 'pw_multiselect',
        'options' => dci_get_terms_options('argomenti'),
        'show_option_none' => false,
        'remove_default' => 'true',
    ) );


    }

