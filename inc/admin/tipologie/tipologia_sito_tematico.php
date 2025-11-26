<?php

/**
 * Definisce post type Sito Tematico
 */
add_action('init', 'dci_register_post_type_sito_tematico', 60);
function dci_register_post_type_sito_tematico()
{

    /** evento **/
    $labels = array(
        'name'                  => _x('Siti Tematici', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'         => _x('Sito Tematico', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'               => _x('Aggiungi un Sito Tematico', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new_item'               => _x('Aggiungi un Sito Tematico', 'Post Type Singular Name', 'design_comuni_italia'),
        'featured_image' => __('Logo Identificativo del Sito Tematico', 'design_comuni_italia'),
        'edit_item'      => _x('Modifica il Sito Tematico', 'Post Type Singular Name', 'design_comuni_italia'),
        'view_item'      => _x('Visualizza il Sito Tematico', 'Post Type Singular Name', 'design_comuni_italia'),
        'set_featured_image' => __('Seleziona Immagine Sito Tematico'),
        'remove_featured_image' => __('Rimuovi Immagine Sito Tematico', 'design_comuni_italia'),
        'use_featured_image' => __('Usa come Immagine Sito Tematico', 'design_comuni_italia'),
    );
    $args = array(
        'label'                 => __('Sito Tematico', 'design_comuni_italia'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-admin-links',
        'has_archive'           => true,
        'capability_type' => array('sito_tematico', 'siti_tematici'),
        'map_meta_cap'    => true,
        'description'    => __("Questa Tipologia descrive la struttura di un Sito Tematico, che può essere fisico, virtuale o digitale", 'design_comuni_italia'),

    );
    register_post_type('sito_tematico', $args);

    remove_post_type_support('sito_tematico', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action('edit_form_after_title', 'dci_sito_tematico_add_content_after_title');
function dci_sito_tematico_add_content_after_title($post)
{
    if ($post->post_type == "sito_tematico")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo del Sito Tematico *</b></i></span><br><br><br> ', 'design_comuni_italia');
}

/**
 * Crea i metabox del post type Sito Tematico
 */
add_action('cmb2_init', 'dci_add_sito_tematico_metaboxes');
function dci_add_sito_tematico_metaboxes()
{
    $prefix = '_dci_sito_tematico_';

    $cmb_dati = new_cmb2_box(array(
        'id'           => $prefix . 'box_dati_card',
        'title'        => __('Card'),
        'object_types' => array('sito_tematico'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));


    $cmb_dati->add_field(array(
        'name'       => __('Sottotitolo/descrizione *', 'design_comuni_italia'),
        'desc'       => __('Esempio: "Il Sistema Museale della città, polo di attrazione cittadina e turistica"', 'design_comuni_italia'),
        'id'         => $prefix . 'descrizione_breve',
        'type'       => 'textarea',
        'attributes'    => array(
            'required'    => 'required',
            'maxlength'  => '255',
        ),
    ));

    $cmb_dati->add_field(array(
        'name'       => __('URL sito tematico *', 'design_comuni_italia'),
        'desc'       => __('URL esterno, link al sito tematico"', 'design_comuni_italia'),
        'id'         => $prefix . 'link',
        'type'       => 'text_url',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ));

    $cmb_dati->add_field(
        array(
            'id'    => $prefix . 'immagine',
            'name' => __('Immagine', 'design_comuni_italia'),
            'desc' => __('Seleziona un\'immagine da mostrare  nella Card del sito tematico', 'design_comuni_italia'),
            'type' => 'file',
            'query_args' => array('type' => 'image'),
        )
    );

    $cmb_dati->add_field(array(
        'name'  => __('Mostra pagina principale', 'design_comuni_italia'),
        'id'    => $prefix . 'mostra_pagina',
        'type'  => 'checkbox',

        // Descrizione posizionata sotto il checkbox
        'after' => '<p class="cmb2-metabox-description" style="margin-top:6px;">' .
            __('Se selezionato, l’utente visualizzerà la pagina principale del sito tematico anziché essere reindirizzato al link esterno.  
        Quando questa opzione è attiva, il campo “Link principale” deve essere lasciato vuoto.', 'design_comuni_italia') .
            '</p>',
    ));



    // Box link multipli

    $cmb_multi_link = new_cmb2_box(
        array(
            'id' => $prefix . 'box_multi_link',
            'title' => __('Link Multipli', 'design_comuni_italia'),
            'object_types' => array('sito_tematico'),
            'context' => 'normal',
            'priority' => 'high',
        )
    );


    // Gruppo per URL multipli
    $cmb_multi_link->add_field(array(
        'id'          => $prefix . 'url_link',
        'type'        => 'group',
        'description' => __('Aggiungi uno o più link', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Link {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi link', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi link', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));

    // URL del collegamento
    $cmb_multi_link->add_group_field($prefix . 'url_link', array(
        'name' => __('URL pagina da collegare', 'design_comuni_italia'),
        'id'   => 'url_link_page',
        'type' => 'text_url',
    ));

    // Titolo del collegamento
    $cmb_multi_link->add_group_field($prefix . 'url_link', array(
        'name' => __('Titolo del link', 'design_comuni_italia'),
        'id'   => 'titolo',
        'type' => 'text',
    ));

    // descrizione del collegamento
    $cmb_multi_link->add_group_field($prefix . 'url_link', array(
        'name' => __('Descrizione del link', 'design_comuni_italia'),
        'id'   => 'descrizione',
        'type' => 'textarea',
        'attributes' => array(
            'rows' => 2,
        ),
        'attributes' => array(
            'maxlength' => '128',
        ),
    ));

    // Checkbox: apri in nuova scheda
    $cmb_multi_link->add_group_field($prefix . 'url_link', array(
        'name' => __('Apri in nuova scheda', 'design_comuni_italia'),
        'id'   => 'target_blank',
        'type' => 'checkbox',
    ));

    // Altre opzioni 

    $cmb_other_option = new_cmb2_box(
        array(
            'id' => $prefix . 'box_other_option',
            'title' => __('Altre Opzioni', 'design_comuni_italia'),
            'object_types' => array('sito_tematico'),
            'context' => 'normal',
            'priority' => 'high',
        )
    );

    $cmb_other_option->add_field(array(
        'id' => $prefix . 'descrizione_completa',
        'name'        => __('Descrizione completa', 'design_comuni_italia'),
        'desc' => __('Descrizione opzionale che verrà mostrata nella pagina del sito tematico se viene selezionata l\'opzione "Mostra link principale"', 'design_comuni_italia'),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, // output the minimal editor config used in Press This
        ),
    ));

    $cmb_other_option->add_field(array(
        'name'  => __('Mostra Immagine nella pagina principale', 'design_comuni_italia'),
        'id'    => $prefix . 'mostra_immagine',
        'type'  => 'checkbox',
        'desc'  => __('Se selezionato, l\'immagine del sito tematico sarà mostrata nella pagina principale. Funziona solo se l\'opzione "Mostra pagina principale" è abilitata.', 'design_comuni_italia'),
        'attributes' => array(
            'style' => 'margin-bottom: 6px;',
        ),
    ));
}

/**
 * Script inline per rendere obbligatorio il campo URL se la checkbox non è selezionata
 */
add_action('admin_print_footer_scripts', 'dci_sito_tematico_inline_script');
function dci_sito_tematico_inline_script()
{

    global $post;

    // Mostra lo script solo nel CPT corretto
    if (!isset($post) || $post->post_type !== 'sito_tematico') {
        return;
    }
?>

    <script>
        jQuery(document).ready(function($) {

            function toggleUrlRequired() {
                const checkbox = $('#_dci_sito_tematico_mostra_pagina');
                const urlField = $('#_dci_sito_tematico_link');

                if (checkbox.length && urlField.length) {
                    if (checkbox.is(':checked')) {
                        // Checkbox attiva → campo NON obbligatorio
                        urlField.removeAttr('required');
                    } else {
                        // Checkbox non attiva → campo OBBLIGATORIO
                        urlField.attr('required', 'required');
                    }
                }
            }

            // Controllo al caricamento
            toggleUrlRequired();

            // Controllo quando cambia il valore della checkbox
            $(document).on('change', '#_dci_sito_tematico_mostra_link', function() {
                toggleUrlRequired();
            });

        });
    </script>

<?php
}
?>