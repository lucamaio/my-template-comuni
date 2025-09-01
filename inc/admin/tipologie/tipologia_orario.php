<?php

/**
 * Definisce post type orario
 */
add_action( 'init', 'dci_register_post_type_orario', 60 );
function dci_register_post_type_orario() {

    $labels = array(
        'name'          => _x( 'Orari', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name' => _x( 'Orario', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'       => _x( 'Aggiungi un orario', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'  => _x( 'Aggiungi un nuovo orario', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'       => _x( "Modifica l'orario", 'Post Type Singular Name', 'design_comuni_italia' ),
        'featured_image' => __( 'Immagine di riferimento', 'design_comuni_italia' ),
    );
    $args   = array(
        'label'         => __( 'orario', 'design_comuni_italia'),
        'labels'        => $labels,
        'supports'      => array( 'title', 'editor', 'author'),
        'hierarchical'  => true,
        'public'        => true,
        'show_in_menu'        => 'edit.php?post_type=unita_organizzativa',
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-clock',
        'has_archive'   => true,
        'map_meta_cap'    => true,
    );
    register_post_type('orario', $args );

    remove_post_type_support( 'orario', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_orario_add_content_after_title' );
function dci_orario_add_content_after_title($post) {
    if($post->post_type == "orario")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo della orario</b>.</i></span><br><br>', 'design_comuni_italia' );
}

add_action( 'cmb2_init', 'dci_add_orario_metaboxes' );
function dci_add_orario_metaboxes() {
    $prefix = '_dci_orario_';

    // Metabox per date inizio/fine
    $cmb_dati = new_cmb2_box( array(
        'id'           => $prefix . 'box_dati',
        'title'        => __( 'Periodo di validità', 'design_comuni_italia' ),
        'object_types' => array( 'orario' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

     $cmb_dati->add_field(array(
        'id' => $prefix . 'descrizione',
        'name'        => __('Descrizione:', 'design_comuni_italia'),
        'desc' => __('Descrizione sintentica del orario di apartura, inferiore a 255 caratteri', 'design_comuni_italia'),
        'type' => 'textarea',
        'attributes'    => array( 'maxlength'  => '255' ),
    ));

    $cmb_dati->add_field( array(
        'id'         => $prefix . 'data_inizio',
        'name'       => __('Data Inizio*', 'design_comuni_italia' ),
        'desc'       => __('Inserisci la data di inizio validità', 'design_comuni_italia' ),
        'type'       => 'text_date',
        'date_format' => 'd-m-Y',
        'attributes' => array( 'required' => true )
    ) );

    $cmb_dati->add_field( array(
        'id'         => $prefix . 'data_fine',
        'name'       => __('Data Fine*', 'design_comuni_italia' ),
        'desc'       => __('Inserisci la data di fine validità', 'design_comuni_italia' ),
        'type'       => 'text_date',
        'date_format' => 'd-m-Y',
        'attributes' => array( 'required' => true )
    ) );

    // Metabox per orari settimanali
    $cmb_orari = new_cmb2_box( array(
        'id'           => $prefix . 'box_orari',
        'title'        => __( 'Orari di apertura settimanale', 'design_comuni_italia' ),
        'object_types' => array( 'orario' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $giorni_settimana = array(
        'lun' => 'Lunedì',
        'mar' => 'Martedì',
        'mer' => 'Mercoledì',
        'gio' => 'Giovedì',
        'ven' => 'Venerdì',
        'sab' => 'Sabato',
        'dom' => 'Domenica'
    );

    foreach ( $giorni_settimana as $abbr => $nome_giorno ) {

        $cmb_orari->add_field( array(
            'name' => '⏱ <span style="color: green;">' . $nome_giorno.'</span>',
            'id'   => $prefix . $abbr . '_titolo',
            'type' => 'title',
            'desc' => 'Inserisci gli orari di apertura del ' . strtolower($nome_giorno),
        ) );

        $cmb_orari->add_field( array(
            'name' => 'Mattina',
            'id'   => $prefix . $abbr . '_mattina',
            'type' => 'text',
            'description' => __('Es. 08:30 - 12:30', 'design_comuni_italia'),
            'attributes' => array( 'placeholder' => '08:30 - 12:30' )
        ) );

        $cmb_orari->add_field( array(
            'name' => 'Pomeriggio',
            'id'   => $prefix . $abbr . '_pomeriggio',
            'type' => 'text',
            'description' => __('Es. 15:00 - 19:00', 'design_comuni_italia'),
            'attributes' => array( 'placeholder' => '15:00 - 19:00' )
        ) );
    }
}